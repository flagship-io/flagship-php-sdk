<?php

namespace Flagship;

use Exception;
use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Traits\Guid;
use Flagship\Traits\LogTrait;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Visitor\VisitorAbstract;
use Flagship\Visitor\VisitorBuilder;

/**
 * Flagship main singleton.
 */
class Flagship
{
    use LogTrait;
    use Guid;

    /**
     * @var Flagship
     */
    private static $instance;

    /**
     * Dependency injection container
     *
     * @var Container
     */
    private $container;
    /**
     * @var FlagshipConfig
     */
    private $config;

    /**
     * @var ConfigManager
     */
    private $configManager;
    /**
     * @var int
     */
    private $status = FlagshipStatus::NOT_INITIALIZED;

    /**
     * @var string
     */
    private $flagshipInstanceId;

    /**
     * Flagship constructor.
     */
    private function __construct()
    {
        //private singleton constructor
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
        //private singleton clone
    }

    /**
     * Flagship singleton instance
     *
     * @return Flagship
     * @throws Exception
     */
    protected static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Flagship();
            self::$instance->container = self::$instance->containerInitialization();
        }
        return self::$instance;
    }


    /**
     * Start the flagship SDK
     *
     * @param string $envId  Environment id provided by Flagship.
     * @param string $apiKey Secure api key provided by Flagship.
     * @param BucketingConfig|DecisionApiConfig|null $config : (optional) SDK configuration.
     */
    public static function start($envId, $apiKey, FlagshipConfig $config = null)
    {
        try {
            $flagship = self::getInstance();
            $flagship->flagshipInstanceId = $flagship->newGuid();
            $container = $flagship->getContainer();

            if (!$config) {
                $config = $container->get('Flagship\Config\DecisionApiConfig', [$envId, $apiKey]);
            }
            $config->setEnvId($envId);
            $config->setApiKey($apiKey);

            $flagship->setConfig($config);

            $flagship->setStatus(FlagshipStatus::STARTING);

            if (!$config->getLogManager()) {
                $logManager = $container->get('Psr\Log\LoggerInterface');
                $config->setLogManager($logManager);
            }

            $httpClient = $container->get('Flagship\Utils\HttpClientInterface');

            if ($config->getDecisionMode() === DecisionMode::BUCKETING) {
                $murmurHash = $container->get('Flagship\Utils\MurmurHash');
                $decisionManager = $container->get(
                    'Flagship\Decision\BucketingManager',
                    [$httpClient, $config, $murmurHash]
                );
            } else {
                $decisionManager = $container->get('Flagship\Decision\ApiManager', [$httpClient, $config]);
            }
            $decisionManager->setFlagshipInstanceId($flagship->flagshipInstanceId);

            //Will trigger setStatus method of Flagship if decisionManager want update status
            $decisionManager->setStatusChangedCallback([$flagship,'setStatus']);

            $configManager = $container->get('Flagship\Utils\ConfigManager');

            $configManager->setDecisionManager($decisionManager);

            $trackingManager = $container->get('Flagship\Api\TrackingManager', [$config,$httpClient]);

            $configManager->setTrackingManager($trackingManager);

            $configManager->setConfig($config);

            $decisionManager->setTrackingManager($trackingManager);

            $flagship->setConfigManager($configManager);

            if (empty($envId) || empty($apiKey)) {
                $flagship->logError(
                    $config,
                    FlagshipConstant::INITIALIZATION_PARAM_ERROR,
                    [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
                );
            }

            if (self::isReady()) {
                $flagship->logInfo(
                    $config,
                    sprintf(FlagshipConstant::SDK_STARTED_INFO, FlagshipConstant::SDK_VERSION),
                    [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
                );
                $flagship->setStatus(FlagshipStatus::READY);
            } else {
                $flagship->setStatus(FlagshipStatus::NOT_INITIALIZED);
            }
        } catch (Exception $exception) {
            self::getInstance()->setStatus(FlagshipStatus::NOT_INITIALIZED);

            self::getInstance()->logError(
                $config,
                $exception->getMessage(),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
            );
        }
    }

    /**
     * This function initialize the dependency injection container
     *
     * @return Container
     * @throws Exception
     */
    private function containerInitialization()
    {
        $newContainer = new Container();

        $newContainer->bind(
            'Flagship\Utils\HttpClientInterface',
            'Flagship\Utils\HttpClient'
        );
        if (version_compare(phpversion(), '8', '>=')) {
            $newContainer->bind(
                'Psr\Log\LoggerInterface',
                'Flagship\Utils\FlagshipLogManager8'
            );
        } else {
            $newContainer->bind(
                'Psr\Log\LoggerInterface',
                'Flagship\Utils\FlagshipLogManager'
            );
        }
        return $newContainer;
    }

    /**
     * Return true if the SDK is properly initialized, otherwise return false
     *
     * @return bool
     */
    public static function isReady()
    {
        if (!self::$instance || !self::$instance->config) {
            return false;
        }
        $envId = self::$instance->config->getEnvId();
        $apiKey = self::$instance->config->getApiKey();
        return !empty($envId) && !empty($apiKey);
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->configManager;
    }

    /**
     * @param ConfigManager $configManager
     * @return Flagship
     */
    protected function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
        return $this;
    }


    /**
     * Return the current config set by the customer and used by the SDK.
     *
     * @return FlagshipConfig
     */
    public static function getConfig()
    {
        return self::getInstance()->config;
    }

    /**
     * @param  FlagshipConfig $config
     * @return Flagship
     */
    protected function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Return current status of Flagship SDK.
     * @see FlagshipStatus
     * @return int
     */
    public static function getStatus()
    {
        return self::getInstance()->status;
    }

    /**
     * Set Flagship SDK status
     *
     * @param int $status FlagshipStatus::READY or FlagshipStatus::NOT_READY
     * @return Flagship
     */
    public function setStatus($status)
    {
        if ($this->config && $this->config->getStatusChangedCallback() && $this->status !== $status) {
            call_user_func($this->config->getStatusChangedCallback(), $status);
        }
        VisitorAbstract::setSdkStatus($status);
        $this->status = $status;
        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Every time your **application** (**script**) is about to **terminate**, you must call this method
     * to batch and send all hits that are in the pool
     * @return void
     */
    public static function close()
    {
        $instance = self::getInstance();
        if (!$instance->getConfigManager()) {
            return;
        }
        $instance->getConfigManager()->getTrackingManager()->sendBatch();
    }

    /**
     * Initialize the builder and Return a \Flagship\Visitor\VisitorBuilder
     * or null if the SDK hasn't started successfully.
     *
     * @param string $visitorId : Unique visitor identifier.
     * @return VisitorBuilder
     */
    public static function newVisitor($visitorId = null)
    {
        $instance = self::getInstance();
        return VisitorBuilder::builder($visitorId, $instance->getConfigManager(), $instance->getContainer());
    }
}

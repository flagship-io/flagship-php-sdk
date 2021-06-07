<?php

namespace Flagship;

use Exception;
use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Traits\LogTrait;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;

/**
 * Flagship main singleton.
 */
class Flagship
{
    use LogTrait;

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
     * @param $envId  : Environment id provided by Flagship.
     * @param $apiKey : Secure api key provided by Flagship.
     * @param BucketingConfig|DecisionApiConfig|null $config : (optional) SDK configuration.
     */
    public static function start($envId, $apiKey, FlagshipConfig $config = null)
    {
        try {
            $flagship = self::getInstance();
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

            if ($config->getDecisionMode() === DecisionMode::BUCKETING) {
                $decisionManager = $container->get('Flagship\Decision\BucketingManager');
            } else {
                $decisionManager = $container->get('Flagship\Decision\ApiManager');
            }

            //Will trigger setStatus method of Flagship if decisionManager want update status
            $decisionManager->setStatusChangedCallable([$flagship,'setStatus']);

            $configManager = $container->get('Flagship\Utils\ConfigManager');

            $configManager->setDecisionManager($decisionManager);

            $trackingManager = $container->get('Flagship\Api\TrackingManager');

            $configManager->setTrackingManager($trackingManager);

            $configManager->setConfig($config);

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
        $newContainer->bind(
            'Psr\Log\LoggerInterface',
            'Flagship\Utils\FlagshipLogManager'
        );
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
     * @see \Flagship\Enum\FlagshipStatus
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
        if ($this->config && $this->config->getStatusChangedCallable() && $this->status !== $status) {
            call_user_func($this->config->getStatusChangedCallable(), $status);
        }

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
     * Create a new visitor with a context.
     *
     * @param string $visitorId : Unique visitor identifier.
     * @param array $context   : visitor context. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"].
     * @return Visitor|null
     */
    public static function newVisitor($visitorId, array $context = [])
    {
        if (empty($visitorId) || !self::isReady()) {
            return  null;
        }
        $instance = self::getInstance();
        $visitorDelegate = $instance->getContainer()->get('Flagship\Visitor\VisitorDelegate', [
            $instance->getContainer(),
            $instance->getConfigManager(),
            $visitorId,
            $context
        ], true);
        return new Visitor($visitorDelegate);
    }
}

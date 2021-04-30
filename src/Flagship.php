<?php

namespace Flagship;

use Exception;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Traits\LogTrait;
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
     * @var int
     */
    private $status = FlagshipStatus::NOT_READY;

    private function __construct()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * Flagship singleton instance
     *
     * @return Flagship
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
     * Start the flagship SDK, with a custom configuration implementation
     *
     * @param $envId : Environment id provided by Flagship.
     * @param $apiKey : Secure api key provided by Flagship.
     * @param FlagshipConfig|null $config : SDK configuration. @see FlagshipConfig
     */
    public static function start($envId, $apiKey, FlagshipConfig $config = null)
    {
        try {
            $flagship = self::getInstance();
            $container = $flagship->getContainer();

            if (!$config) {
                $config = $container->get('Flagship\FlagshipConfig', [$envId, $apiKey]);
            }

            if (!$config->getLogManager()) {
                $logManager = $container->get('Flagship\Utils\LogManager');
                $config->setLogManager($logManager);
            }

            $decisionManager = null;

            switch ($config->getDecisionMode()){
                case DecisionMode::DECISION_API:
                    $decisionManager = $container->get('Flagship\Decision\ApiManager');
                    break;
            }

            $config->setDecisionManager($decisionManager);

            $trackingManager= $container->get('Flagship\Api\TrackingManager');

            $config->setTrackingManager($trackingManager);


            $config->setEnvId($envId);
            $config->setApiKey($apiKey);
            $flagship->setConfig($config);

            if (empty($envId) || empty($apiKey)) {
                $flagship->logError(
                    $config->getLogManager(),
                    FlagshipConstant::INITIALIZATION_PARAM_ERROR,
                    [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_INITIALIZATION]
                );
            }

            if (self::isReady()) {
                $flagship->logInfo(
                    $config->getLogManager(),
                    sprintf(FlagshipConstant::SDK_STARTED_INFO, FlagshipConstant::SDK_VERSION),
                    [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_INITIALIZATION]
                );
                $flagship->setStatus(FlagshipStatus::READY);
            } else {
                $flagship->setStatus(FlagshipStatus::NOT_READY);
            }
        }
        catch (Exception $exception){
            self::getInstance()->logError(
                $config->getLogManager(),
                $exception->getMessage(),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_INITIALIZATION]
            );
        }

    }

    /**
     * This function initialize the dependency injection container
     * @return Container
     */
    private function containerInitialization()
    {
        $container = new Container();

        $container->bind(
            'Flagship\Utils\HttpClientInterface',
            'Flagship\Utils\HttpClient'
        );
        $container->bind(
            'Flagship\Utils\LogManagerInterface',
            'Flagship\Utils\LogManager'
        );
        return $container;
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
     * Return the current config set by the customer and used by the SDK.
     *
     * @return FlagshipConfig
     */
    public static function getConfig()
    {
        return self::getInstance()->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return Flagship
     */
    protected function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Return current status of Flagship SDK.
     * e.g. FlagshipStatus::READY or FlagshipStatus::NOT_READY
     *
     * @return int
     */
    public static function getStatus()
    {
        return self::getInstance()->status;
    }

    /**
     * Set Flagship SDK status
     * @param int $status   FlagshipStatus::READY or FlagshipStatus::NOT_READY
     * @return Flagship
     */
    protected function setStatus($status)
    {
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
     * @param $visitorId : Unique visitor identifier.
     * @param array $context : visitor context. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"].
     * @return Visitor|null
     */
    public static function newVisitor($visitorId, $context = [])
    {
        if (empty($visitorId) || !self::isReady()) {
            return  null;
        }
        $instance = self::getInstance();
        return new Visitor($instance->config, $visitorId, $context);
    }
}

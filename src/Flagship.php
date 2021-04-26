<?php

namespace Flagship;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Traits\LogTrait;

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
        $flagship = self::getInstance();
        if (!$config) {
            $config = new FlagshipConfig($envId, $apiKey);
        }
        $config->setEnvId($envId);
        $config->setApiKey($apiKey);

        if (empty($envId) || empty($apiKey)) {
            $flagship->logError(
                $config->getLogManager(),
                FlagshipConstant::INITIALIZATION_PARAM_ERROR,
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_INITIALIZATION]
            );
        }

        $flagship->setConfig($config);

        if (self::isReady()) {
            $flagship->logInfo(
                $config->getLogManager(),
                sprintf(FlagshipConstant::SDK_STARTED_INFO, FlagshipConstant::SDK_VERSION),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_INITIALIZATION]
            );
            self::getInstance()->setStatus(FlagshipStatus::READY);
        } else {
            self::getInstance()->setStatus(FlagshipStatus::NOT_READY);
        }
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
        return new Visitor(self::getConfig(), $visitorId, $context);
    }
}

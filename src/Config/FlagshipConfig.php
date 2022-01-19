<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\LogLevel;
use Flagship\Traits\ValidatorTrait;
use JsonSerializable;
use Psr\Log\LoggerInterface;

/**
 * Flagship SDK configuration class to provide at initialization.
 *
 * @package Flagship
 */
abstract class FlagshipConfig implements JsonSerializable
{
    use ValidatorTrait;

    /**
     * @var string
     */
    private $envId;
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var int
     */
    private $decisionMode = DecisionMode::DECISION_API;
    /**
     * @var int
     */
    private $timeout = FlagshipConstant::REQUEST_TIME_OUT;
    /**
     * @var LoggerInterface
     */
    private $logManager;
    /**
     * @var int
     */
    private $logLevel = LogLevel::ALL;

    /**
     * @var callable
     */
    private $statusChangedCallback;


    /**
     * Create a new FlagshipConfig configuration.
     *
     * @param string $envId : Environment id provided by Flagship.
     * @param string $apiKey : Secure api key provided by Flagship.
     */
    public function __construct($envId = null, $apiKey = null)
    {
        $this->envId = $envId;
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getEnvId()
    {
        return $this->envId;
    }

    /**
     * Specify the environment id provided by Flagship, to use.
     *
     * @param string $envId environment id.
     * @return $this
     */
    public function setEnvId($envId)
    {
        $this->envId = $envId;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Specify the secure api key provided by Flagship, to use.
     *
     * @param string $apiKey secure api key.
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return int
     */
    public function getDecisionMode()
    {
        return $this->decisionMode;
    }

    /**
     * Specify the SDK running mode.
     *
     * @param int $decisionMode decision mode value e.g DecisionMode::DECISION_API
     * @see \Flagship\Enum\DecisionMode Enum Decision mode
     * @return $this
     */
    protected function setDecisionMode($decisionMode)
    {
        if (DecisionMode::isDecisionMode($decisionMode)) {
            $this->decisionMode = $decisionMode;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout * 1000;
    }

    /**
     * Specify timeout for api request.
     *
     * @param int $timeout : Milliseconds for connect and read timeouts. Default is 2000ms.
     * @return $this
     */
    public function setTimeout($timeout)
    {
        if (is_numeric($timeout) && $timeout > 0) {
            $this->logError($this, FlagshipConstant::TIMEOUT_TYPE_ERROR);
            $this->timeout = $timeout / 1000;
        }
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogManager()
    {
        return $this->logManager;
    }

    /**
     * Specify a custom implementation of LogManager in order to receive logs from the SDK.
     *
     * @param LoggerInterface $logManager custom implementation of LogManager.
     * @return $this
     */
    public function setLogManager(LoggerInterface $logManager)
    {
        $this->logManager = $logManager;
        return $this;
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * Set the maximum log level to display
     * @see \Flagship\Enum\LogLevel Loglevel enum list
     * @param int $logLevel
     * @return $this
     */
    public function setLogLevel($logLevel)
    {
        if (!is_int($logLevel) || $logLevel < LogLevel::NONE || $logLevel > LogLevel::ALL) {
            $this->logError($this, FlagshipConstant::LOG_LEVEL_ERROR);
            return $this;
        }
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * @return callable
     */
    public function getStatusChangedCallback()
    {
        return $this->statusChangedCallback;
    }

    /**
     * Define a callable in order to get callback when the SDK status has changed.
     * @param callable $statusChangedCallback callback
     * @return $this
     */
    public function setStatusChangedCallback($statusChangedCallback)
    {
        if (is_callable($statusChangedCallback)) {
            $this->statusChangedCallback = $statusChangedCallback;
        } else {
            $this->logError(
                $this,
                sprintf(FlagshipConstant::IS_NOT_CALLABLE_ERROR, json_encode($statusChangedCallback)),
                [
                    FlagshipConstant::TAG => __FUNCTION__
                ]
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            FlagshipField::FIELD_ENVIRONMENT_ID => $this->getEnvId(),
            FlagshipField::FIELD_API_KEY => $this->getApiKey(),
            FlagshipField::FIELD_TIMEOUT => $this->getTimeout(),
            FlagshipField::FIELD_LOG_LEVEL => $this->getLogLevel()
        ];
    }

    public static function bucketing()
    {
        return new BucketingConfig();
    }

    public static function decisionApi()
    {
        return new DecisionApiConfig();
    }
}

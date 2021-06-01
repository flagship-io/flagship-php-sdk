<?php

namespace Flagship;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;
use JsonSerializable;
use Psr\Log\LoggerInterface;

/**
 * Flagship SDK configuration class to provide at initialization.
 *
 * @package Flagship
 */
class FlagshipConfig implements JsonSerializable
{
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
     * @return FlagshipConfig
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
     * @return FlagshipConfig
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
     * @return FlagshipConfig
     */
    private function setDecisionMode($decisionMode)
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
     * @return FlagshipConfig
     */
    public function setTimeout($timeout)
    {
        if (is_numeric($timeout) && $timeout > 0) {
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
     * @return FlagshipConfig
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
     * @return FlagshipConfig
     */
    public function setLogLevel($logLevel)
    {
        if (!is_int($logLevel) || $logLevel < LogLevel::NONE || $logLevel > LogLevel::ALL) {
            return $this;
        }
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "environmentId" => $this->getEnvId(),
            "apiKey" => $this->getApiKey(),
            "timeout" => $this->getTimeout(),
            "logLevel" => $this->getLogLevel()
        ];
    }
}

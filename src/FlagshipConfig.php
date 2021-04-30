<?php

namespace Flagship;

use Flagship\Api\TrackingManagerAbstract;
use Flagship\Decision\DecisionManagerAbstract;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Utils\LogManagerInterface;

/**
 * Flagship SDK configuration class to provide at initialization.
 *
 * @package Flagship
 */
class FlagshipConfig
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
    private $timeOut = FlagshipConstant::REQUEST_TIME_OUT;
    /**
     * @var LogManagerInterface
     */
    private $logManager;

    /**
     * @var DecisionManagerAbstract
     */
    private $decisionManager;

    /**
     * @var TrackingManagerAbstract
     */
    private $trackingManager;

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
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * Specify timeout for api request.
     *
     * @param int $timeOut milliseconds for connect and read timeouts. Default is 2000.
     * @return FlagshipConfig
     */
    public function setTimeOut($timeOut)
    {
        if (is_numeric($timeOut) && $timeOut > 0) {
            $this->timeOut = $timeOut;
        }
        return $this;
    }

    /**
     * @return LogManagerInterface
     */
    public function getLogManager()
    {
        return $this->logManager;
    }

    /**
     * Specify a custom implementation of LogManager in order to receive logs from the SDK.
     *
     * @param LogManagerInterface $logManager custom implementation of LogManager.
     * @return FlagshipConfig
     */
    public function setLogManager(LogManagerInterface $logManager)
    {
        $this->logManager = $logManager;
        return $this;
    }

    /**
     * @return DecisionManagerAbstract
     */
    public function getDecisionManager()
    {
        return $this->decisionManager;
    }

    /**
     * @param DecisionManagerAbstract $decisionManager
     * @return FlagshipConfig
     */
    public function setDecisionManager($decisionManager)
    {
        $this->decisionManager = $decisionManager;
        return $this;
    }

    /**
     * @return TrackingManagerAbstract
     */
    public function getTrackingManager()
    {
        return $this->trackingManager;
    }

    /**
     * @param TrackingManagerAbstract $trackerManager
     * @return FlagshipConfig
     */
    public function setTrackingManager($trackerManager)
    {
        $this->trackingManager = $trackerManager;
        return $this;
    }


}

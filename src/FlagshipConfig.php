<?php

namespace Abtasty\FlagshipPhpSdk;

use Abtasty\FlagshipPhpSdk\Interfaces\LogManagerInterface;

/**
 * Class FlagshipConfig
 *
 * @package Abtasty\FlagshipPhpSdk
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
    private $decisionMode;
    /**
     * @var int
     */
    private $timeOut = 2000;
    /**
     * @var LogManagerInterface
     */
    private $logManager;

    /**
     * FlagshipConfig constructor.
     * @param $envId
     * @param $apiKey
     * @param $decisionMode
     */
    public function __construct($envId, $apiKey, $decisionMode)
    {
        $this->envId = $envId;
        $this->apiKey = $apiKey;
        $this->decisionMode = $decisionMode;
    }

    /**
     * @return int
     */
    public function getDecisionMode()
    {
        return $this->decisionMode;
    }

    /**
     * @param int $decisionMode
     * @return FlagshipConfig
     */
    public function setDecisionMode($decisionMode)
    {
        $this->decisionMode = $decisionMode;
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
     * @param int $timeOut
     * @return FlagshipConfig
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
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
     * @param LogManagerInterface $logManager
     * @return FlagshipConfig
     */
    public function setLogManager($logManager)
    {
        $this->logManager = $logManager;
        return $this;
    }

}

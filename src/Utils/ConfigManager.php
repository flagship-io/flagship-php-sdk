<?php

namespace Flagship\Utils;

use Flagship\Api\TrackingManagerAbstract;
use Flagship\Config\FlagshipConfig;
use Flagship\Decision\DecisionManagerAbstract;

class ConfigManager
{
    /**
     * @var FlagshipConfig
     */
    private $config;
    /**
     * @var DecisionManagerAbstract
     */
    private $decisionManager;

    /**
     * @var TrackingManagerAbstract
     */
    private $trackingManager;

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return ConfigManager
     */
    public function setConfig($config)
    {
        $this->config = $config;
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
     * @return ConfigManager
     */
    public function setDecisionManager(DecisionManagerAbstract $decisionManager)
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
     * @return ConfigManager
     */
    public function setTrackingManager(TrackingManagerAbstract $trackerManager)
    {
        $this->trackingManager = $trackerManager;
        return $this;
    }
}

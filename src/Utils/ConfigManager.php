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
    private FlagshipConfig $config;
    /**
     * @var DecisionManagerAbstract
     */
    private DecisionManagerAbstract $decisionManager;

    /**
     * @var TrackingManagerAbstract
     */
    private TrackingManagerAbstract $trackingManager;

    public function __construct(
        FlagshipConfig $config,
        DecisionManagerAbstract $decisionManager,
        TrackingManagerAbstract $trackingManager
    ) {
        $this->config = $config;
        $this->decisionManager = $decisionManager;
        $this->trackingManager = $trackingManager;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig(): FlagshipConfig
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return ConfigManager
     */
    public function setConfig(FlagshipConfig $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return DecisionManagerAbstract
     */
    public function getDecisionManager(): DecisionManagerAbstract
    {
        return $this->decisionManager;
    }

    /**
     * @param DecisionManagerAbstract $decisionManager
     * @return ConfigManager
     */
    public function setDecisionManager(DecisionManagerAbstract $decisionManager): self
    {
        $this->decisionManager = $decisionManager;
        return $this;
    }

    /**
     * @return TrackingManagerAbstract
     */
    public function getTrackingManager(): TrackingManagerAbstract
    {
        return $this->trackingManager;
    }

    /**
     * @param TrackingManagerAbstract $trackerManager
     * @return ConfigManager
     */
    public function setTrackingManager(TrackingManagerAbstract $trackerManager): self
    {
        $this->trackingManager = $trackerManager;
        return $this;
    }
}

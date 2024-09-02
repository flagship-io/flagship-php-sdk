<?php

namespace Flagship\Utils;

use Flagship\Api\TrackingManager;
use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use PHPUnit\Framework\TestCase;

class ConfigManagerTest extends TestCase
{
    public function testInstance()
    {
        $config = new DecisionApiConfig();
        $decisionManager = new ApiManager(new HttpClient(), $config);
        $trackingManager = new TrackingManager($config, new HttpClient());
        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);

        $this->assertSame($config, $configManager->getConfig());
        $this->assertSame($decisionManager, $configManager->getDecisionManager());
        $this->assertSame($trackingManager, $configManager->getTrackingManager());
    }
}

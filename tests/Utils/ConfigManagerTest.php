<?php

namespace Flagship\Utils;

use Flagship\Api\TrackingManager;
use Flagship\Decision\ApiManager;
use Flagship\FlagshipConfig;
use PHPUnit\Framework\TestCase;

class ConfigManagerTest extends TestCase
{
    public function testInstance()
    {
        $configManager = new ConfigManager();

        $this->assertNull($configManager->getConfig());
        $this->assertNull($configManager->getTrackingManager());
        $this->assertNull($configManager->getDecisionManager());

        $config =  new FlagshipConfig();
        $configManager->setConfig($config);
        $this->assertSame($config, $configManager->getConfig());

        $decisionManager =  new ApiManager(new HttpClient());
        $configManager->setDecisionManager($decisionManager);
        $this->assertSame($decisionManager, $configManager->getDecisionManager());

        $trackingManager = new TrackingManager(new HttpClient());
        $configManager->setTrackingManager($trackingManager);
        $this->assertSame($trackingManager, $configManager->getTrackingManager());
    }
}

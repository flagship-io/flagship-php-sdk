<?php

namespace Controller;

use Flagship\Config\DecisionApiConfig;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Visitor\Visitor;
use Flagship\Visitor\VisitorDelegate;
use Illuminate\Support\Facades\Session;

trait GeneralMockTrait
{
    private function startFlagShip()
    {
        $data = [
            "environment_id" => "env_id",
            "api_key" => "api_key",
            "timeout" => 2000,
        ];
        $this->put('/env', $data);
        return $data;
    }

    /**
     * @param $envId
     * @param $apiKey
     * @return \PHPUnit\Framework\MockObject\MockObject|Visitor
     */
    public function getVisitorMock($envId, $apiKey, $visitorId = "visitorId")
    {
        $config = new DecisionApiConfig($envId, $apiKey);
        $configManager = new ConfigManager();
        $configManager->setConfig($config);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, []);
        $visitor = $this->getMockBuilder(Visitor::class)
            ->onlyMethods(['getConfig','sendHit'])
            ->setConstructorArgs([$visitorDelegate])
            ->getMock();
        $visitor->method('getConfig')->willReturn($config);
        Session::put('visitor', $visitor);
        return $visitor;
    }
}

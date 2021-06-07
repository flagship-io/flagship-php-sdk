<?php

namespace Controller;

use Flagship\FlagshipConfig;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Visitor;
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
    public function getVisitorMock($envId, $apiKey)
    {
        $config = new FlagshipConfig($envId, $apiKey);
        $configManager = new ConfigManager();
        $configManager->setConfig($config);
        $visitorDelegate = new Visitor\VisitorDelegate(new Container(), $configManager, 'visitorId', []);
        $visitor = $this->getMockBuilder(Visitor::class)
            ->setConstructorArgs([$visitorDelegate])->getMock();
        $visitor->method('getConfig')->willReturn($config);
        Session::put('visitor', $visitor);
        return $visitor;
    }
}

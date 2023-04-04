<?php

namespace Controller;

use App\Traits\ErrorFormatTrait;
use Flagship\Config\DecisionApiConfig;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Visitor\Visitor;
use Flagship\Visitor\VisitorDelegate;
use TestCase;
use Illuminate\Support\Facades\Session;

class FlagControllerTest extends TestCase
{
    use ErrorFormatTrait;

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

    public function getVisitorMock($envId, $apiKey)
    {
        $configManager = new ConfigManager();
        $config = new DecisionApiConfig($envId, $apiKey);
        $configManager->setConfig($config);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, 'visitorId', []);
        $visitor = $this->getMockBuilder(Visitor::class)
            ->setConstructorArgs([$visitorDelegate])->getMock();
        Session::start();
        Session::put('visitor', $visitor);
        return $visitor;
    }

    public function testGetModification()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $valueReturn = 'result_return';
        $expectArray = [
            "value" => $valueReturn,
            'error' => null
        ];

        $visitor->expects($this->once())
            ->method('getModification')
            ->willReturn($valueReturn);

        $this->get('/flag/key?' . http_build_query(['type' => 'string','activate' => true, 'defaultValue' => 'yes']));
        $this->assertJsonStringEqualsJsonString(json_encode($expectArray), $this->response->content());

        //Test Validation error
        $this->get('/flag/key?' . http_build_query(['type' => 'string', 'defaultValue' => 'yes']));
        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["activate" => ["The activate field is required."]])),
            $this->response->content()
        );
    }

    public function testGetModificationInfo()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $valueReturn = 'response';

        $visitor->expects($this->exactly(2))
            ->method('getModificationInfo')
            ->willReturnOnConsecutiveCalls($valueReturn, null);

        $this->get('/flag/key/info?' .
            http_build_query(['type' => 'string','activate' => true, 'defaultValue' => 'yes']));
        $this->assertSame(json_encode($valueReturn), $this->response->content());

        $this->get('/flag/key/info?' .
            http_build_query(['type' => 'string','activate' => true, 'defaultValue' => 'yes']));

        $this->assertSame("{}", $this->response->content());
    }

    public function testActiveModification()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $visitor->expects($this->once())
            ->method('activateModification');

        $this->get('/flag/key/activate?' .
            http_build_query(['type' => 'string','activate' => true, 'defaultValue' => 'yes']));
        $this->assertSame(json_encode('successful operation'), $this->response->content());
    }
}

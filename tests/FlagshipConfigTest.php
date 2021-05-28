<?php

namespace Flagship;

use Flagship\Api\TrackingManager;
use Flagship\Decision\ApiManager;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;
use Flagship\Utils\HttpClient;
use Flagship\Utils\FlagshipLogManager;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class FlagshipConfigTest extends TestCase
{
    public function configData()
    {
        return [[['envId' => 'env_value','apiKey' => 'key_value']]];
    }

    /**
     * @dataProvider configData
     * @param        array $configData
     */
    public function testSetTimeOut($configData)
    {
        $timeOut = 5000;
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);

        $this->assertEquals(FlagshipConstant::REQUEST_TIME_OUT, $config->getTimeout());

        $config->setTimeout($timeOut);
        $this->assertEquals($timeOut, $config->getTimeout());

        $config->setTimeout(0);
        $this->assertEquals($timeOut, $config->getTimeout());

        $config->setTimeout("not a number");
        $this->assertEquals($timeOut, $config->getTimeout());
    }

    public function testSetLogLevel()
    {
        $envId = "envId";
        $apiKey = "apiKey";

        $config = new FlagshipConfig($envId, $apiKey);
        $this->assertSame(LogLevel::ALL, $config->getLogLevel());

        $config->setLogLevel(-2);
        $this->assertSame(LogLevel::ALL, $config->getLogLevel());

        $config->setLogLevel(12);
        $this->assertSame(LogLevel::ALL, $config->getLogLevel());

        $config->setLogLevel("abc");
        $this->assertSame(LogLevel::ALL, $config->getLogLevel());

        $config->setLogLevel(LogLevel::ERROR);
        $this->assertSame(LogLevel::ERROR, $config->getLogLevel());
    }

    public function testSetPollingInterval()
    {
        $envId = "envId";
        $apiKey = "apiKey";

        $config = new FlagshipConfig($envId, $apiKey);

        //Test default value
        $this->assertSame(FlagshipConstant::REQUEST_TIME_OUT, $config->getPollingInterval());

        //Test set with alpha
        $config->setPollingInterval('abc');
        $this->assertSame(FlagshipConstant::REQUEST_TIME_OUT, $config->getPollingInterval());

        $polling = 5;
        $config->setPollingInterval($polling);
        $this->assertSame($polling, $config->getPollingInterval());
    }
    /**
     * @dataProvider configData
     * @param        array $configData
     */
    public function testSetApiKey($configData)
    {
        $newApiKey = 'new_api_key';
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $config->setApiKey($newApiKey);
        $this->assertEquals($newApiKey, $config->getApiKey());
    }

    /**
     * @dataProvider configData
     * @param        array $configData
     */
    public function testSetEnvId($configData)
    {
        $newEnvId = 'new_env_id';
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $config->setEnvId($newEnvId);
        $this->assertEquals($newEnvId, $config->getEnvId());
    }

    /**
     * @dataProvider configData
     * @param        array $configData
     */
    public function testDecisionMode($configData)
    {
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $this->assertEquals(DecisionMode::DECISION_API, $config->getDecisionMode());
    }

    /**
     * @dataProvider configData
     * @param        array $configData
     */
    public function testConstruct($configData)
    {
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $this->assertInstanceOf("Flagship\FlagshipConfig", $config);
        $this->assertEquals($config->getEnvId(), $configData['envId']);
        $this->assertEquals($config->getApiKey(), $configData['apiKey']);
    }

    /**
     * @dataProvider configData
     * @param        array $configData
     */
    public function testSetDecisionMode($configData)
    {
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $setDecisionMode = Utils::getMethod($config, 'setDecisionMode');
        $setDecisionMode->invokeArgs($config, [DecisionMode::DECISION_API]);
        $this->assertSame(DecisionMode::DECISION_API, $config->getDecisionMode());
        $setDecisionMode->invokeArgs($config, [5]);
        $this->assertSame(DecisionMode::DECISION_API, $config->getDecisionMode());
    }

    public function testSetStatusChangedCallable()
    {
        $config = new FlagshipConfig();
        $this->assertNull($config->getStatusChangedCallable());
        $callable = function () {
        };
        $config->setStatusChangedCallable($callable);

        $this->assertSame($callable, $config->getStatusChangedCallable());
    }

    public function testJson()
    {
        $data =  [
            "environmentId" => 'envId',
            "apiKey" => "apiKey",
            "timeout" => 2000,
            "logLevel" => LogLevel::ALL
        ];

        $config = new FlagshipConfig($data['environmentId'], $data['apiKey']);
        $config->setTimeout($data['timeout']);

        $this->assertJsonStringEqualsJsonString(
            json_encode($data),
            json_encode($config)
        );
        $logManager = new FlagshipLogManager();
        $config->setLogManager($logManager);
        $this->assertSame($logManager, $config->getLogManager());
    }
}

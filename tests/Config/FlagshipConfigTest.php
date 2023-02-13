<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\LogLevel;
use Flagship\Utils\FlagshipLogManager;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class FlagshipConfigTest extends TestCase
{
    public  function configData()
    {
        return ['envId' => 'env_value','apiKey' => 'key_value'];
    }


    public function testSetTimeOut()
    {
        $configData = $this->configData();
        $timeOut = 5000;
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);

        $this->assertEquals(FlagshipConstant::REQUEST_TIME_OUT * 1000, $config->getTimeout());

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

        $config = new DecisionApiConfig($envId, $apiKey);
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



    public function testSetApiKey()
    {
        $configData = $this->configData();
        $newApiKey = 'new_api_key';
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $config->setApiKey($newApiKey);
        $this->assertEquals($newApiKey, $config->getApiKey());
    }


    public function testSetEnvId()
    {
        $configData = $this->configData();
        $newEnvId = 'new_env_id';
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $config->setEnvId($newEnvId);
        $this->assertEquals($newEnvId, $config->getEnvId());
    }


    public function testDecisionMode()
    {
        $configData = $this->configData();
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $this->assertEquals(DecisionMode::DECISION_API, $config->getDecisionMode());
    }


    public function testConstruct()
    {
        $configData = $this->configData();
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $this->assertInstanceOf("Flagship\Config\DecisionApiConfig", $config);
        $this->assertEquals($config->getEnvId(), $configData['envId']);
        $this->assertEquals($config->getApiKey(), $configData['apiKey']);
    }


    public function testSetDecisionMode()
    {
        $configData = $this->configData();
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $setDecisionMode = Utils::getMethod($config, 'setDecisionMode');
        $setDecisionMode->invokeArgs($config, [DecisionMode::DECISION_API]);
        $this->assertSame(DecisionMode::DECISION_API, $config->getDecisionMode());
        $setDecisionMode->invokeArgs($config, [5]);
        $this->assertSame(DecisionMode::DECISION_API, $config->getDecisionMode());
    }

    public function testSetStatusChangedCallback()
    {
        $logManagerMock = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            '',
            false,
            true,
            true,
            ['error', 'info']
        );

        $logManagerMock->expects($this->once())
            ->method('error')
            ->with(
                sprintf(FlagshipConstant::IS_NOT_CALLABLE_ERROR, "[]"),
                [
                    FlagshipConstant::TAG => "setStatusChangedCallback"
                ]
            );

        $config = new DecisionApiConfig();

        $config->setLogManager($logManagerMock);

        $this->assertNull($config->getStatusChangedCallback());

        $config->setStatusChangedCallback([]);

        $this->assertNull($config->getStatusChangedCallback());

        $callable = function () {
        };
        $config->setStatusChangedCallback($callable);

        $this->assertSame($callable, $config->getStatusChangedCallback());
    }

    public function testJson()
    {
        $data =  [
            FlagshipField::FIELD_ENVIRONMENT_ID => 'envId',
            FlagshipField::FIELD_API_KEY => "apiKey",
            FlagshipField::FIELD_TIMEOUT => 2000,
            FlagshipField::FIELD_LOG_LEVEL => LogLevel::ALL,
        ];

        $config = new DecisionApiConfig($data['environmentId'], $data['apiKey']);
        $config->setTimeout($data['timeout']);

        $this->assertJsonStringEqualsJsonString(
            json_encode($data),
            json_encode($config)
        );
        $logManager = new FlagshipLogManager();
        $config->setLogManager($logManager);
        $this->assertSame($logManager, $config->getLogManager());
    }

    public function testBuilder()
    {
        $this->assertInstanceOf("Flagship\Config\DecisionApiConfig", FlagshipConfig::decisionApi());
        $this->assertInstanceOf("Flagship\Config\BucketingConfig", FlagshipConfig::bucketing("http:127.0.0.1:3000"));
    }
}

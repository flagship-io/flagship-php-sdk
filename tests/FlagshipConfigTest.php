<?php

namespace Flagship;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
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

        $this->assertEquals(FlagshipConstant::REQUEST_TIME_OUT, $config->getTimeOut());

        $config->setTimeOut($timeOut);
        $this->assertEquals($timeOut, $config->getTimeOut());

        $config->setTimeOut(0);
        $this->assertEquals($timeOut, $config->getTimeOut());

        $config->setTimeOut("not a number");
        $this->assertEquals($timeOut, $config->getTimeOut());
    }

    public function testSetLogManager()
    {
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
}

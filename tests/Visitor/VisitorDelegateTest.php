<?php

namespace Flagship\Visitor;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Hit\Page;
use Flagship\Model\Modification;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class VisitorDelegateTest extends TestCase
{
    public function testConstruct()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $ageKey = 'age';
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);

        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, $visitorContext);

        //Test default visitorId
        $this->assertEquals($visitorId, $visitorDelegate->getVisitorId());

        //Test context
        $this->assertSame($visitorContext, $visitorDelegate->getContext());

        //Test configManager
        $this->assertSame($configManager, $visitorDelegate->getConfigManager());

        //Test new visitorId

        $newVisitorId = 'new_visitor_id';
        $visitorDelegate->setVisitorId($newVisitorId);
        $this->assertEquals($newVisitorId, $visitorDelegate->getVisitorId());

        //Test consent
        $this->assertFalse($visitorDelegate->hasConsented());
        $visitorDelegate->setConsent(true);
        $this->assertTrue($visitorDelegate->hasConsented());

        //Test Config
        $this->assertSame($config, $visitorDelegate->getConfig());

        $modifications = [
            new Modification()
        ];

        $visitorDelegate->setModifications($modifications);

        $this->assertSame($modifications, $visitorDelegate->getModifications());
    }

    public function testSetVisitorLog()
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);

        $config->setLogManager($logManagerStub);

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);

        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, $visitorContext);
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerStub->expects($this->once())
            ->method('error')->with(
                "[$flagshipSdk] " . FlagshipConstant::VISITOR_ID_ERROR,
                [FlagshipConstant::TAG => "setVisitorId"]
            );
        $visitorDelegate->setVisitorId('');
    }

    public function testMethods()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";

        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $defaultStrategy = $this->getMockBuilder('Flagship\Visitor\DefaultStrategy')
            ->setMethods([
                'setContext', 'updateContext', 'updateContextCollection',
                'clearContext', 'getModification','getModificationInfo', 'synchronizedModifications',
                'activateModification', 'sendHit'
            ])->disableOriginalConstructor()
            ->getMock();

        $containerMock->method('get')->willReturn($defaultStrategy);

        $visitor = new VisitorDelegate($containerMock, $configManager, $visitorId, $visitorContext);

        //test SetContext
        $defaultStrategy->expects($this->exactly(2))
            ->method('updateContextCollection')
            ->with($visitorContext);

        $visitor->setContext($visitorContext);

        //test updateContext
        $key = "age";
        $value = 20;
        $defaultStrategy->expects($this->once())
            ->method('updateContext')
            ->with($key, $value);

        $visitor->updateContext($key, $value);

        //test updateContextCollection

        $visitor->updateContextCollection($visitorContext);

        //Test clearContext
        $defaultStrategy->expects($this->once())->method('clearContext');
        $visitor->clearContext();

        //Test getModification
        $key = "age";
        $defaultValue = 20;

        $defaultStrategy->expects($this->once())
            ->method('getModification')
            ->with($key, $defaultValue, false);

        $visitor->getModification($key, $defaultValue, false);

        //Test getModificationInfo
        $key = "age";
        $defaultStrategy->expects($this->once())
            ->method('getModificationInfo')
            ->with($key);

        $visitor->getModificationInfo($key);

        //Test synchronizedModifications
        $defaultStrategy->expects($this->once())
            ->method('synchronizedModifications');

        $visitor->synchronizedModifications();

        //Test activateModification
        $key = "age";
        $defaultStrategy->expects($this->once())
            ->method('activateModification')->with($key);

        $visitor->activateModification($key);

        //Test sendHit
        $hit = new Page("http://localhost");
        $defaultStrategy->expects($this->once())
            ->method('sendHit')->with($hit);

        $visitor->sendHit($hit);
    }

    public function testJson()
    {
        $config = new FlagshipConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20];
        $configManager = (new ConfigManager())->setConfig($config);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, $context);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'visitorId' => $visitorId,
                'context' => $context,
                'hasConsent' => false
            ]),
            json_encode($visitorDelegate)
        );
    }

    public function testGetStrategy()
    {
        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);
        $setStatusMethod = Utils::getMethod($instance, 'setStatus');
        $setStatusMethod->invoke($instance, FlagshipStatus::NOT_INITIALIZED);

        $config = new FlagshipConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20];
        $configManager = (new ConfigManager())->setConfig($config);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, $context);

        $getStrategyMethod = Utils::getMethod($visitorDelegate, 'getStrategy');
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\NotReadyStrategy', $strategy);

        $setStatusMethod->invoke($instance, FlagshipStatus::READY_PANIC_ON);
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\PanicStrategy', $strategy);

        $setStatusMethod->invoke($instance, FlagshipStatus::READY);
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\NoConsentStrategy', $strategy);

        $setStatusMethod->invoke($instance, FlagshipStatus::READY);
        $visitorDelegate->setConsent(true);
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\DefaultStrategy', $strategy);
    }
}

<?php

namespace Flagship\Visitor;

use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Enum\FlagshipStatus;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\Event;
use Flagship\Hit\Page;
use Flagship\Model\FlagDTO;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class VisitorDelegateTest extends TestCase
{
    public function testVisitorDelegateConstruct()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $newVisitorId = 'new_visitor_id';
        $ageKey = 'age';
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25,
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
        ];

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [],
            "",
            false
        );

        $configManager = (new ConfigManager())->setConfig($config)->setTrackingManager($trackerManager);

        $containerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\ContainerInterface',
            [],
            '',
            false
        );


        $containerGetMethod = function () {
            $args = func_get_args();
            $params = $args[1];
            return new DefaultStrategy($params[0]);
        };

        $containerMock->method('get')->will($this->returnCallback($containerGetMethod));

        $consentHit = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "false")
            ->setConfig($config)
            ->setVisitorId($visitorId);

        $consentHit2 = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit2->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "true")
            ->setConfig($config)
            ->setVisitorId($newVisitorId);

        $trackerManager->expects($this->exactly(2))
            ->method('addHit')
            ->withConsecutive([$consentHit], [$consentHit2]);

        $visitorDelegate = new VisitorDelegate($containerMock, $configManager, $visitorId, false, $visitorContext);

        //Test default visitorId
        $this->assertEquals($visitorId, $visitorDelegate->getVisitorId());

        //Test context
        $this->assertSame($visitorContext, $visitorDelegate->getContext());

        //Test configManager
        $this->assertSame($configManager, $visitorDelegate->getConfigManager());

        //Test new visitorId


        $visitorDelegate->setVisitorId($newVisitorId);
        $this->assertEquals($newVisitorId, $visitorDelegate->getVisitorId());

        //Test consent
        $this->assertFalse($visitorDelegate->hasConsented());
        $visitorDelegate->setConsent(true);
        $this->assertTrue($visitorDelegate->hasConsented());


        //Test Config
        $this->assertSame($config, $visitorDelegate->getConfig());

        $modifications = [
            new FlagDTO()
        ];

        $visitorDelegate->setModifications($modifications);

        $this->assertSame($modifications, $visitorDelegate->getModifications());
    }

    public function testSetAnonymous()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $configManager = (new ConfigManager())->setConfig($config);

        //With default value
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);
        $this->assertNull($visitorDelegate->getAnonymousId());

        //Test isAuthenticate true and DecisionApiConfig
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, true, [], true);
        $this->assertNotNull($visitorDelegate->getAnonymousId());

        //Test with bucketing mode
        $configManager->setConfig(new BucketingConfig("http://127.0.0.1:3000"));
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, true, [], true);
        $this->assertNull($visitorDelegate->getAnonymousId());
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
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);

        $config->setLogManager($logManagerStub);

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);

        $visitorDelegate = new VisitorDelegate(
            new Container(),
            $configManager,
            $visitorId,
            false,
            $visitorContext,
            true
        );
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerStub->expects($this->once())
            ->method('error')->with(
                FlagshipConstant::VISITOR_ID_ERROR,
                [FlagshipConstant::TAG => "setVisitorId"]
            );
        $visitorDelegate->setVisitorId('');
    }
    public function testMethods()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
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
                'setContext', 'updateContext', 'updateContextCollection', "cacheVisitor",
                'clearContext', 'authenticate', 'unauthenticate', 'getModification',
                'getModificationInfo', 'synchronizeModifications', 'setConsent',
                'activateModification', 'sendHit', 'fetchFlags','userExposed', 'getFlagValue', 'getFlagMetadata','lookupVisitor'
            ])->disableOriginalConstructor()
            ->getMock();

        $containerMock->method('get')->willReturn($defaultStrategy);

        $defaultStrategy->expects($this->once())->method("lookupVisitor");

        $visitor = new VisitorDelegate($containerMock, $configManager, $visitorId, false, $visitorContext, true);



        $defaultContext = [
            FlagshipContext::OS_NAME => PHP_OS,
        ];

        //test SetContext
        $defaultStrategy->expects($this->exactly(5))
            ->method('updateContextCollection')
            ->withConsecutive(
                [$visitorContext],
                [$visitorContext],
                [$defaultContext]
            );

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

        //Test authenticate
        $newVisitorId = "newVisitorId";
        $defaultStrategy->expects($this->once())->method('authenticate')
            ->with($newVisitorId);
        $visitor->authenticate($newVisitorId);

        //Test unauthenticate
        $defaultStrategy->expects($this->once())->method('unauthenticate');
        $visitor->unauthenticate();

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
            ->method('synchronizeModifications');

        $defaultStrategy->expects($this->exactly(2))
            ->method('cacheVisitor');

        $visitor->synchronizeModifications();

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

        //Test fetchFlags
        $defaultStrategy->expects($this->once())->method('fetchFlags');
        $visitor->fetchFlags();

        //Test userExposed
        $key = 'key';
        $flagDTO = new FlagDTO();
        $defaultStrategy->expects($this->once())->method('userExposed')
            ->with($key, true, $flagDTO);
        $visitor->userExposed($key, true, $flagDTO);

        //Test getFlagValue
        $key = 'key';
        $flagDTO = new FlagDTO();
        $defaultValue = "defaultValue";
        $defaultStrategy->expects($this->once())
            ->method('getFlagValue')
            ->with($key, $defaultValue, $flagDTO, true);
        $visitor->getFlagValue($key, $defaultValue, $flagDTO);

        //Test getFlagMetadata
        $key = 'key';
        $metadata = FlagMetadata::getEmpty();
        $defaultStrategy->expects($this->exactly(1))
           ->method('getFlagMetadata')
           ->withConsecutive([$key, $metadata, true]);

        $visitor->getFlagMetadata($key, $metadata, true);

        //Test getFlag
        $flagDTO = new FlagDTO();
        $flagDTO->setKey("key1")
            ->setCampaignId('campaignID')
            ->setVariationGroupId("varGroupID")
            ->setVariationId('varID')
            ->setIsReference(true)->setValue("value")
        ->setCampaignType("ab");


        $flagsDTO = [
            $flagDTO
        ];
        $visitor->setFlagsDTO($flagsDTO);
        $defaultValue = "defaultValue";
        $flag = $visitor->getFlag('key1', $defaultValue);
        $this->assertInstanceOf("Flagship\Flag\Flag", $flag);

        //Test getFlag null
        $flag = $visitor->getFlag('key2', $defaultValue);
        $this->assertInstanceOf("Flagship\Flag\Flag", $flag);
    }

    public function testJson()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20,
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
        ];
        $configManager = (new ConfigManager())->setConfig($config);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $context, true);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'visitorId' => $visitorId,
                'context' => $context,
                'hasConsent' => true
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

        $decisionManagerMock = $this->getMockBuilder('Flagship\Api\TrackingManager')
            ->setMethods(['addHit'])
            ->disableOriginalConstructor()->getMock();

        $config = new DecisionApiConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20];
        $configManager = (new ConfigManager())->setConfig($config)->setTrackingManager($decisionManagerMock);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $context);

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

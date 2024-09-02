<?php

namespace Flagship\Visitor;

use Flagship\Api\TrackingManagerAbstract;
use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Enum\FSFetchReason;
use Flagship\Enum\FSFetchStatus;
use Flagship\Enum\FSSdkStatus;
use Flagship\Flag\FSFlagMetadata;
use Flagship\Hit\Event;
use Flagship\Hit\Page;
use Flagship\Model\FetchFlagsStatus;
use Flagship\Model\FlagDTO;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\ContainerInterface;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class VisitorDelegateTest extends TestCase
{
    public function testVisitorDelegateConstruct()
    {
        $configData = [
                       'envId'  => 'env_value',
                       'apiKey' => 'key_value',
                      ];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $newVisitorId = 'new_visitor_id';
        $ageKey = 'age';
        $visitorContext = [
                           'name'                       => 'visitor_name',
                           'age'                        => 25,
                           "sdk_osName"                 => PHP_OS,
                           "sdk_deviceType"             => "server",
                           FlagshipConstant::FS_CLIENT  => FlagshipConstant::SDK_LANGUAGE,
                           FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
                           FlagshipConstant::FS_USERS   => $visitorId,
                          ];

        $trackerManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            [],
            "",
            false
        );

        $decisionManager = $this->getMockBuilder(ApiManager::class)->disableOriginalConstructor()->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $containerMock = $this->getMockForAbstractClass(
            ContainerInterface::class,
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
        $consentHit->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "false")->setConfig($config)->setVisitorId($visitorId);

        $consentHit2 = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit2->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "true")->setConfig($config)->setVisitorId($newVisitorId);

        $trackerManager->expects($this->exactly(2))->method('addHit')->with(
            $this->logicalOr(
                $this->equalTo($consentHit),
                $this->equalTo($consentHit2)
            )
        );

        $visitorDelegate = new VisitorDelegate($containerMock, $configManager, $visitorId, false, $visitorContext);

        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitorDelegate->getFetchStatus()->getStatus());
        $this->assertSame(FSFetchReason::VISITOR_CREATED, $visitorDelegate->getFetchStatus()->getReason());
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

        //Test onFetchFlagsStatusChanged
        $onFetchFlagsStatusChanged = function ($fetchFlagsStatus) {
            $this->assertSame(FSFetchStatus::FETCHED, $fetchFlagsStatus->getStatus());
            $this->assertSame(FSFetchReason::NONE, $fetchFlagsStatus->getReason());
        };
        $visitorDelegate->setOnFetchFlagsStatusChanged($onFetchFlagsStatusChanged);
        $this->assertSame($onFetchFlagsStatusChanged, $visitorDelegate->getOnFetchFlagsStatusChanged());

        //Test getFetchStatus
        $fetchStatus = new FetchFlagsStatus(FSFetchStatus::FETCHED, FSFetchReason::NONE);
        $visitorDelegate->setFetchStatus($fetchStatus);
        $this->assertInstanceOf(FetchFlagsStatus::class, $visitorDelegate->getFetchStatus());
        $this->assertSame($fetchStatus, $visitorDelegate->getFetchStatus());
    }

    public function testSetAnonymous()
    {
        $configData = [
                       'envId'  => 'env_value',
                       'apiKey' => 'key_value',
                      ];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";

        $trackerManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            [],
            "",
            false
        );

        $decisionManager = $this->getMockBuilder(ApiManager::class)->disableOriginalConstructor()->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

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

        $configData = [
                       'envId'  => 'env_value',
                       'apiKey' => 'key_value',
                      ];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);

        $config->setLogManager($logManagerStub);

        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        $trackerManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            [],
            "",
            false
        );

        $decisionManager = $this->getMockBuilder(ApiManager::class)->disableOriginalConstructor()->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitorDelegate = new VisitorDelegate(
            new Container(),
            $configManager,
            $visitorId,
            false,
            $visitorContext,
            true
        );
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerStub->expects($this->once())->method('error')->with(
            FlagshipConstant::VISITOR_ID_ERROR,
            [FlagshipConstant::TAG => "setVisitorId"]
        );
        $visitorDelegate->setVisitorId('');
    }
    public function testMethods()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );
        $configData = [
                       'envId'  => 'env_value',
                       'apiKey' => 'key_value',
                      ];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";

        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        $trackerManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            [],
            "",
            false
        );

        $decisionManager = $this->getMockBuilder(ApiManager::class)->disableOriginalConstructor()->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->onlyMethods(['get'])->disableOriginalConstructor()->getMock();

        $defaultStrategy = $this->getMockBuilder('Flagship\Visitor\DefaultStrategy')->onlyMethods([
                                                                                                   'initialContext',
                                                                                                   'updateContext',
                                                                                                   'updateContextCollection',
                                                                                                   "cacheVisitor",
                                                                                                   'clearContext',
                                                                                                   'authenticate',
                                                                                                   'unauthenticate',
                                                                                                   'setConsent',
                                                                                                   'sendHit',
                                                                                                   'fetchFlags',
                                                                                                   'visitorExposed',
                                                                                                   'getFlagValue',
                                                                                                   'getFlagMetadata',
                                                                                                   'lookupVisitor',
                                                                                                  ])->disableOriginalConstructor()->getMock();

        $containerMock->method('get')->willReturn($defaultStrategy);

        $defaultStrategy->expects($this->once())->method("lookupVisitor");

        $visitor = new VisitorDelegate($containerMock, $configManager, $visitorId, false, $visitorContext, true);

        $defaultContext = [FlagshipContext::OS_NAME => PHP_OS];

        //test SetContext
        $defaultStrategy->expects($this->exactly(2))->method('updateContextCollection')->with(
            $this->logicalOr(
                $visitorContext,
                $defaultContext
            )
        );

        $visitor->setContext($visitorContext);

        //test updateContext
        $key = "age";
        $value = 20;
        $defaultStrategy->expects($this->once())->method('updateContext')->with($key, $value);

        $visitor->updateContext($key, $value);

        //test updateContextCollection

        $visitor->updateContextCollection($visitorContext);

        //Test clearContext
        $defaultStrategy->expects($this->once())->method('clearContext');
        $visitor->clearContext();

        //Test authenticate
        $newVisitorId = "newVisitorId";
        $defaultStrategy->expects($this->once())->method('authenticate')->with($newVisitorId);
        $visitor->authenticate($newVisitorId);

        //Test unauthenticate
        $defaultStrategy->expects($this->once())->method('unauthenticate');
        $visitor->unauthenticate();


        //Test sendHit
        $hit = new Page("http://localhost");
        $defaultStrategy->expects($this->once())->method('sendHit')->with($hit);

        $visitor->sendHit($hit);

        //Test fetchFlags
        $defaultStrategy->expects($this->once())->method('fetchFlags');
        $visitor->fetchFlags();

        //Test userExposed
        $key = 'key';
        $flagDTO = new FlagDTO();
        $defaultStrategy->expects($this->once())->method('visitorExposed')->with($key, true, $flagDTO);
        $visitor->visitorExposed($key, true, $flagDTO);

        //Test getFlagValue
        $flagDTO = new FlagDTO();
        $defaultValue = "defaultValue";
        $defaultStrategy->expects($this->once())->method('getFlagValue')->with($key, $defaultValue, $flagDTO, true);
        $visitor->getFlagValue($key, $defaultValue, $flagDTO);

        //Test getFlagMetadata
        $flagDTO = new FlagDTO();

        $defaultStrategy->expects($this->exactly(1))->method('getFlagMetadata')->with($key, $flagDTO);

        $visitor->getFlagMetadata($key, $flagDTO);

        //Test getFlag
        $flagDTO = new FlagDTO();
        $flagDTO->setKey("key1")->setCampaignId('campaignID')->setVariationGroupId("varGroupID")->setVariationId('varID')->setIsReference(true)->setValue("value")->setCampaignType("ab");

        $flagsDTO = [$flagDTO];
        $visitor->setFlagsDTO($flagsDTO);
        $flag = $visitor->getFlag('key1');

        //Test getFlag null
        $flag = $visitor->getFlag('key2');

        //Test getFlags
        $flags = $visitor->getFlags();


        //Test  flag warning
        $config->setLogManager($logManagerStub);
        $logManagerStub->expects($this->exactly(6))->method('warning');
        $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::VISITOR_CREATED));
        $visitor->getFlag('key1');
        $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::UPDATE_CONTEXT));

        $visitor->getFlag('key1');
        $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::AUTHENTICATE));
        $visitor->getFlag('key1');
        $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::UNAUTHENTICATE));
        $visitor->getFlag('key1');
        $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::FETCH_ERROR));
        $visitor->getFlag('key1');
        $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::FLAGS_FETCHED_FROM_CACHE));
        $visitor->getFlag('key1');
        $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCHED, FSFetchReason::NONE));
        $visitor->getFlag('key1');
    }

    public function testJson()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitor_id";
        $context = [
                    "age"                        => 20,
                    "sdk_osName"                 => PHP_OS,
                    "sdk_deviceType"             => "server",
                    FlagshipConstant::FS_CLIENT  => FlagshipConstant::SDK_LANGUAGE,
                    FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
                    FlagshipConstant::FS_USERS   => $visitorId,
                   ];
        $trackerManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            [],
            "",
            false
        );

        $decisionManager = $this->getMockBuilder(ApiManager::class)->disableOriginalConstructor()->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $context, true);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                         'visitorId'  => $visitorId,
                         'context'    => $context,
                         'hasConsent' => true,
                        ]),
            json_encode($visitorDelegate)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testGetStrategy()
    {
        $config = new DecisionApiConfig();
        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'config', $config);

        $setStatusMethod = Utils::getMethod($instance, 'setStatus');
        $setStatusMethod->invoke($instance, FSSdkStatus::SDK_NOT_INITIALIZED);

        $trackerManager = $this->getMockBuilder('Flagship\Api\TrackingManager')->onlyMethods(['addHit'])->disableOriginalConstructor()->getMock();

        $config = new DecisionApiConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20];

        $decisionManager = $this->getMockBuilder(ApiManager::class)->disableOriginalConstructor()->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $context);

        $getStrategyMethod = Utils::getMethod($visitorDelegate, 'getStrategy');
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\NotReadyStrategy', $strategy);

        $setStatusMethod->invoke($instance, FSSdkStatus::SDK_PANIC);
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\PanicStrategy', $strategy);

        $setStatusMethod->invoke($instance, FSSdkStatus::SDK_INITIALIZED);
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\NoConsentStrategy', $strategy);

        $setStatusMethod->invoke($instance, FSSdkStatus::SDK_INITIALIZED);
        $visitorDelegate->setConsent(true);
        $strategy = $getStrategyMethod->invoke($visitorDelegate);

        $this->assertInstanceOf('Flagship\Visitor\DefaultStrategy', $strategy);
    }
}

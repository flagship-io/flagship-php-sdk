<?php

declare(strict_types=1);

namespace Flagship\Visitor;

require_once __dir__ . '/../Assets/Round.php';

use DateTime;
use Exception;
use Flagship\Hit\Item;
use Flagship\Hit\Page;
use Flagship\Hit\Event;
use Flagship\Hit\Screen;
use Flagship\Enum\HitType;
use Flagship\Hit\Activate;
use Flagship\Hit\UsageHit;
use Flagship\Enum\LogLevel;
use Flagship\Model\FlagDTO;
use Psr\Log\LoggerInterface;
use Flagship\Hit\Transaction;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\MurmurHash;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSFetchReason;
use Flagship\Enum\FSFetchStatus;
use Flagship\Model\HttpResponse;
use Flagship\Decision\ApiManager;
use Flagship\Flag\FSFlagMetadata;
use Flagship\Utils\ConfigManager;
use Flagship\Enum\FlagshipContext;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\BucketingConfig;
use Flagship\Enum\VisitorCacheStatus;
use Flagship\Config\DecisionApiConfig;
use Flagship\Utils\ContainerInterface;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Utils\HttpClientInterface;
use Flagship\Api\TrackingManagerAbstract;
use PHPUnit\Framework\MockObject\MockObject;
use Flagship\Cache\IVisitorCacheImplementation;


class DefaultStrategyTest extends TestCase
{
    use CampaignsData;

    /**
     * @return \array[][]|FlagDTO[]
     */
    public function modifications(): array
    {
        return [
                (new FlagDTO())->setKey('background')->setValue('EE3300')->setIsReference(false)->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')->setCampaignId('c1e3t1nvfu1ncqfcdco0')->setVariationId('c1e3t1nvfu1ncqfcdcq0'),
                (new FlagDTO())->setKey('borderColor')->setValue('blue')->setIsReference(false)->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
                (new FlagDTO())->setKey('Null')->setValue(null)->setIsReference(false)->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
                (new FlagDTO())->setKey('Empty')->setValue("")->setIsReference(false)->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
                (new FlagDTO())->setKey('isBool')->setValue(false)->setIsReference(false)->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
                (new FlagDTO())->setKey('Number')->setValue(5)->setIsReference(false)->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
               ];
    }


    public function testUpdateContext()
    {
        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );
        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);


        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        /**
         * @var ApiManager|MockObject $decisionManager
         */
        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var TrackingManagerAbstract|MockObject $trackingManager
         */
        $trackingManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendHit'],
            '',
            false
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);
        //Test number value
        $ageKey = 'age';
        $newAge = 45;
        $defaultStrategy->updateContext($ageKey, $newAge);
        $context = $visitor->getContext();
        $this->assertArrayHasKey($ageKey, $context);
        $this->assertEquals($newAge, $context[$ageKey]);
        $this->assertSame(FSFetchReason::UPDATE_CONTEXT, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitor->getFetchStatus()->getStatus());

        //Test bool value
        $isAdminKey = "isAdmin";
        $isAdmin = true;
        $defaultStrategy->updateContext($isAdminKey, $isAdmin);
        $context = $visitor->getContext();
        $this->assertArrayHasKey($isAdminKey, $context);
        $this->assertEquals($isAdmin, $context[$isAdminKey]);

        //Test string value
        $townKey = "town";
        $town = 'visitor_town';

        $defaultStrategy->updateContext($townKey, $town);
        $context = $visitor->getContext();
        $this->assertArrayHasKey($townKey, $context);
        $this->assertEquals($town, $context[$townKey]);

        //Predefined Context

        $deviceLocation = "here";

        $defaultStrategy->updateContext(FlagshipContext::DEVICE_LOCALE, $deviceLocation);
        $context = $visitor->getContext();

        $this->assertArrayHasKey("sdk_deviceLanguage", $context);
        $this->assertEquals($deviceLocation, $context["sdk_deviceLanguage"]);

        //Test predefined with different type

        $deviceType = 10.5;

        $defaultStrategy->updateContext(FlagshipContext::LOCATION_REGION, $deviceType);
        $context = $visitor->getContext();

        $this->assertArrayNotHasKey("sdk_region", $context);

        //Test predefined fs_

        $defaultStrategy->updateContext(FlagshipContext::FLAGSHIP_VERSION, "2");

        $this->assertSame($context, $visitor->getContext());

        //Test value!= string,number, bool
        $this->assertArrayNotHasKey('extra_info', $visitor->getContext());


        $visitor->setContext([]);


        //Test key is empty
        $defaultStrategy->updateContext("", "Hp");
        $this->assertCount(0, $visitor->getContext());

        //Test value is empty
        $visitor->updateContext("computer", "");
        $this->assertCount(1, $visitor->getContext());

        //Test value and key are empty
        $visitor->updateContext("", "");
        $this->assertCount(1, $visitor->getContext());
    }

    public function testUpdateContextCollection()
    {
        $configData = [
                       'envId'  => 'env_value',
                       'apiKey' => 'key_value',
                      ];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];
        /**
         * @var ApiManager|MockObject $decisionManager
         */
        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /**
         * @var TrackingManagerAbstract|MockObject $trackingManager
         */
        $trackingManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendHit'],
            '',
            false
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);
        $newVisitorContext = [
                              'vip'    => true,
                              'gender' => 'F',
                             ];
        $defaultStrategy->updateContextCollection($newVisitorContext);
        $this->assertCount(8, $visitor->getContext());

        //Test without Key

        $newVisitorContext = ['vip'];

        $defaultStrategy->updateContextCollection($newVisitorContext);
        $this->assertCount(8, $visitor->getContext());
    }

    public function testClearContext()
    {
        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];
        $config = new DecisionApiConfig('envId', 'apiKey');
        /**
         * @var ApiManager|MockObject $decisionManager
         */
        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        /**
         * @var TrackingManagerAbstract|MockObject $trackingManager
         */
        $trackingManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendHit'],
            '',
            false
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $this->assertCount(6, $visitor->getContext());

        $defaultStrategy->clearContext();

        $this->assertCount(0, $visitor->getContext());
        $this->assertSame(FSFetchReason::UPDATE_CONTEXT, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitor->getFetchStatus()->getStatus());
        $this->assertTrue($visitor->getHasContextBeenUpdated());

        //Test clearContext with empty context
        $visitor->setHasContextBeenUpdated(false);
        $defaultStrategy->clearContext();
        $this->assertCount(0, $visitor->getContext());
        $this->assertFalse($visitor->getHasContextBeenUpdated());
    }

    public function testAuthenticate()
    {
        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            "",
            true,
            true,
            true,
            ['error', 'warning']
        );

        /**
         * @var TrackingManagerAbstract|MockObject $trackerManager
         */
        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );

        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        /**
         * @var ApiManager|MockObject $decisionManager
         */
        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $authenticateName = "authenticate";
        $logManagerStub->expects($this->exactly(3))->method('error')->with(
            $this->logicalOr(
                sprintf(
                    FlagshipConstant::VISITOR_ID_ERROR,
                    $authenticateName
                ),
                sprintf(
                    FlagshipConstant::FLAGSHIP_VISITOR_ALREADY_AUTHENTICATE,
                    $authenticateName
                ),
                sprintf(
                    FlagshipConstant::METHOD_DEACTIVATED_BUCKETING_ERROR,
                    $authenticateName
                )
            ),
            [FlagshipConstant::TAG => $authenticateName]
        );

        //Test authenticate with null visitorId

        $defaultStrategy = new DefaultStrategy($visitor);

        //Test authenticate with "" visitorId
        $defaultStrategy->authenticate("");
        $this->assertNull($visitor->getAnonymousId());
        $this->assertSame($visitorId, $visitor->getVisitorId());

        $newVisitorId = "new_visitor_id";
        $defaultStrategy->authenticate($newVisitorId);
        $this->assertSame($visitorId, $visitor->getAnonymousId());
        $this->assertSame($newVisitorId, $visitor->getVisitorId());
        $this->assertSame(FSFetchReason::AUTHENTICATE, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitor->getFetchStatus()->getStatus());

        //
        $newVisitorId2 = "new_visitor_id_2";
        $defaultStrategy->authenticate($newVisitorId2);
        $this->assertSame($visitorId, $visitor->getAnonymousId());
        $this->assertSame($newVisitorId, $visitor->getVisitorId());
    }

    public function testAuthenticateBucketingMode()
    {
        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            "",
            true,
            true,
            true,
            ['error', 'warning']
        );

        /**
         * @var TrackingManagerAbstract|MockObject $trackerManager
         */
        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        /**
         * @var IVisitorCacheImplementation|MockObject $visitorCache
         */
        $visitorCache = $this->getMockForAbstractClass(
            IVisitorCacheImplementation::class,
            [],
            '',
            false
        );

        $config = new BucketingConfig('http:127.0.0.1:3000', 'envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        /**
         * @var ApiManager|MockObject $decisionManager
         */
        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $authenticateName = "authenticate";

        $logManagerStub->expects($this->exactly(1))
            ->method('warning')
            ->with(
                $this->logicalOr(
                    sprintf(
                        FlagshipConstant::XPC_BUCKETING_WARNING,
                        $authenticateName
                    ),
                ),
                [FlagshipConstant::TAG => $authenticateName]
            );

        //Test authenticate with null visitorId

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->authenticate("new_visitor_id_xpc");

        $config->setVisitorCacheImplementation($visitorCache);

        $newVisitorId = "new_visitor_id";
        $defaultStrategy->authenticate($newVisitorId);
        $this->assertSame($visitorId, $visitor->getAnonymousId());
        $this->assertSame($newVisitorId, $visitor->getVisitorId());
        $this->assertSame(FSFetchReason::AUTHENTICATE, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitor->getFetchStatus()->getStatus());

        //
        $newVisitorId2 = "new_visitor_id_2";
        $defaultStrategy->authenticate($newVisitorId2);
        $this->assertSame($visitorId, $visitor->getAnonymousId());
        $this->assertSame($newVisitorId, $visitor->getVisitorId());
    }

    public function testUnauthenticate()
    {
        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        /**
         * @var TrackingManagerAbstract|MockObject $trackerManager
         */
        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );



        $visitorId = "visitor_id";

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        /**
         * @var ApiManager|MockObject $decisionManager
         */
        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $config->setLogManager($logManagerStub);
        $visitor->setConfig($config);
        // $visitor->setConfig((new BucketingConfig("http://127.0.0.1:3000"))->setLogManager($logManagerStub));

        $unauthenticateName = "unauthenticate";
        $logManagerStub->expects($this->exactly(1))
            ->method('error')
            ->with(
                $this->logicalOr(
                    FlagshipConstant::FLAGSHIP_VISITOR_NOT_AUTHENTIFICATE
                ),
                FlagshipConstant::FLAGSHIP_VISITOR_NOT_AUTHENTIFICATE
            ),
            [FlagshipConstant::TAG => $unauthenticateName]
        );

        $defaultStrategy = new DefaultStrategy($visitor);
        $defaultStrategy->unauthenticate();

        //Test valid data
        $newVisitorId = "newVisitorId";
        $defaultStrategy->authenticate($newVisitorId);

        $anonymous = $visitor->getAnonymousId();
        $defaultStrategy->unauthenticate();
        $this->assertNull($visitor->getAnonymousId());
        $this->assertSame($anonymous, $visitor->getVisitorId());
        $this->assertSame(FSFetchReason::UNAUTHENTICATE, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitor->getFetchStatus()->getStatus());
    }

    public function testUnauthenticateBucketingMode()
    {
        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error', 'warning']
        );

        /**
         * @var TrackingManagerAbstract|MockObject $trackerManager
         */
        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );

        /**
         * @var IVisitorCacheImplementation|MockObject $visitorCache
         */
        $visitorCache = $this->getMockForAbstractClass(
            IVisitorCacheImplementation::class,
            [],
            '',
            false
        );


        $visitorId = "visitor_id";

        $config = new BucketingConfig("http://127.0.0.1:3000", 'envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        /**
         * @var ApiManager|MockObject $decisionManager
         */
        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $config->setLogManager($logManagerStub);
        $visitor->setConfig($config);

        $unauthenticateName = "unauthenticate";

        $logManagerStub->expects($this->exactly(1))
            ->method('warning')
            ->with(
                $this->logicalOr(
                    FlagshipConstant::XPC_BUCKETING_WARNING
                ),
                [FlagshipConstant::TAG => $unauthenticateName]
            );


        $defaultStrategy = new DefaultStrategy($visitor);
        $defaultStrategy->unauthenticate();

        $config->setVisitorCacheImplementation($visitorCache);

        //Test valid data
        $newVisitorId = "newVisitorId";
        $defaultStrategy->authenticate($newVisitorId);

        $anonymous = $visitor->getAnonymousId();
        $defaultStrategy->unauthenticate();
        $this->assertNull($visitor->getAnonymousId());
        $this->assertSame($anonymous, $visitor->getVisitorId());
        $this->assertSame(FSFetchReason::UNAUTHENTICATE, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitor->getFetchStatus()->getStatus());
    }

    public function testFetchFlags()
    {
        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setDisableDeveloperUsageTracking(true);
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false,
            false,
            true,
            ["setTroubleshootingData"]
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $httpClientMock->expects($this->once())->method("post")->willReturn(new HttpResponse(200, $this->campaigns()));
        $trackingManagerMock->expects($this->once())->method("setTroubleshootingData")->with(null);

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $configManager->setDecisionManager($decisionManager)->setTrackingManager($trackingManagerMock);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->fetchFlags();

        $modifications = $this->campaignsModifications();

        $this->assertJsonStringEqualsJsonString(json_encode($modifications), json_encode($visitor->getFlagsDTO()));
        $this->assertSame(FSFetchReason::NONE, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCHED, $visitor->getFetchStatus()->getStatus());
    }

    public function testFetchFlagsTroubleshootingData()
    {
        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false,
            false,
            true,
            [
             "setTroubleshootingData",
             "addTroubleshootingHit",
            ]
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $httpResponseBody = $this->campaigns();
        $troubleshootingData = [
                                "startDate" => "2023-04-13T09:33:38.049Z",
                                "endDate"   => "2023-04-13T10:03:38.049Z",
                                "timezone"  => "Europe/Paris",
                                "traffic"   => 40,
                               ];

        $httpResponseBody["extras"] = [
                                       "accountSettings" => [
                                                             "@type"           => "type.googleapis.com/flagship.protobuf.AccountSettings",
                                                             "enabledXPC"      => false,
                                                             "enabled1V1T"     => false,
                                                             "troubleshooting" => $troubleshootingData,
                                                            ],
                                      ];

        $httpClientMock->expects($this->exactly(3))->method("post")->willReturn(new HttpResponse(200, $httpResponseBody));

        $trackingManagerMock->expects($this->exactly(3))->method("setTroubleshootingData")->with($this->callback(function ($param) use ($troubleshootingData) {
                $startDate = new DateTime($troubleshootingData['startDate']);
                $endDate = new DateTime($troubleshootingData['endDate']);
                return $param->getTraffic() === $troubleshootingData['traffic'] &&
                    $param->getTimezone() === $troubleshootingData['timezone'] &&
                    $param->getStartDate()->getTimestamp() === $startDate->getTimestamp() &&
                    $param->getEndDate()->getTimestamp() === $endDate->getTimestamp();
        }));

        $matcher = $this->exactly(6);
        $trackingManagerMock->expects($matcher)->method("addTroubleshootingHit")->with(
            $this->logicalOr(
                $this->callback(function ($param) {
                        return $param->getLabel() === TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS ||
                            $param->getLabel() === TroubleshootingLabel::VISITOR_SEND_HIT;
                })
            )
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackingManagerMock);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategyMock = $this->getMockForAbstractClass(
            "Flagship\Visitor\DefaultStrategy",
            [$visitor],
            "",
            true,
            false,
            true,
            ["sendSdkConfigAnalyticHit"]
        );

        $defaultStrategyMock->expects($this->exactly(2))->method("sendSdkConfigAnalyticHit");

        $defaultStrategyMock->setMurmurHash(new MurmurHash());

        $defaultStrategyMock->fetchFlags();

        $defaultStrategyMock->fetchFlags();

        //Test send consent Troubleshooting

        $defaultStrategyMock->setConsent(true);

        $config = new BucketingConfig('envId', 'apiKey');

        $configManager = new ConfigManager($config, $decisionManager, $trackingManagerMock);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategyMock = $this->getMockForAbstractClass(
            "Flagship\Visitor\DefaultStrategy",
            [$visitor],
            "",
            true,
            false,
            true,
            ["sendSdkConfigAnalyticHit"]
        );

        $defaultStrategyMock->setMurmurHash(new MurmurHash());

        $defaultStrategyMock->fetchFlags();
    }


    public function testSendHit()
    {
        $config = new DecisionApiConfig();
        /**
         * @var MockObject|TrackingManagerAbstract $trackerManagerMock
         */
        $trackerManagerMock = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [
             $config,
             new HttpClient(),
            ],
            '',
            true,
            true,
            true,
            ['addHit']
        );

        $envId = "envId";
        $apiKey = "apiKey";
        $visitorId = "visitorId";

        $config = new DecisionApiConfig($envId, $apiKey);

        $apiManager = new ApiManager(new HttpClient(), $config);
        $configManager = new ConfigManager($config, $apiManager, $trackerManagerMock);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, true);
        $defaultStrategy = new DefaultStrategy($visitor);

        $pageUrl = 'https://locahost';
        $page = new Page($pageUrl);

        $screenName = 'ScreenName';
        $screen = new Screen($screenName);

        $transitionId = "transitionId";
        $transitionAffiliation = "transitionAffiliation";
        $transition = new Transaction($transitionId, $transitionAffiliation);

        $eventCategory = EventCategory::ACTION_TRACKING;
        $eventAction = "eventAction";

        $event = new Event($eventCategory, $eventAction);

        $itemName = "itemName";
        $itemCode = "itemCode";

        $item = new Item($transitionId, $itemName, $itemCode);

        $trackerManagerMock->expects($this->exactly(5))->method('addHit')->with(
            $this->logicalOr($page, $screen, $transition, $event, $item)
        );

        //Test type page
        $defaultStrategy->sendHit($page);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $page->getVisitorId()); // test abstract class property
        $this->assertSame(HitType::PAGE_VIEW, $page->getType()); // test abstract class property

        $this->assertSame($pageUrl, $page->getPageUrl());

        // Test type screen
        $defaultStrategy->sendHit($screen);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $screen->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::SCREEN_VIEW, $screen->getType());
        $this->assertSame($screenName, $screen->getScreenName());

        //Test type Transition
        $defaultStrategy->sendHit($transition);
        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $transition->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::TRANSACTION, $transition->getType());

        //Test type Event
        $defaultStrategy->sendHit($event);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $event->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::EVENT, $event->getType());

        //Test type Item
        $defaultStrategy->sendHit($item);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $item->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::ITEM, $item->getType());
    }

    public function testSendHitWithLog()
    {
        $config = new DecisionApiConfig();
        $trackerManagerMock = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [
             $config,
             new HttpClient(),
            ],
            '',
            true,
            true,
            true,
            ['sendHit']
        );

        $logManagerMock = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $envId = "envId";
        $apiKey = "apiKey";
        $visitorId = "visitorId";

        $config = new DecisionApiConfig($envId, $apiKey);

        $config->setLogManager($logManagerMock);

        $apiManager = new ApiManager(new HttpClient(), $config);
        $configManager = new ConfigManager($config, $apiManager, $trackerManagerMock);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, true, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $page = new Page("");

        $logManagerMock->expects($this->exactly(1))->method('error')->with(
            $page->getErrorMessage(),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
        );

        $trackerManagerMock->expects($this->never())->method('sendHit');

        $defaultStrategy->sendHit($page);
    }

    public function testUserExposed()
    {
        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            [
             'error',
             'info',
            ]
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        /**
         * @var MockObject|TrackingManagerAbstract $trackerManagerStub
         */
        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [
             $config,
             new HttpClient(),
            ],
            '',
            true,
            true,
            true,
            ['activateFlag']
        );

        $apiManager = new ApiManager(new HttpClient(), $config);

        $configManager = new ConfigManager($config, $apiManager, $trackerManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $key = "key";
        $flagDTO = new FlagDTO();
        $flagDTO->setKey($key)->setCampaignId("campaignId")->setVariationGroupId("variationGroupId")->setVariationId("variationId")->setIsReference(false)->setCampaignType("campaignType")->setSlug("slug")->setCampaignName("campaignName")->setVariationGroupName("variationGroupName")->setVariationName("variationName")->setValue("value");
        $defaultValue = "default";

        $flagMetadata = new FSFlagMetadata(
            $flagDTO->getCampaignId(),
            $flagDTO->getVariationGroupId(),
            $flagDTO->getVariationId(),
            $flagDTO->getIsReference(),
            $flagDTO->getCampaignType(),
            $flagDTO->getSlug(),
            $flagDTO->getCampaignName(),
            $flagDTO->getVariationGroupName(),
            $flagDTO->getVariationName()
        );

        $activate = new Activate($flagDTO->getVariationGroupId(), $flagDTO->getVariationId());
        $activate->setFlagKey($flagDTO->getKey())->setFlagValue($flagDTO->getValue())->setFlagDefaultValue($defaultValue)->setFlagMetadata($flagMetadata)->setVisitorContext($visitor->getContext())->setVisitorId($visitor->getVisitorId())->setConfig($config);

        $trackerManagerStub->expects($this->exactly(2))
            ->method('activateFlag')
            ->with($activate);

        $defaultStrategy->visitorExposed($key, $defaultValue, $flagDTO, true);

        //Test defaultValue null

        $activate->setFlagDefaultValue(null);
        $defaultStrategy->visitorExposed($key, null, $flagDTO, true);

        $functionName = FlagshipConstant::FLAG_USER_EXPOSED;

        $logManagerStub->expects($this->exactly(3))->method('info')->with(
            $this->logicalOr(
                sprintf(
                    FlagshipConstant::USER_EXPOSED_NO_FLAG_ERROR,
                    $visitor->getVisitorId(),
                    $key
                ),
                sprintf(
                    FlagshipConstant::USER_EXPOSED_CAST_ERROR,
                    $visitor->getVisitorId(),
                    $key
                ),
                sprintf(
                    FlagshipConstant::VISITOR_EXPOSED_VALUE_NOT_CALLED,
                    $visitor->getVisitorId(),
                    $key
                )
            ),
            [FlagshipConstant::TAG => $functionName]
        );

        //Test flag null
        $activate->setFlagDefaultValue($defaultValue);
        $defaultStrategy->visitorExposed($key, $defaultValue, null, true);

        //Test flag with different type
        $activate->setFlagDefaultValue(false);
        $defaultStrategy->visitorExposed($key, false, $flagDTO, true);

        //Test flag when getValue() has not been called first
        $activate->setFlagDefaultValue($defaultValue);
        $defaultStrategy->visitorExposed($key, $defaultValue, $flagDTO, false);
    }

    public function testGetFlagValue()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            [
             'error',
             'info',
            ]
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);


        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [
             $config,
             new HttpClient(),
            ],
            '',
            true,
            true,
            true,
            ['activateFlag']
        );

        $apiManager = new ApiManager(new HttpClient(), $config);

        $configManager = new ConfigManager($config, $apiManager, $trackerManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $key = "key";
        $defaultValue = "defaultValue";
        $flagDTO = new FlagDTO();
        $flagDTO->setKey($key)->setCampaignId("campaignId")->setVariationGroupId("variationGroupId")->setVariationId("variationId")->setIsReference(false)->setCampaignType("campaignType")->setSlug("slug")->setCampaignName("campaignName")->setVariationGroupName("variationGroupName")->setVariationName("variationName")->setValue("value");

        $flagMetadata = new FSFlagMetadata(
            $flagDTO->getCampaignId(),
            $flagDTO->getVariationGroupId(),
            $flagDTO->getVariationId(),
            $flagDTO->getIsReference(),
            $flagDTO->getCampaignType(),
            $flagDTO->getSlug(),
            $flagDTO->getCampaignName(),
            $flagDTO->getVariationGroupName(),
            $flagDTO->getVariationName()
        );

        $activate = new Activate($flagDTO->getVariationGroupId(), $flagDTO->getVariationId());
        $activate->setFlagKey($flagDTO->getKey())->setFlagValue($flagDTO->getValue())->setFlagDefaultValue($defaultValue)->setFlagMetadata($flagMetadata)->setVisitorContext($visitor->getContext())->setVisitorId($visitor->getVisitorId())->setConfig($config);

        $trackerManagerStub->expects($this->exactly(4))->method('activateFlag')->with($activate);

        $value = $defaultStrategy->getFlagValue($key, $defaultValue, $flagDTO);
        $this->assertEquals($value, $flagDTO->getValue());

        //Test with default value is null

        $activate->setFlagDefaultValue(null);
        $value = $defaultStrategy->getFlagValue($key, null, $flagDTO);
        $this->assertEquals($value, $flagDTO->getValue());

        $logManagerStub->expects($this->exactly(2))->method('info');

        // Test flag null
        $activate->setFlagDefaultValue($defaultValue);
        $value = $defaultStrategy->getFlagValue($key, $defaultValue, null);
        $this->assertEquals($value, $defaultValue);

        // Test flag with different type

        $defaultValue = 12;

        $activate->setFlagDefaultValue($defaultValue);
        $value = $defaultStrategy->getFlagValue($key, $defaultValue, $flagDTO);
        $this->assertEquals($value, $defaultValue);

        // Test flag with value null
        $activate->setFlagValue(null)->setFlagDefaultValue($defaultValue);
        $flagDTO->setValue(null);
        $value = $defaultStrategy->getFlagValue($key, $defaultValue, $flagDTO);

        $this->assertEquals($value, $defaultValue);

        // Test flag with value null
        $flagDTO->setValue(null);
        $value = $defaultStrategy->getFlagValue($key, $defaultValue, $flagDTO, false);

        $this->assertEquals($value, $defaultValue);
    }

    public function testGetFlagMetadata()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            [
             'error',
             'info',
            ]
        );

        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false,
            false,
            true,
            [
             "setTroubleshootingData",
             "addTroubleshootingHit",
            ]
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $apiManager = new ApiManager(new HttpClient(), $config);

        $configManager = new ConfigManager($config, $apiManager, $trackingManagerMock);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $key = "key";
        $campaignId = "campaignID";
        $varGroupId = "varGroupID";
        $varId = "varID";
        $isReference = true;
        $campaignType = "ab";
        $slug = "slug";
        $campaignName = "campaignName";
        $varGrpName = "varGrpName";
        $varName = "varName";
        $metadata = new FSFlagMetadata(
            $campaignId,
            $varGroupId,
            $varId,
            $isReference,
            $campaignType,
            $slug,
            $campaignName,
            $varGrpName,
            $varName
        );

        $flagDTO = new FlagDTO();
        $flagDTO->setKey($key)->setValue("value")->setCampaignId($campaignId)->setVariationGroupId($varGroupId)->setVariationId($varId)->setIsReference($isReference)->setCampaignType($campaignType)->setSlug($slug)->setCampaignName($campaignName)->setVariationGroupName($varGrpName)->setVariationName($varName);

        $metadataValue = $defaultStrategy->getFlagMetadata($key, $flagDTO);
        $this->assertEquals($metadata, $metadataValue);

        //Test flag null
        $metadataValue = $defaultStrategy->getFlagMetadata($key);
        $this->assertEquals(FSFlagMetadata::getEmpty(), $metadataValue);
    }

    public function testLookupVisitor()
    {
        $config = new DecisionApiConfig('envId', 'apiKey');

        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            "",
            true,
            true,
            true,
            [
             'error',
             'info',
            ]
        );

        $config->setLogManager($logManagerStub);

        $VisitorCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IVisitorCacheImplementation",
            [],
            "",
            true,
            true,
            true,
            ['lookupVisitor']
        );

        /**
         * @var ApiManager|MockObject $apiManager
         */
        $apiManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var TrackingManagerAbstract|MockObject $trackingManagerMock
         */
        $trackingManagerMock = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendHit'],
            '',
            false
        );

        $configManager = new ConfigManager($config, $apiManager, $trackingManagerMock);

        $container = new Container();

        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $visitorCache1 = [StrategyAbstract::VERSION => 1];

        $differentVisitorId = "different visitorID";
        $visitorCache2 = [
                          StrategyAbstract::VERSION => 1,
                          StrategyAbstract::DATA    => [StrategyAbstract::VISITOR_ID => $differentVisitorId],
                         ];

        $visitorCache3 = [
                          StrategyAbstract::VERSION => 1,
                          StrategyAbstract::DATA    => [StrategyAbstract::VISITOR_ID => $visitorId],
                         ];

        $visitorCache4 = [
                          StrategyAbstract::VERSION => 1,
                          StrategyAbstract::DATA    => [
                                                        StrategyAbstract::VISITOR_ID => $visitorId,
                                                        StrategyAbstract::CAMPAIGNS  => "not an array",
                                                       ],
                         ];
        $visitorCache5 = [
                          StrategyAbstract::VERSION => 1,
                          StrategyAbstract::DATA    => [
                                                        StrategyAbstract::VISITOR_ID => $visitorId,
                                                        StrategyAbstract::CAMPAIGNS  => ["anythings"],
                                                       ],
                         ];

        $visitorCache6 = [
                          StrategyAbstract::VERSION => 1,
                          StrategyAbstract::DATA    => [
                                                        StrategyAbstract::VISITOR_ID => $visitorId,
                                                        StrategyAbstract::CAMPAIGNS  => [
                                                                                         [
                                                                                          FlagshipField::FIELD_CAMPAIGN_ID        => "c8pimlr7n0ig3a0pt2ig",
                                                                                          FlagshipField::FIELD_VARIATION_GROUP_ID => "c8pimlr7n0ig3a0pt2jg",
                                                                                          FlagshipField::FIELD_VARIATION_ID       => "c8pimlr7n0ig3a0pt2kg",
                                                                                          FlagshipField::FIELD_IS_REFERENCE       => false,
                                                                                          FlagshipField::FIELD_CAMPAIGN_TYPE      => "ab",
                                                                                          StrategyAbstract::ACTIVATED             => false,
                                                                                          StrategyAbstract::FLAGS                 => [
                                                                                                                                      "Number"      => 5,
                                                                                                                                      "isBool"      => false,
                                                                                                                                      "background"  => "EE3300",
                                                                                                                                      "borderColor" => "blue",
                                                                                                                                      "Null"        => null,
                                                                                                                                      "Empty"       => "",
                                                                                                                                     ],
                                                                                         ],
                                                                                        ],
                                                       ],
                         ];

        $visitorCache7 = [
            StrategyAbstract::VERSION => 2,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::CAMPAIGNS => [
                    "anythings"
                ]
            ]
        ];

        /**
         * @var IVisitorCacheImplementation|MockObject $VisitorCacheImplementationMock
         */
        $VisitorCacheImplementationMock->expects($this->exactly(8))
            ->method("lookupVisitor")
            ->with($visitorId)
            ->willReturnOnConsecutiveCalls(
                [],
                $visitorCache1,
                $visitorCache2,
                $visitorCache3,
                $visitorCache4,
                $visitorCache5,
                $visitorCache6,
                $visitorCache7
            );
        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);

        $functionName = "lookupVisitor";

        $logManagerStub->expects($this->exactly(6))->method('error')
            ->with(
                $this->logicalOr(
                    StrategyAbstract::LOOKUP_VISITOR_JSON_OBJECT_ERROR,
                    sprintf(StrategyAbstract::VISITOR_ID_MISMATCH_ERROR, $differentVisitorId, $visitorId)
                ),
                [FlagshipConstant::TAG => $functionName]
            );

        $this->assertCount(0, $visitor->visitorCache);

        // test return empty array
        $defaultStrategy->lookupVisitor();

        $this->assertEquals($visitor->getVisitorCacheStatus(), VisitorCacheStatus::NONE);

        $this->assertCount(0, $visitor->visitorCache);

        // test return array["version"=>1] only
        $defaultStrategy->lookupVisitor();

        $this->assertEquals($visitor->getVisitorCacheStatus(), VisitorCacheStatus::VISITOR_ID_CACHE);

        $this->assertCount(0, $visitor->visitorCache);

        // test return cache with different visitor id

        $defaultStrategy->lookupVisitor();

        $this->assertEquals($visitor->getVisitorCacheStatus(), VisitorCacheStatus::VISITOR_ID_CACHE);

        $this->assertCount(0, $visitor->visitorCache);


        // test return cache without campaings

        $defaultStrategy->lookupVisitor();

        $this->assertEquals($visitor->getVisitorCacheStatus(), VisitorCacheStatus::VISITOR_ID_CACHE);

        $this->assertCount(0, $visitor->visitorCache);

        // test return cache with is_array(campaings) === false

        $defaultStrategy->lookupVisitor();

        $this->assertEquals($visitor->getVisitorCacheStatus(), VisitorCacheStatus::VISITOR_ID_CACHE);

        $this->assertCount(0, $visitor->visitorCache);

        // test return cache with invalid campaigns

        $defaultStrategy->lookupVisitor();

        $this->assertEquals($visitor->getVisitorCacheStatus(), VisitorCacheStatus::VISITOR_ID_CACHE);

        $this->assertCount(0, $visitor->visitorCache);

        // test return cache with valid cache

        $defaultStrategy->lookupVisitor();

        $this->assertEquals($visitor->getVisitorCacheStatus(), VisitorCacheStatus::VISITOR_ID_CACHE);

        $this->assertSame($visitorCache6, $visitor->visitorCache);

        // 
        $defaultStrategy->lookupVisitor();

        $this->assertEquals(VisitorCacheStatus::VISITOR_ID_CACHE, $visitor->getVisitorCacheStatus(), );

        $this->assertSame($visitorCache6, $visitor->visitorCache);
    }

    public function testLookupVisitorXpc()
    {
        $config = new DecisionApiConfig('envId', 'apiKey');

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            "",
            true,
            true,
            true,
            ['error', 'info']
        );

        $config->setLogManager($logManagerStub);

        $VisitorCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IVisitorCacheImplementation",
            [],
            "",
            true,
            true,
            true,
            ['lookupVisitor']
        );

        /**
         * @var ApiManager|MockObject $apiManager
         */
        $apiManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var TrackingManagerAbstract|MockObject $trackingManagerMock
         */
        $trackingManagerMock = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendHit'],
            '',
            false
        );

        $configManager = new ConfigManager($config, $apiManager, $trackingManagerMock);

        $container = new Container();

        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $differentVisitorId = "different visitorID";

        $anonymousId = "anonymousId";

        $visitorCache = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::CAMPAIGNS => [
                    [
                        FlagshipField::FIELD_CAMPAIGN_ID => "c8pimlr7n0ig3a0pt2ig",
                        FlagshipField::FIELD_VARIATION_GROUP_ID => "c8pimlr7n0ig3a0pt2jg",
                        FlagshipField::FIELD_VARIATION_ID => "c8pimlr7n0ig3a0pt2kg",
                        FlagshipField::FIELD_IS_REFERENCE => false,
                        FlagshipField::FIELD_CAMPAIGN_TYPE => "ab",
                        StrategyAbstract::ACTIVATED => false,
                        StrategyAbstract::FLAGS => [
                            "Number" => 5,
                            "isBool" => false,
                            "background" => "EE3300",
                            "borderColor" => "blue",
                            "Null" => null,
                            "Empty" => ""
                        ]
                    ]
                ]
            ]
        ];
        $visitorCacheAnonymous = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $anonymousId,
                StrategyAbstract::CAMPAIGNS => [
                    [
                        FlagshipField::FIELD_CAMPAIGN_ID => $anonymousId . "c8pimlr7n0ig3a0pt2ig",
                        FlagshipField::FIELD_VARIATION_GROUP_ID => $anonymousId . "c8pimlr7n0ig3a0pt2jg",
                        FlagshipField::FIELD_VARIATION_ID => $anonymousId . "c8pimlr7n0ig3a0pt2kg",
                        FlagshipField::FIELD_IS_REFERENCE => false,
                        FlagshipField::FIELD_CAMPAIGN_TYPE => "ab",
                        StrategyAbstract::ACTIVATED => false,
                        StrategyAbstract::FLAGS => [
                            "Number" => 5,
                            "isBool" => false,
                            "background" => "EE3300",
                            "borderColor" => "blue",
                            "Null" => null,
                            "Empty" => ""
                        ]
                    ]
                ]
            ]
        ];

        /**
         * @var IVisitorCacheImplementation|MockObject $VisitorCacheImplementationMock
         */
        $VisitorCacheImplementationMock->expects($this->exactly(7))
            ->method("lookupVisitor")->willReturnCallback(function ($id) use ($visitorCache, $visitorId, $anonymousId, $visitorCacheAnonymous) {
                if ($id === $visitorId) {
                    return $visitorCache;
                }
                if ($id === $anonymousId) {
                    return $visitorCacheAnonymous;
                }

                return [];
            });

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);


        $this->assertCount(0, $visitor->visitorCache);


        $defaultStrategy->lookupVisitor();

        $this->assertSame($visitorCache, $visitor->visitorCache);
        $this->assertSame(VisitorCacheStatus::VISITOR_ID_CACHE, $visitor->getVisitorCacheStatus());

        $visitor->setAnonymousId($anonymousId);

        $defaultStrategy->lookupVisitor();

        $this->assertSame($visitorCache, $visitor->visitorCache);
        $this->assertEquals(VisitorCacheStatus::VISITOR_ID_CACHE_WITH_ANONYMOUS_ID_CACHE, $visitor->getVisitorCacheStatus());

        $visitor->setVisitorId("new_visitor_id");

        $defaultStrategy->lookupVisitor();
        $this->assertEquals(VisitorCacheStatus::ANONYMOUS_ID_CACHE, $visitor->getVisitorCacheStatus());
        $this->assertSame($visitorCacheAnonymous, $visitor->visitorCache);

        $visitor->setAnonymousId("another_anonymous_id");
        $visitor->setVisitorId("another_visitor_id");
        $defaultStrategy->lookupVisitor();

        $this->assertEquals(VisitorCacheStatus::NONE, $visitor->getVisitorCacheStatus());
        $this->assertCount(0, $visitor->visitorCache);
    }

    public function testCacheVisitor()
    {

        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        $config = new DecisionApiConfig('envId', 'apiKey');

        /**
         * @var HttpClientInterface|MockObject $httpClientMock
         */
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $campaignsData = $this->campaigns();
        $campaignsData2 = $this->campaigns2();

        $httpClientMock->expects($this->exactly(3))->method("post")->willReturnOnConsecutiveCalls(
            new HttpResponse(200, $campaignsData),
            new HttpResponse(200, $campaignsData2),
            new HttpResponse(200, $campaignsData2)
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            "",
            true,
            true,
            true,
            [
             'error',
             'info',
            ]
        );

        /**
         * @var TrackingManagerAbstract|MockObject $trackingManagerMock
         */
        $trackingManagerMock = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            [],
            "",
            false,
            false,
            true,
            [
             "setTroubleshootingData",
             "addTroubleshootingHit",
            ]
        );

        $config->setLogManager($logManagerStub);

        /**
         * @var IVisitorCacheImplementation|MockObject $VisitorCacheImplementationMock
         */
        $VisitorCacheImplementationMock = $this->getMockForAbstractClass(
            IVisitorCacheImplementation::class,
            [],
            "",
            true,
            true,
            true,
            [
             'lookupVisitor',
             'cacheVisitor',
            ]
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackingManagerMock);

        /**
         * @var ContainerInterface|MockObject $containerMock
         */
        $containerMock = $this->getMockForAbstractClass(
            ContainerInterface::class,
            ['get'],
            '',
            false
        );

        $containerGetMethod = function () {
            $args = func_get_args();
            $params = $args[1];
            return new DefaultStrategy($params[0]);
        };

        $containerMock->method('get')->willReturnCallback($containerGetMethod);
        $visitor = new VisitorDelegate(
            $containerMock,
            $configManager,
            $visitorId,
            false,
            $visitorContext,
            true
        );

        $assignmentsHistory = [];
        $campaigns = [];
        foreach ($campaignsData[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory[$campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]] = $variation[FlagshipField::FIELD_ID];

            $campaigns[] = [
                            FlagshipField::FIELD_CAMPAIGN_ID        => $campaign[FlagshipField::FIELD_ID],
                            FlagshipField::FIELD_SLUG               => $campaign[FlagshipField::FIELD_SLUG] ?? null,
                            FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                            FlagshipField::FIELD_VARIATION_ID       => $variation[FlagshipField::FIELD_ID],
                            FlagshipField::FIELD_IS_REFERENCE       => $variation[FlagshipField::FIELD_REFERENCE],
                            FlagshipField::FIELD_CAMPAIGN_TYPE      => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                            StrategyAbstract::ACTIVATED             => false,
                            StrategyAbstract::FLAGS                 => $modifications[FlagshipField::FIELD_VALUE],
                           ];
        }

        $visitorCache = [
                         StrategyAbstract::VERSION => 1,
                         StrategyAbstract::DATA    => [
                                                       StrategyAbstract::VISITOR_ID          => $visitorId,
                                                       StrategyAbstract::ANONYMOUS_ID        => $visitor->getAnonymousId(),
                                                       StrategyAbstract::CONSENT             => $visitor->hasConsented(),
                                                       StrategyAbstract::CONTEXT             => $visitor->getContext(),
                                                       StrategyAbstract::CAMPAIGNS           => $campaigns,
                                                       StrategyAbstract::ASSIGNMENTS_HISTORY => $assignmentsHistory,
                                                      ],
                        ];
        $assignmentsHistory2 = [];
        $campaigns2 = [];
        foreach ($campaignsData2[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory2[$campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]] = $variation[FlagshipField::FIELD_ID];

            $campaigns2[] = [
                             FlagshipField::FIELD_CAMPAIGN_ID        => $campaign[FlagshipField::FIELD_ID],
                             FlagshipField::FIELD_SLUG               => $campaign[FlagshipField::FIELD_SLUG] ?? null,
                             FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                             FlagshipField::FIELD_VARIATION_ID       => $variation[FlagshipField::FIELD_ID],
                             FlagshipField::FIELD_IS_REFERENCE       => $variation[FlagshipField::FIELD_REFERENCE],
                             FlagshipField::FIELD_CAMPAIGN_TYPE      => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                             StrategyAbstract::ACTIVATED             => false,
                             StrategyAbstract::FLAGS                 => $modifications[FlagshipField::FIELD_VALUE],
                            ];
        }

        $visitorCache2 = [
                          StrategyAbstract::VERSION => 1,
                          StrategyAbstract::DATA    => [
                                                        StrategyAbstract::VISITOR_ID          => $visitorId,
                                                        StrategyAbstract::ANONYMOUS_ID        => $visitor->getAnonymousId(),
                                                        StrategyAbstract::CONSENT             => $visitor->hasConsented(),
                                                        StrategyAbstract::CONTEXT             => $visitor->getContext(),
                                                        StrategyAbstract::CAMPAIGNS           => $campaigns2,
                                                        StrategyAbstract::ASSIGNMENTS_HISTORY => array_merge($assignmentsHistory, $assignmentsHistory2),
                                                       ],
                         ];

        $exception = new Exception("Message error");

        $VisitorCacheImplementationMock->expects($this->exactly(3))->method("cacheVisitor")->with(
            $this->logicalOr(
                $visitorId
            ),
            $this->logicalOr(
                $visitorCache,
                $visitorCache2
            )
        )->willReturnOnConsecutiveCalls(null, null, $this->throwException($exception));

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);


        $functionName = "cacheVisitor";

        $visitor->fetchFlags();

        $VisitorCacheImplementationMock->expects($this->exactly(2))
            ->method("lookupVisitor")
            ->with(
                $visitorId
            )->willReturn(
                $visitorCache
            );

        $visitor->fetchFlags();

        $logManagerStub->expects($this->exactly(1))->method('error')->with(
            $exception->getMessage(),
            [FlagshipConstant::TAG => $functionName]
        );

        $visitor->fetchFlags();
    }

    public function testCacheVisitorXpc()
    {

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $config = new DecisionApiConfig('envId', 'apiKey');

        /**
         * @var HttpClientInterface|MockObject $httpClientMock
         */
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );


        $decisionManager = new ApiManager($httpClientMock, $config);

        /**
         * @var LoggerInterface|MockObject $logManagerStub
         */
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error', 'info']
        );

        /**
         * @var TrackingManagerAbstract|MockObject $trackingManagerMock
         */
        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false,
            false,
            true,
            ["setTroubleshootingData", "addTroubleshootingHit"]
        );

        $config->setLogManager($logManagerStub);

        /**
         * @var IVisitorCacheImplementation|MockObject $VisitorCacheImplementationMock
         */
        $VisitorCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IVisitorCacheImplementation",
            [],
            "",
            true,
            true,
            true,
            ['lookupVisitor', 'cacheVisitor']
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackingManagerMock);

        /**
         * @var ContainerInterface|MockObject $containerMock
         */
        $containerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\ContainerInterface',
            ['get'],
            '',
            false
        );

        $containerGetMethod = function () {
            $args = func_get_args();
            $params = $args[1];
            return new DefaultStrategy($params[0]);
        };

        $containerMock->method('get')->willReturnCallback($containerGetMethod);
        $visitor = new VisitorDelegate(
            $containerMock,
            $configManager,
            $visitorId,
            false,
            $visitorContext,
            true
        );

        $defaultStrategy = new DefaultStrategy($visitor);

        $anonymousId = "anonymousId";

        $visitorCache = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::ANONYMOUS_ID => null,
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => [],
                StrategyAbstract::ASSIGNMENTS_HISTORY => []
            ]
        ];

        $visitorCache2 = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::ANONYMOUS_ID => $anonymousId,
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => [],
                StrategyAbstract::ASSIGNMENTS_HISTORY => []
            ]
        ];

        $visitorCacheAnonymous = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $anonymousId,
                StrategyAbstract::ANONYMOUS_ID => null,
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => [],
                StrategyAbstract::ASSIGNMENTS_HISTORY => []
            ]
        ];

        $exception = new Exception("Message error");

        $VisitorCacheImplementationMock->expects($this->exactly(7))
            ->method("cacheVisitor")
            ->with(
                $this->logicalOr(
                    $visitorId,
                    $anonymousId
                ),
                $this->logicalOr(
                    $visitorCache,
                    $visitorCache2,
                    $visitorCacheAnonymous
                )
            );

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);

        $defaultStrategy->cacheVisitor();

        $this->assertSame($visitorCache, $visitor->visitorCache);

        $visitor->setAnonymousId($anonymousId);

        $visitor->setVisitorCacheStatus(VisitorCacheStatus::ANONYMOUS_ID_CACHE);

        $defaultStrategy->cacheVisitor();

        $this->assertSame($visitorCache2, $visitor->visitorCache);

        $visitor->setVisitorCacheStatus(VisitorCacheStatus::NONE);

        $defaultStrategy->cacheVisitor();

        $visitor->setVisitorCacheStatus(VisitorCacheStatus::VISITOR_ID_CACHE);

        $defaultStrategy->cacheVisitor();

        $visitor->setVisitorCacheStatus(VisitorCacheStatus::VISITOR_ID_CACHE_WITH_ANONYMOUS_ID_CACHE);

        $defaultStrategy->cacheVisitor();
    }

    public function testFlushVisitor()
    {
        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );


        $httpClientMock->expects($this->exactly(0))->method("post");

        $decisionManager = new ApiManager($httpClientMock, $config);

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            [
             'error',
             'info',
            ]
        );

        $config->setLogManager($logManagerStub);

        $VisitorCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IVisitorCacheImplementation",
            [],
            "",
            true,
            true,
            true,
            ['flushVisitor']
        );

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $container = new Container();

        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $exception = new Exception("Message error");

        $VisitorCacheImplementationMock->expects($this->exactly(2))->method("flushVisitor")->with($visitorId)->willReturnOnConsecutiveCalls(null, $this->throwException($exception));

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);

        $defaultStrategy->setConsent(false); // will flush Visitor cache

        $defaultStrategy->setConsent(true); // will not flush Visitor cache


        $functionName = "flushVisitor";

        $logManagerStub->expects($this->exactly(1))->method('error')->with(
            $exception->getMessage(),
            [FlagshipConstant::TAG => $functionName]
        );

        $defaultStrategy->setConsent(false);
    }


    public function testFetchVisitorCampaigns()
    {

        $visitorId = "visitor_id";
        $visitorContext = [
                           'name' => 'visitor_name',
                           'age'  => 25,
                          ];

        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $campaignsData = $this->campaigns();

        $httpClientMock->expects($this->exactly(2))->method("post")->willThrowException(new Exception());

        $decisionManager = new ApiManager($httpClientMock, $config);

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            [
             'error',
             'info',
            ]
        );

        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false,
            false,
            true,
            [
             "setTroubleshootingData",
             "addTroubleshootingHit",
            ]
        );

        $config->setLogManager($logManagerStub);

        $configManager = new ConfigManager($config, $decisionManager, $trackingManagerMock);

        $containerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\ContainerInterface',
            ['get'],
            '',
            false
        );

        $containerGetMethod = function () {
            $args = func_get_args();
            $params = $args[1];
            return new DefaultStrategy($params[0]);
        };

        $containerMock->method('get')->willReturnCallback($containerGetMethod);

        $visitor = new VisitorDelegate(
            $containerMock,
            $configManager,
            $visitorId,
            false,
            $visitorContext,
            true
        );


        $assignmentsHistory = [];
        $campaigns = [];
        foreach ($campaignsData[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory[$campaign[FlagshipField::FIELD_ID]] = $variation[FlagshipField::FIELD_ID];

            $campaigns[] = [
                            FlagshipField::FIELD_CAMPAIGN_ID        => $campaign[FlagshipField::FIELD_ID],
                            FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                            FlagshipField::FIELD_VARIATION_ID       => $variation[FlagshipField::FIELD_ID],
                            FlagshipField::FIELD_IS_REFERENCE       => $variation[FlagshipField::FIELD_REFERENCE],
                            FlagshipField::FIELD_CAMPAIGN_TYPE      => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                            StrategyAbstract::ACTIVATED             => false,
                            StrategyAbstract::FLAGS                 => $modifications[FlagshipField::FIELD_VALUE],
                           ];
        }

        $visitorCache = [
                         StrategyAbstract::VERSION => 1,
                         StrategyAbstract::DATA    => [
                                                       StrategyAbstract::VISITOR_ID          => $visitorId,
                                                       StrategyAbstract::ANONYMOUS_ID        => $visitor->getAnonymousId(),
                                                       StrategyAbstract::CONSENT             => $visitor->hasConsented(),
                                                       StrategyAbstract::CONTEXT             => $visitor->getContext(),
                                                       StrategyAbstract::CAMPAIGNS           => $campaigns,
                                                       StrategyAbstract::ASSIGNMENTS_HISTORY => $assignmentsHistory,
                                                      ],
                        ];


        $visitor->visitorCache = $visitorCache;

        $visitor->fetchFlags();

        $this->assertCount(7, $visitor->getFlagsDTO());

        $visitor->visitorCache = [];

        $visitor->fetchFlags();

        $this->assertCount(0, $visitor->getFlagsDTO());
    }

    public function testSendAnalyticsHit()
    {
        $bucketingUrl = "https://terst.com";
        $config = new BucketingConfig($bucketingUrl, 'envId', 'apiKey');

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false,
            false,
            true,
            ["addUsageHit"]
        );

        $murmurHashMock = $this->getMockForAbstractClass(
            'Flagship\Utils\MurmurHash',
            [],
            "",
            false,
            false,
            true,
            ["murmurHash3Int32"]
        );

        $flagshipInstanceId = "flagshipInstanceId";
        $decisionManager = new ApiManager($httpClientMock, $config);
        $configManager = new ConfigManager($config, $decisionManager, $trackingManagerMock);
        $configManager->setDecisionManager($decisionManager)->setTrackingManager($trackingManagerMock);

        $visitor = new VisitorDelegate(
            new Container(),
            $configManager,
            "b8808e0a-d268-4d53-bf17-d88bbaac9638",
            false,
            [],
            true
        );

        $analytic = new UsageHit();
        $analytic->setLabel(TroubleshootingLabel::SDK_CONFIG)->setLogLevel(LogLevel::INFO)->setSdkConfigLogLevel($config->getLogLevel())->setSdkConfigMode($config->getDecisionMode())->setSdkConfigTimeout($config->getTimeout())->setSdkConfigTrackingManagerConfigStrategy($config->getCacheStrategy())->setSdkConfigUsingOnVisitorExposed(!!$config->getOnVisitorExposed())->setSdkConfigUsingCustomHitCache(!!$config->getHitCacheImplementation())->setSdkConfigUsingCustomVisitorCache(!!$config->getVisitorCacheImplementation())->setSdkConfigBucketingUrl($bucketingUrl)->setSdkStatus($visitor->getSdkStatus())->setFlagshipInstanceId($flagshipInstanceId)->setConfig($config)->setVisitorId($flagshipInstanceId);

        $trackingManagerMock->expects($this->once())->method("addUsageHit")->with($analytic);

        $murmurHashMock->expects($this->exactly(2))->method('murmurHash3Int32')->willReturnOnConsecutiveCalls(10, 0);

        $defaultStrategy = new DefaultStrategy($visitor);
        $defaultStrategy->setFlagshipInstanceId($flagshipInstanceId);
        $defaultStrategy->setMurmurHash($murmurHashMock);

        $defaultStrategy->sendSdkConfigAnalyticHit();

        $defaultStrategy->sendSdkConfigAnalyticHit();

        $config->setDisableDeveloperUsageTracking(true);

        $defaultStrategy->sendSdkConfigAnalyticHit();
    }
}

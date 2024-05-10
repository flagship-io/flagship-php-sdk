<?php

namespace Flagship\Visitor;

require_once __dir__ . '/../Assets/Round.php';

use DateTime;
use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSFetchReason;
use Flagship\Enum\FSFetchStatus;
use Flagship\Enum\HitType;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\Activate;
use Flagship\Hit\UsageHit;
use Flagship\Hit\Event;
use Flagship\Hit\Item;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Hit\Transaction;
use Flagship\Model\FlagDTO;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\MurmurHash;
use PHPUnit\Framework\TestCase;


class DefaultStrategyTest extends TestCase
{
    use CampaignsData;

    /**
     * @return \array[][]|FlagDTO[]
     */
    public function modifications()
    {
        return [
            (new FlagDTO())
                ->setKey('background')
                ->setValue('EE3300')
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
                ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
                ->setVariationId('c1e3t1nvfu1ncqfcdcq0'),
            (new FlagDTO())
                ->setKey('borderColor')
                ->setValue('blue')
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new FlagDTO())
                ->setKey('Null')
                ->setValue(null)
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new FlagDTO())
                ->setKey('Empty')
                ->setValue("")
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new FlagDTO())
                ->setKey('isBool')
                ->setValue(false)
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new FlagDTO())
                ->setKey('Number')
                ->setValue(5)
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
        ];
    }


    public function testUpdateContext()
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
        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);


        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);
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

        // Test Collection
        $collectionContext = [
            'address' => 'visitor_address',
            'browser' => 'chrome'
        ];

        $defaultStrategy->updateContext('extra_info', $collectionContext);

        //Test value!= string,number, bool
        $this->assertArrayNotHasKey('extra_info', $visitor->getContext());

        //Test value ==null
        $visitor->updateContext('os', null);
        $this->assertArrayHasKey('os', $visitor->getContext());

        $visitor->setContext([]);

        //Test key ==null
        $defaultStrategy->updateContext(null, "Hp");
        $this->assertCount(0, $visitor->getContext());

        //Test key=null and value==null
        $defaultStrategy->updateContext(null, null);
        $this->assertCount(0, $visitor->getContext());

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
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];
        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);
        $newVisitorContext = [
            'vip' => true,
            'gender' => 'F'
        ];
        $defaultStrategy->updateContextCollection($newVisitorContext);
        $this->assertCount(8, $visitor->getContext());

        //Test without Key

        $newVisitorContext = [
            'vip'
        ];

        $defaultStrategy->updateContextCollection($newVisitorContext);
        $this->assertCount(8, $visitor->getContext());
    }

    public function testClearContext()
    {
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];
        $config = new DecisionApiConfig('envId', 'apiKey');
        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $this->assertCount(6, $visitor->getContext());

        $defaultStrategy->clearContext();

        $this->assertCount(0, $visitor->getContext());
    }

    public function testAuthenticate()
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

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);


        $configManager = (new ConfigManager())
            ->setConfig($config)->setTrackingManager($trackerManager);
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext, true);

        $authenticateName = "authenticate";
        $logManagerStub->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive([ sprintf(
                FlagshipConstant::VISITOR_ID_ERROR,
                $authenticateName
            )], [sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_BUCKETING_ERROR,
                $authenticateName
            ), [FlagshipConstant::TAG => $authenticateName]]);


        $defaultStrategy = new DefaultStrategy($visitor);
        $newVisitorId = "new_visitor_id";
        $defaultStrategy->authenticate($newVisitorId);
        $this->assertSame($visitorId, $visitor->getAnonymousId());
        $this->assertSame($newVisitorId, $visitor->getVisitorId());
        $this->assertSame(FSFetchReason::AUTHENTICATE, $visitor->getFetchStatus()->getReason());
        $this->assertSame(FSFetchStatus::FETCH_REQUIRED, $visitor->getFetchStatus()->getStatus());

        //Test authenticate with null visitorId

        $defaultStrategy->authenticate(null);
        $this->assertSame($visitorId, $visitor->getAnonymousId());
        $this->assertSame($newVisitorId, $visitor->getVisitorId());

        //Test with bcuketing mode
        $newVisitorId2 = "new_visitor_id";
        $visitor->setConfig((new BucketingConfig("http:127.0.0.1:3000"))->setLogManager($logManagerStub));
        $defaultStrategy->authenticate($newVisitorId2);
        $this->assertSame($visitorId, $visitor->getAnonymousId());
        $this->assertSame($newVisitorId, $visitor->getVisitorId());
    }

    public function testUnauthenticate()
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

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );



        $visitorId = "visitor_id";

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())
            ->setConfig($config)->setTrackingManager($trackerManager);
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $visitor->setConfig((new BucketingConfig("http://127.0.0.1:3000"))->setLogManager($logManagerStub));

        $unauthenticateName = "unauthenticate";
        $logManagerStub->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive(
                [sprintf(
                    FlagshipConstant::METHOD_DEACTIVATED_BUCKETING_ERROR,
                    $unauthenticateName
                ), [FlagshipConstant::TAG => $unauthenticateName]],
                [
                    FlagshipConstant::FLAGSHIP_VISITOR_NOT_AUTHENTIFICATE,
                    [FlagshipConstant::TAG => $unauthenticateName]
                ]
            );

        $defaultStrategy = new DefaultStrategy($visitor);
        $defaultStrategy->unauthenticate();


        //Test Visitor not authenticate yet
        $config->setLogManager($logManagerStub);
        $visitor->setConfig($config);
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

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($decisionManager)
        ->setTrackingManager($trackingManagerMock);

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
        $config = new BucketingConfig('envId', 'apiKey');
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
            ["setTroubleshootingData", "addTroubleshootingHit"]
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $httpResponseBody = $this->campaigns();
        $troubleshootingData = [
            "startDate" => "2023-04-13T09:33:38.049Z",
            "endDate" => "2023-04-13T10:03:38.049Z",
            "timezone" => "Europe/Paris",
            "traffic" => 40
        ];

        $httpResponseBody["extras"] = [
            "accountSettings" => [
                "@type" => "type.googleapis.com/flagship.protobuf.AccountSettings",
                "enabledXPC" => false,
                "enabled1V1T" => false,
                "troubleshooting" => $troubleshootingData
            ]];

        $httpClientMock->expects($this->exactly(2))->method("post")->willReturn(new HttpResponse(200, $httpResponseBody));

        $trackingManagerMock->expects($this->exactly(2))->method("setTroubleshootingData")
            ->with($this->callback(function ($param) use ($troubleshootingData) {
                $startDate = new DateTime($troubleshootingData['startDate']);
                $endDate = new DateTime($troubleshootingData['endDate']);
                return $param->getTraffic() === $troubleshootingData['traffic'] &&
                    $param->getTimezone() === $troubleshootingData['timezone'] &&
                    $param->getStartDate()->getTimestamp() === $startDate->getTimestamp() &&
                    $param->getEndDate()->getTimestamp() === $endDate->getTimestamp();
            }));

        $matcher = $this->exactly(4);
        $trackingManagerMock->expects($matcher)
            ->method("addTroubleshootingHit")->with($this->callback(function ($param) use ($matcher) {
                switch ($matcher->getInvocationCount()) {
                    case 1:
                    case 3:
                    {
                        return $param->getLabel() === TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS;
                    }
                    case 2:
                    case 4:
                    {
                        return $param->getLabel() === TroubleshootingLabel::VISITOR_SEND_HIT;
                    }
                }
                return  false;
            }));



        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($decisionManager)
            ->setTrackingManager($trackingManagerMock);

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
    }


    public function testFetchFlagsWithoutDecisionManager()
    {
        $modifications = $this->modifications();
        $logManagerStub = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);
        $defaultStrategy = new DefaultStrategy($visitor);

        $logManagerStub->expects($this->exactly(1))->method('error')
            ->with(
                FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => "fetchFlags"]
            );

        $defaultStrategy->fetchFlags();
    }

    public function testSendHit()
    {
        $config = new DecisionApiConfig();
        $trackerManagerMock = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [$config, new HttpClient()],
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
        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerMock);

        $apiManager = new ApiManager(new HttpClient(), $config);

        $configManager->setDecisionManager($apiManager);

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

        $trackerManagerMock->expects($this->exactly(5))
            ->method('addHit')
            ->withConsecutive([$page], [$screen], [$transition], [$event], [$item]);

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
            [$config, new HttpClient()],
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

        $configManager = (new ConfigManager())
            ->setConfig($config);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, true, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $pageUrl = 'https://locahost';
        $page = new Page($pageUrl);

        $paramsExpected = [];

        $logManagerMock->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive($paramsExpected);

        //Test sendHit with TrackingManager null
        //Call error
        $paramsExpected[] = [
            FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
            [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
        ];

        $defaultStrategy->sendHit($page);

        //Set TrackingManager
        $configManager->setTrackingManager($trackerManagerMock);

        // Test SendHit with invalid require field
        //Call error
        $page = new Page(null);

        $trackerManagerMock->expects($this->never())
            ->method('sendHit');

        $paramsExpected[] = [
            $page->getErrorMessage(),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
        ];

        $defaultStrategy->sendHit($page);
    }

    public function testUserExposed()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error','info']
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);


        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [$config, new HttpClient()],
            '',
            true,
            true,
            true,
            ['activateFlag']
        );

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $key = "key";
        $flagDTO = new FlagDTO();
        $flagDTO->setKey($key)
            ->setValue("value");
        $defaultValue = "default";

        $flagMetadata = new FlagMetadata(
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
        $activate
            ->setFlagKey($flagDTO->getKey())
            ->setFlagValue($flagDTO->getValue())
            ->setFlagDefaultValue($defaultValue)
            ->setFlagMetadata($flagMetadata)
            ->setVisitorContext($visitor->getContext())
            ->setVisitorId($visitor->getVisitorId())
            ->setConfig($config);

        $trackerManagerStub->expects($this->exactly(2))
            ->method('activateFlag')
            ->with($activate);

        $defaultStrategy->visitorExposed($key, $defaultValue, $flagDTO);

        //Test defaultValue null

        $activate->setFlagDefaultValue(null);
        $defaultStrategy->visitorExposed($key, null, $flagDTO);

        $functionName = FlagshipConstant::FLAG_USER_EXPOSED;

        $logManagerStub->expects($this->exactly(2))->method('info')
            ->withConsecutive(
                [sprintf(
                    FlagshipConstant::USER_EXPOSED_NO_FLAG_ERROR,
                    $visitor->getVisitorId(),
                    $key
                ),
                [FlagshipConstant::TAG => $functionName]],
                [sprintf(
                    FlagshipConstant::USER_EXPOSED_CAST_ERROR,
                    $visitor->getVisitorId(),
                    $key
                ),
                    [FlagshipConstant::TAG => $functionName]]
            );

        $activate->setFlagDefaultValue($defaultValue);
        $defaultStrategy->visitorExposed($key, $defaultValue, null);

        $activate->setFlagDefaultValue(false);
        $defaultStrategy->visitorExposed($key, false, $flagDTO);
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
            ['error','info']
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);


        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [$config, new HttpClient()],
            '',
            true,
            true,
            true,
            ['activateFlag']
        );

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $key = "key";
        $defaultValue = "defaultValue";
        $flagDTO = new FlagDTO();
        $flagDTO->setKey($key)
            ->setValue("value");

        $flagMetadata = new FlagMetadata(
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
        $activate
            ->setFlagKey($flagDTO->getKey())
            ->setFlagValue($flagDTO->getValue())
            ->setFlagDefaultValue($defaultValue)
            ->setFlagMetadata($flagMetadata)
            ->setVisitorContext($visitor->getContext())
            ->setVisitorId($visitor->getVisitorId())
            ->setConfig($config);

        $functionName = FlagshipConstant::FLAG_VALUE;

        $trackerManagerStub->expects($this->exactly(3))
            ->method('activateFlag')
            ->with($activate);

        $value = $defaultStrategy->getFlagValue($key, $defaultValue, $flagDTO);
        $this->assertEquals($value, $flagDTO->getValue());

        //Test with default value is null

        $activate->setFlagDefaultValue(null);
        $value = $defaultStrategy->getFlagValue($key, null, $flagDTO);
        $this->assertEquals($value, $flagDTO->getValue());

        $logDifferentType  = [];
        $logManagerStub->expects($this->exactly(2))->method('info')
            ->withConsecutive(
                [sprintf(
                    FlagshipConstant::GET_FLAG_MISSING_ERROR,
                    $visitor->getVisitorId(),
                    $key,
                    $defaultValue
                ),
                [FlagshipConstant::TAG => $functionName]],
                $logDifferentType
            );

        // Test flag null
        $activate->setFlagDefaultValue($defaultValue);
        $value = $defaultStrategy->getFlagValue($key, $defaultValue, null);
        $this->assertEquals($value, $defaultValue);

        // Test flag with different type

        $defaultValue = 12;

        $logDifferentType[] = sprintf(
            FlagshipConstant::GET_FLAG_CAST_ERROR,
            $visitor->getVisitorId(),
            $key,
            $defaultValue
        );
        $logDifferentType[] = [FlagshipConstant::TAG => $functionName];

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
            ['error','info']
        );

        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false,
            false,
            true,
            ["setTroubleshootingData", "addTroubleshootingHit"]
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())
            ->setConfig($config)->setTrackingManager($trackingManagerMock);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $key = "key";
        $metadata = new FlagMetadata(
            "campaignID",
            "varGroupID",
            "varID",
            true,
            "ab",
            "slug",
            "campaignName",
            "varGrpName",
            "varName"
        );

        $functionName = "flag.metadata";

        $metadataValue = $defaultStrategy->getFlagMetadata($key, $metadata, true);
        $this->assertEquals($metadata, $metadataValue);



        $logManagerStub->expects($this->exactly(1))->method('info')
            ->withConsecutive(
                [sprintf(FlagshipConstant::GET_METADATA_CAST_ERROR, $key),
                    [FlagshipConstant::TAG => $functionName]]
            );
        $metadataValue = $defaultStrategy->getFlagMetadata($key, $metadata, false);
        $this->assertEquals(FlagMetadata::getEmpty(), $metadataValue);
    }

    public function testLookupVisitor()
    {
        $config = new DecisionApiConfig('envId', 'apiKey');

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error','info']
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


        $configManager = (new ConfigManager())->setConfig($config);

        $container = new Container();

        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $visitorCache1 = [
            StrategyAbstract::VERSION => 1
        ];

        $differentVisitorId = "different visitorID";
        $visitorCache2 = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $differentVisitorId
            ]
        ];

        $visitorCache3 = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId
            ]
        ];

        $visitorCache4 = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::CAMPAIGNS => "not an array"
            ]
        ];
        $visitorCache5 = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::CAMPAIGNS => [
                    "anythings"
                ]
            ]
        ];

        $visitorCache6 = [
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

        $VisitorCacheImplementationMock->expects($this->exactly(8))
            ->method("lookupVisitor")
            ->with($visitorId)
            ->willReturnOnConsecutiveCalls(
                null,
                [],
                $visitorCache1,
                $visitorCache2,
                $visitorCache3,
                $visitorCache4,
                $visitorCache5,
                $visitorCache6
            );
        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);


        $functionName = "lookupVisitor";

        $lookupVisitorJson  = function () use ($functionName) {
            return [StrategyAbstract::LOOKUP_VISITOR_JSON_OBJECT_ERROR,
                [FlagshipConstant::TAG => $functionName]];
        };

        $logManagerStub->expects($this->exactly(5))->method('error')
            ->withConsecutive(
                $lookupVisitorJson(),
                $lookupVisitorJson(),
                [sprintf(StrategyAbstract::VISITOR_ID_MISMATCH_ERROR, $differentVisitorId, $visitorId),
                    [FlagshipConstant::TAG => $functionName]],
                $lookupVisitorJson()
            );

        // Test return null
        $defaultStrategy->lookupVisitor();

        $this->assertCount(0, $visitor->visitorCache);

        // test return empty array
        $defaultStrategy->lookupVisitor();

        $this->assertCount(0, $visitor->visitorCache);

        // test return array["version"=>1] only
        $defaultStrategy->lookupVisitor();

        $this->assertCount(0, $visitor->visitorCache);

        // test return cache with different visitor id

        $defaultStrategy->lookupVisitor();

        $this->assertCount(0, $visitor->visitorCache);

        // test return cache without campaings

        $defaultStrategy->lookupVisitor();

        $this->assertSame($visitorCache3, $visitor->visitorCache);

        // test return cache with is_array(campaings) === false

        $defaultStrategy->lookupVisitor();

        $this->assertSame($visitorCache3, $visitor->visitorCache);

        // test return cache with invalid campaigns

        $defaultStrategy->lookupVisitor();

        $this->assertSame($visitorCache3, $visitor->visitorCache);

        // test return cache with valid cache

        $defaultStrategy->lookupVisitor();

        $this->assertSame($visitorCache6, $visitor->visitorCache);
    }

    public function testCacheVisitor()
    {

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $campaignsData = $this->campaigns();
        $campaignsData2 = $this->campaigns2();

        $httpClientMock->expects($this->exactly(3))->method("post")
            ->willReturnOnConsecutiveCalls(
                new HttpResponse(200, $campaignsData),
                new HttpResponse(200, $campaignsData2),
                new HttpResponse(200, $campaignsData2)
            );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error','info']
        );

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

        $VisitorCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IVisitorCacheImplementation",
            [],
            "",
            true,
            true,
            true,
            ['lookupVisitor', 'cacheVisitor']
        );


        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($decisionManager)
            ->setTrackingManager($trackingManagerMock);


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

        $visitor = new VisitorDelegate($containerMock, $configManager, $visitorId, false, $visitorContext, true);

        $assignmentsHistory = [];
        $campaigns = [];
        foreach ($campaignsData[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory[$campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]] = $variation[FlagshipField::FIELD_ID];

            $campaigns[] = [
                FlagshipField::FIELD_CAMPAIGN_ID => $campaign[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_SLUG => isset($campaign[FlagshipField::FIELD_SLUG]) ? $campaign[FlagshipField::FIELD_SLUG] : null,
                FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                FlagshipField::FIELD_VARIATION_ID => $variation[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_IS_REFERENCE => $variation[FlagshipField::FIELD_REFERENCE],
                FlagshipField::FIELD_CAMPAIGN_TYPE => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                StrategyAbstract::ACTIVATED => false,
                StrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }

        $visitorCache = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => $campaigns,
                StrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
            ]
        ];

        $assignmentsHistory2 = [];
        $campaigns2 = [];
        foreach ($campaignsData2[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory2[$campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]] =
                $variation[FlagshipField::FIELD_ID];

            $campaigns2[] = [
                FlagshipField::FIELD_CAMPAIGN_ID => $campaign[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_SLUG => isset($campaign[FlagshipField::FIELD_SLUG]) ?
                    $campaign[FlagshipField::FIELD_SLUG] : null,
                FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                FlagshipField::FIELD_VARIATION_ID => $variation[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_IS_REFERENCE => $variation[FlagshipField::FIELD_REFERENCE],
                FlagshipField::FIELD_CAMPAIGN_TYPE => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                StrategyAbstract::ACTIVATED => false,
                StrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }


        $visitorCache2 = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => $campaigns2,
                StrategyAbstract::ASSIGNMENTS_HISTORY => array_merge($assignmentsHistory, $assignmentsHistory2)
            ]
        ];

        $exception = new \Exception("Message error");

        $VisitorCacheImplementationMock->expects($this->exactly(3))
            ->method("cacheVisitor")
            ->withConsecutive([$visitorId, $visitorCache], [$visitorId, $visitorCache2])
            ->willReturnOnConsecutiveCalls(null, null, $this->throwException($exception));

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);


        $functionName = "cacheVisitor";

        $visitor->fetchFlags();

        $visitor->fetchFlags();

        $logManagerStub->expects($this->exactly(1))->method('error')
            ->withConsecutive(
                [$exception->getMessage(),
                    [FlagshipConstant::TAG => $functionName]]
            );

        $visitor->fetchFlags();
    }

    public function testFlushVisitor()
    {
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
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
            ['error','info']
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

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($decisionManager)
            ->setTrackingManager($trackerManager);

        $container = new Container();

        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $exception = new \Exception("Message error");

        $VisitorCacheImplementationMock->expects($this->exactly(2))
            ->method("flushVisitor")
            ->withConsecutive([$visitorId])
            ->willReturnOnConsecutiveCalls(null, $this->throwException($exception));

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);

        $defaultStrategy->setConsent(false); // will flush Visitor cache

        $defaultStrategy->setConsent(true); // will not flush Visitor cache


        $functionName = "flushVisitor";

        $logManagerStub->expects($this->exactly(1))->method('error')
            ->withConsecutive(
                [$exception->getMessage(),
                    [FlagshipConstant::TAG => $functionName]]
            );
        $defaultStrategy->setConsent(false); // will throw exception
    }


    public function testFetchVisitorCampaigns()
    {

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $campaignsData = $this->campaigns();

        $httpClientMock->expects($this->exactly(2))
            ->method("post")
            ->willThrowException(new \Exception());

        $decisionManager = new ApiManager($httpClientMock, $config);

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error','info']
        );

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

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($decisionManager)
            ->setTrackingManager($trackingManagerMock);


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

        $visitor = new VisitorDelegate($containerMock, $configManager, $visitorId, false, $visitorContext, true);


        $assignmentsHistory = [];
        $campaigns = [];
        foreach ($campaignsData[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory[$campaign[FlagshipField::FIELD_ID]] = $variation[FlagshipField::FIELD_ID];

            $campaigns[] = [
                FlagshipField::FIELD_CAMPAIGN_ID => $campaign[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                FlagshipField::FIELD_VARIATION_ID => $variation[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_IS_REFERENCE => $variation[FlagshipField::FIELD_REFERENCE],
                FlagshipField::FIELD_CAMPAIGN_TYPE => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                StrategyAbstract::ACTIVATED => false,
                StrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }

        $visitorCache = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => $campaigns,
                StrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
            ]
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
        $visitorId = "visitorId";

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
        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($decisionManager)
            ->setTrackingManager($trackingManagerMock);

        $visitor = new VisitorDelegate(
            new Container(),
            $configManager,
            "b8808e0a-d268-4d53-bf17-d88bbaac9638",
            false,
            [],
            true
        );

        $analytic = new UsageHit();
        $analytic->setLabel(TroubleshootingLabel::SDK_CONFIG)
            ->setLogLevel(LogLevel::INFO)

            ->setSdkConfigLogLeve($config->getLogLevel())
            ->setSdkConfigMode($config->getDecisionMode())
            ->setSdkConfigTimeout($config->getTimeout())
            ->setSdkConfigTrackingManagerConfigStrategy($config->getCacheStrategy())
            ->setSdkConfigUsingOnVisitorExposed(!!$config->getOnVisitorExposed())
            ->setSdkConfigUsingCustomHitCache(!!$config->getHitCacheImplementation())
            ->setSdkConfigUsingCustomVisitorCache(!!$config->getVisitorCacheImplementation())
            ->setSdkConfigBucketingUrl($bucketingUrl)
            ->setSdkStatus($visitor->getSdkStatus())
            ->setFlagshipInstanceId($flagshipInstanceId)
            ->setConfig($config)
            ->setVisitorId($flagshipInstanceId)
;

        $uniqueId = $visitor->getVisitorId() . "2024-01-29";

        $trackingManagerMock->expects($this->once())->method("addUsageHit")->with($analytic);

        $murmurHashMock->expects($this->exactly(2))
            ->method('murmurHash3Int32')
            ->willReturnOnConsecutiveCalls(10, 0);

        $defaultStrategy = new DefaultStrategy($visitor);
        $defaultStrategy->setFlagshipInstanceId($flagshipInstanceId);
        $defaultStrategy->setMurmurHash($murmurHashMock);

        $defaultStrategy->sendSdkConfigAnalyticHit();

        $defaultStrategy->sendSdkConfigAnalyticHit();

        $config->setDisableDeveloperUsageTracking(true);

        $defaultStrategy->sendSdkConfigAnalyticHit();
    }
}

<?php

namespace Flagship\Visitor;

require_once __dir__ . '/../Assets/Round.php';

use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FlagSyncStatus;
use Flagship\Enum\HitType;
use Flagship\Flag\Flag;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\Activate;
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
        $this->assertSame(FlagSyncStatus::CONTEXT_UPDATED, $visitor->getFlagSyncStatus());

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
        $this->assertSame(FlagSyncStatus::AUTHENTICATED, $visitor->getFlagSyncStatus());

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
        $this->assertSame(FlagSyncStatus::UNAUTHENTICATED, $visitor->getFlagSyncStatus());
    }

    public function testSynchronizeModifications()
    {
        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $httpClientMock->expects($this->once())->method("post")
            ->willReturn(new HttpResponse(200, $this->campaigns()));


        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($decisionManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->synchronizeModifications();

        $modifications = $this->campaignsModifications();

        $this->assertJsonStringEqualsJsonString(
            json_encode($modifications),
            json_encode($defaultStrategy->getModifications())
        );

        //Test getModification keyValue is string and DefaultValue is string
        //Return KeyValue

        $key = $modifications[2]->getKey();
        $keyValue = $modifications[2]->getValue();
        $defaultValue = "red";
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is boolean and DefaultValue is boolean
        //Return KeyValue

        $key = $modifications[1]->getKey();
        $keyValue = $modifications[1]->getValue();
        $defaultValue = false;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is numeric and DefaultValue is numeric
        //Return KeyValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = 14;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue

        $key = $modifications[2]->getKey();
        $keyValue = $modifications[2]->getValue();
        $defaultValue = 25; // default is numeric
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = true; // default is boolean
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = []; // is not numeric and bool and string
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification key is not string or is empty
        //Return DefaultValue

        $defaultValue = true;
        $modificationValue = $defaultStrategy->getModification(null, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = 58;
        $modificationValue = $defaultStrategy->getModification('', $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification keyValue is null
        //Return DefaultValue

        $key = $modifications[4]->getKey();
        $defaultValue = 14;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification keyValue is empty
        //Return DefaultValue

        $key = $modifications[5]->getKey();
        $keyValue = $modifications[5]->getValue();
        $defaultValue = "blue-border";
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification key is not exist
        //Return DefaultValue
        $defaultValue = "blue-border";
        $modificationValue = $defaultStrategy->getModification('keyNotExist', $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);
    }


    public function testSynchronizeModificationsWithoutDecisionManager()
    {
        $modifications = $this->modifications();
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

        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);
        $defaultStrategy = new DefaultStrategy($visitor);


        $logManagerStub->expects($this->exactly(1))->method('error')
            ->with(
                FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => "synchronizeModifications"]
            );

        $defaultStrategy->synchronizeModifications();
    }


    public function testFetchFlags()
    {
        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $httpClientMock->expects($this->once())->method("post")->willReturn(new HttpResponse(200, $this->campaigns()));


        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($decisionManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->fetchFlags();

        $modifications = $this->campaignsModifications();

        $this->assertJsonStringEqualsJsonString(json_encode($modifications), json_encode($visitor->getFlagsDTO()));
        $this->assertSame(FlagSyncStatus::FLAGS_FETCHED, $visitor->getFlagSyncStatus());

        //Test getModification keyValue is string and DefaultValue is string
        //Return KeyValue

        $key = $modifications[2]->getKey();
        $keyValue = $modifications[2]->getValue();
        $defaultValue = "red";
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is boolean and DefaultValue is boolean
        //Return KeyValue

        $key = $modifications[1]->getKey();
        $keyValue = $modifications[1]->getValue();
        $defaultValue = false;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is numeric and DefaultValue is numeric
        //Return KeyValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = 14;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue

        $key = $modifications[2]->getKey();
        $keyValue = $modifications[2]->getValue();
        $defaultValue = 25; // default is numeric
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = true; // default is boolean
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = []; // is not numeric and bool and string
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification key is not string or is empty
        //Return DefaultValue

        $defaultValue = true;
        $modificationValue = $defaultStrategy->getModification(null, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = 58;
        $modificationValue = $defaultStrategy->getModification('', $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification keyValue is null
        //Return DefaultValue

        $key = $modifications[4]->getKey();
        $defaultValue = 14;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification keyValue is empty
        //Return DefaultValue

        $key = $modifications[5]->getKey();
        $keyValue = $modifications[5]->getValue();
        $defaultValue = "blue-border";
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification key is not exist
        //Return DefaultValue
        $defaultValue = "blue-border";
        $modificationValue = $defaultStrategy->getModification('keyNotExist', $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);
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
                [FlagshipConstant::TAG => "synchronizeModifications"]
            );

        $defaultStrategy->synchronizeModifications();
    }


    public function testGetModificationWithActive()
    {
        $modifications = $this->campaignsModifications();
        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );


        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [$config, new HttpClient()],
            '',
            true,
            true,
            true,
            ['activateFlag']
        );


        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerStub);

        $httpClientMock->expects($this->once())->method("post")
            ->willReturn(new HttpResponse(200, $this->campaigns()));

        $configManager->setDecisionManager($decisionManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);
        $defaultStrategy = new DefaultStrategy($visitor);

        $activate = new Activate($modifications[0]->getVariationGroupId(), $modifications[0]->getVariationId());
        $activate->setConfig($config)->setVisitorId($visitor->getVisitorId());

        $trackerManagerStub->expects($this->exactly(2))
            ->method('activateFlag');
//            ->with($activate);

        $defaultStrategy->fetchFlags();

        $defaultStrategy->getModification($modifications[0]->getKey(), 10, true);

        //Test activate on get Modification when value is null

        $defaultStrategy->getModification($modifications[2]->getKey(), 'defaultValue', true);
    }


    public function testGetModificationLog()
    {
        $modifications = $this->modifications();
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getModifications', 'getConfig', 'getCampaigns']
        );
        $config = new DecisionApiConfig('envId', 'apiKey');

        $apiManagerStub->method('getCampaigns')->willReturn([]);
        $apiManagerStub->method('getModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategyMock = $this->getMockBuilder('Flagship\Visitor\DefaultStrategy')
            ->setMethods(['logError','logInfo'])
            ->setConstructorArgs([$visitor])->getMock();

        $defaultStrategyMock->synchronizeModifications();

        //Test getModification key is null
        //Return DefaultValue
        $defaultValue = true;
        $key = null;

        $expectedParams = [
            [$config,
                sprintf(FlagshipConstant::GET_MODIFICATION_KEY_ERROR, $key),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]
            ], [], []
        ];

        $defaultStrategyMock->expects($this->exactly(2))
            ->method('logInfo')
            ->withConsecutive(
                $expectedParams[1],
                $expectedParams[2]
            );

        $defaultStrategyMock->expects($this->exactly(1))
            ->method('logError')
            ->withConsecutive(
                $expectedParams[0]
            );

        $defaultStrategyMock->getModification($key, $defaultValue);

        //Test getModification key is not exist
        //Return DefaultValue
        $key = "notExistKey";
        $defaultValue = true;

        $expectedParams[] = [$config,
            sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]];

        $defaultStrategyMock->getModification($key, $defaultValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue
        $key = $modifications[0]->getKey();
        $defaultValue = 25; // default is numeric

        $expectedParams[] = [$config,
            sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]];

        $defaultStrategyMock->getModification($key, $defaultValue);
    }


    public function testGetModificationInfo()
    {
        $modifications = $this->modifications();
        $modifications = $this->campaignsModifications();
        $config = new DecisionApiConfig('envId', 'apiKey');
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

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


        $config->setLogManager($logManagerStub);
        $configManager = (new ConfigManager())->setConfig($config)->setTrackingManager($trackerManager);
        $configManager->setDecisionManager($decisionManager);

        $paramsExpected = [];

        $httpClientMock->expects($this->once())->method("post")
            ->willReturn(new HttpResponse(200, $this->campaigns()));



        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $logManagerStub->expects($this->exactly(2))->method('error')
            ->withConsecutive($paramsExpected);

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->synchronizeModifications();

        $modification = $modifications[0];

        $campaignExpected = [
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference(),
            FlagshipField::FIELD_VALUE => $modification->getValue()
        ];

        //Test key exist in modifications set
        $campaign = $defaultStrategy->getModificationInfo($modification->getKey());
        $this->assertSame($campaignExpected, $campaign);

        //Test key doesn't exist in modifications set
        //call $logManagerStub->error
        $notExistKey = "notExistKey";
        $paramsExpected[] = [sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $notExistKey),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]];

        $campaign = $defaultStrategy->getModificationInfo($notExistKey);
        $this->assertNull($campaign);

        //Test Key is null
        //call $logManagerStub->error
        $paramsExpected[] = [sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, null),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]];

        $campaign = $defaultStrategy->getModificationInfo(null);
        $this->assertNull($campaign);
    }


    public function testActivateModification()
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

        $modifications = $this->campaignsModifications();

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [$config, new HttpClient()],
            '',
            true,
            true,
            true,
            ['activateFlag']
        );

        $httpClientMock->expects($this->once())->method("post")
            ->willReturn(new HttpResponse(200, $this->campaigns()));

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($decisionManager)
            ->setTrackingManager($trackerManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $activate = new Activate($modifications[0]->getVariationGroupId(), $modifications[0]->getVariationId());
        $activate->setVisitorId($visitor->getVisitorId())->setConfig($config);
        $trackerManagerStub->expects($this->once())
            ->method('activateFlag');
//            ->with($activate);

        $defaultStrategy->synchronizeModifications();

        $defaultStrategy->activateModification($modifications[0]->getKey());

        $paramsExpected = [];
        $logManagerStub->expects($this->exactly(1))
            ->method('info')
            ->withConsecutive($paramsExpected);

        //Test key not exist
        //Call $logManagerStub->error
        $key = "KeyNotExist";

        $paramsExpected[] = [sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]];

        $defaultStrategy->activateModification($key);
    }


    public function testActivateModificationWithoutTrackerManager()
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

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $modifications = $this->campaignsModifications();

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $httpClientMock->expects($this->once())->method("post")
            ->willReturn(new HttpResponse(200, $this->campaigns()));

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($decisionManager);


        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);
        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->synchronizeModifications();



        $logManagerStub->expects($this->exactly(1))->method('error')->with(
            FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
            [FlagshipConstant::TAG => "activateModification"]
        );

        //Call error
        $defaultStrategy->activateModification($modifications[0]->getKey());
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
        $activate->setVisitorId($visitor->getVisitorId())
            ->setConfig($config)
            ->setFlagKey($flagDTO->getKey())
            ->setFlagValue($flagDTO->getValue())
            ->setFlagDefaultValue($defaultValue)
            ->setFlagMetadata($flagMetadata)
            ->setVisitorContext($visitor->getContext());

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
        $activate->setVisitorId($visitor->getVisitorId())
            ->setConfig($config)
            ->setFlagKey($flagDTO->getKey())
            ->setFlagValue($flagDTO->getValue())
            ->setFlagDefaultValue($defaultValue)
            ->setFlagMetadata($flagMetadata)
            ->setVisitorContext($visitor->getContext());

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

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())
            ->setConfig($config);

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
            VisitorStrategyAbstract::VERSION => 1
        ];

        $differentVisitorId = "different visitorID";
        $visitorCache2 = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $differentVisitorId
            ]
        ];

        $visitorCache3 = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitorId
            ]
        ];

        $visitorCache4 = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitorId,
                VisitorStrategyAbstract::CAMPAIGNS => "not an array"
            ]
        ];
        $visitorCache5 = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitorId,
                VisitorStrategyAbstract::CAMPAIGNS => [
                    "anythings"
                ]
            ]
        ];

        $visitorCache6 = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitorId,
                VisitorStrategyAbstract::CAMPAIGNS => [
                    [
                        FlagshipField::FIELD_CAMPAIGN_ID => "c8pimlr7n0ig3a0pt2ig",
                        FlagshipField::FIELD_VARIATION_GROUP_ID => "c8pimlr7n0ig3a0pt2jg",
                        FlagshipField::FIELD_VARIATION_ID => "c8pimlr7n0ig3a0pt2kg",
                        FlagshipField::FIELD_IS_REFERENCE => false,
                        FlagshipField::FIELD_CAMPAIGN_TYPE => "ab",
                        VisitorStrategyAbstract::ACTIVATED => false,
                        VisitorStrategyAbstract::FLAGS => [
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
            return [VisitorStrategyAbstract::LOOKUP_VISITOR_JSON_OBJECT_ERROR,
                [FlagshipConstant::TAG => $functionName]];
        };

        $logManagerStub->expects($this->exactly(5))->method('error')
            ->withConsecutive(
                $lookupVisitorJson(),
                $lookupVisitorJson(),
                [sprintf(VisitorStrategyAbstract::VISITOR_ID_MISMATCH_ERROR, $differentVisitorId, $visitorId),
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
            ->setDecisionManager($decisionManager);


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
                VisitorStrategyAbstract::ACTIVATED => false,
                VisitorStrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }

        $visitorCache = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitorId,
                VisitorStrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                VisitorStrategyAbstract::CONSENT => $visitor->hasConsented(),
                VisitorStrategyAbstract::CONTEXT => $visitor->getContext(),
                VisitorStrategyAbstract::CAMPAIGNS => $campaigns,
                VisitorStrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
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
                VisitorStrategyAbstract::ACTIVATED => false,
                VisitorStrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }


        $visitorCache2 = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitorId,
                VisitorStrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                VisitorStrategyAbstract::CONSENT => $visitor->hasConsented(),
                VisitorStrategyAbstract::CONTEXT => $visitor->getContext(),
                VisitorStrategyAbstract::CAMPAIGNS => $campaigns2,
                VisitorStrategyAbstract::ASSIGNMENTS_HISTORY => array_merge($assignmentsHistory, $assignmentsHistory2)
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

        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($decisionManager);


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
                VisitorStrategyAbstract::ACTIVATED => false,
                VisitorStrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }

        $visitorCache = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitorId,
                VisitorStrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                VisitorStrategyAbstract::CONSENT => $visitor->hasConsented(),
                VisitorStrategyAbstract::CONTEXT => $visitor->getContext(),
                VisitorStrategyAbstract::CAMPAIGNS => $campaigns,
                VisitorStrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
            ]
        ];


        $visitor->visitorCache = $visitorCache;

        $visitor->fetchFlags();

        $this->assertCount(7, $visitor->getFlagsDTO());

        $visitor->visitorCache = [];

        $visitor->fetchFlags();

        $this->assertCount(0, $visitor->getFlagsDTO());
    }
}

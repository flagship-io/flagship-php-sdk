<?php

namespace Flagship;

use Flagship\Decision\ApiManager;
use Flagship\Decision\DecisionManagerAbstract;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\HitType;
use Flagship\Hit\Event;
use Flagship\Hit\Item;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Hit\Transaction;
use Flagship\Model\Modification;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\HttpClient;
use PHPUnit\Framework\TestCase;

class VisitorTest extends TestCase
{

    /**
     * @return Visitor
     */
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

        $visitor = new Visitor($configManager, $visitorId, $visitorContext);
        $this->assertEquals($visitorId, $visitor->getVisitorId());

        //Test new visitorId

        $newVisitorId = 'new_visitor_id';
        $visitor->setVisitorId($newVisitorId);
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //Begin Test visitor null

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
        $logManagerStub->expects($this->exactly(2))->method('error');

        $config->setLogManager($logManagerStub);

        $visitor->setVisitorId(null);
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //Test visitor is empty
        $visitor->setVisitorId("");
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //End Test visitor null

        //Test context
        $this->assertSame($visitorContext, $visitor->getContext());

        //Test ClearContext
        $visitor->clearContext();

        $this->assertCount(0, $visitor->getContext());

        return $visitor;
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
        $config = new FlagshipConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);


        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new Visitor($configManager, $visitorId, $visitorContext);
        //Test number value
        $ageKey = 'age';
        $newAge = 45;
        $visitor->updateContext($ageKey, $newAge);
        $context = $visitor->getContext();
        $this->assertArrayHasKey($ageKey, $context);
        $this->assertEquals($newAge, $context[$ageKey]);

        //Test bool value
        $isAdminKey = "isAdmin";
        $isAdmin = true;
        $visitor->updateContext($isAdminKey, $isAdmin);
        $context = $visitor->getContext();
        $this->assertArrayHasKey($isAdminKey, $context);
        $this->assertEquals($isAdmin, $context[$isAdminKey]);

        //Test string value
        $townKey = "town";
        $town = 'visitor_town';

        $visitor->updateContext($townKey, $town);
        $context = $visitor->getContext();
        $this->assertArrayHasKey($townKey, $context);
        $this->assertEquals($town, $context[$townKey]);

        $collectionContext = [
            'address' => 'visitor_address',
            'browser' => 'chrome'
        ];

        $visitor->updateContext('extra_info', $collectionContext);

        //Test value!= string,number, bool
        $this->assertArrayNotHasKey('extra_info', $visitor->getContext());

        //Test value ==null
        $visitor->updateContext('os', null);
        $this->assertArrayNotHasKey('os', $visitor->getContext());

        $visitor->setContext([]);

        //Test key ==null
        $visitor->updateContext(null, "Hp");
        $this->assertCount(0, $visitor->getContext());

        //Test key=null and value==null
        $visitor->updateContext(null, null);
        $this->assertCount(0, $visitor->getContext());

        //Test key is empty
        $visitor->updateContext("", "Hp");
        $this->assertCount(0, $visitor->getContext());

        //Test value is empty
        $visitor->updateContext("computer", "");
        $this->assertCount(0, $visitor->getContext());

        //Test value and key are empty
        $visitor->updateContext("", "");
        $this->assertCount(0, $visitor->getContext());
    }


    public function testUpdateContextCollection()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];
        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new Visitor($configManager, $visitorId, $visitorContext);

        $newVisitorContext = [
            'vip' => true,
            'gender' => 'F'
        ];
        $visitor->updateContextCollection($newVisitorContext);
        $this->assertCount(4, $visitor->getContext());

        //Test without Key

        $newVisitorContext = [
            'vip'
        ];

        $visitor->updateContextCollection($newVisitorContext);
        $this->assertCount(4, $visitor->getContext());
    }

    /**
     * @return \array[][]|Modification[]
     */
    public function modifications()
    {
        return [[[
            (new Modification())
                ->setKey('background')
                ->setValue('EE3300')
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
                ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
                ->setVariationId('c1e3t1nvfu1ncqfcdcq0'),
            (new Modification())
                ->setKey('borderColor')
                ->setValue('blue')
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new Modification())
                ->setKey('Null')
                ->setValue(null)
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new Modification())
                ->setKey('Empty')
                ->setValue("")
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new Modification())
                ->setKey('isBool')
                ->setValue(false)
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
            (new Modification())
                ->setKey('Number')
                ->setValue(5)
                ->setIsReference(false)
                ->setVariationGroupId('c1e3t1sddfu1ncqfcdcp0')
                ->setCampaignId('c1slf3t1nvfu1ncqfcdcfd')
                ->setVariationId('cleo3t1nvfu1ncqfcdcsdf'),
        ]]];
    }

    /**
     * @dataProvider modifications
     * @param Modification[] $modifications
     */
    public function testSynchronizedModifications($modifications)
    {

        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getCampaignModifications', 'getConfig']
        );
        $config = new FlagshipConfig('envId', 'apiKey');

        $apiManagerStub->expects($this->once())->method('getCampaignModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new Visitor($configManager, "visitorId", []);

        $visitor->synchronizedModifications();

//        $this->assertSame($modifications, $visitor->getModifications());

        //Test getModification keyValue is string and DefaultValue is string
        //Return KeyValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = "red";
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is boolean and DefaultValue is boolean
        //Return KeyValue

        $key = $modifications[4]->getKey();
        $keyValue = $modifications[4]->getValue();
        $defaultValue = false;
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is numeric and DefaultValue is numeric
        //Return KeyValue

        $key = $modifications[5]->getKey();
        $keyValue = $modifications[5]->getValue();
        $defaultValue = 14;
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = 25; // default is numeric
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = true; // default is boolean
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = []; // is not numeric and bool and string
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification key is not string or is empty
        //Return DefaultValue

        $defaultValue = true;
        $modificationValue = $visitor->getModification(null, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        $defaultValue = 58;
        $modificationValue = $visitor->getModification('', $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification keyValue is null
        //Return DefaultValue

        $key = $modifications[2]->getKey();
        $keyValue = $modifications[2]->getValue();
        $defaultValue = 14;
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification keyValue is empty
        //Return DefaultValue

        $key = $modifications[3]->getKey();
        $keyValue = $modifications[3]->getValue();
        $defaultValue = "blue-border";
        $modificationValue = $visitor->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification key is not exist
        //Return DefaultValue
        $defaultValue = "blue-border";
        $modificationValue = $visitor->getModification('keyNotExist', $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

//        //Test getModification on Panic Mode
//        $apiManagerStub->setIsPanicMode(true);
//        $modificationValue = $visitor->getModification($modifications[0]->getKey(), $defaultValue);
//        $this->assertSame($defaultValue, $modificationValue);
    }

    /**
     * @dataProvider modifications
     * @param Modification[] $modifications
     */
    public function testSynchronizedModificationsWithoutDecisionManager($modifications)
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

        $config = new FlagshipConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new Visitor($configManager, "visitorId", []);

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerStub->expects($this->exactly(1))->method('error')
            ->with(
                "[$flagshipSdk] " . FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => FlagshipConstant::TAG_SYNCHRONIZED_MODIFICATION]
            );

        $visitor->synchronizedModifications();
    }

    /**
     * @dataProvider modifications
     * @param Modification[] $modifications
     */
    public function testGetModificationLog($modifications)
    {
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getCampaignModifications', 'getConfig']
        );
        $config = new FlagshipConfig('envId', 'apiKey');

        $apiManagerStub->method('getCampaignModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $visitorMock = $this->getMockBuilder('Flagship\Visitor')
            ->setMethods(['logError'])
            ->setConstructorArgs([$configManager, "visitorId", []])->getMock();

        $visitorMock->synchronizedModifications();

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

        $visitorMock->expects($this->exactly(4))
            ->method('logError')
            ->withConsecutive(
                $expectedParams[0],
                $expectedParams[1],
                $expectedParams[2],
                $expectedParams[2]
            );
        $visitorMock->getModification($key, $defaultValue);

        //Test getModification key is not exist
        //Return DefaultValue

        $key = "notExistKey";
        $defaultValue = true;

        $expectedParams[] = [$config,
            sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]];

        $visitorMock->getModification($key, $defaultValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = 25; // default is numeric

        $expectedParams[] = [$config,
            sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]];

        $visitorMock->getModification($key, $defaultValue);

        ////Test getModification on Panic Mode
        //Return DefaultValue

        $expectedParams[] = [$config,
            sprintf(FlagshipConstant::PANIC_MODE_ERROR, "getModification"),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION]];

        $apiManagerStub->setIsPanicMode(true);
        $visitorMock->getModification($key, $defaultValue);
    }

    /**
     * @dataProvider modifications
     * @param Modification[] $modifications
     */
    public function testGetModificationInfo($modifications)
    {
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getCampaignModifications']
        );

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $config = new FlagshipConfig('envId', 'apiKey');


        $config->setLogManager($logManagerStub);
        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $paramsExpected = [];

        $apiManagerStub->method('getCampaignModifications')->willReturn($modifications);

        $logManagerStub->expects($this->exactly(3))->method('error')
            ->withConsecutive($paramsExpected);

        $visitor = new Visitor($configManager, "visitorId", []);

        $visitor->synchronizedModifications();

        $modification = $modifications[0];

        $campaignExpected = [
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference(),
            FlagshipField::FIELD_VALUE => $modification->getValue()
        ];

        //Test key exist in modifications set

        $campaign = $visitor->getModificationInfo($modification->getKey());
        $this->assertSame($campaignExpected, $campaign);

        //Test key doesn't exist in modifications set
        $notExistKey = "notExistKey";
        $paramsExpected[] = [sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $notExistKey),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]];

        $campaign = $visitor->getModificationInfo($notExistKey);
        $this->assertNull($campaign);

        //Test Key is null
        $paramsExpected[] = [sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, null),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]];

        $campaign = $visitor->getModificationInfo(null);
        $this->assertNull($campaign);

//        //Test on Panic Mode
//        $paramsExpected[] = [sprintf(FlagshipConstant::PANIC_MODE_ERROR, "getModificationInfo"),
//            [FlagshipConstant::TAG => FlagshipConstant::TAG_GET_MODIFICATION_INFO]];
//
//        $apiManagerStub->setIsPanicMode(true);
//        $campaign = $visitor->getModificationInfo($modification->getKey());
//        $this->assertNull($campaign);
    }

    /**
     * @dataProvider modifications
     * @param Modification[] $modifications
     */
    public function testActivateModification($modifications)
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

        $config = new FlagshipConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [new HttpClient()],
            '',
            true,
            true,
            true,
            ['getCampaignModifications']
        );

        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [new HttpClient()],
            '',
            true,
            true,
            true,
            ['sendActive']
        );

        $apiManagerStub->method('getCampaignModifications')
            ->willReturn($modifications);

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($apiManagerStub)
            ->setTrackingManager($trackerManagerStub);

        $visitor = new Visitor($configManager, "visitorId", []);

        $trackerManagerStub->expects($this->once())
            ->method('sendActive')
            ->with($visitor, $modifications[0]);

        $visitor->synchronizedModifications();

        $visitor->activateModification($modifications[0]->getKey());

        $paramsExpected = [];
        $logManagerStub->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive($paramsExpected);

        //Test key not exist
        $key = "KeyNotExist";

        $paramsExpected[] = [sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]];

        $visitor->activateModification($key);

        //Test on panic panic Mode
        $paramsExpected[] = [sprintf(FlagshipConstant::PANIC_MODE_ERROR, "activateModification"),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]];

        $apiManagerStub->setIsPanicMode(true);
        $visitor->activateModification("anyKey");
    }

    /**
     * @dataProvider modifications
     * @param Modification[] $modifications
     */
    public function testActivateModificationWithoutTrackerManager($modifications)
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

        $config = new FlagshipConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [new HttpClient()],
            '',
            true,
            true,
            true,
            ['getCampaignModifications']
        );

        $apiManagerStub->method('getCampaignModifications')
            ->willReturn($modifications);

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setDecisionManager($apiManagerStub);


        $visitor = new Visitor($configManager, "visitorId", []);

        $visitor->synchronizedModifications();

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerStub->expects($this->exactly(1))->method('error')->with(
            "[$flagshipSdk] " . FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
            [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]
        );

        $visitor->activateModification($modifications[0]->getKey());
    }

    /**
     * @dataProvider modifications
     * @param Modification[] $modifications
     */
    public function testGetModificationWithActive($modifications)
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

        $trackerManagerStub = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [new HttpClient()],
            '',
            true,
            true,
            true,
            ['sendActive']
        );

        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [new HttpClient()],
            'ApiManagerInterface',
            true,
            true,
            true,
            ['getCampaignModifications']
        );

        $config = new FlagshipConfig('envId', 'apiKey');

        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerStub);

        $apiManagerStub->method('getCampaignModifications')
            ->willReturn($modifications);

        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new Visitor($configManager, "visitorId", []);

        $trackerManagerStub->expects($this->once())
            ->method('sendActive')
            ->with($visitor, $modifications[0]);

        $visitor->synchronizedModifications();

        $visitor->getModification($modifications[0]->getKey(), 'defaultValue', true);
    }

    public function testSendHit()
    {
        $trackerManagerMock = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [new HttpClient()],
            '',
            true,
            true,
            true,
            ['sendHit']
        );

        $envId = "envId";
        $apiKey = "apiKey";
        $visitorId = "visitorId";

        $config = new FlagshipConfig($envId, $apiKey);
        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerMock);

        $apiManager = new ApiManager(new HttpClient());

        $configManager->setDecisionManager($apiManager);

        $visitor = new Visitor($configManager, $visitorId);

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
            ->method('sendHit')
            ->withConsecutive([$page], [$screen], [$transition], [$event], [$item]);

        $visitor->sendHit($page);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $page->getVisitorId()); // test abstract class property
        $this->assertSame(HitType::PAGE_VIEW, $page->getType()); // test abstract class property

        $this->assertSame($pageUrl, $page->getPageUrl());

        // Test type screen
        $visitor->sendHit($screen);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $screen->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::SCREEN_VIEW, $screen->getType());
        $this->assertSame($screenName, $screen->getScreenName());

        //Test type Transition
        $visitor->sendHit($transition);
        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $transition->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::TRANSACTION, $transition->getType());

        //Test type Event
        $visitor->sendHit($event);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $event->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::EVENT, $event->getType());

        //Test type Item
        $visitor->sendHit($item);

        $this->assertSame($config, $page->getConfig()); // test abstract class property
        $this->assertSame($visitorId, $item->getVisitorId()); // test abstract class property

        $this->assertSame(HitType::ITEM, $item->getType());
    }

    public function testSendHitWithLog()
    {

        $trackerManagerMock = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [new HttpClient()],
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

        $config = new FlagshipConfig($envId, $apiKey);

        $config->setLogManager($logManagerMock);

        $configManager = (new ConfigManager())
            ->setConfig($config);

        $visitor = new Visitor($configManager, $visitorId);

        $pageUrl = 'https://locahost';
        $page = new Page($pageUrl);

        $paramsExpected = [];

        $logManagerMock->expects($this->exactly(4))
            ->method('error')
            ->withConsecutive($paramsExpected);

        //Test with DecisionManager is null

        $paramsExpected[] = [
            FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
            [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
        ];

        $visitor->sendHit($page);

        //Set DecisionManager

        $decisionManager = new ApiManager(new HttpClient());
        $configManager->setDecisionManager($decisionManager);

        //Test send with TrackingManager null
        $paramsExpected[] = [
            FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
            [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
        ];

        $visitor->sendHit($page);

        //Test with TrackingManager not null
        $configManager->setTrackingManager($trackerManagerMock);

        // Test SendHit with invalid require field
        $page = new Page(null);

        $trackerManagerMock->expects($this->never())
            ->method('sendHit');

        $paramsExpected[] = [
            $page->getErrorMessage(),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_SEND_HIT]
        ];

        $visitor->sendHit($page);

        //Test send Hit on Panic Mode
        $paramsExpected[] = [
            sprintf(FlagshipConstant::PANIC_MODE_ERROR, "activateModification"),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]
        ];

        $decisionManager->setIsPanicMode(true);

        $visitor->sendHit($page);
    }

    public function testJson()
    {
        $config = new FlagshipConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20];
        $configManager = (new ConfigManager())->setConfig($config);

        $visitor = new Visitor($configManager, $visitorId, $context);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'visitorId' => $visitorId,
                'context' => $context,
            ]),
            json_encode($visitor)
        );
    }

    public function testSetConsent()
    {
        $configManager = new ConfigManager();
        $visitor = new Visitor($configManager, "visitorId", []);
        $this->assertFalse($visitor->hasConsented());
        $visitor->setConsent(true);
        $this->assertTrue($visitor->hasConsented());
    }
}

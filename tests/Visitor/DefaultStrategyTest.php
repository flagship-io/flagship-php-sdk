<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
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
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use PHPUnit\Framework\TestCase;

class DefaultStrategyTest extends TestCase
{
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
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, $visitorContext);

        $defaultStrategy = new DefaultStrategy($visitor);
        //Test number value
        $ageKey = 'age';
        $newAge = 45;
        $defaultStrategy->updateContext($ageKey, $newAge);
        $context = $visitor->getContext();
        $this->assertArrayHasKey($ageKey, $context);
        $this->assertEquals($newAge, $context[$ageKey]);

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

        $collectionContext = [
            'address' => 'visitor_address',
            'browser' => 'chrome'
        ];

        $defaultStrategy->updateContext('extra_info', $collectionContext);

        //Test value!= string,number, bool
        $this->assertArrayNotHasKey('extra_info', $visitor->getContext());

        //Test value ==null
        $defaultStrategy->updateContext('os', null);
        $this->assertArrayNotHasKey('os', $visitor->getContext());

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
        $defaultStrategy->updateContext("computer", "");
        $this->assertCount(0, $visitor->getContext());

        //Test value and key are empty
        $defaultStrategy->updateContext("", "");
        $this->assertCount(0, $visitor->getContext());
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
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, $visitorContext);

        $defaultStrategy = new DefaultStrategy($visitor);
        $newVisitorContext = [
            'vip' => true,
            'gender' => 'F'
        ];
        $defaultStrategy->updateContextCollection($newVisitorContext);
        $this->assertCount(4, $visitor->getContext());

        //Test without Key

        $newVisitorContext = [
            'vip'
        ];

        $defaultStrategy->updateContextCollection($newVisitorContext);
        $this->assertCount(4, $visitor->getContext());
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
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, $visitorContext);

        $defaultStrategy = new DefaultStrategy($visitor);

        $this->assertCount(2, $visitor->getContext());

        $defaultStrategy->clearContext();

        $this->assertCount(0, $visitor->getContext());
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
        $config = new DecisionApiConfig('envId', 'apiKey');

        $apiManagerStub->expects($this->once())->method('getCampaignModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->synchronizedModifications();

        $this->assertSame($modifications, $defaultStrategy->getModifications());

        //Test getModification keyValue is string and DefaultValue is string
        //Return KeyValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = "red";
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is boolean and DefaultValue is boolean
        //Return KeyValue

        $key = $modifications[4]->getKey();
        $keyValue = $modifications[4]->getValue();
        $defaultValue = false;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is numeric and DefaultValue is numeric
        //Return KeyValue

        $key = $modifications[5]->getKey();
        $keyValue = $modifications[5]->getValue();
        $defaultValue = 14;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
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

        $key = $modifications[2]->getKey();
        $defaultValue = 14;
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);

        //Test getModification keyValue is empty
        //Return DefaultValue

        $key = $modifications[3]->getKey();
        $keyValue = $modifications[3]->getValue();
        $defaultValue = "blue-border";
        $modificationValue = $defaultStrategy->getModification($key, $defaultValue);
        $this->assertSame($keyValue, $modificationValue);

        //Test getModification key is not exist
        //Return DefaultValue
        $defaultValue = "blue-border";
        $modificationValue = $defaultStrategy->getModification('keyNotExist', $defaultValue);
        $this->assertSame($defaultValue, $modificationValue);
    }


    public function testSynchronizedModificationsWithoutDecisionManager()
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

        $configManager = (new ConfigManager())->setConfig($config);
        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);
        $defaultStrategy = new DefaultStrategy($visitor);

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerStub->expects($this->exactly(1))->method('error')
            ->with(
                "[$flagshipSdk] " . FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => FlagshipConstant::TAG_SYNCHRONIZED_MODIFICATION]
            );

        $defaultStrategy->synchronizedModifications();
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

        $config = new DecisionApiConfig('envId', 'apiKey');

        $config->setLogManager($logManagerStub);

        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerStub);

        $apiManagerStub->method('getCampaignModifications')
            ->willReturn($modifications);

        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);
        $defaultStrategy = new DefaultStrategy($visitor);

        $trackerManagerStub->expects($this->exactly(2))
            ->method('sendActive')
            ->withConsecutive([$visitor, $modifications[0]], [$visitor, $modifications[2]]);

        $defaultStrategy->synchronizedModifications();

        $defaultStrategy->getModification($modifications[0]->getKey(), 'defaultValue', true);

        //Test activate on get Modification when value is null

        $defaultStrategy->getModification($modifications[2]->getKey(), 'defaultValue', true);
    }

    /**
     *
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
        $config = new DecisionApiConfig('envId', 'apiKey');

        $apiManagerStub->method('getCampaignModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);

        $defaultStrategyMock = $this->getMockBuilder('Flagship\Visitor\DefaultStrategy')
            ->setMethods(['logError'])
            ->setConstructorArgs([$visitor])->getMock();

        $defaultStrategyMock->synchronizedModifications();

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

        $defaultStrategyMock->expects($this->exactly(3))
            ->method('logError')
            ->withConsecutive(
                $expectedParams[0],
                $expectedParams[1],
                $expectedParams[2]
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

        $config = new DecisionApiConfig('envId', 'apiKey');


        $config->setLogManager($logManagerStub);
        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $paramsExpected = [];

        $apiManagerStub->method('getCampaignModifications')->willReturn($modifications);

        $logManagerStub->expects($this->exactly(2))->method('error')
            ->withConsecutive($paramsExpected);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->synchronizedModifications();

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

        $config = new DecisionApiConfig('envId', 'apiKey');
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

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);

        $defaultStrategy = new DefaultStrategy($visitor);

        $trackerManagerStub->expects($this->once())
            ->method('sendActive')
            ->with($visitor, $modifications[0]);

        $defaultStrategy->synchronizedModifications();

        $defaultStrategy->activateModification($modifications[0]->getKey());

        $paramsExpected = [];
        $logManagerStub->expects($this->exactly(1))
            ->method('error')
            ->withConsecutive($paramsExpected);

        //Test key not exist
        //Call $logManagerStub->error
        $key = "KeyNotExist";

        $paramsExpected[] = [sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]];

        $defaultStrategy->activateModification($key);
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

        $config = new DecisionApiConfig('envId', 'apiKey');
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


        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);
        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->synchronizedModifications();

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerStub->expects($this->exactly(1))->method('error')->with(
            "[$flagshipSdk] " . FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
            [FlagshipConstant::TAG => FlagshipConstant::TAG_ACTIVE_MODIFICATION]
        );

        //Call error
        $defaultStrategy->activateModification($modifications[0]->getKey());
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

        $config = new DecisionApiConfig($envId, $apiKey);
        $configManager = (new ConfigManager())
            ->setConfig($config)
            ->setTrackingManager($trackerManagerMock);

        $apiManager = new ApiManager(new HttpClient());

        $configManager->setDecisionManager($apiManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId);
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
            ->method('sendHit')
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

        $config = new DecisionApiConfig($envId, $apiKey);

        $config->setLogManager($logManagerMock);

        $configManager = (new ConfigManager())
            ->setConfig($config);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId);

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
}

<?php

namespace Flagship;

use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Model\Modification;
use Flagship\Utils\HttpClient;
use PHPUnit\Framework\TestCase;

class VisitorTest extends TestCase
{

    /**
     * @return Visitor
     */
    public function testConstruct()
    {
        $configData = ['envId' => 'env_value','apiKey' => 'key_value'];
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $apiManager = new ApiManager($config, new HttpClient());

        $visitorId = "visitor_id";
        $ageKey = 'age';
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];
        $visitor = new Visitor($apiManager, $visitorId, $visitorContext);
        $this->assertEquals($visitorId, $visitor->getVisitorId());

        //Test new visitorId

        $newVisitorId = 'new_visitor_id';
        $visitor->setVisitorId($newVisitorId);
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //Begin Test visitor null

        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );
        $logManagerStub->expects($this->exactly(2))->method('error');

        $apiManager->getConfig()->setLogManager($logManagerStub);
        $visitor->setVisitorId(null);
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //Test visitor is empty
        $visitor->setVisitorId("");
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //End Test visitor null

        //Test context
        $this->assertEquals($visitorContext['name'], $visitor->getContext()['name']);
        $this->assertEquals($visitorContext[$ageKey], $visitor->getContext()[$ageKey]);

        return $visitor;
    }

    /**
     * @depends testConstruct
     * @param   $visitor
     */
    public function testUpdateContext($visitor)
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );
        $config = new FlagshipConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $apiManager = new ApiManager($config, new HttpClient());

        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $visitor = new Visitor($apiManager, $visitorId, $visitorContext);
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
        $configData = ['envId' => 'env_value','apiKey' => 'key_value'];
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];
        $apiManager = new ApiManager($config, new HttpClient());
        $visitor = new Visitor($apiManager, $visitorId, $visitorContext);

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
     * @param        Modification[] $modifications
     */
    public function testSynchronizedModifications($modifications)
    {
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\ApiManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getCampaignsModifications','getConfig']
        );
        $config = new FlagshipConfig('envId', 'apiKey');

        $apiManagerStub->method('getCampaignsModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $visitor = new Visitor($apiManagerStub, "visitorId", []);

        $visitor->synchronizedModifications();
        $this->assertSame($modifications, $visitor->getModifications());

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
        $this->assertSame($defaultValue, $defaultValue);

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
    }

    /**
     * @dataProvider modifications
     * @param        Modification[] $modifications
     */
    public function testGetModificationLog($modifications)
    {
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\ApiManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getCampaignsModifications','getConfig']
        );
        $config = new FlagshipConfig('envId', 'apiKey');

        $apiManagerStub->method('getCampaignsModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $visitorMock = $this->getMockBuilder('Flagship\Visitor')
            ->setMethods(['logError'])
            ->setConstructorArgs([$apiManagerStub, "visitorId", []])->getMock();

        $visitorMock->synchronizedModifications();

        //Test getModification key is null
        //Return DefaultValue
        $defaultValue = true;
        $key = null;

        $expectedParams = [
            [$apiManagerStub->getConfig()->getLogManager(),
                sprintf(FlagshipConstant::GET_MODIFICATION_KEY_ERROR, $key),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]
            ],[],[]
        ];

        $visitorMock->expects($this->exactly(3))
            ->method('logError')
            ->withConsecutive(
                $expectedParams[0],
                $expectedParams[1],
                $expectedParams[2]
            );
        $visitorMock->getModification($key, $defaultValue);

        //Test getModification key is not exist
        //Return DefaultValue

        $key = "notExistKey";
        $defaultValue = true;

        $expectedParams[] = [$apiManagerStub->getConfig()->getLogManager(),
            sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
            [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]];

        $visitorMock->getModification($key, $defaultValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = 25; // default is numeric

        $expectedParams[] = [$apiManagerStub->getConfig()->getLogManager(),
            sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
            [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]];

        $visitorMock->getModification($key, $defaultValue);
    }

    /**
     * @dataProvider modifications
     * @param        Modification[] $modifications
     */
    public function testGetModificationInfo($modifications)
    {
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\ApiManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getCampaignsModifications','getConfig']
        );
        $config = new FlagshipConfig('envId', 'apiKey');

        $apiManagerStub->method('getCampaignsModifications')->willReturn($modifications);
        $apiManagerStub->method('getConfig')->willReturn($config);

        $visitor = new Visitor($apiManagerStub, "visitorId", []);
        $visitor->synchronizedModifications();

        $modification = $modifications[0];

        $campaignExpected = [
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference()
        ];

        //Test key exist in modifications set

        $campaign = $visitor->getModificationInfo($modification->getKey());
        $this->assertSame($campaignExpected, $campaign);

        //Test key doesn't exist in modifications set
        $campaign = $visitor->getModificationInfo('notExistKey');
        $this->assertNull($campaign);

        //Test Key is null
        $campaign = $visitor->getModificationInfo(null);
        $this->assertNull($campaign);
    }

    /**
     * @dataProvider modifications
     * @param        Modification[] $modifications
     */
    public function testActivateModification($modifications)
    {

        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
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
            'Flagship\Decision\ApiManagerAbstract',
            [$config, new HttpClient()],
            'ApiManagerInterface',
            true,
            true,
            true,
            ['getCampaignsModifications','sendActiveModification']
        );

        $apiManagerStub->method('getCampaignsModifications')
            ->willReturn($modifications);

        $visitor = new Visitor($apiManagerStub, "visitorId", []);

        $apiManagerStub->expects($this->once())
            ->method('sendActiveModification')
            ->with($visitor, $modifications[0]);

        $visitor->synchronizedModifications();

        $visitor->activateModification($modifications[0]->getKey());

        //Test ke not exist
        $key = "KeyNotExist";
        $logManagerStub->expects($this->exactly(1))->method('error')->with(
            sprintf(FlagshipConstant::GET_MODIFICATION_ERROR, $key),
            [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_ACTIVE_MODIFICATION]);

        $visitor->activateModification($key);
    }

    /**
     * @dataProvider modifications
     * @param        Modification[] $modifications
     */
    public function testGetModificationWithActive($modifications)
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
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
            'Flagship\Decision\ApiManagerAbstract',
            [$config, new HttpClient()],
            'ApiManagerInterface',
            true,
            true,
            true,
            ['getCampaignsModifications','sendActiveModification']
        );

        $apiManagerStub->method('getCampaignsModifications')
            ->willReturn($modifications);

        $visitor = new Visitor($apiManagerStub, "visitorId", []);

        $apiManagerStub->expects($this->once())
            ->method('sendActiveModification')
            ->with($visitor, $modifications[0]);

        $visitor->synchronizedModifications();

        $visitor->getModification($modifications[0]->getKey(),'defaultValue', true);
    }
}

<?php

namespace Flagship;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Model\Modification;
use Flagship\Utils\Utils;
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

        $visitorId = "visitor_id";
        $ageKey = 'age';
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];
        $visitor = new Visitor($config, $visitorId, $visitorContext);
        $this->assertInstanceOf("Flagship\FlagshipConfig", $visitor->getConfig());
        $this->assertEquals($visitorId, $visitor->getVisitorId());

        //Test new visitorId

        $newVisitorId = 'new_visitor_id';
        $visitor->setVisitorId($newVisitorId);
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //Begin Test visitor null

        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Interfaces\LogManagerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );
        $logManagerStub->expects($this->exactly(2))->method('error');

        $visitor->getConfig()->setLogManager($logManagerStub);
        $visitor->setVisitorId(null);
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //Test visitor is empty
        $visitor->setVisitorId("");
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());


        //End Test visitor null

        //Test context
        $this->assertEquals($visitorContext['name'], $visitor->getContext()['name']);
        $this->assertEquals($visitorContext[$ageKey], $visitor->getContext()[$ageKey]);

        //Test setConfig

        $config2 = new FlagshipConfig($newVisitorId, $configData['apiKey']);
        $visitor->setConfig($config2);
        $this->assertInstanceOf("Flagship\FlagshipConfig", $visitor->getConfig());
        $this->assertSame($config2, $visitor->getConfig());
        return $visitor;
    }

    /**
     * @depends testConstruct
     * @param   $visitor
     */
    public function testUpdateContext($visitor)
    {
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
        $visitor = new Visitor($config, $visitorId, $visitorContext);

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
            'Flagship\Interfaces\ApiManagerInterface',
            ['getCampaignsModifications'],
            'ApiManagerInterface',
            false
        );
        $apiManagerStub->method('getCampaignsModifications')->willReturn($modifications);
        $config = new FlagshipConfig("EnvId", 'ApiKey');
        $visitor = new Visitor($config, "visitorId", []);
        Utils::setPrivateProperty($visitor, 'decisionAPi', $apiManagerStub);
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
            'Flagship\Interfaces\ApiManagerInterface',
            ['getCampaignsModifications'],
            'ApiManagerInterface',
            false
        );
        $apiManagerStub->method('getCampaignsModifications')->willReturn($modifications);
        $config = new FlagshipConfig("EnvId", 'ApiKey');
        $visitorMock = $this->getMockBuilder('Flagship\Visitor')
            ->setMethods(['logError'])
            ->setConstructorArgs([$config, "visitorId", []])->getMock();

        Utils::setPrivateProperty($visitorMock, 'decisionAPi', $apiManagerStub, 'Flagship\Visitor');

        $visitorMock->synchronizedModifications();

        //Test getModification key is null
        //Return DefaultValue
        $defaultValue = true;
        $key = null;
        $visitorMock->expects($this->at(0))
            ->method('logError')
            ->with(
                $config->getLogManager(),
                sprintf(FlagshipConstant::GET_MODIFICATION_KEY_ERROR, $key),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]
            );
        $visitorMock->getModification($key, $defaultValue);

        //Test getModification key is not exist
        //Return DefaultValue

        $key = "notExistKey";
        $defaultValue = true;

        $visitorMock->expects($this->at(0))
            ->method('logError')
            ->with(
                $config->getLogManager(),
                sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]
            );
        $visitorMock->getModification($key, $defaultValue);

        //Test getModification keyValue is string and DefaultValue is not string
        //Return DefaultValue

        $key = $modifications[0]->getKey();
        $keyValue = $modifications[0]->getValue();
        $defaultValue = 25; // default is numeric
        $visitorMock->expects($this->at(0))
            ->method('logError')
            ->with(
                $config->getLogManager(),
                sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]
            );
        $visitorMock->getModification($key, $defaultValue);
    }

    /**
     * @dataProvider modifications
     * @param        Modification[] $modifications
     */
    public function testGetModificationInfo($modifications)
    {
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Interfaces\ApiManagerInterface',
            ['getCampaignsModifications'],
            'ApiManagerInterface',
            false
        );
        $apiManagerStub->method('getCampaignsModifications')->willReturn($modifications);
        $config = new FlagshipConfig("EnvId", 'ApiKey');
        $visitor = new Visitor($config, "visitorId", []);
        Utils::setPrivateProperty($visitor, 'decisionAPi', $apiManagerStub);
        $visitor->synchronizedModifications();

        $modification = $modifications[0];

        $campaign = [
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference()
        ];
        $campaignJsonExpected = json_encode($campaign);

        //Test key exist in modifications set

        $campaignJson = $visitor->getModificationInfo($modification->getKey());
        $this->assertJsonStringEqualsJsonString($campaignJsonExpected, $campaignJson);

        //Test key doesn't exist in modifications set
        $campaignJson = $visitor->getModificationInfo('notExistKey');
        $this->assertNull($campaignJson);

        //Test Key is null
        $campaignJson = $visitor->getModificationInfo(null);
        $this->assertNull($campaignJson);
    }
}

<?php

namespace Flagship;

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
        $logManagerStub = $this->getMockBuilder('Flagship\utils\LogManager')->getMock();
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
     * @param $visitor
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
}

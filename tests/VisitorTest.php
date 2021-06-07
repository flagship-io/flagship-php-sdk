<?php

namespace Flagship;

use Flagship\Hit\Page;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Visitor\VisitorDelegate;
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

        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, $visitorContext);

        $visitor = new Visitor($visitorDelegate);
        $this->assertEquals($visitorId, $visitor->getVisitorId());

        //Test new visitorId

        $newVisitorId = 'new_visitor_id';
        $visitor->setVisitorId($newVisitorId);
        $this->assertEquals($newVisitorId, $visitor->getVisitorId());

        //Test consent
        $this->assertFalse($visitor->hasConsented());
        $visitor->setConsent(true);
        $this->assertTrue($visitor->hasConsented());

        //Test Config
        $this->assertSame($config, $visitor->getConfig());

        return $visitor;
    }

    public function testMethods()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new FlagshipConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);

        $visitorDelegateMock = $this->getMockBuilder('Flagship\Visitor\VisitorDelegate')
            ->setMethods([
                'getContext', 'setContext', 'updateContext', 'updateContextCollection',
                'clearContext', 'getModification','getModifications','getModificationInfo', 'synchronizedModifications',
                'activateModification', 'sendHit'
                ])
            ->setConstructorArgs([new Container(),$configManager, $visitorId, $visitorContext])->getMock();

        $visitor = new Visitor($visitorDelegateMock);

        //Test getContext
        $visitorDelegateMock->expects($this->once())->method('getContext');
        $visitor->getContext();

        //test SetContext
        $visitorDelegateMock->expects($this->once())
            ->method('setContext')
            ->with($visitorContext);

        $visitor->setContext($visitorContext);

        //test updateContext
        $key = "age";
        $value = 20;
        $visitorDelegateMock->expects($this->once())
            ->method('updateContext')
            ->with($key, $value);

        $visitor->updateContext($key, $value);

        //test updateContextCollection
        $visitorDelegateMock->expects($this->once())
            ->method('updateContextCollection')
            ->with($visitorContext);

        $visitor->updateContextCollection($visitorContext);

        //Test clearContext
        $visitorDelegateMock->expects($this->once())->method('clearContext');
        $visitor->clearContext();

        //Test getModification
        $key = "age";
        $defaultValue = 20;

        $visitorDelegateMock->expects($this->once())
            ->method('getModification')
            ->with($key, $defaultValue, false);

        $visitor->getModification($key, $defaultValue, false);

        //Test getModificationInfo
        $key = "age";
        $visitorDelegateMock->expects($this->once())
            ->method('getModificationInfo')
            ->with($key);

        $visitor->getModificationInfo($key);

        //Test getModifications
        $key = "age";
        $visitorDelegateMock->expects($this->once())
            ->method('getModifications');

        $visitor->getModifications();

        //Test synchronizedModifications
        $visitorDelegateMock->expects($this->once())
            ->method('synchronizedModifications');

        $visitor->synchronizedModifications();

        //Test activateModification
        $key = "age";
        $visitorDelegateMock->expects($this->once())
            ->method('activateModification')->with($key);

        $visitor->activateModification($key);

        //Test sendHit
        $hit = new Page("http://localhost");
        $visitorDelegateMock->expects($this->once())
            ->method('sendHit')->with($hit);

        $visitor->sendHit($hit);
    }

    public function testJson()
    {
        $config = new FlagshipConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20];
        $configManager = (new ConfigManager())->setConfig($config);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, $context);

        $visitor = new Visitor($visitorDelegate);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'visitorId' => $visitorId,
                'context' => $context,
                'hasConsent' => false
            ]),
            json_encode($visitor)
        );
    }
}

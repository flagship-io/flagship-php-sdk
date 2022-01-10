<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\Page;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;

class VisitorTest extends TestCase
{

    /**
     * @return Visitor
     */
    public function testConstruct()
    {
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $ageKey = 'age';
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $decisionManagerMock = $this->getMockBuilder('Flagship\Api\TrackingManager')
            ->setMethods(['sendConsentHit'])
            ->disableOriginalConstructor()->getMock();


        $configManager = (new ConfigManager())->setConfig($config)->setTrackingManager($decisionManagerMock);

        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $visitorContext);

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
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";
        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);

        $visitorDelegateMock = $this->getMockBuilder('Flagship\Visitor\VisitorDelegate')
            ->setMethods([
                'getContext', 'setContext', 'updateContext', 'updateContextCollection',
                'clearContext', 'authenticate', 'unauthenticate','getAnonymousId',
                'getModification','getModifications','getModificationInfo', 'synchronizeModifications',
                'activateModification', 'sendHit'
                ])
            ->setConstructorArgs([new Container(),$configManager, $visitorId, false, $visitorContext, true])->getMock();

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

        //Test getAnonymousId
        $visitorDelegateMock->expects($this->once())->method('getAnonymousId');
        $visitor->getAnonymousId();

        //Test authenticate
        $newVisitorId = "newVisitorId";
        $visitorDelegateMock->expects($this->once())->method('authenticate')
            ->with($newVisitorId);
        $visitor->authenticate($newVisitorId);

        //Test unauthenticate
        $visitorDelegateMock->expects($this->once())->method('unauthenticate');
        $visitor->unauthenticate();

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
            ->method('synchronizeModifications');

        $visitor->synchronizeModifications();

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
        $config = new DecisionApiConfig();
        $visitorId = "visitor_id";
        $context = ["age" => 20,
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,];
        $configManager = (new ConfigManager())->setConfig($config);
        $visitorDelegate = new VisitorDelegate(new Container(), $configManager, $visitorId, false, $context, true);

        $visitor = new Visitor($visitorDelegate);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'visitorId' => $visitorId,
                'context' => $context,
                'hasConsent' => true
            ]),
            json_encode($visitor)
        );
    }
}

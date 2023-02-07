<?php

namespace Flagship\Api;

require_once __DIR__. "/Round.php";

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\Activate;
use Flagship\Hit\Event;
use Flagship\Hit\Page;
use PHPUnit\Framework\TestCase;

class BatchingContinuousCachingStrategyTest extends TestCase
{
    public function testGeneralMethods()
    {
        $config = new DecisionApiConfig();
        //Mock class Curl
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);

        $page = new Page("http://localhost");
        $key = "page-key";

        $strategy->hydrateHitsPoolQueue($key, $page);
        $this->assertSame([$key=>$page], $strategy->getHitsPoolQueue());

        $activate = new Activate("varGroupId", "varID");
        $activateKey = "activate-key";
        $strategy->hydrateActivatePoolQueue($activateKey, $activate);
        $this->assertSame([$activateKey=>$activate], $strategy->getActivatePoolQueue());

        //Test getNow method
        $this->assertEquals(0, $strategy->getNow());

        //Test getActivateHeaders
        $activateHeaders = [
            FlagshipConstant::HEADER_X_API_KEY => $config->getApiKey(),
            FlagshipConstant::HEADER_X_SDK_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON,
            FlagshipConstant::HEADER_X_SDK_CLIENT => FlagshipConstant::SDK_LANGUAGE,
        ];
        $this->assertSame($activateHeaders, $strategy->getActivateHeaders());

        //Test generateHitKey method
        $visitorId = "visitorId";
        $this->assertRegExp("/^$visitorId:/", $strategy->generateHitKey($visitorId));
    }

    public function testAddHit()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";
        //Mock class Curl
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["flushHits","cacheHit"],
            '',
            false
        );

        $hitCacheImplementationMock->expects($this->once())
            ->method("flushHits")->will($this->returnCallback(function ($args) use ($visitorId) {
                foreach ($args as $arg) {
                    $this->assertStringStartsWith($visitorId, $arg);
                }
                $this->assertCount(1, $args);
            }));

        $hitCacheImplementationMock->expects($this->exactly(3))->method("cacheHit");

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);


        $page = new Page("http://localhost");

        $page->setConfig($config)->setVisitorId($visitorId);
        $strategy->addHit($page);

        $this->assertContains($page, $strategy->getHitsPoolQueue());
        $this->assertCount(1, $strategy->getHitsPoolQueue());

        // Test consent true
        $consentHit1 = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit1->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "true");
        $consentHit1->setConfig($config);
        $consentHit1->setVisitorId($visitorId);

        $strategy->addHit($consentHit1);

        $this->assertContains($page, $strategy->getHitsPoolQueue());
        $this->assertContains($consentHit1, $strategy->getHitsPoolQueue());
        $this->assertCount(2, $strategy->getHitsPoolQueue());

        // Test consent false
        $consentHit = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "false");
        $consentHit->setConfig($config);
        $consentHit->setVisitorId($visitorId);

        $strategy->addHit($consentHit);

        $this->assertNotContains($page, $strategy->getHitsPoolQueue());
        $this->assertContains($consentHit1, $strategy->getHitsPoolQueue());
        $this->assertContains($consentHit, $strategy->getHitsPoolQueue());
        $this->assertCount(2, $strategy->getHitsPoolQueue());
    }

    public function testActivateFlag()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";
        //Mock class Curl
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["cacheHit"],
            '',
            false
        );

        $activate = new Activate("varGrId", "VarId");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("cacheHit")->will($this->returnCallback(function ($args) use ($activate) {
                $this->assertContains($activate, $args);
            }));

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);
        
        $strategy->activateFlag($activate);

        $this->assertContains($activate, $strategy->getActivatePoolQueue());
        $this->assertCount(1, $strategy->getActivatePoolQueue());
    }
}

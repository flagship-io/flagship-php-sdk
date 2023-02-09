<?php

namespace Flagship\Api;

use Exception;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\Event;
use Flagship\Hit\Page;
use Flagship\Traits\LogTrait;
use PHPUnit\Framework\TestCase;

class BatchingPeriodicCachingStrategyTest extends TestCase
{
    use LogTrait;

    public function testAddHit()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";
        $newVisitor = "newVisitor";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingPeriodicCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushHits","flushAllHits"]
        );

        $strategy->expects($this->once())->method("cacheHit");

        $strategy->expects($this->once())
            ->method("flushAllHits");

        $strategy->expects($this->never())
            ->method("flushHits");


        $page = new Page("http://localhost");
        $page->setConfig($config)->setVisitorId($visitorId);

        $strategy->addHit($page);

        $page2 = new Page("http://localhost");
        $page2->setConfig($config)->setVisitorId($newVisitor);

        $strategy->addHit($page2);

        $activate = new Activate("varGrId", "varId");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $strategy->activateFlag($activate);

        $activate2 = new Activate("varGrId", "varId");
        $activate2->setConfig($config)->setVisitorId($newVisitor);

        $strategy->activateFlag($activate2);

        $this->assertContains($page, $strategy->getHitsPoolQueue());
        $this->assertContains($page2, $strategy->getHitsPoolQueue());
        $this->assertCount(2, $strategy->getHitsPoolQueue());
        $this->assertContains($activate, $strategy->getActivatePoolQueue());
        $this->assertContains($activate2, $strategy->getActivatePoolQueue());
        $this->assertCount(2, $strategy->getActivatePoolQueue());

        // Test consent true
        $consentHit1 = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit1->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "true");
        $consentHit1->setConfig($config);
        $consentHit1->setVisitorId($visitorId);

        $strategy->addHit($consentHit1);

        $this->assertContains($page, $strategy->getHitsPoolQueue());
        $this->assertContains($page2, $strategy->getHitsPoolQueue());
        $this->assertContains($consentHit1, $strategy->getHitsPoolQueue());
        $this->assertCount(3, $strategy->getHitsPoolQueue());
        $this->assertContains($activate, $strategy->getActivatePoolQueue());
        $this->assertContains($activate2, $strategy->getActivatePoolQueue());
        $this->assertCount(2, $strategy->getActivatePoolQueue());

        // Test consent false
        $consentHit = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "false");
        $consentHit->setConfig($config);
        $consentHit->setVisitorId($visitorId);

        $strategy->addHit($consentHit);

        $this->assertNotContains($page, $strategy->getHitsPoolQueue());
        $this->assertContains($page2, $strategy->getHitsPoolQueue());
        $this->assertContains($consentHit1, $strategy->getHitsPoolQueue());
        $this->assertContains($consentHit, $strategy->getHitsPoolQueue());

        $this->assertCount(3, $strategy->getHitsPoolQueue());
        $this->assertNotContains($activate, $strategy->getActivatePoolQueue());
        $this->assertContains($activate2, $strategy->getActivatePoolQueue());
        $this->assertCount(1, $strategy->getActivatePoolQueue());

        //Test consent hit false when no hits for visitorId exist HitsPoolQueue
        $strategy->addHit($consentHit);

        $this->assertCount(4, $strategy->getHitsPoolQueue());
        $this->assertNotContains($activate, $strategy->getActivatePoolQueue());
        $this->assertContains($activate2, $strategy->getActivatePoolQueue());
        $this->assertCount(1, $strategy->getActivatePoolQueue());
    }

    public function testActivateFlag()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $activate = new Activate("varGrId", "VarId");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingPeriodicCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushAllHits"]
        );

        $strategy->expects($this->never())
            ->method("cacheHit")->with([$activate]);

        $strategy->activateFlag($activate);

        $this->assertContains($activate, $strategy->getActivatePoolQueue());
        $this->assertCount(1, $strategy->getActivatePoolQueue());
    }

    public function testSendActivateHit()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $activate = new Activate("varGrId", "VarId");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $activate2 = new Activate("varGrId", "VarId");
        $activate2->setConfig($config)->setVisitorId($visitorId);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingPeriodicCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["flushHits","logDebugSprintf","cacheHit","flushAllHits"]
        );

        $strategy->activateFlag($activate);
        $strategy->activateFlag($activate2);

        $activateBatch = new ActivateBatch($config, $strategy->getActivatePoolQueue());

        $requestBody = $activateBatch->toApiKeys();

        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody);

        $headers = $strategy->getActivateHeaders();

        $strategy
            ->expects($this->never())
            ->method("flushHits");

        $strategy
            ->expects($this->once())
            ->method("cacheHit")
            ->with([]);

        $strategy
            ->expects($this->once())
            ->method("flushAllHits");

        $logMessage = $this->getLogFormat(
            null,
            $url,
            $requestBody,
            $headers,
            0
        );

        $strategy->expects($this->once())->method("logDebugSprintf")
            ->with(
                $config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::HIT_SENT_SUCCESS,
                [FlagshipConstant::SEND_ACTIVATE, $logMessage ]
            );

        $this->assertCount(2, $strategy->getActivatePoolQueue());

        $strategy->sendBatch();

        $this->assertCount(0, $strategy->getActivatePoolQueue());
    }

    public function testSendActivateHitFailed()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $activate = new Activate("varGrId", "VarId");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $activate2 = new Activate("varGrId", "VarId");
        $activate2->setConfig($config)->setVisitorId($visitorId);


        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingPeriodicCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["flushHits","logErrorSprintf","cacheHit"]
        );

        $strategy->activateFlag($activate);
        $strategy->activateFlag($activate2);

        $activateBatch = new ActivateBatch($config, $strategy->getActivatePoolQueue());

        $requestBody = $activateBatch->toApiKeys();

        $exception = new Exception("activate error");
        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody)->willThrowException($exception);

        $strategy
            ->expects($this->exactly(0))
            ->method("flushHits");

        $strategy
            ->expects($this->once())
            ->method("cacheHit")
            ->with([$activate, $activate2]);

        $strategy
            ->expects($this->once())
            ->method("flushAllHits");

        $logMessage = $this->getLogFormat(
            $exception->getMessage(),
            $url,
            $requestBody,
            $strategy->getActivateHeaders(),
            0
        );

        $strategy->expects($this->once())->method("logErrorSprintf")
            ->with(
                $config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::TRACKING_MANAGER_ERROR,
                [FlagshipConstant::SEND_ACTIVATE, $logMessage ]
            );

        $this->assertCount(2, $strategy->getActivatePoolQueue());

        $strategy->sendBatch();

        $this->assertCount(2, $strategy->getActivatePoolQueue());
    }

}

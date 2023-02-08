<?php

namespace Flagship\Api;

require_once __DIR__. "/Round.php";
require_once __DIR__. "/../Assets/Round.php";

use Exception;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Flagship;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\Event;
use Flagship\Hit\HitBatch;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Traits\LogTrait;
use PHPUnit\Framework\TestCase;

class BatchingContinuousCachingStrategyTest extends TestCase
{
    use LogTrait;
    public function testGeneralMethods()
    {
        $config = new DecisionApiConfig("envId", "apiKey");
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
        $newVisitor = "newVisitor";
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
            ->method("flushHits")->with($this->callback(function ($args) use ($visitorId) {
                if (count($args)!=2) {
                    return  false;
                }
                foreach ($args as $arg) {
                    if (preg_match("#^$visitorId#", $arg)!==1) {
                        return  false;
                    }
                }
                return true;
            }));

        $hitCacheImplementationMock->expects($this->exactly(6))->method("cacheHit");

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);


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

    public function testSendActivateHit()
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

        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error','debug'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["flushHits"],
            '',
            false
        );

        $config->setLogManager($logManagerStub);

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $activate = new Activate("varGrId", "VarId");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $activate2 = new Activate("varGrId", "VarId");
        $activate2->setConfig($config)->setVisitorId($visitorId);

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);

        $strategy->activateFlag($activate);
        $strategy->activateFlag($activate2);

        $activateBatch = new ActivateBatch($config, $strategy->getActivatePoolQueue());

        $requestBody = $activateBatch->toApiKeys();

        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody);

        $headers = $strategy->getActivateHeaders();

        $logMessage = $this->getLogFormat(null, $url, $requestBody, $headers, 0);

        $customMessage = vsprintf(
            FlagshipConstant::HIT_SENT_SUCCESS,
            $this->formatArgs([
                FlagshipConstant::SEND_ACTIVATE,
                $logMessage
            ])
        );

        $hitCacheImplementationMock
            ->expects($this->exactly(1))
            ->method("flushHits")->with($this->callback(function ($args) use ($visitorId) {
                if (count($args)!=2) {
                    return  false;
                }
                foreach ($args as $arg) {
                    if (preg_match("#^$visitorId#", $arg)!==1) {
                        return  false;
                    }
                }
                return true;
            }));

        $logManagerStub->expects($this->exactly(2))->method('debug')
            ->withConsecutive(
                [$customMessage,
                [FlagshipConstant::TAG => FlagshipConstant::TRACKING_MANAGER]]
            );

        $this->assertCount(2, $strategy->getActivatePoolQueue());

        $strategy->sendBatch();

        $this->assertCount(0, $strategy->getActivatePoolQueue());
    }

    public function testSendActivateHitFailed()
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

        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error','debug'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["flushHits"],
            '',
            false
        );

        $config->setLogManager($logManagerStub);

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $activate = new Activate("varGrId", "VarId");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $activate2 = new Activate("varGrId", "VarId");
        $activate2->setConfig($config)->setVisitorId($visitorId);

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);

        $strategy->activateFlag($activate);
        $strategy->activateFlag($activate2);

        $activateBatch = new ActivateBatch($config, $strategy->getActivatePoolQueue());

        $requestBody = $activateBatch->toApiKeys();

        $exception = new Exception("activate error");
        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody)->willThrowException($exception);

        $headers = $strategy->getActivateHeaders();

        $logMessage = $this->getLogFormat($exception->getMessage(), $url, $requestBody, $headers, 0);

        $customMessage = vsprintf(
            FlagshipConstant::TRACKING_MANAGER_ERROR,
            $this->formatArgs([
                FlagshipConstant::SEND_ACTIVATE,
                $logMessage
            ])
        );

        $hitCacheImplementationMock
            ->expects($this->exactly(0))
            ->method("flushHits");

        $logManagerStub->expects($this->exactly(1))->method('error')
            ->withConsecutive(
                [$customMessage,
                    [FlagshipConstant::TAG => FlagshipConstant::TRACKING_MANAGER]]
            );

        $this->assertCount(2, $strategy->getActivatePoolQueue());

        $strategy->sendBatch();

        $this->assertCount(2, $strategy->getActivatePoolQueue());
    }

    public function testSendBatch()
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

        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error','debug'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["flushHits"],
            '',
            false
        );

        $config->setLogManager($logManagerStub);

        $url = FlagshipConstant::HIT_EVENT_URL;

        $page = new Page("https://myurl.com");
        $page->setConfig($config)->setVisitorId($visitorId);

        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId);

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);

        $strategy->addHit($page);
        $strategy->addHit($screen);

        $batchHit = new HitBatch($config, $strategy->getHitsPoolQueue());
        $batchHit->setConfig($config);

        $requestBody = $batchHit->toApiKeys();

        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody);

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $logMessage = $this->getLogFormat(null, $url, $requestBody, $headers, 0);

        $customMessage = vsprintf(
            FlagshipConstant::HIT_SENT_SUCCESS,
            $this->formatArgs([
                FlagshipConstant::SEND_BATCH,
                $logMessage
            ])
        );

        $hitCacheImplementationMock
            ->expects($this->exactly(1))
            ->method("flushHits")->with($this->callback(function ($args) use ($visitorId) {
                if (count($args)!=2) {
                    return  false;
                }
                foreach ($args as $arg) {
                    if (preg_match("#^$visitorId#", $arg)!==1) {
                        return  false;
                    }
                }
                return true;
            }));

        $logManagerStub->expects($this->exactly(2))->method('debug')
            ->withConsecutive(
                [$customMessage,
                    [FlagshipConstant::TAG => FlagshipConstant::TRACKING_MANAGER]]
            );

        $this->assertCount(2, $strategy->getHitsPoolQueue());
        $strategy->sendBatch();
        $this->assertCount(0, $strategy->getHitsPoolQueue());
    }

    public function testSendBatchFailed()
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

        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error','debug'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["flushHits"],
            '',
            false
        );

        $config->setLogManager($logManagerStub);

        $url = FlagshipConstant::HIT_EVENT_URL;

        $page = new Page("https://myurl.com");
        $page->setConfig($config)->setVisitorId($visitorId);

        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId);

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);

        $strategy->addHit($page);
        $strategy->addHit($screen);

        $batchHit = new HitBatch($config, $strategy->getHitsPoolQueue());
        $batchHit->setConfig($config);

        $requestBody = $batchHit->toApiKeys();

        $exception = new Exception("batch error");
        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody)->willThrowException($exception);

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $logMessage = $this->getLogFormat($exception->getMessage(), $url, $requestBody, $headers, 0);

        $customMessage = vsprintf(
            FlagshipConstant::TRACKING_MANAGER_ERROR,
            $this->formatArgs([
                FlagshipConstant::SEND_BATCH,
                $logMessage
            ])
        );

        $hitCacheImplementationMock
            ->expects($this->exactly(0))
            ->method("flushHits");

        $logManagerStub->expects($this->exactly(1))->method('error')
            ->withConsecutive(
                [$customMessage,
                    [FlagshipConstant::TAG => FlagshipConstant::TRACKING_MANAGER]]
            );

        $this->assertCount(2, $strategy->getHitsPoolQueue());
        $strategy->sendBatch();
        $this->assertCount(2, $strategy->getHitsPoolQueue());
    }


    public function testSendBatchWithExpiredHit()
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

        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error','debug'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["flushHits"],
            '',
            false
        );

        $config->setLogManager($logManagerStub);

        $url = FlagshipConstant::HIT_EVENT_URL;

        \Flagship\Assets\Round::$returnValue = FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
        $page = new Page("https://myurl.com");
        $page->setConfig($config)->setVisitorId($visitorId);

        \Flagship\Assets\Round::$returnValue = 0;
        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId);

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = new BatchingContinuousCachingStrategy($config, $httpClientMock);

        $strategy->addHit($page);
        $strategy->addHit($screen);

        $hits = [];
        foreach ($strategy->getHitsPoolQueue() as $key => $item) {
            if ($item instanceof Page) {
                $hits[$key] = $item;
            }
        }

        $batchHit = new HitBatch($config, $hits);
        $batchHit->setConfig($config);

        $requestBody = $batchHit->toApiKeys();

        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody);

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $logMessage = $this->getLogFormat(null, $url, $requestBody, $headers, 0);

        $customMessage = vsprintf(
            FlagshipConstant::HIT_SENT_SUCCESS,
            $this->formatArgs([
                FlagshipConstant::SEND_BATCH,
                $logMessage
            ])
        );

        $hitCacheImplementationMock
            ->expects($this->exactly(1))
            ->method("flushHits")->with($this->callback(function ($args) use ($visitorId) {
                if (count($args)!=2) {
                    return  false;
                }
                foreach ($args as $arg) {
                    if (preg_match("#^$visitorId#", $arg)!==1) {
                        return  false;
                    }
                }
                return true;
            }));

        $logManagerStub->expects($this->exactly(2))->method('debug')
            ->withConsecutive(
                [$customMessage,
                    [FlagshipConstant::TAG => FlagshipConstant::TRACKING_MANAGER]]
            );

        $this->assertCount(2, $strategy->getHitsPoolQueue());
        Round::$returnValue = FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
        $strategy->sendBatch();
    }


}

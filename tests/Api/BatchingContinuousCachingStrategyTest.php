<?php

namespace Flagship\Api;

require_once __DIR__. "/Round.php";
require_once __DIR__. "/../Assets/Round.php";

use Exception;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitCacheFields;
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

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

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

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushHits"]
        );

        $strategy->expects($this->exactly(7))->method("cacheHit");

        $strategy->expects($this->once())
            ->method("flushHits")
            ->with($this->callback(function ($args) use ($visitorId) {
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
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit"]
        );

        $strategy->expects($this->exactly(1))
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
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["flushHits","logDebugSprintf","cacheHit"]
        );

        $strategy->activateFlag($activate);
        $strategy->activateFlag($activate2);

        $activateBatch = new ActivateBatch($config, $strategy->getActivatePoolQueue());

        $requestBody = $activateBatch->toApiKeys();

        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody);

        $headers = $strategy->getActivateHeaders();

        $strategy
            ->expects($this->exactly(1))
            ->method("flushHits")
            ->with($this->callback(function ($args) use ($visitorId) {
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
            "Flagship\Api\BatchingContinuousCachingStrategy",
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
            ->expects($this->never())
            ->method("flushHits");

        $strategy
            ->expects($this->never())
            ->method("cacheHit");

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

    public function testSendBatch()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";
        //Mock class Curl
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $url = FlagshipConstant::HIT_EVENT_URL;

        $page = new Page("https://myurl.com");
        $page->setConfig($config)->setVisitorId($visitorId);

        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId);


        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["flushHits","logDebugSprintf","cacheHit","flushAllHits"]
        );

        $strategy->addHit($page);
        $strategy->addHit($screen);

        $batchHit = new HitBatch($config, $strategy->getHitsPoolQueue());
        $batchHit->setConfig($config);

        $requestBody = $batchHit->toApiKeys();

        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody);

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $httpClientMock->expects($this->once())->method('setHeaders')->with($headers);
        $httpClientMock->expects($this->once())->method("setTimeout")->with($config->getTimeout());

        $strategy
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

        $strategy
            ->expects($this->never())
            ->method("flushAllHits");

        $strategy
            ->expects($this->never())
            ->method("cacheHit")
            ->with([]);

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
                [FlagshipConstant::SEND_BATCH, $logMessage ]
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
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $url = FlagshipConstant::HIT_EVENT_URL;

        $page = new Page("https://myurl.com");
        $page->setConfig($config)->setVisitorId($visitorId);

        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["flushHits","logErrorSprintf","cacheHit"]
        );

        $strategy->addHit($page);
        $strategy->addHit($screen);

        $batchHit = new HitBatch($config, $strategy->getHitsPoolQueue());
        $batchHit->setConfig($config);

        $requestBody = $batchHit->toApiKeys();

        $exception = new Exception("batch error");
        $httpClientMock->expects($this->once())->method("post")
            ->with($url, [], $requestBody)->willThrowException($exception);

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $strategy
            ->expects($this->exactly(0))
            ->method("flushHits");

        $logMessage = $this->getLogFormat(
            $exception->getMessage(),
            $url,
            $requestBody,
            $headers,
            0
        );

        $strategy->expects($this->once())->method("logErrorSprintf")
            ->with(
                $config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::TRACKING_MANAGER_ERROR,
                [FlagshipConstant::SEND_BATCH, $logMessage ]
            );

        $this->assertCount(2, $strategy->getHitsPoolQueue());
        $strategy->sendBatch();
        $this->assertCount(2, $strategy->getHitsPoolQueue());
    }


    public function testSendBatchWithExpiredHit()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $url = FlagshipConstant::HIT_EVENT_URL;

        \Flagship\Assets\Round::$returnValue = FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
        $page = new Page("https://myurl.com");
        $page->setConfig($config)->setVisitorId($visitorId);

        \Flagship\Assets\Round::$returnValue = 0;
        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["flushHits","logErrorSprintf","cacheHit"]
        );

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

        $strategy
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

        $this->assertCount(2, $strategy->getHitsPoolQueue());
        Round::$returnValue = FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
        $strategy->sendBatch();
    }

    public function testFlushHits()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $hitCacheImplementationMock = $this->getMockForAbstractClass("Flagship\Cache\IHitCacheImplementation");

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["logDebugSprintf","cacheHit"]
        );

        $keyToRemove = ["key1","key2","key3"];

        $strategy->expects($this->once())->method("logDebugSprintf")
            ->with(
                $config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_DATA_FLUSHED,
                [$keyToRemove]
            );

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("flushHits")->with($keyToRemove);

        $strategy->flushHits($keyToRemove);

        $config->setHitCacheImplementation(null);
        $strategy->flushHits($keyToRemove);
    }

    public function testFlushHitsFailed()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $hitCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IHitCacheImplementation",
            ["flushHits"],
            '',
            false
        );

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["logErrorSprintf"]
        );

        $keyToRemove = ["key1","key2","key3"];

        $exception = new Exception("flushHits error");

        $strategy->expects($this->once())->method("logErrorSprintf")
            ->with(
                $config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["flushHits", $exception->getMessage()]
            );

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("flushHits")->with($keyToRemove)
            ->willThrowException($exception);

        $strategy->flushHits($keyToRemove);
    }


    public function testFlushAllHits()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $hitCacheImplementationMock = $this->getMockForAbstractClass("Flagship\Cache\IHitCacheImplementation");

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["logDebugSprintf"]
        );

        $strategy->expects($this->once())->method("logDebugSprintf")
            ->with(
                $config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::ALL_HITS_FLUSHED
            );

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("flushAllHits");

        $strategy->flushAllHits();

        $config->setHitCacheImplementation(null);
        $strategy->flushAllHits();
    }

    public function testFlushAllHitsFailed()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $hitCacheImplementationMock = $this->getMockForAbstractClass("Flagship\Cache\IHitCacheImplementation");

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["logErrorSprintf"]
        );

        $exception = new Exception("flushHits error");

        $strategy->expects($this->exactly(1))->method('logErrorSprintf')
            ->with(
                $config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["flushAllHits", $exception->getMessage()]
            );

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("flushAllHits")->willThrowException($exception);

        $strategy->flushAllHits();
    }

    public function testCacheHit()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $hitCacheImplementationMock = $this->getMockForAbstractClass("Flagship\Cache\IHitCacheImplementation");

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["logDebugSprintf"]
        );

        $visitorId = "visitorId";
        $key = "$visitorId:key";
        $activate = new Activate("varGrid", "varId");
        $activate->setVisitorId($visitorId)->setConfig($config);
        $activate->setKey($key);

        $hitData = [
            HitCacheFields::VERSION => 1,
            HitCacheFields::DATA => [
                HitCacheFields::VISITOR_ID => $activate->getVisitorId(),
                HitCacheFields::ANONYMOUS_ID => $activate->getAnonymousId(),
                HitCacheFields::TYPE => $activate->getType(),
                HitCacheFields::CONTENT => $activate->toArray(),
                HitCacheFields::TIME => 0
            ]
        ];

        $data = [];
        $data[$key] = $hitData;

        $strategy->expects($this->once())->method("logDebugSprintf")
            ->with(
                $config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_SAVED,
                [$data]
            );

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("cacheHit")->with($data);

        $strategy->cacheHit([$activate]);

        $config->setHitCacheImplementation(null);
        $strategy->cacheHit([$activate]);
    }

    public function testCacheHitFailed()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $hitCacheImplementationMock = $this->getMockForAbstractClass("Flagship\Cache\IHitCacheImplementation");

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["logErrorSprintf"]
        );

        $visitorId = "visitorId";
        $key = "$visitorId:key";
        $activate = new Activate("varGrid", "varId");
        $activate->setVisitorId($visitorId)->setConfig($config);
        $activate->setKey($key);

        $hitData = [
            HitCacheFields::VERSION => 1,
            HitCacheFields::DATA => [
                HitCacheFields::VISITOR_ID => $activate->getVisitorId(),
                HitCacheFields::ANONYMOUS_ID => $activate->getAnonymousId(),
                HitCacheFields::TYPE => $activate->getType(),
                HitCacheFields::CONTENT => $activate->toArray(),
                HitCacheFields::TIME => 0
            ]
        ];

        $data = [];
        $data[$key] = $hitData;

        $exception = new Exception("Cache error");

        $strategy->expects($this->once())->method("logErrorSprintf")
            ->with(
                $config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["cacheHit", $exception->getMessage()]
            );

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("cacheHit")->with($data)->willThrowException($exception);

        $strategy->cacheHit([$activate]);
    }
}

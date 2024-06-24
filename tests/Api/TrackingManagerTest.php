<?php

namespace Flagship\Api;

use DateTime;
use Exception;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitCacheFields;
use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\UsageHit;
use Flagship\Hit\Event;
use Flagship\Hit\Item;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Hit\Segment;
use Flagship\Hit\Transaction;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\TroubleshootingData;
use Flagship\Traits\BuildApiTrait;
use Flagship\Utils\HttpClient;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class TrackingManagerTest extends TestCase
{
    use BuildApiTrait;

    public const PSR_LOG_INTERFACE = 'Psr\Log\LoggerInterface';

    public function testConstruct()
    {
        $config = new DecisionApiConfig();
        $httpClient = new HttpClient();
        $trackingManager = new TrackingManager($config, $httpClient);

        $this->assertSame($httpClient, $trackingManager->getHttpClient());
        $this->assertSame($config, $trackingManager->getConfig());
    }

    public function testInitStrategy()
    {
        $config = new DecisionApiConfig();
        $httpClient = new HttpClient();
        $trackingManager = new TrackingManager($config, $httpClient);
        $strategy = $trackingManager->initStrategy();
        $this->assertInstanceOf("Flagship\Api\BatchingOnFailedCachingStrategy", $strategy);

        $config->setCacheStrategy(CacheStrategy::NO_BATCHING_AND_CACHING_ON_FAILURE);
        $strategy = $trackingManager->initStrategy();
        $this->assertInstanceOf("Flagship\Api\NoBatchingContinuousCachingStrategy", $strategy);

        $config->setCacheStrategy(CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE);
        $strategy = $trackingManager->initStrategy();
        $this->assertInstanceOf("Flagship\Api\BatchingOnFailedCachingStrategy", $strategy);
    }

    public function testCommonMethod()
    {
        $config = new DecisionApiConfig();
        $httpClient = new HttpClient();

        $BatchingCachingStrategyMock = $this->getMockForAbstractClass(
            "Flagship\Api\BatchingCachingStrategyAbstract",
            [$config, $httpClient],
            "",
            true,
            true,
            true,
            [
                "addHit",
                "activateFlag",
                "sendBatch",
                "sendTroubleshootingQueue",
                "addTroubleshootingHit",
                "setTroubleshootingData",
                "getTroubleshootingData",
                "addUsageHit"
            ]
        );

        $trackingManager = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManager",
            [$config, $httpClient],
            "",
            true,
            true,
            true,
            ["getStrategy", "lookupHits"]
        );

        $trackingManager->expects($this->exactly(7))
            ->method("getStrategy")
            ->willReturn($BatchingCachingStrategyMock);

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("addHit");

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("setTroubleshootingData");

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("getTroubleshootingData");

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("activateFlag");

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("addTroubleshootingHit");

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("sendBatch");

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("sendTroubleshootingQueue");

        $BatchingCachingStrategyMock->expects($this->once())
            ->method("addUsageHit");

        $page = new Page("http://localhost");
        $page->setConfig($config);
        $trackingManager->addHit($page);

        $activate = new Activate("varGrId", "varId");
        $activate->setConfig($config);
        $trackingManager->activateFlag($activate);

        $troubleshooting = new Troubleshooting();
        $troubleshooting->setConfig($config)->setTraffic(100);
        $trackingManager->addTroubleshootingHit($troubleshooting);

        $troubleshootingData = new TroubleshootingData();
        $trackingManager->setTroubleshootingData($troubleshootingData);

        $trackingManager->getTroubleshootingData();

        $trackingManager->sendBatch();

        $analytic = new UsageHit();
        $trackingManager->addUsageHit($analytic);
    }

    public function testLookupHits()
    {
        $config = new DecisionApiConfig();
        $httpClient = new HttpClient();
        $hitCacheImplementationMock = $this->getMockForAbstractClass("Flagship\Cache\IHitCacheImplementation");

        $trackingManager = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManager",
            [$config, $httpClient],
            "",
            true,
            true,
            true,
            ["logDebugSprintf","logErrorSprintf"]
        );

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $visitorId = "visitorId";

        $event = new Event(EventCategory::ACTION_TRACKING, "click");
        $event->setConfig($config)->setVisitorId($visitorId)->setKey("$visitorId:key1");

        $item = new Item("transactId", "productName", "code");
        $item->setConfig($config)->setVisitorId($visitorId)->setKey("$visitorId:key2");

        $page = new Page("http://localhost");
        $page->setConfig($config)->setVisitorId($visitorId)->setKey("$visitorId:key3");

        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId)->setKey("$visitorId:key4");

        $segment = new Segment(["key" => "value"]);
        $segment->setConfig($config)->setVisitorId($visitorId)->setKey("$visitorId:key5");

        $activate = new Activate("varGrid", "varId");
        $activate->setVisitorId($visitorId)->setConfig($config)->setKey("$visitorId:key6");

        $transaction = new Transaction("transId", "aff");
        $transaction->setVisitorId($visitorId)->setConfig($config)->setKey("$visitorId:key7");

        /**
         * @var $hits HitAbstract[]
         */
        $hits = [$event, $item, $page, $screen, $segment, $activate, $transaction];
        $data = [];

        foreach ($hits as $hit) {
            $hitData = [
                HitCacheFields::VERSION => 1,
                HitCacheFields::DATA => [
                    HitCacheFields::VISITOR_ID => $hit->getVisitorId(),
                    HitCacheFields::ANONYMOUS_ID => $hit->getAnonymousId(),
                    HitCacheFields::TYPE => $hit->getType()->value,
                    HitCacheFields::CONTENT => $hit->toArray(),
                    HitCacheFields::TIME => \round(microtime(true) * 1000)
                ]
            ];
            $data[$hit->getKey()] = $hitData;
        }

        $data["$visitorId:key8"] = [
            HitCacheFields::VERSION => 1,
            HitCacheFields::DATA => [
                HitCacheFields::VISITOR_ID => $page->getVisitorId(),
                HitCacheFields::ANONYMOUS_ID => $page->getAnonymousId(),
                HitCacheFields::TYPE => $page->getType()->value,
                HitCacheFields::CONTENT => $page->toArray(),
                HitCacheFields::TIME => (new DateTime("2020/01/01"))->format("Uv")
            ]
        ];

        $data["$visitorId:key9"] = [
            HitCacheFields::VERSION => 1,
            HitCacheFields::DATA => [
                HitCacheFields::VISITOR_ID => $page->getVisitorId(),
                HitCacheFields::ANONYMOUS_ID => $page->getAnonymousId(),
                HitCacheFields::TYPE => "unknown",
                HitCacheFields::CONTENT => $page->toArray(),
                HitCacheFields::TIME => \round(microtime(true) * 1000)
            ]
        ];

        $key10 = "$visitorId:key10";
        $data[$key10] = [
            HitCacheFields::DATA => [
                HitCacheFields::VISITOR_ID => $page->getVisitorId(),
                HitCacheFields::ANONYMOUS_ID => $page->getAnonymousId(),
                HitCacheFields::TYPE => "unknown",
                HitCacheFields::CONTENT => $page->toArray(),
                HitCacheFields::TIME => \round(microtime(true) * 1000)
            ]
        ];

        $hitCacheImplementationMock->expects($this->exactly(2))
            ->method("lookupHits")
            ->willReturnOnConsecutiveCalls($data, []);


        Round::$returnValue = \round(microtime(true) * 1000);

        $trackingManager->lookupHits();

        $strategy = Utils::getProperty($trackingManager, 'strategy')->getValue($trackingManager);

        $this->assertCount(6, $strategy->getHitsPoolQueue());
        $this->assertCount(1, $strategy->getActivatePoolQueue());

        $trackingManager->lookupHits();
    }

    public function testLookupHitsFailed()
    {
        $config = new DecisionApiConfig();
        $httpClient = new HttpClient();
        $hitCacheImplementationMock = $this->getMockForAbstractClass("Flagship\Cache\IHitCacheImplementation");

        $trackingManager = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManager",
            [$config, $httpClient],
            "",
            true,
            true,
            true,
            ["logErrorSprintf"]
        );

        $config->setHitCacheImplementation($hitCacheImplementationMock);

        $exception = new Exception("error");

        $hitCacheImplementationMock->expects($this->exactly(1))
            ->method("lookupHits")
            ->willThrowException($exception);

        $trackingManager->expects($this->once())
            ->method("logErrorSprintf")
            ->with(
                $config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["lookupHits", $exception->getMessage()]
            );

        $trackingManager->lookupHits();
    }
}

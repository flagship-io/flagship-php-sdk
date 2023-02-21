<?php

namespace Flagship\Api;

use Exception;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\Page;
use Flagship\Traits\LogTrait;
use PHPUnit\Framework\TestCase;

class NoBatchingContinuousCachingStrategyTest extends TestCase
{
    use LogTrait;

    public function testAddHit()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\NoBatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushHits","logDebugSprintf"]
        );

        $strategy->expects($this->never())->method("cacheHit");

        $strategy->expects($this->never())->method("flushHits");

        $page = new Page("http://localhost");
        $page->setConfig($config)->setVisitorId($visitorId);

        $page2 = new Page("http://localhost2");
        $page2->setConfig($config)->setVisitorId($visitorId);

        $consentHit1 = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit1->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "true");
        $consentHit1->setConfig($config);
        $consentHit1->setVisitorId($visitorId);

        $requestBody = $page->toApiKeys();
        $requestBody2 = $page2->toApiKeys();
        $requestBody3 = $consentHit1->toApiKeys();

        $url = FlagshipConstant::HIT_EVENT_URL;

        $httpClientMock->expects($this->exactly(3))->method("post")
            ->withConsecutive(
                [$url, [], $requestBody],
                [$url, [], $requestBody2],
                [$url, [], $requestBody3]
            );

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $httpClientMock->expects($this->exactly(3))->method('setHeaders')->with($headers);
        $httpClientMock->expects($this->exactly(3))->method("setTimeout")->with($config->getTimeout());

        $logMessage = $this->getLogFormat(
            null,
            $url,
            $requestBody,
            $headers,
            0
        );
        $logMessage1 = $this->getLogFormat(
            null,
            $url,
            $requestBody2,
            $headers,
            0
        );

        $logMessage2 = $this->getLogFormat(
            null,
            $url,
            $requestBody3,
            $headers,
            0
        );

        $strategy->expects($this->exactly(3))->method("logDebugSprintf")
            ->withConsecutive(
                [
                $config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::HIT_SENT_SUCCESS,
                [FlagshipConstant::SEND_HIT, $logMessage ]
                ],
                [
                    $config,
                    FlagshipConstant::TRACKING_MANAGER,
                    FlagshipConstant::HIT_SENT_SUCCESS,
                    [FlagshipConstant::SEND_HIT, $logMessage1 ]
                ],
                [
                    $config,
                    FlagshipConstant::TRACKING_MANAGER,
                    FlagshipConstant::HIT_SENT_SUCCESS,
                    [FlagshipConstant::SEND_HIT, $logMessage2 ]
                ]
            );

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());

        $strategy->addHit($page);
        $strategy->addHit($page2);

        //Test consent true
        $strategy->addHit($consentHit1);

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());
    }

    public function testAddHitFailed()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\NoBatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushHits","logErrorSprintf"]
        );

        $strategy->expects($this->never())->method("flushHits");

        $page = new Page("http://localhost");
        $page->setConfig($config)->setVisitorId($visitorId);

        $strategy->expects($this->once())->method("cacheHit")->with([$page]);

        $requestBody = $page->toApiKeys();

        $url = FlagshipConstant::HIT_EVENT_URL;

        $exception = new Exception("error");

        $httpClientMock->expects($this->exactly(1))->method("post")
            ->withConsecutive(
                [$url, [], $requestBody]
            )->willThrowException($exception);

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $httpClientMock->expects($this->exactly(1))->method('setHeaders')->with($headers);
        $httpClientMock->expects($this->exactly(1))->method("setTimeout")->with($config->getTimeout());

        $logMessage = $this->getLogFormat(
            $exception->getMessage(),
            $url,
            $requestBody,
            $headers,
            0
        );

        $strategy->expects($this->once())->method("logErrorSprintf")
            ->withConsecutive(
                [
                    $config,
                    FlagshipConstant::TRACKING_MANAGER,
                    FlagshipConstant::TRACKING_MANAGER_ERROR,
                    [FlagshipConstant::SEND_HIT, $logMessage ]
                ]
            );

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());
        $strategy->addHit($page);

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());
    }

    public function testAddHitConsent()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";
        $newVisitor = "newVisitor";

        $page3Key = "$visitorId:b1b48180-0d72-410d-8e9b-44ee90dfafc6";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\NoBatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushHits"]
        );

        $strategy->expects($this->never())->method("cacheHit");

        $key1 = "$visitorId:key1";
        $key2 = "$visitorId:key2";

        $strategy->expects($this->once())
            ->method("flushHits")->with([$page3Key]);

        $page = new Page("http://localhost");
        $page->setConfig($config)->setVisitorId($visitorId)->setKey($key1);

        $page2 = new Page("http://localhost2");
        $page2->setConfig($config)->setVisitorId($visitorId)->setKey($key2);

        $strategy->hydrateHitsPoolQueue($key1, $page);
        $strategy->hydrateHitsPoolQueue($key2, $page2);

        $contentPage3= [
            'pageUrl' => 'page1',
            'visitorId' => $visitorId,
            'ds' => 'APP',
            'type' => 'PAGEVIEW',
            'anonymousId' => NULL,
            'userIP' => NULL,
            'pageResolution' => NULL,
            'locale' => NULL,
            'sessionNumber' => NULL,
            'key' => $page3Key,
            'createdAt' => 1676542078047,
        ];

        $page3 = HitAbstract::hydrate(Event::getClassName(), $contentPage3);

        $page3->setConfig($config);

        $strategy->hydrateHitsPoolQueue($page3Key, $page3);

        $consentHit1 = new Event(EventCategory::USER_ENGAGEMENT, FlagshipConstant::FS_CONSENT);
        $consentHit1->setLabel(FlagshipConstant::SDK_LANGUAGE . ":" . "false");
        $consentHit1->setConfig($config);
        $consentHit1->setVisitorId($visitorId);

        $requestBody3 = $consentHit1->toApiKeys();

        $url = FlagshipConstant::HIT_EVENT_URL;

        $httpClientMock->expects($this->exactly(2))->method("post")
            ->withConsecutive(
                [$url, [], $requestBody3]
            );

        $headers = [FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON];

        $httpClientMock->expects($this->exactly(2))->method('setHeaders')->with($headers);
        $httpClientMock->expects($this->exactly(2))->method("setTimeout")->with($config->getTimeout());

        $this->assertCount(3, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());
        //Test consent false
        $strategy->addHit($consentHit1);
        $strategy->addHit($consentHit1);

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());
    }

    public function testActivateFlag()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\NoBatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushHits","logDebugSprintf"]
        );

        $strategy->expects($this->never())->method("cacheHit");

        $strategy->expects($this->never())->method("flushHits");

        $activate = new Activate("varGr1", "varId1");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $activate2 = new Activate("varGrId2", "varId2");
        $activate2->setConfig($config)->setVisitorId($visitorId);

        $activateBatch = new ActivateBatch($config, [$activate]);
        $requestBody = $activateBatch->toApiKeys();

        $activateBatch2 = new ActivateBatch($config, [$activate2]);
        $requestBody2 = $activateBatch2->toApiKeys();

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $httpClientMock->expects($this->exactly(2))->method("post")
            ->withConsecutive(
                [$url, [], $requestBody],
                [$url, [], $requestBody2]
            );

        $headers = $strategy->getActivateHeaders();

        $httpClientMock->expects($this->exactly(2))->method('setHeaders')->with($headers);
        $httpClientMock->expects($this->exactly(2))->method("setTimeout")->with($config->getTimeout());

        $logMessage = $this->getLogFormat(
            null,
            $url,
            $requestBody,
            $headers,
            0
        );
        $logMessage1 = $this->getLogFormat(
            null,
            $url,
            $requestBody2,
            $headers,
            0
        );

        $strategy->expects($this->exactly(2))->method("logDebugSprintf")
            ->withConsecutive(
                [
                    $config,
                    FlagshipConstant::TRACKING_MANAGER,
                    FlagshipConstant::HIT_SENT_SUCCESS,
                    [FlagshipConstant::SEND_ACTIVATE, $logMessage ]
                ],
                [
                    $config,
                    FlagshipConstant::TRACKING_MANAGER,
                    FlagshipConstant::HIT_SENT_SUCCESS,
                    [FlagshipConstant::SEND_ACTIVATE, $logMessage1 ]
                ]
            );

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());

        $strategy->activateFlag($activate);
        $strategy->activateFlag($activate2);

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());
    }

    public function testActivateFlagFailed()
    {
        $config = new DecisionApiConfig();
        $visitorId = "visitorId";

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $strategy = $this->getMockForAbstractClass(
            "Flagship\Api\NoBatchingContinuousCachingStrategy",
            [$config, $httpClientMock],
            "",
            true,
            true,
            true,
            ["cacheHit","flushHits","logErrorSprintf"]
        );

        $strategy->expects($this->never())->method("flushHits");

        $activate = new Activate("varGr1", "varId1");
        $activate->setConfig($config)->setVisitorId($visitorId);

        $strategy->expects($this->once())->method("cacheHit")->with([$activate]);

        $activateBatch = new ActivateBatch($config, [$activate]);
        $requestBody = $activateBatch->toApiKeys();

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $exception = new Exception("error");

        $httpClientMock->expects($this->exactly(1))->method("post")
            ->withConsecutive(
                [$url, [], $requestBody]
            )->willThrowException($exception);

        $headers = $strategy->getActivateHeaders();

        $httpClientMock->expects($this->exactly(1))->method('setHeaders')->with($headers);
        $httpClientMock->expects($this->exactly(1))->method("setTimeout")->with($config->getTimeout());

        $logMessage = $this->getLogFormat(
            $exception->getMessage(),
            $url,
            $requestBody,
            $headers,
            0
        );

        $strategy->expects($this->exactly(1))->method("logErrorSprintf")
            ->withConsecutive(
                [
                    $config,
                    FlagshipConstant::TRACKING_MANAGER,
                    FlagshipConstant::TRACKING_MANAGER_ERROR,
                    [FlagshipConstant::SEND_ACTIVATE, $logMessage ]
                ]
            );

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());

        $strategy->activateFlag($activate);

        $this->assertCount(0, $strategy->getHitsPoolQueue());
        $this->assertCount(0, $strategy->getActivatePoolQueue());
    }
}

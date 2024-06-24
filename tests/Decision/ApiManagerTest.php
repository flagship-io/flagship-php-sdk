<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Api\TrackingManagerAbstract;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FSSdkStatus;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor\VisitorDelegate;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ApiManagerTest extends TestCase
{
    public function testConstruct()
    {
        $httpClient = new HttpClient();
        $config = new DecisionApiConfig();
        $apiManager = new ApiManager($httpClient, $config);
        $flagshipInstanceId = "flagshipInstanceId";
        $apiManager->setFlagshipInstanceId($flagshipInstanceId);
        $this->assertSame($httpClient, $apiManager->getHttpClient());
        $this->assertSame($config, $apiManager->getConfig());
        $this->assertFalse($apiManager->getIsPanicMode());
        $apiManager->setIsPanicMode(true);
        $this->assertTrue($apiManager->getIsPanicMode());
        $this->assertSame($flagshipInstanceId, $apiManager->getFlagshipInstanceId());
    }

    public function testGetCampaignModifications()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            "",
            false
        );

        $trackingManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            "",
            false
        );

        $decisionManager = $this->getMockForAbstractClass(
            DecisionManagerAbstract::class,
            [],
            "",
            false
        );

        $modificationValue1 = [
            "background" => "bleu ciel",
            "btnColor" => "#EE3300",
            "borderColor" => null, //test modification null
            'isVip' => false, //test modification false
            'firstConnect' => true
        ];
        $modificationValue2 = [
            "key" => "variation 2",
            "key2" => 1,
            "key3" => 3,
            "key4" => 4,
            "key5" => '' //test modification empty
        ];
        $modificationValue3 = [
            'key' => 'variation 3',
            'key2' => 3
        ];

        $mergeModification = array_merge($modificationValue1, $modificationValue2);

        $campaigns = [
            [
                "id" => "c1e3t1nvfu1ncqfcdco0",
                "variationGroupId" => "c1e3t1nvfu1ncqfcdcp0",
                "variation" => [
                    "id" => "c1e3t1nvfu1ncqfcdcq0",
                    "modifications" => [
                        "type" => "FLAG",
                        "value" => $modificationValue1
                    ],
                    "reference" => false]
            ],
            [
                "id" => "c20j8bk3fk9hdphqtd1g",
                "variationGroupId" => "c20j8bk3fk9hdphqtd2g",
                "variation" => [
                    "id" => "c20j9lgbcahhf2mvhbf0",
                    "modifications" => [
                        "type" => "JSON",
                        "value" => $modificationValue2
                    ],
                    "reference" => true
                ]
            ],
            [
                "id" => "c20j8bksdfk9hdphqtd1g",
                "variationGroupId" => "c2sf8bk3fk9hdphqtd2g",
                "variation" => [
                    "id" => "c20j9lrfcahhf2mvhbf0",
                    "modifications" => [
                        "type" => "JSON",
                        "value" => $modificationValue3
                    ],
                    "reference" => true
                ]
            ]
        ];

        $visitorId = "visitorId";
        $body = [
            "visitorId" => $visitorId,
            "campaigns" => $campaigns
        ];

        $httpPost = $httpClientMock->expects($this->exactly(2))
            ->method('post')
            ->willReturn(new HttpResponse(204, $body));

        $config = new DecisionApiConfig("env_id", "apiKey");
        $manager = new ApiManager($httpClientMock, $config);

        $statusCallback = function ($status) {
            // test status change
            $this->assertSame(FSSdkStatus::SDK_INITIALIZED, $status);
        };

        $manager->setStatusChangedCallback($statusCallback);
        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $postData = [
            "visitorId" => $visitor->getVisitorId(),
            "anonymousId" => $visitor->getAnonymousId(),
            "trigger_hit" => false,
            "context" => count($visitor->getContext()) > 0 ? $visitor->getContext() : null,
            "visitor_consent" => $visitor->hasConsented()
        ];

        $url = FlagshipConstant::BASE_API_URL . '/' . $config->getEnvId() . '/' .
            FlagshipConstant::URL_CAMPAIGNS . '?' .
            FlagshipConstant::EXPOSE_ALL_KEYS . '=true&extras[]=accountSettings';

        $httpPost
            ->with(
                $this->logicalOr(
                    $this->equalTo($url),
                    $this->equalTo($url)
                ),
                $this->logicalOr(
                    $this->equalTo([]),
                    $this->equalTo([])
                ),
                $this->logicalOr(
                    $this->equalTo($postData),
                    $this->equalTo([
                        "visitorId" => $visitor->getVisitorId(),
                        "anonymousId" => $visitor->getAnonymousId(),
                        "trigger_hit" => false,
                        "context" => count($visitor->getContext()) > 0 ? $visitor->getContext() : null,
                        "visitor_consent" => false
                    ])
                )
            );


        $modifications = $manager->getCampaignFlags($visitor);

        //Test duplicate keys are overwritten
        $this->assertCount(count($mergeModification), $modifications);

        $this->assertSame($modificationValue2['key3'], $modifications[7]->getValue());
        $this->assertSame($mergeModification['background'], $modifications[0]->getValue());

        //Test campaignId
        $this->assertSame($campaigns[0]['id'], $modifications[2]->getCampaignId());

        //Test Variation group
        $this->assertSame($campaigns[2]['variationGroupId'], $modifications[5]->getVariationGroupId());

        //Test Variation
        $this->assertSame($campaigns[2]['variation']['id'], $modifications[6]->getVariationId());

        //Test reference
        $this->assertSame($campaigns[2]['variation']['reference'], $modifications[6]->getIsReference());

        // Test with consent = false
        $visitor->setConsent(false);
        $manager->getCampaignFlags($visitor);
    }

    public function testGetCampaignModificationsWithPanicMode()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['post'], "", false);

        $visitorId = "visitorId";
        $body = [
            "visitorId" => $visitorId,
            "campaigns" => [],
            "panic" => true
        ];

        $httpClientMock->method('post')->willReturn(new HttpResponse(204, $body));

        $config = new DecisionApiConfig("env_id", "apiKey");
        $manager = new ApiManager($httpClientMock, $config);

        $statusCallback = function (FSSdkStatus $status) {
            echo $status->name;
        };

        $manager->setStatusChangedCallback($statusCallback);

        $this->assertFalse($manager->getIsPanicMode());

        $trackingManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            ['sendConsentHit'],
            "",
            false
        );
        $decisionManager = $this->getMockForAbstractClass(
            DecisionManagerAbstract::class,
            [],
            "",
            false
        );
        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        //Test Change Status to FlagshipStatus::READY_PANIC_ON
        $this->expectOutputString(FSSdkStatus::SDK_PANIC->name);
        $modifications = $manager->getCampaignFlags($visitor);

        $this->assertTrue($manager->getIsPanicMode());

        $this->assertSame([], $modifications);
    }

    public function testGetCampaignModificationsWithSomeFailed()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['post'], "", false);

        $modificationValue = [
            "background" => "bleu ciel",
            "btnColor" => "#EE3300",
            "borderColor" => null,
            'isVip' => false,
            'firstConnect' => true,
            '' => 'hello world' //Test with invalid key
        ];

        $campaigns = [
            [
                "id" => "c1e3t1nvfu1ncqfcdco0",
                "variationGroupId" => "c1e3t1nvfu1ncqfcdcp0",
                "variation" => [
                    "id" => "c1e3t1nvfu1ncqfcdcq0",
                    "modifications" => [ //Test modification without Value
                        "type" => "FLAG",
                    ],
                    "reference" => false]
            ],
            [
                "id" => "c20j8bk3fk9hdphqtd1g",
                "variationGroupId" => "c20j8bk3fk9hdphqtd2g",
                "variation" => [ //Test Variation without modification
                    "id" => "c20j9lgbcahhf2mvhbf0",
                    "reference" => true
                ]
            ],
            [ // Test Campaign without variation
                "id" => "c20j8bksdfk9hdphqtd1g",
                "variationGroupId" => "c2sf8bk3fk9hdphqtd2g",

            ],
            [
                "id" => "c20j8bksdfk9hdphqtd1g",
                "variationGroupId" => "c2sf8bk3fk9hdphqtd2g",
                "variation" => [
                    "id" => "c20j9lrfcahhf2mvhbf0",
                    "modifications" => [
                        "type" => "JSON",
                        "value" => $modificationValue
                    ],
                    "reference" => true
                ]
            ]
        ];

        $visitorId = "visitorId";
        $body = [
            "visitorId" => $visitorId,
            "campaigns" => $campaigns
        ];

        $httpClientMock->method('post')->willReturn(new HttpResponse(204, $body));

        $config = new DecisionApiConfig("env_id", "apiKey");
        $manager = new ApiManager($httpClientMock, $config);
        $trackingManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            ['sendConsentHit'],
            "",
            false
        );
        $decisionManager = $this->getMockForAbstractClass(
            DecisionManagerAbstract::class,
            [],
            "",
            false
        );
        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $modifications = $manager->getCampaignFlags($visitor);

        $this->assertCount(count($modificationValue) - 1, $modifications);
    }

    public function testGetCampaignThrowException()
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            LoggerInterface::class,
            ['error'],
            '',
            false
        );

        //Mock class Curl
        $httpClientMock = $this->getMockForAbstractClass(
            HttpClientInterface::class,
            ['post'],
            '',
            false
        );

        //Mock method curl->post to throw Exception
        $errorMessage = "{'message': 'Forbidden'}";
        $httpClientMock->method('post')
            ->willThrowException(new Exception($errorMessage, 403));

        $config = new DecisionApiConfig("env_id", "api_key");

        $config->setLogManager($logManagerStub);
        $trackingManager = $this->getMockForAbstractClass(
            TrackingManagerAbstract::class,
            ['sendHit'],
            "",
            false
        );
        $decisionManager = $this->getMockForAbstractClass(
            DecisionManagerAbstract::class,
            [],
            "",
            false
        );
        $configManager = new ConfigManager($config, $decisionManager, $trackingManager);

        $apiManager = new ApiManager($httpClientMock, $config);

        $visitor = new VisitorDelegate(new Container(), $configManager, 'visitor_id', false, ['age' => 15], true);

        $logManagerStub->expects($this->once())->method('error')
            ->with(
                $errorMessage
            );

        $value = $apiManager->getCampaigns($visitor);

        $this->assertCount(0, $value);
    }
}

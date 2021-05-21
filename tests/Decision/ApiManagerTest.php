<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\FlagshipConfig;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\HttpClient;
use Flagship\Visitor;
use PHPUnit\Framework\TestCase;

class ApiManagerTest extends TestCase
{
    public function testConstruct()
    {
        $httpClient = new HttpClient();
        $apiManager = new ApiManager($httpClient);
        $this->assertSame($httpClient, $apiManager->getHttpClient());
        $this->assertFalse($apiManager->getIsPanicMode());
        $apiManager->setIsPanicMode(true);
        $this->assertTrue($apiManager->getIsPanicMode());
    }

    public function testGetCampaignModifications()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['post'], "", false);
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

        $httpClientMock->method('post')->willReturn(new HttpResponse(204, $body));

        $manager = new ApiManager($httpClientMock);

        $statusCallback = function ($status) {
            echo $status;
        };

        $manager->setStatusChangedCallable($statusCallback);
        $configManager = (new ConfigManager())->setConfig(new FlagshipConfig());

        $visitor = new Visitor($configManager, $visitorId, []);

        //Test Change Status to FlagshipStatus::READY_PANIC_ON
        $this->expectOutputString((string)FlagshipStatus::READY);

        $modifications = $manager->getCampaignModifications($visitor);

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

        $manager = new ApiManager($httpClientMock);

        $statusCallback = function ($status) {
            echo $status;
        };

        $manager->setStatusChangedCallable($statusCallback);

        $this->assertFalse($manager->getIsPanicMode());
        $configManager = (new ConfigManager())->setConfig(new FlagshipConfig());

        $visitor = new Visitor($configManager, $visitorId, []);

        //Test Change Status to FlagshipStatus::READY_PANIC_ON
        $this->expectOutputString((string)FlagshipStatus::READY_PANIC_ON);
        $modifications = $manager->getCampaignModifications($visitor);

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

        $manager = new ApiManager($httpClientMock);
        $configManager = (new ConfigManager())->setConfig(new FlagshipConfig());

        $visitor = new Visitor($configManager, $visitorId, []);

        $modifications = $manager->getCampaignModifications($visitor);

        $this->assertCount(count($modificationValue) - 1, $modifications);
    }

    public function testGetCampaignThrowException()
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error'],
            '',
            false
        );

        //Mock class Curl
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );
        ;

        //Mock method curl->post to throw Exception
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $errorMessage = "{'message': 'Forbidden'}";
        $httpClientMock->method('post')
            ->willThrowException(new Exception($errorMessage, 403));

        $config = new FlagshipConfig("env_id", "api_key");
        $logManagerStub->expects($this->once())->method('error')->withConsecutive(
            ["[$flagshipSdk] " . $errorMessage]
        );

        $config->setLogManager($logManagerStub);
        $configManager = new ConfigManager();
        $configManager->setConfig($config);

        $apiManager = new ApiManager($httpClientMock);

        $visitor = new Visitor($configManager, 'visitor_id', ['age' => 15]);
        $value = $apiManager->getCampaignModifications($visitor);

        $this->assertSame([], $value);
    }
}

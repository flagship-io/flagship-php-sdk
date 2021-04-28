<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\FlagshipConfig;
use Flagship\Model\Modification;
use Flagship\Utils\HttpClient;
use Flagship\Visitor;
use PHPUnit\Framework\TestCase;

class ApiManagerTest extends TestCase
{
    public function testConstruct()
    {
        $config = new FlagshipConfig('envId', 'api');
        $httpClient = new HttpClient();
        $apiManager = new ApiManager($config, $httpClient);

        $this->assertSame($config, $apiManager->getConfig());
        $this->assertSame($httpClient, $apiManager->getHttpClient());

        $config2 = new FlagshipConfig('newEnvId', 'newApi');
        $apiManager->setConfig($config2);

        $this->assertNotSame($config, $apiManager->getConfig());
        $this->assertSame($config2, $apiManager->getConfig());
    }

    public function testGetAllModifications()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['post'], "", false);
        $visitorId = 'visitor_id';
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

        $result = [
            "visitorId" => $visitorId,
            "campaigns" => $campaigns
        ];

        $httpClientMock->method('post')
            ->willReturn($result);
        $config = new FlagshipConfig("env_id", "api_key");
        $manager = new ApiManager($config, $httpClientMock);
        $visitor = new Visitor($manager, $visitorId, ['age' => 15]);
        $modifications = $manager->getCampaignsModifications($visitor);

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

    public function testGetCampaigns()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['post'], '', false);
        $visitorId = 'visitor_id';
        $campaigns = [
            [
                "id" => "c1e3t1nvfu1ncqfcdco0",
                "variationGroupId" => "c1e3t1nvfu1ncqfcdcp0",
                "variation" => [
                    "id" => "c1e3t1nvfu1ncqfcdcq0",
                    "modifications" => [
                        "type" => "JSON",
                        "value" => [
                            "btnColor" => "green"
                        ]
                    ],
                    "reference" => false]
            ]
        ];
        $result = [
            "visitorId" => $visitorId,
            "campaigns" => $campaigns
        ];
        $httpClientMock->method('post')
            ->willReturn($result);
        $config = new FlagshipConfig("env_id", "api_key");
        $manager = new ApiManager($config, $httpClientMock);
        $visitor = new Visitor($manager, $visitorId, ['age' => 15]);
        $value = $manager->getCampaigns($visitor);
        $this->assertSame($campaigns, $value);
    }

    public function testGetCampaignThrowException()
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
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
        );;

        //Mock method curl->post to throw Exception
        $errorMessage = '{"message": "Forbidden"}';
        $httpClientMock->method('post')
            ->willThrowException(new Exception($errorMessage, 403));

        $config = new FlagshipConfig("env_id", "api_key");
        $logManagerStub->expects($this->once())->method('error')->withConsecutive(
            [$errorMessage]
        );

        $config->setLogManager($logManagerStub);

        $apiManager = new ApiManager($config, $httpClientMock);
        $visitor = new Visitor($apiManager, 'visitor_id', ['age' => 15]);
        $value = $apiManager->getCampaigns($visitor);
        $this->assertSame([], $value);
    }

    public function testSendActiveModification()
    {
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            ['error'],
            '',
            false
        );

        //Mock class HttpClient
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $modification = new Modification();
        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $config = new FlagshipConfig("env_id", "api_key");

        $logManagerStub->expects($this->never())->method('error');

        $config->setLogManager($logManagerStub);

        $apiManager = new ApiManager($config, $httpClientMock);
        $visitor = new Visitor($apiManager, 'visitor_id', ['age' => 15]);

        $postData=[
            FlagshipConstant::VISITOR_ID => $visitor->getVisitorId(),
            FlagshipConstant::VARIATION_ID=> $modification->getVariationId(),
            FlagshipConstant::VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipConstant::CUSTOMER_ENV_ID=> $config->getEnvId()
        ];
        $url = FlagshipConstant::BASE_API_URL . '/activate';

        $httpClientMock->expects($this->once())
            ->method('post')
            ->willReturn(null)
            ->with($url,[],$postData);

        $apiManager->sendActiveModification($visitor, $modification);
    }

    public function testSendActiveModificationException(){
        //Mock logManger
        $logManagerStub = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            ['error'],
            '',
            false
        );

        //Mock class HttpClient
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $modification = new Modification();
        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $config = new FlagshipConfig("env_id", "api_key");

        $config->setLogManager($logManagerStub);

        $apiManager = new ApiManager($config, $httpClientMock);
        $visitor = new Visitor($apiManager, 'visitor_id', ['age' => 15]);

        $postData=[
            FlagshipConstant::VISITOR_ID => $visitor->getVisitorId(),
            FlagshipConstant::VARIATION_ID=> $modification->getVariationId(),
            FlagshipConstant::VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipConstant::CUSTOMER_ENV_ID=> $config->getEnvId()
        ];
        $url = FlagshipConstant::BASE_API_URL . '/activate';

        $exception = new Exception('test exception');

        $httpClientMock->expects($this->once())
            ->method('post')
            ->willThrowException($exception)
            ->with($url,[],$postData);

        $logManagerStub->expects($this->once())->method('error')->with(
            $exception->getMessage()
        );

        $apiManager->sendActiveModification($visitor, $modification);
    }
}

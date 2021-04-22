<?php

namespace Flagship\Decision;

use Exception;
use Flagship\FlagshipConfig;
use Flagship\Visitor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiManagerTest extends TestCase
{

    public function tearDown()
    {
        parent::tearDown();
        $config = new FlagshipConfig("env_id", "api_key");
        $singleton = ApiManager::getInstance($config);
        $reflection = new ReflectionClass($singleton);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);
    }

    public function testParseCampaigns()
    {
    }

    public function testGetInstance()
    {
        $config = new FlagshipConfig("env_id", "api_key");
        $manager1 = ApiManager::getInstance($config);
        $manager2 = ApiManager::getInstance($config);
        $this->assertSame($manager1, $manager2);
    }

    public function testGetAllModifications()
    {
        $stub = $this->getMockBuilder('Flagship\utils\HttpClient')->getMock();
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

        $stub->method('post')
            ->willReturn($result);
        $config = new FlagshipConfig("env_id", "api_key");
        $manager = ApiManager::getInstance($config);
        $visitor = new Visitor($config, $visitorId, ['age' => 15]);
        $modifications = $manager->getCampaignsModifications($visitor, $stub);

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
        $stub = $this->getMockBuilder('Flagship\utils\HttpClient')->getMock();
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
        $stub->method('post')
            ->willReturn($result);
        $config = new FlagshipConfig("env_id", "api_key");
        $manager = ApiManager::getInstance($config);
        $visitor = new Visitor($config, $visitorId, ['age' => 15]);
        $value = $manager->getCampaigns($visitor, $stub);
        $this->assertSame($campaigns, $value);
    }

    public function testGetCampaignThrowException()
    {
        //Mock logManger
        $logManagerStub = $this->getMockBuilder('Flagship\utils\LogManager')->getMock();

        //Mock class Curl
        $curlStub = $this->getMockBuilder('Flagship\utils\HttpClient')->getMock();

        //Mock method curl->post to throw Exception
        $errorMessage = '{"message": "Forbidden"}';
        $curlStub->method('post')
            ->willThrowException(new Exception($errorMessage, 403));

        $config = new FlagshipConfig("env_id", "api_key");
        $logManagerStub->expects($this->once())->method('error')->withConsecutive(
            [$errorMessage]
        );

        $config->setLogManager($logManagerStub);


        $manager = ApiManager::getInstance($config);
        $visitor = new Visitor($config, 'visitor_id', ['age' => 15]);
        $value = $manager->getCampaigns($visitor, $curlStub);
        $this->assertSame([], $value);
    }

    public function testGetCampaignsModifications()
    {
    }
}

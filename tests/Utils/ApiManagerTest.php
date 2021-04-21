<?php

namespace Flagship\Utils;

use Exception;
use Flagship\Decision\ApiManager;
use Flagship\FlagshipConfig;
use Flagship\Visitor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiManagerTest extends TestCase
{

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

    public function testGetInstance()
    {
        $config = new FlagshipConfig("env_id", "api_key");
        $manager1 = ApiManager::getInstance($config);
        $manager2 = ApiManager::getInstance($config);
        $this->assertSame($manager1, $manager2);
    }

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
}

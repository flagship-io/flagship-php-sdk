<?php

namespace Flagship\Config;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\LogLevel;
use Flagship\Utils\FlagshipLogManager;
use PHPUnit\Framework\TestCase;

class BucketingConfigTest extends TestCase
{

    public function testSetPollingInterval()
    {
        $envId = "envId";
        $apiKey = "apiKey";

        $config = new BucketingConfig($envId, $apiKey);

        //Test default value
        $this->assertSame(FlagshipConstant::REQUEST_TIME_OUT * 1000, $config->getPollingInterval());

        //Test set with alpha
        $config->setPollingInterval('abc');
        $this->assertSame(FlagshipConstant::REQUEST_TIME_OUT * 1000, $config->getPollingInterval());

        $polling = 5000;
        $config->setPollingInterval($polling);
        $this->assertSame($polling, $config->getPollingInterval());
    }

    public function testSetBucketingDirectory()
    {
        $config = new BucketingConfig();
        $myDirectory = FlagshipConstant::BUCKETING_DIRECTORY;
        $this->assertMatchesRegularExpression(
            "/\/\.\.\/\.\.\/\.\.\/$myDirectory$/",
            $config->getBucketingDirectory()
        );

        $myDirectory = "myDirectory";
        $config->setBucketingDirectory($myDirectory);
        $this->assertMatchesRegularExpression(
            "/\/\.\.\/\.\.\/\.\.\/\.\.\/$myDirectory$/",
            $config->getBucketingDirectory()
        );
    }

    public function testJson()
    {
        $data =  [
            FlagshipField::FIELD_ENVIRONMENT_ID => 'envId',
            FlagshipField::FIELD_API_KEY => "apiKey",
            FlagshipField::FIELD_TIMEOUT => 2000,
            FlagshipField::FIELD_LOG_LEVEL => LogLevel::ALL,
            FlagshipField::FIELD_POLLING_INTERVAL => 2000,
            FlagshipField::FIELD_BUCKETING_DIRECTORY => "flagship"
        ];

        $config = new BucketingConfig($data[FlagshipField::FIELD_ENVIRONMENT_ID], $data[FlagshipField::FIELD_API_KEY]);
        $config->setTimeout($data[FlagshipField::FIELD_TIMEOUT]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($data),
            json_encode($config)
        );
        $logManager = new FlagshipLogManager();
        $config->setLogManager($logManager);
        $this->assertSame($logManager, $config->getLogManager());
    }
}

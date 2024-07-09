<?php

namespace Flagship\Config;

use Flagship\Enum\FlagshipField;
use Flagship\Enum\LogLevel;
use PHPUnit\Framework\TestCase;

class BucketingConfigTest extends TestCase
{
    public function testBucketingUrl()
    {
        $bucketingUrl = "http:127.0.0.1:3000";

        $config = new BucketingConfig($bucketingUrl);

        //Test default value
        $this->assertSame($bucketingUrl, $config->getSyncAgentUrl());

        $newBucketingUrl = "http:127.0.0.2:3000";
        $config->setSyncAgentUrl($newBucketingUrl);
        $this->assertSame($newBucketingUrl, $config->getSyncAgentUrl());
    }

    public function testJson()
    {
        $bucketingUrl = "http:127.0.0.1:3000";
        $data =  [
            FlagshipField::FIELD_ENVIRONMENT_ID => null,
            FlagshipField::FIELD_API_KEY => null,
            FlagshipField::FIELD_TIMEOUT => 2000,
            FlagshipField::FIELD_LOG_LEVEL => LogLevel::ALL,
            FlagshipField::FIELD_BUCKETING_URL => $bucketingUrl
        ];

        $config = new BucketingConfig($bucketingUrl);
        $config->setTimeout($data[FlagshipField::FIELD_TIMEOUT]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($data),
            json_encode($config)
        );
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");
        $config->setLogManager($logManager);
        $this->assertSame($logManager, $config->getLogManager());
    }
}

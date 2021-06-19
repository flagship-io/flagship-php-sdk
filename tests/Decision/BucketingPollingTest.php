<?php

namespace Flagship\Decision;

require_once __dir__ . "/../Assets/File.php";

use Exception;
use Flagship\Assets\File;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\HttpResponse;
use PHPUnit\Framework\TestCase;

class BucketingPollingTest extends TestCase
{

    public function testStartPolling()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['get'], "", false);
        $envId = "envId";
        $pollingInterval = 0;
        $body = "body Content";

        $url = sprintf(FlagshipConstant::BUCKETING_API_URL, $envId);

        File::$fileContent = null;

        $httpClientMock->expects($this->exactly(2))
            ->method('get')
            ->with($url)
            ->willReturnOnConsecutiveCalls(
                new HttpResponse(204, null),
                new HttpResponse(204, $body)
            );

        $bucketingPolling = new BucketingPolling($envId, $pollingInterval, $httpClientMock);

        $bucketingPolling->startPolling();

        File::$fileExist = false;

        $this->assertNull(File::$fileContent);

        $bucketingPolling->startPolling();

        $this->assertSame(File::$fileContent, json_encode($body));
    }

    public function testStartPollingThrowError()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['get'], "", false);
        $envId = "envId";
        $pollingInterval = 0;
        $bucketingPolling = new BucketingPolling($envId, $pollingInterval, $httpClientMock);
        $url = sprintf(FlagshipConstant::BUCKETING_API_URL, $envId);

        $exception = new Exception("test exception");

        $httpClientMock->expects($this->once())
            ->method('get')
            ->with($url)
            ->willThrowException($exception);

        $bucketingPolling->startPolling();

        $this->assertSame(File::$fwriteData, $exception->getMessage() . PHP_EOL);
    }

    public function testCheckAndUpdateField()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['get'], "", false);
        $envId = "envId";
        $envId2 = "envId2";
        $pollingInterval = 0;
        $body = "body Content";

        $url = sprintf(FlagshipConstant::BUCKETING_API_URL, $envId);
        $url2 = sprintf(FlagshipConstant::BUCKETING_API_URL, $envId2);

        File::$fileExist = true;
        File::$fileContent = null;

        $httpClientMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([$url], [$url2])
            ->willReturn(new HttpResponse(204, $body));

        $bucketingPolling = new BucketingPolling($envId, $pollingInterval, $httpClientMock);

        $bucketingPolling->startPolling();

        File::$fileContent = '
                        {
                          "envId" : "' . $envId2 . '",
                          "apiKey" : "apikey",
                          "pollingInterval": 2000,
                          "timeout": 0,
                          "logLevel": 9,
                          "bucketingPath": "flagship"
                        }';

        $bucketingPolling->startPolling();
    }
}

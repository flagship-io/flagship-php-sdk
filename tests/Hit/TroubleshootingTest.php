<?php

namespace Flagship\Hit;

use DateTime;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Enum\HitType;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Model\FlagDTO;
use PHPUnit\Framework\TestCase;

class TroubleshootingTest extends TestCase
{
    public function testConstruct()
    {
        $config = new DecisionApiConfig();
        $config->setTimeout(5000);

        $troubleshooting = new Troubleshooting();

        $visitorId = "visitorId";
        $anonymousId = "anonymousId";
        $flagshipInstanceId = "flagshipInstanceId";
        $visitorInstanceId  = "visitorInstanceId";
        $stackOriginName = "stackOriginName";
        $stackOriginVersion = "1";
        $sdkStatus = FlagshipStatus::READY;
        $sdkConfigMode = DecisionMode::DECISION_API;
        $cacheStrategy = CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE;
        $httpRequestUrl = "https://localhost";
        $httpRequestMethod = "GET";
        $httpRequestHeaders = [
            "key" => "value"
        ];
        $httpRequestBody = [
            "key" => "value"
        ];
        $httpResponseUrl = "https://localhost";
        $httpResponseMethod = "GET";
        $httpResponseHeaders = [
            "key" => "value"
        ];
        $httpResponseTime = 1;
        $httpResponseCode = 200;
        $httpResponseBody = [
            'key' => "value"
        ];
        $visitorContext = [
            "key1" => "value1",
            "key2" => "value2"
        ];
        $visitorAssignmentHistory = [
            "key1" => "value1",
            "key2" => "value2"
        ];
        $flagDto = new FlagDTO();
        $flagDto->setKey("key")
            ->setValue("value")
            ->setCampaignId("campaignId")
            ->setCampaignType("ab")
            ->setCampaignName("campaignName")
            ->setVariationName("variationName")
            ->setVariationGroupId("varGroupId")
            ->setVariationGroupName("varGroupName");
        $visitorFlag = [
            $flagDto
        ];

        $troubleshooting->setVisitorId($visitorId)
            ->setAnonymousId($anonymousId)
            ->setConfig($config)
            ->setLogLevel(LogLevel::INFO)
            ->setLabel(TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS)
            ->setFlagshipInstanceId($flagshipInstanceId)
            ->setVisitorInstanceId($visitorInstanceId)
            ->setStackOriginName($stackOriginName)
            ->setStackOriginVersion($stackOriginVersion)
            ->setSdkStatus($sdkStatus)
            ->setSdkConfigMode($sdkConfigMode)
            ->setSdkConfigCustomLogManager(true)
            ->setSdkConfigCustomCacheManager(true)
            ->setSdkConfigStatusListener(false)
            ->setSdkConfigTimeout($config->getTimeout())
            ->setSdkConfigTrackingManagerConfigStrategy($cacheStrategy)
            ->setHttpRequestUrl($httpRequestUrl)
            ->setHttpRequestMethod($httpRequestMethod)
            ->setHttpRequestHeaders($httpRequestHeaders)
            ->setHttpRequestBody($httpRequestBody)
            ->setHttpResponseUrl($httpResponseUrl)
            ->setHttpResponseMethod($httpResponseMethod)
            ->setHttpResponseHeaders($httpResponseHeaders)
            ->setHttpResponseTime($httpResponseTime)
            ->setHttpResponseCode($httpResponseCode)
            ->setHttpResponseBody($httpResponseBody)
            ->setVisitorConsent(true)
            ->setVisitorContext($visitorContext)
            ->setVisitorAssignmentHistory($visitorAssignmentHistory)
            ->setVisitorFlags($visitorFlag)
        ;

        $customVariable = [
            'version' => FlagshipConstant::TROUBLESHOOTING_VERSION,
            'logLevel' => LogLevel::getLogName(LogLevel::INFO),
            'envId' => $config->getEnvId(),
            'timeZone' => (new DateTime())->getTimezone()->getName(),
            'label' => TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS,
            'stack.type' => FlagshipConstant::SDK,
            'stack.name' => FlagshipConstant::SDK_LANGUAGE,
            'stack.version' => FlagshipConstant::SDK_VERSION,
            'visitor.visitorId' => $visitorId,
            'visitor.anonymousId' => $anonymousId,
            'visitor.instanceId' => $visitorInstanceId,
            'flagshipInstanceId' => $flagshipInstanceId,
            'stack.origin.name' => $stackOriginName,
            'stack.origin.version' => $stackOriginVersion,
            'sdk.status' => FlagshipStatus::getStatusName($sdkStatus),
            'sdk.config.mode' => DecisionMode::getDecisionModeName($sdkConfigMode),
            'sdk.config.customLogManager' => 'true',
            'sdk.config.customCacheManager' => 'true',
            'sdk.config.custom.StatusListener' => 'false',
            'sdk.config.timeout' => (string) $config->getTimeout(),
            'sdk.config.trackingManager.config.strategy' => CacheStrategy::getCacheStrategyName($cacheStrategy),
            'http.request.url' => $httpRequestUrl,
            'http.request.method' => $httpRequestMethod,
            'http.request.headers' => json_encode($httpRequestHeaders),
            'http.request.body' => json_encode($httpRequestBody),
            'http.response.url' => $httpResponseUrl,
            'http.response.method' => $httpResponseMethod,
            'http.response.headers' => json_encode($httpResponseHeaders),
            'http.response.code' => (string)$httpResponseCode,
            "http.response.body" => json_encode($httpResponseBody),
            'http.response.time' => (string)$httpResponseTime,
            'visitor.context.key1' => 'value1',
            'visitor.context.key2' => 'value2',
            'visitor.consent' => 'true',
            'visitor.assignments.key1' => 'value1',
            'visitor.assignments.key2' => 'value2'
        ];

        $expectedApiKey = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
            FlagshipConstant::T_API_ITEM => HitType::TROUBLESHOOTING,
            'cv' => $customVariable
        ];

        $apiKey = $troubleshooting->toApiKeys();
        unset($apiKey['cv']['timestamp']);

        $this->assertSame($expectedApiKey, $apiKey);
    }
}

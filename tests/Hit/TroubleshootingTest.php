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
            ->setVariationId("varId")
            ->setVariationName("variationName")
            ->setVariationGroupId("varGroupId")
            ->setIsReference(false)
            ->setSlug("slug")
            ->setVariationGroupName("varGroupName");

        $flagDto2 = new FlagDTO();
        $flagDto2->setKey("key2")
            ->setValue([])
            ->setCampaignId("campaignId")
            ->setCampaignType("ab")
            ->setCampaignName("campaignName")
            ->setVariationId("varId")
            ->setVariationName("variationName")
            ->setVariationGroupId("varGroupId")
            ->setIsReference(false)
            ->setVariationGroupName("varGroupName");
        $visitorFlag = [
            $flagDto,
            $flagDto2
        ];

        $activateHit = new Activate("varGroupId", "varId");
        $activateHit->setConfig($config)
        ->setVisitorId($visitorId);

        $sdkConfigBucketingUrl = 'http://localhost';

        $troubleshooting->setVisitorId($visitorId)
            ->setAnonymousId($anonymousId)
            ->setConfig($config)
            ->setLogLevel(LogLevel::INFO)
            ->setLabel(TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS)
            ->setFlagshipInstanceId($flagshipInstanceId)
            ->setVisitorSessionId($visitorInstanceId)
            ->setStackOriginName($stackOriginName)
            ->setStackOriginVersion($stackOriginVersion)
            ->setSdkStatus($sdkStatus)
            ->setSdkConfigMode($sdkConfigMode)
            ->setSdkConfigCustomLogManager(true)
            ->setSdkConfigCustomCacheManager(true)
            ->setSdkConfigStatusListener(false)
            ->setSdkConfigBucketingUrl($sdkConfigBucketingUrl)
            ->setSdkConfigUsingCustomHitCache(true)
            ->setSdkConfigUsingOnVisitorExposed(true)
            ->setSdkConfigUsingCustomVisitorCache(true)
            ->setSdkConfigFetchThirdPartyData(true)
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
            ->setVisitorIsAuthenticated(true)
            ->setVisitorCampaigns([])
            ->setFlagKey($flagDto->getKey())
            ->setFlagValue($flagDto->getValue())
            ->setFlagMetadataCampaignIsReference($flagDto->getIsReference())
            ->setFlagMetadataVariationId($flagDto->getVariationId())
            ->setFlagMetadataVariationName($flagDto->getVariationName())
            ->setFlagMetadataVariationGroupId($flagDto->getVariationGroupId())
            ->setFlagMetadataVariationGroupName($flagDto->getVariationGroupName())
            ->setFlagMetadataCampaignId($flagDto->getCampaignId())
            ->setFlagMetadataCampaignName($flagDto->getCampaignName())
            ->setFlagMetadataCampaignType($flagDto->getCampaignType())
            ->setFlagDefault("default")
            ->setFlagMetadataCampaignSlug($flagDto->getSlug())
            ->setVisitorExposed(true)
            ->setHitContent($activateHit->toApiKeys())
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
            'visitor.sessionId' => $visitorInstanceId,
            'flagshipInstanceId' => $flagshipInstanceId,
            'stack.origin.name' => $stackOriginName,
            'stack.origin.version' => $stackOriginVersion,
            'sdk.status' => FlagshipStatus::getStatusName($sdkStatus),
            'sdk.config.mode' => DecisionMode::getDecisionModeName($sdkConfigMode),
            'sdk.config.customLogManager' => 'true',
            'sdk.config.customCacheManager' => 'true',
            'sdk.config.custom.StatusListener' => 'false',
            'sdk.config.timeout' => (string) $config->getTimeout(),
            'sdk.config.trackingManager.strategy' => CacheStrategy::getCacheStrategyName($cacheStrategy),
            'sdk.config.bucketingUrl' => $sdkConfigBucketingUrl,
            'sdk.config.fetchThirdPartyData' => 'true',
            'sdk.config.onVisitorExposed' => 'true',
            'sdk.config.usingCustomHitCache' => 'true',
            'sdk.config.usingCustomVisitorCache' => 'true',
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
            'visitor.assignments.key2' => 'value2',
            'visitor.flags.[key].key' => $flagDto->getKey(),
            'visitor.flags.[key].value' => $flagDto->getValue(),
            'visitor.flags.[key].metadata.variationId' => $flagDto->getVariationId(),
            'visitor.flags.[key].metadata.variationName' => $flagDto->getVariationName(),
            'visitor.flags.[key].metadata.variationGroupId' => $flagDto->getVariationGroupId(),
            'visitor.flags.[key].metadata.variationGroupName' => $flagDto->getVariationGroupName(),
            'visitor.flags.[key].metadata.campaignId' => $flagDto->getCampaignId(),
            'visitor.flags.[key].metadata.campaignName' => $flagDto->getCampaignName(),
            'visitor.flags.[key].metadata.campaignType' => $flagDto->getCampaignType(),
            'visitor.flags.[key].metadata.slug' => $flagDto->getSlug(),
            'visitor.flags.[key].metadata.isReference' => json_encode($flagDto->getIsReference()),
            'visitor.flags.[key2].key' => $flagDto2->getKey(),
            'visitor.flags.[key2].value' => json_encode($flagDto2->getValue()),
            'visitor.flags.[key2].metadata.variationId' => $flagDto2->getVariationId(),
            'visitor.flags.[key2].metadata.variationName' => $flagDto2->getVariationName(),
            'visitor.flags.[key2].metadata.variationGroupId' => $flagDto2->getVariationGroupId(),
            'visitor.flags.[key2].metadata.variationGroupName' => $flagDto2->getVariationGroupName(),
            'visitor.flags.[key2].metadata.campaignId' => $flagDto2->getCampaignId(),
            'visitor.flags.[key2].metadata.campaignName' => $flagDto2->getCampaignName(),
            'visitor.flags.[key2].metadata.campaignType' => $flagDto2->getCampaignType(),
            'visitor.flags.[key2].metadata.slug' => '',
            'visitor.flags.[key2].metadata.isReference' => json_encode($flagDto2->getIsReference()),
            'visitor.isAuthenticated' => 'true',
            'visitor.campaigns' => '[]',
            'flag.key' => $flagDto->getKey(),
            'flag.value' => $flagDto->getValue(),
            'flag.default' => "default",
            'flag.visitorExposed' => "true",
            'flag.metadata.campaignId' => $flagDto->getCampaignId(),
            'flag.metadata.campaignName' => $flagDto->getCampaignName(),
            'flag.metadata.variationGroupId' => $flagDto->getVariationGroupId(),
            'flag.metadata.variationGroupName' => $flagDto->getVariationGroupName(),
            'flag.metadata.variationId' => $flagDto->getVariationId(),
            'flag.metadata.variationName' => $flagDto->getVariationName(),
            'flag.metadata.campaignSlug' => $flagDto->getSlug(),
            'flag.metadata.campaignType' => $flagDto->getCampaignType(),
            'flag.metadata.isReference' => json_encode($flagDto->getIsReference()),
        ];

        foreach ($activateHit->toApiKeys() as $key => $item) {
            $customVariable["hit." . $key] =  is_string($item) ? $item : json_encode($item);
        }

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


        $flagDto = new FlagDTO();
        $flagDto->setKey("key")
            ->setValue([])
            ->setCampaignId("campaignId")
            ->setCampaignType("ab")
            ->setCampaignName("campaignName")
            ->setVariationId("varId")
            ->setVariationName("variationName")
            ->setVariationGroupId("varGroupId")
            ->setIsReference(false)
            ->setSlug("slug")
            ->setVariationGroupName("varGroupName");

        $troubleshooting = new Troubleshooting();

        $troubleshooting->setVisitorId($visitorId)
            ->setAnonymousId($anonymousId)
            ->setConfig($config)
            ->setLogLevel(LogLevel::INFO)
            ->setLabel(TroubleshootingLabel::VISITOR_FETCH_CAMPAIGNS)
            ->setFlagshipInstanceId($flagshipInstanceId)
            ->setVisitorSessionId($visitorInstanceId)
            ->setFlagKey($flagDto->getKey())
            ->setFlagValue($flagDto->getValue())
            ->setFlagMetadataCampaignIsReference($flagDto->getIsReference())
            ->setFlagMetadataVariationId($flagDto->getVariationId())
            ->setFlagMetadataVariationGroupId($flagDto->getVariationGroupId())
            ->setFlagMetadataCampaignId($flagDto->getCampaignId())
            ->setFlagMetadataCampaignType($flagDto->getCampaignType())
            ->setFlagDefault([])
            ->setFlagMetadataCampaignSlug($flagDto->getSlug())
            ->setVisitorExposed(true)
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
            'visitor.sessionId' => $visitorInstanceId,
            'flagshipInstanceId' => $flagshipInstanceId,
            'flag.key' => $flagDto->getKey(),
            'flag.value' => json_encode($flagDto->getValue()),
            'flag.default' => json_encode([]),
            'flag.visitorExposed' => "true",
            'flag.metadata.campaignId' => $flagDto->getCampaignId(),
            'flag.metadata.variationGroupId' => $flagDto->getVariationGroupId(),
            'flag.metadata.variationId' => $flagDto->getVariationId(),
            'flag.metadata.campaignSlug' => $flagDto->getSlug(),
            'flag.metadata.campaignType' => $flagDto->getCampaignType(),
            'flag.metadata.isReference' => json_encode($flagDto->getIsReference())
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

        $traffic  = 50;
        $troubleshooting->setTraffic($traffic);
        $this->assertSame($troubleshooting->getTraffic(), $traffic);

        $this->assertSame($troubleshooting->getErrorMessage(), "");
    }
}

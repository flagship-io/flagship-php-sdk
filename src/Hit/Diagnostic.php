<?php

namespace Flagship\Hit;

use DateTime;
use DateTimeZone;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Enum\LogLevel;
use Flagship\Model\FlagDTO;

class Diagnostic extends HitAbstract
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var int
     */
    private $logLevel;

    /**
     * @var string
     */
    private $timestamp;

    /**
     * @var string
     */
    private $timeZone;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $stackType;

    /**
     * @var string
     */
    private $stackName;

    /**
     * @var string
     */
    private $stackVersion;

    /**
     * @var string
     */
    private $stackOriginName;

    /**
     * @var string
     */
    private $stackOriginVersion;

    /**
     * @var int
     */
    private $sdkStatus;

    /**
     * @var numeric
     */
    private $sdkConfigMode;

    /**
     * @var bool
     */
    private $sdkConfigCustomLogManager;

    /**
     * @var bool
     */
    private $sdkConfigCustomCacheManager;

    /**
     * @var bool
     */
    private $sdkConfigStatusListener;

    /**
     * @var numeric
     */
    private $sdkConfigTimeout;


    /**
     * @var int
     */
    private $sdkConfigTrackingManagerConfigStrategy;

    /**
     * @var string
     */
    private $sdkConfigBucketingUrl;

    /**
     * @var string
     */
    private $sdkConfigFetchThirdPartyData;

    /**
     * @var boolean
     */
    private $sdkConfigUsingCustomHitCache;

    /**
     * @var boolean
     */
    private $sdkConfigUsingCustomVisitorCache;

    /**
     * @var boolean
     */
    private $sdkConfigUsingOnVisitorExposed;

    /**
     * @var string
     */
    private $httpRequestUrl;

    /**
     * @var string
     */
    private $httpRequestMethod;

    /**
     * @var array
     */
    private $httpRequestHeaders;

    /**
     * @var mixed
     */
    private $httpRequestBody;

    /**
     * @var string
     */
    private $httpResponseUrl;

    /**
     * @var string
     */
    private $httpResponseMethod;

    /**
     * @var array
     */
    private $httpResponseHeaders;

    /**
     * @var int
     */
    private $httpResponseCode;

    /**
     * @var mixed
     */
    private $httpResponseBody;

    /**
     * @var int
     */
    private $httpResponseTime;

    /**
     * @var array
     */
    private $visitorContext;

    /**
     * @var boolean
     */
    private $visitorConsent;

    /**
     * @var array
     */
    private $visitorAssignmentHistory;

    /**
     * @var FlagDTO[]
     */
    private $visitorFlags;

    /**
     * @var array
     */
    private $visitorCampaigns;

    /**
     * @var boolean
     */
    private $visitorIsAuthenticated;

    /**
     * @var string
     */
    private $flagKey;

    /**
     * @var mixed
     */
    private $flagValue;

    /**
     * @var mixed
     */
    private $flagDefault;

    /**
     * @var boolean
     */
    private $visitorExposed;

    /**
     * @var string
     */
    private $flagMetadataCampaignId;

    /**
     * @var string
     */
    private $flagMetadataCampaignName;

    /**
     * @var string
     */
    private $flagMetadataVariationGroupId;

    /**
     * @var string
     */
    private $flagMetadataVariationGroupName;

    /**
     * @var string
     */
    private $flagMetadataVariationId;

    /**
     * @var string
     */
    private $flagMetadataVariationName;

    /**
     * @var string
     */
    private $flagMetadataCampaignSlug;

    /**
     * @var string
     */
    private $flagMetadataCampaignType;

    /**
     * @var boolean
     */
    private $flagMetadataCampaignIsReference;


    /**
     * @var mixed
     */
    private $hitContent;

    /**
     * @var string
     */
    private $visitorSessionId;

    /**
     * @var numeric
     */
    private $traffic;

    private $flagshipInstanceId;



    public function __construct($type)
    {
        parent::__construct($type);
        $this->setVersion(FlagshipConstant::TROUBLESHOOTING_VERSION);
        $date = $this->getCurrentDateTime();
        $this->setTimestamp($date->format('c'))
            ->setTimeZone($date->getTimezone()->getName())
            ->setStackType(FlagshipConstant::SDK)
            ->setStackName(FlagshipConstant::SDK_LANGUAGE)
            ->setStackVersion(FlagshipConstant::SDK_VERSION);
    }

    /**
     * @return string
     */
    public function getFlagshipInstanceId()
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param string $flagshipInstanceId
     * @return Diagnostic
     */
    public function setFlagshipInstanceId($flagshipInstanceId)
    {
        $this->flagshipInstanceId = $flagshipInstanceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisitorSessionId()
    {
        return $this->visitorSessionId;
    }

    /**
     * @param string $visitorSessionId
     * @return Diagnostic
     */
    public function setVisitorSessionId($visitorSessionId)
    {
        $this->visitorSessionId = $visitorSessionId;
        return $this;
    }

    /**
     * @return float|int|string
     */
    public function getTraffic()
    {
        return $this->traffic;
    }

    /**
     * @param float|int|string $traffic
     * @return Diagnostic
     */
    public function setTraffic($traffic)
    {
        $this->traffic = $traffic;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return Diagnostic
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param int $logLevel
     * @return Diagnostic
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     * @return Diagnostic
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     * @return Diagnostic
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Diagnostic
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackType()
    {
        return $this->stackType;
    }

    /**
     * @param string $stackType
     * @return Diagnostic
     */
    public function setStackType($stackType)
    {
        $this->stackType = $stackType;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackName()
    {
        return $this->stackName;
    }

    /**
     * @param string $stackName
     * @return Diagnostic
     */
    public function setStackName($stackName)
    {
        $this->stackName = $stackName;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackVersion()
    {
        return $this->stackVersion;
    }

    /**
     * @param string $stackVersion
     * @return Diagnostic
     */
    public function setStackVersion($stackVersion)
    {
        $this->stackVersion = $stackVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackOriginName()
    {
        return $this->stackOriginName;
    }

    /**
     * @param string $stackOriginName
     * @return Diagnostic
     */
    public function setStackOriginName($stackOriginName)
    {
        $this->stackOriginName = $stackOriginName;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackOriginVersion()
    {
        return $this->stackOriginVersion;
    }

    /**
     * @param string $stackOriginVersion
     * @return Diagnostic
     */
    public function setStackOriginVersion($stackOriginVersion)
    {
        $this->stackOriginVersion = $stackOriginVersion;
        return $this;
    }

    /**
     * @return int
     */
    public function getSdkStatus()
    {
        return $this->sdkStatus;
    }

    /**
     * @param int $sdkStatus
     * @return Diagnostic
     */
    public function setSdkStatus($sdkStatus)
    {
        $this->sdkStatus = $sdkStatus;
        return $this;
    }

    /**
     * @return numeric
     */
    public function getSdkConfigMode()
    {
        return $this->sdkConfigMode;
    }

    /**
     * @param numeric $sdkConfigMode
     * @return Diagnostic
     */
    public function setSdkConfigMode($sdkConfigMode)
    {
        $this->sdkConfigMode = $sdkConfigMode;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSdkConfigCustomLogManager()
    {
        return $this->sdkConfigCustomLogManager;
    }

    /**
     * @param bool $sdkConfigCustomLogManager
     * @return Diagnostic
     */
    public function setSdkConfigCustomLogManager($sdkConfigCustomLogManager)
    {
        $this->sdkConfigCustomLogManager = $sdkConfigCustomLogManager;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSdkConfigCustomCacheManager()
    {
        return $this->sdkConfigCustomCacheManager;
    }

    /**
     * @param bool $sdkConfigCustomCacheManager
     * @return Diagnostic
     */
    public function setSdkConfigCustomCacheManager($sdkConfigCustomCacheManager)
    {
        $this->sdkConfigCustomCacheManager = $sdkConfigCustomCacheManager;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSdkConfigStatusListener()
    {
        return $this->sdkConfigStatusListener;
    }

    /**
     * @param bool $sdkConfigStatusListener
     * @return Diagnostic
     */
    public function setSdkConfigStatusListener($sdkConfigStatusListener)
    {
        $this->sdkConfigStatusListener = $sdkConfigStatusListener;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigBucketingUrl()
    {
        return $this->sdkConfigBucketingUrl;
    }

    /**
     * @param string $sdkConfigBucketingUrl
     * @return Diagnostic
     */
    public function setSdkConfigBucketingUrl($sdkConfigBucketingUrl)
    {
        $this->sdkConfigBucketingUrl = $sdkConfigBucketingUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigFetchThirdPartyData()
    {
        return $this->sdkConfigFetchThirdPartyData;
    }

    /**
     * @param string $sdkConfigFetchThirdPartyData
     * @return Diagnostic
     */
    public function setSdkConfigFetchThirdPartyData($sdkConfigFetchThirdPartyData)
    {
        $this->sdkConfigFetchThirdPartyData = $sdkConfigFetchThirdPartyData;
        return $this;
    }

    /**
     * @return numeric
     */
    public function getSdkConfigTimeout()
    {
        return $this->sdkConfigTimeout;
    }

    /**
     * @param numeric $sdkConfigTimeout
     * @return Diagnostic
     */
    public function setSdkConfigTimeout($sdkConfigTimeout)
    {
        $this->sdkConfigTimeout = $sdkConfigTimeout;
        return $this;
    }


    /**
     * @return int
     */
    public function getSdkConfigTrackingManagerConfigStrategy()
    {
        return $this->sdkConfigTrackingManagerConfigStrategy;
    }

    /**
     * @param int $sdkConfigTrackingManagerConfigStrategy
     * @return Diagnostic
     */
    public function setSdkConfigTrackingManagerConfigStrategy($sdkConfigTrackingManagerConfigStrategy)
    {
        $this->sdkConfigTrackingManagerConfigStrategy = $sdkConfigTrackingManagerConfigStrategy;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSdkConfigUsingCustomHitCache()
    {
        return $this->sdkConfigUsingCustomHitCache;
    }

    /**
     * @param bool $sdkConfigUsingCustomHitCache
     * @return Diagnostic
     */
    public function setSdkConfigUsingCustomHitCache($sdkConfigUsingCustomHitCache)
    {
        $this->sdkConfigUsingCustomHitCache = $sdkConfigUsingCustomHitCache;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSdkConfigUsingCustomVisitorCache()
    {
        return $this->sdkConfigUsingCustomVisitorCache;
    }

    /**
     * @param bool $sdkConfigUsingCustomVisitorCache
     * @return Diagnostic
     */
    public function setSdkConfigUsingCustomVisitorCache($sdkConfigUsingCustomVisitorCache)
    {
        $this->sdkConfigUsingCustomVisitorCache = $sdkConfigUsingCustomVisitorCache;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSdkConfigUsingOnVisitorExposed()
    {
        return $this->sdkConfigUsingOnVisitorExposed;
    }

    /**
     * @param bool $sdkConfigUsingOnVisitorExposed
     * @return Diagnostic
     */
    public function setSdkConfigUsingOnVisitorExposed($sdkConfigUsingOnVisitorExposed)
    {
        $this->sdkConfigUsingOnVisitorExposed = $sdkConfigUsingOnVisitorExposed;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpRequestUrl()
    {
        return $this->httpRequestUrl;
    }

    /**
     * @param string $httpRequestUrl
     * @return Diagnostic
     */
    public function setHttpRequestUrl($httpRequestUrl)
    {
        $this->httpRequestUrl = $httpRequestUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpRequestMethod()
    {
        return $this->httpRequestMethod;
    }

    /**
     * @param string $httpRequestMethod
     * @return Diagnostic
     */
    public function setHttpRequestMethod($httpRequestMethod)
    {
        $this->httpRequestMethod = $httpRequestMethod;
        return $this;
    }

    /**
     * @return array
     */
    public function getHttpRequestHeaders()
    {
        return $this->httpRequestHeaders;
    }

    /**
     * @param array $httpRequestHeaders
     * @return Diagnostic
     */
    public function setHttpRequestHeaders($httpRequestHeaders)
    {
        $this->httpRequestHeaders = $httpRequestHeaders;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttpRequestBody()
    {
        return $this->httpRequestBody;
    }

    /**
     * @param mixed $httpRequestBody
     * @return Diagnostic
     */
    public function setHttpRequestBody($httpRequestBody)
    {
        $this->httpRequestBody = $httpRequestBody;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpResponseUrl()
    {
        return $this->httpResponseUrl;
    }

    /**
     * @param string $httpResponseUrl
     * @return Diagnostic
     */
    public function setHttpResponseUrl($httpResponseUrl)
    {
        $this->httpResponseUrl = $httpResponseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpResponseMethod()
    {
        return $this->httpResponseMethod;
    }

    /**
     * @param string $httpResponseMethod
     * @return Diagnostic
     */
    public function setHttpResponseMethod($httpResponseMethod)
    {
        $this->httpResponseMethod = $httpResponseMethod;
        return $this;
    }

    /**
     * @return array
     */
    public function getHttpResponseHeaders()
    {
        return $this->httpResponseHeaders;
    }

    /**
     * @param array $httpResponseHeaders
     * @return Diagnostic
     */
    public function setHttpResponseHeaders($httpResponseHeaders)
    {
        $this->httpResponseHeaders = $httpResponseHeaders;
        return $this;
    }

    /**
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }

    /**
     * @param int $httpResponseCode
     * @return Diagnostic
     */
    public function setHttpResponseCode($httpResponseCode)
    {
        $this->httpResponseCode = $httpResponseCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttpResponseBody()
    {
        return $this->httpResponseBody;
    }

    /**
     * @param mixed $httpResponseBody
     * @return Diagnostic
     */
    public function setHttpResponseBody($httpResponseBody)
    {
        $this->httpResponseBody = $httpResponseBody;
        return $this;
    }

    /**
     * @return int
     */
    public function getHttpResponseTime()
    {
        return $this->httpResponseTime;
    }

    /**
     * @param int $httpResponseTime
     * @return Diagnostic
     */
    public function setHttpResponseTime($httpResponseTime)
    {
        $this->httpResponseTime = $httpResponseTime;
        return $this;
    }

    /**
     * @return array
     */
    public function getVisitorContext()
    {
        return $this->visitorContext;
    }

    /**
     * @param array $visitorContext
     * @return Diagnostic
     */
    public function setVisitorContext($visitorContext)
    {
        $this->visitorContext = $visitorContext;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisitorConsent()
    {
        return $this->visitorConsent;
    }

    /**
     * @param bool $visitorConsent
     * @return Diagnostic
     */
    public function setVisitorConsent($visitorConsent)
    {
        $this->visitorConsent = $visitorConsent;
        return $this;
    }

    /**
     * @return array
     */
    public function getVisitorAssignmentHistory()
    {
        return $this->visitorAssignmentHistory;
    }

    /**
     * @param array $visitorAssignmentHistory
     * @return Diagnostic
     */
    public function setVisitorAssignmentHistory($visitorAssignmentHistory)
    {
        $this->visitorAssignmentHistory = $visitorAssignmentHistory;
        return $this;
    }

    /**
     * @return FlagDTO[]
     */
    public function getVisitorFlags()
    {
        return $this->visitorFlags;
    }

    /**
     * @param FlagDTO[] $visitorFlags
     * @return Diagnostic
     */
    public function setVisitorFlags($visitorFlags)
    {
        $this->visitorFlags = $visitorFlags;
        return $this;
    }

    /**
     * @return array
     */
    public function getVisitorCampaigns()
    {
        return $this->visitorCampaigns;
    }

    /**
     * @param array $visitorCampaigns
     * @return Diagnostic
     */
    public function setVisitorCampaigns($visitorCampaigns)
    {
        $this->visitorCampaigns = $visitorCampaigns;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisitorIsAuthenticated()
    {
        return $this->visitorIsAuthenticated;
    }

    /**
     * @param bool $visitorIsAuthenticated
     * @return Diagnostic
     */
    public function setVisitorIsAuthenticated($visitorIsAuthenticated)
    {
        $this->visitorIsAuthenticated = $visitorIsAuthenticated;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagKey()
    {
        return $this->flagKey;
    }

    /**
     * @param string $flagKey
     * @return Diagnostic
     */
    public function setFlagKey($flagKey)
    {
        $this->flagKey = $flagKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlagValue()
    {
        return $this->flagValue;
    }

    /**
     * @param mixed $flagValue
     * @return Diagnostic
     */
    public function setFlagValue($flagValue)
    {
        $this->flagValue = $flagValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlagDefault()
    {
        return $this->flagDefault;
    }

    /**
     * @param mixed $flagDefault
     * @return Diagnostic
     */
    public function setFlagDefault($flagDefault)
    {
        $this->flagDefault = $flagDefault;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisitorExposed()
    {
        return $this->visitorExposed;
    }

    /**
     * @param bool $visitorExposed
     * @return Diagnostic
     */
    public function setVisitorExposed($visitorExposed)
    {
        $this->visitorExposed = $visitorExposed;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataCampaignId()
    {
        return $this->flagMetadataCampaignId;
    }

    /**
     * @param string $flagMetadataCampaignId
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignId($flagMetadataCampaignId)
    {
        $this->flagMetadataCampaignId = $flagMetadataCampaignId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataVariationGroupId()
    {
        return $this->flagMetadataVariationGroupId;
    }

    /**
     * @param string $flagMetadataVariationGroupId
     * @return Diagnostic
     */
    public function setFlagMetadataVariationGroupId($flagMetadataVariationGroupId)
    {
        $this->flagMetadataVariationGroupId = $flagMetadataVariationGroupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataVariationId()
    {
        return $this->flagMetadataVariationId;
    }

    /**
     * @param string $flagMetadataVariationId
     * @return Diagnostic
     */
    public function setFlagMetadataVariationId($flagMetadataVariationId)
    {
        $this->flagMetadataVariationId = $flagMetadataVariationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataCampaignSlug()
    {
        return $this->flagMetadataCampaignSlug;
    }

    /**
     * @param string $flagMetadataCampaignSlug
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignSlug($flagMetadataCampaignSlug)
    {
        $this->flagMetadataCampaignSlug = $flagMetadataCampaignSlug;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataCampaignType()
    {
        return $this->flagMetadataCampaignType;
    }

    /**
     * @param string $flagMetadataCampaignType
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignType($flagMetadataCampaignType)
    {
        $this->flagMetadataCampaignType = $flagMetadataCampaignType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFlagMetadataCampaignIsReference()
    {
        return $this->flagMetadataCampaignIsReference;
    }

    /**
     * @param bool $flagMetadataCampaignIsReference
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignIsReference($flagMetadataCampaignIsReference)
    {
        $this->flagMetadataCampaignIsReference = $flagMetadataCampaignIsReference;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataCampaignName()
    {
        return $this->flagMetadataCampaignName;
    }

    /**
     * @param string $flagMetadataCampaignName
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignName($flagMetadataCampaignName)
    {
        $this->flagMetadataCampaignName = $flagMetadataCampaignName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataVariationGroupName()
    {
        return $this->flagMetadataVariationGroupName;
    }

    /**
     * @param string $flagMetadataVariationGroupName
     * @return Diagnostic
     */
    public function setFlagMetadataVariationGroupName($flagMetadataVariationGroupName)
    {
        $this->flagMetadataVariationGroupName = $flagMetadataVariationGroupName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagMetadataVariationName()
    {
        return $this->flagMetadataVariationName;
    }

    /**
     * @param string $flagMetadataVariationName
     * @return Diagnostic
     */
    public function setFlagMetadataVariationName($flagMetadataVariationName)
    {
        $this->flagMetadataVariationName = $flagMetadataVariationName;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getHitContent()
    {
        return $this->hitContent;
    }

    /**
     * @param mixed $hitContent
     * @return Diagnostic
     */
    public function setHitContent($hitContent)
    {
        $this->hitContent = $hitContent;
        return $this;
    }

    public function toApiKeys()
    {
        $customVariable = [
            'version' => $this->getVersion(),
            'logLevel' => LogLevel::getLogName($this->getLogLevel()),
            'envId' => $this->getConfig()->getEnvId(),
            "timestamp" => $this->getTimestamp(),
            'timeZone' => $this->getTimeZone(),
            'label' => $this->getLabel(),
            'stack.type' => $this->getStackType(),
            'stack.name' => $this->getStackName(),
            'stack.version' => $this->getStackVersion()
        ];
        if ($this->getVisitorId() !== null) {
            $customVariable["visitor.visitorId"] = $this->getVisitorId();
        }

        if ($this->getAnonymousId() !== null) {
            $customVariable["visitor.anonymousId"] = $this->getAnonymousId();
        }

        if ($this->getVisitorSessionId() !== null) {
            $customVariable["visitor.sessionId"] = $this->getVisitorSessionId();
        }

        if ($this->getFlagshipInstanceId() !== null) {
            $customVariable["flagshipInstanceId"] = $this->getFlagshipInstanceId();
        }
        if ($this->getStackOriginName() !== null) {
            $customVariable["stack.origin.name"] = $this->getStackOriginName();
        }
        if ($this->getStackOriginVersion() !== null) {
            $customVariable["stack.origin.version"] = $this->getStackOriginVersion();
        }
        if ($this->getSdkStatus() !== null) {
            $customVariable["sdk.status"] = FlagshipStatus::getStatusName($this->getSdkStatus());
        }
        if ($this->getSdkConfigMode() !== null) {
            $customVariable["sdk.config.mode"] = DecisionMode::getDecisionModeName($this->getSdkConfigMode());
        }
        if ($this->getSdkConfigCustomLogManager() !== null) {
            $customVariable["sdk.config.customLogManager"] = json_encode($this->getSdkConfigCustomLogManager());
        }
        if ($this->getSdkConfigCustomCacheManager() !== null) {
            $customVariable["sdk.config.customCacheManager"] = json_encode($this->getSdkConfigCustomCacheManager());
        }
        if ($this->getSdkConfigStatusListener() !== null) {
            $customVariable["sdk.config.custom.StatusListener"] = json_encode($this->getSdkConfigStatusListener());
        }
        if ($this->getSdkConfigTimeout() !== null) {
            $customVariable["sdk.config.timeout"] = (string) $this->getSdkConfigTimeout();
        }

        if ($this->getSdkConfigTrackingManagerConfigStrategy() !== null) {
            $customVariable["sdk.config.trackingManager.config.strategy"] =
                CacheStrategy::getCacheStrategyName($this->getSdkConfigTrackingManagerConfigStrategy());
        }

        if ($this->getSdkConfigBucketingUrl() !== null) {
            $customVariable["sdk.config.bucketingUrl"] = $this->getSdkConfigBucketingUrl();
        }

        if ($this->getSdkConfigFetchThirdPartyData() !== null) {
            $customVariable["sdk.config.fetchThirdPartyData"] = $this->getSdkConfigFetchThirdPartyData();
        }

        if ($this->isSdkConfigUsingOnVisitorExposed() !== null) {
            $customVariable["sdk.config.onVisitorExposed"] = json_encode($this->isSdkConfigUsingOnVisitorExposed());
        }

        if ($this->isSdkConfigUsingCustomHitCache() !== null) {
            $customVariable["sdk.config.usingCustomHitCache"] = json_encode($this->isSdkConfigUsingCustomHitCache());
        }

        if ($this->isSdkConfigUsingCustomVisitorCache() !== null) {
            $customVariable["sdk.config.usingCustomVisitorCache"] =
                json_encode($this->isSdkConfigUsingCustomVisitorCache());
        }

        if ($this->getHttpRequestUrl() !== null) {
            $customVariable["http.request.url"] = $this->getHttpRequestUrl();
        }

        if ($this->getHttpRequestMethod() !== null) {
            $customVariable["http.request.method"] = $this->getHttpRequestMethod();
        }

        if ($this->getHttpRequestHeaders() !== null) {
            $customVariable["http.request.headers"] = json_encode($this->getHttpRequestHeaders());
        }

        if ($this->getHttpRequestBody() !== null) {
            $customVariable["http.request.body"] = json_encode($this->getHttpRequestBody());
        }

        if ($this->getHttpResponseUrl() !== null) {
            $customVariable["http.response.url"] = $this->getHttpResponseUrl();
        }

        if ($this->getHttpResponseMethod() !== null) {
            $customVariable["http.response.method"] = $this->getHttpResponseMethod();
        }

        if ($this->getHttpResponseHeaders() !== null) {
            $customVariable["http.response.headers"] = json_encode($this->getHttpResponseHeaders());
        }

        if ($this->getHttpResponseCode() !== null) {
            $customVariable["http.response.code"] = (string) $this->getHttpResponseCode();
        }

        if ($this->getHttpResponseBody() !== null) {
            $customVariable["http.response.body"] = json_encode($this->getHttpResponseBody());
        }

        if ($this->getHttpResponseTime() !== null) {
            $customVariable["http.response.time"] = (string) $this->getHttpResponseTime();
        }

        if (is_array($this->getVisitorContext())) {
            $context = $this->getVisitorContext();
            foreach ($context as $index => $item) {
                $customVariable["visitor.context.$index"] = is_string($item) ?  $item : json_encode($item);
            }
        }

        if ($this->isVisitorConsent() !== null) {
            $customVariable["visitor.consent"] = json_encode($this->isVisitorConsent());
        }

        if ($this->getVisitorAssignmentHistory() !== null) {
            $visitorAssignmentHistory = $this->getVisitorAssignmentHistory();
            foreach ($visitorAssignmentHistory as $key => $item) {
                $customVariable["visitor.assignments.$key"] = $item;
            }
        }

        if ($this->getVisitorFlags() !== null) {
            foreach ($this->getVisitorFlags() as $visitorFlag) {
                $key = $visitorFlag->getKey();
                $customVariableKey = "visitor.flags.[$key]";
                $customVariableKeyMetadata = "visitor.flags.[$key].metadata";
                $customVariable["$customVariableKey.key"] = $visitorFlag->getKey();
                $customVariable["$customVariableKey.value"] = is_string($visitorFlag->getValue()) ?
                    $visitorFlag->getValue() :
                    json_encode($visitorFlag->getValue());
                $customVariable["$customVariableKeyMetadata.variationId"] = $visitorFlag->getVariationId();
                $customVariable["$customVariableKeyMetadata.variationName"] = $visitorFlag->getVariationName();
                $customVariable["$customVariableKeyMetadata.variationGroupId"] = $visitorFlag->getVariationGroupId();
                $customVariable["$customVariableKeyMetadata.variationGroupName"] =
                    $visitorFlag->getVariationGroupName();
                $customVariable["$customVariableKeyMetadata.campaignId"] = $visitorFlag->getCampaignId();
                $customVariable["$customVariableKeyMetadata.campaignName"] = $visitorFlag->getCampaignName();
                $customVariable["$customVariableKeyMetadata.campaignType"] = $visitorFlag->getCampaignType();
                $customVariable["$customVariableKeyMetadata.slug"] = $visitorFlag->getSlug() ?: "";
                $customVariable["$customVariableKeyMetadata.isReference"] = json_encode($visitorFlag->getIsReference());
            }
        }

        if ($this->isVisitorIsAuthenticated() !== null) {
            $customVariable["visitor.isAuthenticated"] = json_encode($this->isVisitorIsAuthenticated());
        }
        if ($this->getVisitorCampaigns() !== null) {
            $customVariable["visitor.campaigns"] = json_encode($this->getVisitorCampaigns());
        }
        if ($this->getFlagKey() !== null) {
            $customVariable["flag.key"] = $this->getFlagKey();
        }
        if ($this->getFlagValue() !== null) {
            $customVariable["flag.value"] = is_string($this->getFlagValue()) ? $this->getFlagValue() :
            json_encode($this->getFlagValue());
        }
        if ($this->getFlagDefault() !== null) {
            $customVariable["flag.default"] = is_string($this->getFlagDefault()) ? $this->getFlagDefault() :
                json_encode($this->getFlagDefault());
        }
        if ($this->isVisitorExposed() !== null) {
            $customVariable["flag.visitorExposed"] = json_encode($this->isVisitorExposed());
        }
        if ($this->getFlagMetadataCampaignId() !== null) {
            $customVariable["flag.metadata.campaignId"] = $this->getFlagMetadataCampaignId();
        }
        if ($this->getFlagMetadataCampaignName() !== null) {
            $customVariable["flag.metadata.campaignName"] = $this->getFlagMetadataCampaignName();
        }
        if ($this->getFlagMetadataVariationGroupId() !== null) {
            $customVariable["flag.metadata.variationGroupId"] = $this->getFlagMetadataVariationGroupId();
        }
        if ($this->getFlagMetadataVariationGroupName() !== null) {
            $customVariable["flag.metadata.variationGroupName"] = $this->getFlagMetadataVariationGroupName();
        }
        if ($this->getFlagMetadataVariationId() !== null) {
            $customVariable["flag.metadata.variationId"] = $this->getFlagMetadataVariationId();
        }
        if ($this->getFlagMetadataVariationName() !== null) {
            $customVariable["flag.metadata.variationName"] = $this->getFlagMetadataVariationName();
        }
        if ($this->getFlagMetadataCampaignSlug() !== null) {
            $customVariable["flag.metadata.campaignSlug"] = $this->getFlagMetadataCampaignSlug();
        }
        if ($this->getFlagMetadataCampaignType() !== null) {
            $customVariable["flag.metadata.campaignType"] = $this->getFlagMetadataCampaignType();
        }
        if ($this->isFlagMetadataCampaignIsReference() !== null) {
            $customVariable["flag.metadata.isReference"] = json_encode($this->isFlagMetadataCampaignIsReference());
        }
        if ($this->getHitContent() !== null) {
            foreach ($this->getHitContent() as $key => $item) {
                $customVariable["hit." . $key] =  is_string($item) ? $item : json_encode($item);
            }
        }

        return [
            FlagshipConstant::VISITOR_ID_API_ITEM => $this->visitorId,
            FlagshipConstant::DS_API_ITEM => $this->getDs(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->getConfig()->getEnvId(),
            FlagshipConstant::T_API_ITEM => $this->getType(),
            'cv' => $customVariable
        ];
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        // TODO: Implement getErrorMessage() method.
        return  "";
    }
}

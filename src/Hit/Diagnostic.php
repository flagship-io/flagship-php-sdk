<?php

namespace Flagship\Hit;

class Diagnostic extends HitAbstract
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $logLevel;

    /**
     * @var string
     */
    private $envId;

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
     * @var string
     */
    private $sdkStatus;

    /**
     * @var string
     */
    private $sdkConfigMode;

    /**
     * @var string
     */
    private $sdkConfigCustomLogManager;

    /**
     * @var string
     */
    private $sdkConfigCustomCacheManager;

    /**
     * @var string
     */
    private $sdkConfigStatusListener;

    /**
     * @var string
     */
    private $sdkConfigTimeout;

    /**
     * @var string
     */
    private $sdkConfigPollingInterval;

    /**
     * @var string
     */
    private $sdkConfigFetchNow;

    /**
     * @var string
     */
    private $sdkConfigEnableClientCache;

    /**
     * @var string
     */
    private $sdkConfigTrackingManagerConfigStrategy;

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
     * @var string
     */
    private $httpResponseCode;

    /**
     * @var mixed
     */
    private $httpResponseBody;

    /**
     * @var string
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
     * @var array
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
    private $contextKey;

    /**
     * @var mixed
     */
    private $contextValue;

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
    private $flagMetadataVariationGroupId;

    /**
     * @var string
     */
    private $flagMetadataVariationId;

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

    public static function getClassName()
    {
        return __CLASS__;
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
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param string $logLevel
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
    public function getEnvId()
    {
        return $this->envId;
    }

    /**
     * @param string $envId
     * @return Diagnostic
     */
    public function setEnvId($envId)
    {
        $this->envId = $envId;
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
     * @return string
     */
    public function getSdkStatus()
    {
        return $this->sdkStatus;
    }

    /**
     * @param string $sdkStatus
     * @return Diagnostic
     */
    public function setSdkStatus($sdkStatus)
    {
        $this->sdkStatus = $sdkStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigMode()
    {
        return $this->sdkConfigMode;
    }

    /**
     * @param string $sdkConfigMode
     * @return Diagnostic
     */
    public function setSdkConfigMode($sdkConfigMode)
    {
        $this->sdkConfigMode = $sdkConfigMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigCustomLogManager()
    {
        return $this->sdkConfigCustomLogManager;
    }

    /**
     * @param string $sdkConfigCustomLogManager
     * @return Diagnostic
     */
    public function setSdkConfigCustomLogManager($sdkConfigCustomLogManager)
    {
        $this->sdkConfigCustomLogManager = $sdkConfigCustomLogManager;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigCustomCacheManager()
    {
        return $this->sdkConfigCustomCacheManager;
    }

    /**
     * @param string $sdkConfigCustomCacheManager
     * @return Diagnostic
     */
    public function setSdkConfigCustomCacheManager($sdkConfigCustomCacheManager)
    {
        $this->sdkConfigCustomCacheManager = $sdkConfigCustomCacheManager;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigStatusListener()
    {
        return $this->sdkConfigStatusListener;
    }

    /**
     * @param string $sdkConfigStatusListener
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
    public function getSdkConfigTimeout()
    {
        return $this->sdkConfigTimeout;
    }

    /**
     * @param string $sdkConfigTimeout
     * @return Diagnostic
     */
    public function setSdkConfigTimeout($sdkConfigTimeout)
    {
        $this->sdkConfigTimeout = $sdkConfigTimeout;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigPollingInterval()
    {
        return $this->sdkConfigPollingInterval;
    }

    /**
     * @param string $sdkConfigPollingInterval
     * @return Diagnostic
     */
    public function setSdkConfigPollingInterval($sdkConfigPollingInterval)
    {
        $this->sdkConfigPollingInterval = $sdkConfigPollingInterval;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigFetchNow()
    {
        return $this->sdkConfigFetchNow;
    }

    /**
     * @param string $sdkConfigFetchNow
     * @return Diagnostic
     */
    public function setSdkConfigFetchNow($sdkConfigFetchNow)
    {
        $this->sdkConfigFetchNow = $sdkConfigFetchNow;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigEnableClientCache()
    {
        return $this->sdkConfigEnableClientCache;
    }

    /**
     * @param string $sdkConfigEnableClientCache
     * @return Diagnostic
     */
    public function setSdkConfigEnableClientCache($sdkConfigEnableClientCache)
    {
        $this->sdkConfigEnableClientCache = $sdkConfigEnableClientCache;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkConfigTrackingManagerConfigStrategy()
    {
        return $this->sdkConfigTrackingManagerConfigStrategy;
    }

    /**
     * @param string $sdkConfigTrackingManagerConfigStrategy
     * @return Diagnostic
     */
    public function setSdkConfigTrackingManagerConfigStrategy($sdkConfigTrackingManagerConfigStrategy)
    {
        $this->sdkConfigTrackingManagerConfigStrategy = $sdkConfigTrackingManagerConfigStrategy;
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
     * @return string
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }

    /**
     * @param string $httpResponseCode
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
     * @return string
     */
    public function getHttpResponseTime()
    {
        return $this->httpResponseTime;
    }

    /**
     * @param string $httpResponseTime
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
     * @return array
     */
    public function getVisitorFlags()
    {
        return $this->visitorFlags;
    }

    /**
     * @param array $visitorFlags
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
    public function getContextKey()
    {
        return $this->contextKey;
    }

    /**
     * @param string $contextKey
     * @return Diagnostic
     */
    public function setContextKey($contextKey)
    {
        $this->contextKey = $contextKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContextValue()
    {
        return $this->contextValue;
    }

    /**
     * @param mixed $contextValue
     * @return Diagnostic
     */
    public function setContextValue($contextValue)
    {
        $this->contextValue = $contextValue;
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

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        // TODO: Implement getErrorMessage() method.
    }
}
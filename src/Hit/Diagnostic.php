<?php

namespace Flagship\Hit;

use Flagship\Enum\LogLevel;
use Flagship\Model\FlagDTO;
use Flagship\Enum\FSSdkStatus;
use Flagship\Enum\DecisionMode;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\TroubleshootingLabel;

class Diagnostic extends HitAbstract
{
    /**
     * @var string
     */
    private string $version;

    /**
     * @var LogLevel
     */
    private LogLevel $logLevel;

    /**
     * @var string
     */
    private string $timestamp;

    /**
     * @var string
     */
    private string $timeZone;

    /**
     * @var TroubleshootingLabel
     */
    private TroubleshootingLabel $label;

    /**
     * @var string
     */
    private string $stackType;

    /**
     * @var string
     */
    private string $stackName;

    /**
     * @var string
     */
    private string $stackVersion;

    /**
     * @var ?string
     */
    private ?string $stackOriginName = null;

    /**
     * @var ?string
     */
    private ?string $stackOriginVersion = null;

    /**
     * @var ?FSSdkStatus
     */
    private ?FSSdkStatus $sdkStatus = null;

    /**
     * @var ?DecisionMode
     */
    private ?DecisionMode $sdkConfigMode = null;

    /**
     * @var ?LogLevel
     */
    private ?LogLevel $sdkConfigLogLeve = null;

    /**
     * @var ?bool
     */
    private ?bool $sdkConfigCustomLogManager = null;

    /**
     * @var ?bool
     */
    private ?bool $sdkConfigCustomCacheManager = null;

    /**
     * @var ?bool
     */
    private ?bool $sdkConfigStatusListener = null;

    /**
     * @var int|float|null|string
     */
    private int|float|null|string $sdkConfigTimeout = null;


    /**
     * @var ?CacheStrategy
     */
    private ?CacheStrategy $sdkConfigTrackingManagerConfigStrategy = null;

    /**
     * @var ?string
     */
    private ?string $sdkConfigBucketingUrl = null;

    /**
     * @var ?bool
     */
    private ?bool $sdkConfigFetchThirdPartyData = null;

    /**
     * @var ?boolean
     */
    private ?bool $sdkConfigUsingCustomHitCache = null;

    /**
     * @var ?boolean
     */
    private ?bool $sdkConfigUsingCustomVisitorCache = null;

    /**
     * @var ?boolean
     */
    private ?bool $sdkConfigUsingOnVisitorExposed = null;

    /**
     * @var ?string
     */
    private ?string $httpRequestUrl = null;

    /**
     * @var ?string
     */
    private ?string $httpRequestMethod  = null;

    /**
     * @var ?array<mixed>
     */
    private ?array $httpRequestHeaders = null;

    /**
     * @var ?mixed
     */
    private mixed $httpRequestBody = null;

    /**
     * @var ?string
     */
    private ?string $httpResponseUrl = null;

    /**
     * @var ?string
     */
    private ?string $httpResponseMethod = null;

    /**
     * @var ?array<mixed>
     */
    private ?array $httpResponseHeaders = null;

    /**
     * @var int|string|null
     */
    private string|int|null $httpResponseCode = null;

    /**
     * @var mixed
     */
    private mixed $httpResponseBody = null;

    /**
     * @var ?int
     */
    private int|float|null $httpResponseTime = null;

    /**
     * @var ?array<mixed>
     */
    private ?array $visitorContext  = null;

    /**
     * @var ?bool
     */
    private ?bool $visitorConsent = null;

    /**
     * @var ?array<mixed>
     */
    private ?array $visitorAssignmentHistory = null;

    /**
     * @var ?FlagDTO[]
     */
    private ?array $visitorFlags = null;

    /**
     * @var ?array<mixed>
     */
    private ?array $visitorCampaigns = null;

    /**
     * @var ?bool
     */
    private ?bool $visitorIsAuthenticated = null;

    /**
     * @var ?string
     */
    private ?string $flagKey = null;

    /**
     * @var mixed
     */
    private mixed $flagValue = null;

    /**
     * @var mixed
     */
    private mixed $flagDefault = null;

    /**
     * @var ?bool
     */
    private ?bool $visitorExposed = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataCampaignId = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataCampaignName = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataVariationGroupId = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataVariationGroupName = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataVariationId = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataVariationName = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataCampaignSlug = null;

    /**
     * @var ?string
     */
    private ?string $flagMetadataCampaignType = null;

    /**
     * @var ?bool
     */
    private ?bool $flagMetadataCampaignIsReference = null;


    /**
     * @var array<string, mixed>
     */
    private ?array $hitContent = null;

    /**
     * @var ?string
     */
    private ?string $visitorSessionId = null;

    /**
     * @var int|float|null
     */
    private int|float|null $traffic;

    private ?string $flagshipInstanceId = null;


    public function __construct($type)
    {
        parent::__construct($type);
        $this->setVersion(FlagshipConstant::TROUBLESHOOTING_VERSION);
        $date = $this->getCurrentDateTime();
        $this->setTimestamp($date->format('c'))->setTimeZone($date->getTimezone()->getName())->setStackType(FlagshipConstant::SDK)->setStackName(FlagshipConstant::SDK_LANGUAGE)->setStackVersion(FlagshipConstant::SDK_VERSION);
    }

    /**
     * @return string|null
     */
    public function getFlagshipInstanceId(): ?string
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param ?string $flagshipInstanceId
     * @return Diagnostic
     */
    public function setFlagshipInstanceId(?string $flagshipInstanceId): self
    {
        $this->flagshipInstanceId = $flagshipInstanceId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVisitorSessionId(): ?string
    {
        return $this->visitorSessionId;
    }

    /**
     * @param string $visitorSessionId
     * @return Diagnostic
     */
    public function setVisitorSessionId(string $visitorSessionId): self
    {
        $this->visitorSessionId = $visitorSessionId;
        return $this;
    }

    /**
     * @return float|int|null
     */
    public function getTraffic(): float|int|null
    {
        return $this->traffic;
    }

    /**
     * @param float|int|null $traffic
     * @return Diagnostic
     */
    public function setTraffic(float|int|null $traffic): self
    {
        $this->traffic = $traffic;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return Diagnostic
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return LogLevel
     */
    public function getLogLevel(): LogLevel
    {
        return $this->logLevel;
    }

    /**
     * @param LogLevel $logLevel
     * @return Diagnostic
     */
    public function setLogLevel(LogLevel $logLevel): self
    {
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     * @return Diagnostic
     */
    public function setTimestamp(string $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     * @return Diagnostic
     */
    public function setTimeZone(string $timeZone): self
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    /**
     * @return TroubleshootingLabel
     */
    public function getLabel(): TroubleshootingLabel
    {
        return $this->label;
    }

    /**
     * @param TroubleshootingLabel $label
     * @return Diagnostic
     */
    public function setLabel(TroubleshootingLabel $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackType(): string
    {
        return $this->stackType;
    }

    /**
     * @param string $stackType
     * @return Diagnostic
     */
    public function setStackType(string $stackType): self
    {
        $this->stackType = $stackType;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackName(): string
    {
        return $this->stackName;
    }

    /**
     * @param string $stackName
     * @return Diagnostic
     */
    public function setStackName(string $stackName): self
    {
        $this->stackName = $stackName;
        return $this;
    }

    /**
     * @return string
     */
    public function getStackVersion(): string
    {
        return $this->stackVersion;
    }

    /**
     * @param string $stackVersion
     * @return Diagnostic
     */
    public function setStackVersion(string $stackVersion): self
    {
        $this->stackVersion = $stackVersion;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStackOriginName(): ?string
    {
        return $this->stackOriginName;
    }

    /**
     * @param string $stackOriginName
     * @return Diagnostic
     */
    public function setStackOriginName(string $stackOriginName): self
    {
        $this->stackOriginName = $stackOriginName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStackOriginVersion(): ?string
    {
        return $this->stackOriginVersion;
    }

    /**
     * @param string $stackOriginVersion
     * @return Diagnostic
     */
    public function setStackOriginVersion(string $stackOriginVersion): self
    {
        $this->stackOriginVersion = $stackOriginVersion;
        return $this;
    }

    /**
     * @return ?FSSdkStatus
     */
    public function getSdkStatus(): ?FSSdkStatus
    {
        return $this->sdkStatus;
    }

    /**
     * @param ?FSSdkStatus $sdkStatus
     * @return Diagnostic
     */
    public function setSdkStatus(?FSSdkStatus $sdkStatus): self
    {
        $this->sdkStatus = $sdkStatus;
        return $this;
    }

    /**
     * @return ?DecisionMode
     */
    public function getSdkConfigMode(): ?DecisionMode
    {
        return $this->sdkConfigMode;
    }

    /**
     * @param DecisionMode $sdkConfigMode
     * @return Diagnostic
     */
    public function setSdkConfigMode(DecisionMode $sdkConfigMode): self
    {
        $this->sdkConfigMode = $sdkConfigMode;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSdkConfigCustomLogManager(): ?bool
    {
        return $this->sdkConfigCustomLogManager;
    }

    /**
     * @param bool $sdkConfigCustomLogManager
     * @return Diagnostic
     */
    public function setSdkConfigCustomLogManager(bool $sdkConfigCustomLogManager): self
    {
        $this->sdkConfigCustomLogManager = $sdkConfigCustomLogManager;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSdkConfigCustomCacheManager(): ?bool
    {
        return $this->sdkConfigCustomCacheManager;
    }

    /**
     * @param bool $sdkConfigCustomCacheManager
     * @return Diagnostic
     */
    public function setSdkConfigCustomCacheManager(bool $sdkConfigCustomCacheManager): self
    {
        $this->sdkConfigCustomCacheManager = $sdkConfigCustomCacheManager;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSdkConfigStatusListener(): ?bool
    {
        return $this->sdkConfigStatusListener;
    }

    /**
     * @param bool $sdkConfigStatusListener
     * @return Diagnostic
     */
    public function setSdkConfigStatusListener(bool $sdkConfigStatusListener): self
    {
        $this->sdkConfigStatusListener = $sdkConfigStatusListener;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSdkConfigBucketingUrl(): ?string
    {
        return $this->sdkConfigBucketingUrl;
    }

    /**
     * @param ?string $sdkConfigBucketingUrl
     * @return Diagnostic
     */
    public function setSdkConfigBucketingUrl(?string $sdkConfigBucketingUrl): self
    {
        $this->sdkConfigBucketingUrl = $sdkConfigBucketingUrl;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSdkConfigFetchThirdPartyData(): ?bool
    {
        return $this->sdkConfigFetchThirdPartyData;
    }

    /**
     * @param ?bool $sdkConfigFetchThirdPartyData
     * @return Diagnostic
     */
    public function setSdkConfigFetchThirdPartyData(?bool $sdkConfigFetchThirdPartyData): self
    {
        $this->sdkConfigFetchThirdPartyData = $sdkConfigFetchThirdPartyData;
        return $this;
    }

    /**
     * @return float|int|string|null
     */
    public function getSdkConfigTimeout(): float|int|string|null
    {
        return $this->sdkConfigTimeout;
    }

    /**
     * @param numeric $sdkConfigTimeout
     * @return Diagnostic
     */
    public function setSdkConfigTimeout(float|int|string $sdkConfigTimeout): self
    {
        $this->sdkConfigTimeout = $sdkConfigTimeout;
        return $this;
    }


    /**
     * @return ?CacheStrategy
     */
    public function getSdkConfigTrackingManagerConfigStrategy(): ?CacheStrategy
    {
        return $this->sdkConfigTrackingManagerConfigStrategy;
    }

    /**
     * @param CacheStrategy $sdkConfigTrackingManagerConfigStrategy
     * @return Diagnostic
     */
    public function setSdkConfigTrackingManagerConfigStrategy(
        CacheStrategy $sdkConfigTrackingManagerConfigStrategy
    ): self {
        $this->sdkConfigTrackingManagerConfigStrategy = $sdkConfigTrackingManagerConfigStrategy;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSdkConfigUsingCustomHitCache(): ?bool
    {
        return $this->sdkConfigUsingCustomHitCache;
    }

    /**
     * @param bool $sdkConfigUsingCustomHitCache
     * @return Diagnostic
     */
    public function setSdkConfigUsingCustomHitCache(bool $sdkConfigUsingCustomHitCache): self
    {
        $this->sdkConfigUsingCustomHitCache = $sdkConfigUsingCustomHitCache;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSdkConfigUsingCustomVisitorCache(): ?bool
    {
        return $this->sdkConfigUsingCustomVisitorCache;
    }

    /**
     * @param bool $sdkConfigUsingCustomVisitorCache
     * @return Diagnostic
     */
    public function setSdkConfigUsingCustomVisitorCache(bool $sdkConfigUsingCustomVisitorCache): self
    {
        $this->sdkConfigUsingCustomVisitorCache = $sdkConfigUsingCustomVisitorCache;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSdkConfigUsingOnVisitorExposed(): ?bool
    {
        return $this->sdkConfigUsingOnVisitorExposed;
    }

    /**
     * @param bool $sdkConfigUsingOnVisitorExposed
     * @return Diagnostic
     */
    public function setSdkConfigUsingOnVisitorExposed(bool $sdkConfigUsingOnVisitorExposed): self
    {
        $this->sdkConfigUsingOnVisitorExposed = $sdkConfigUsingOnVisitorExposed;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHttpRequestUrl(): ?string
    {
        return $this->httpRequestUrl;
    }

    /**
     * @param string $httpRequestUrl
     * @return Diagnostic
     */
    public function setHttpRequestUrl(string $httpRequestUrl): self
    {
        $this->httpRequestUrl = $httpRequestUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHttpRequestMethod(): ?string
    {
        return $this->httpRequestMethod;
    }

    /**
     * @param string $httpRequestMethod
     * @return Diagnostic
     */
    public function setHttpRequestMethod(string $httpRequestMethod): self
    {
        $this->httpRequestMethod = $httpRequestMethod;
        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getHttpRequestHeaders(): ?array
    {
        return $this->httpRequestHeaders;
    }

    /**
     * @param array<mixed> $httpRequestHeaders
     * @return Diagnostic
     */
    public function setHttpRequestHeaders(array $httpRequestHeaders): self
    {
        $this->httpRequestHeaders = $httpRequestHeaders;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttpRequestBody(): mixed
    {
        return $this->httpRequestBody;
    }

    /**
     * @param mixed $httpRequestBody
     * @return Diagnostic
     */
    public function setHttpRequestBody(mixed $httpRequestBody): self
    {
        $this->httpRequestBody = $httpRequestBody;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHttpResponseUrl(): ?string
    {
        return $this->httpResponseUrl;
    }

    /**
     * @param string $httpResponseUrl
     * @return Diagnostic
     */
    public function setHttpResponseUrl(string $httpResponseUrl): self
    {
        $this->httpResponseUrl = $httpResponseUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHttpResponseMethod(): ?string
    {
        return $this->httpResponseMethod;
    }

    /**
     * @param string $httpResponseMethod
     * @return Diagnostic
     */
    public function setHttpResponseMethod(string $httpResponseMethod): self
    {
        $this->httpResponseMethod = $httpResponseMethod;
        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getHttpResponseHeaders(): ?array
    {
        return $this->httpResponseHeaders;
    }

    /**
     * @param array<mixed> $httpResponseHeaders
     * @return Diagnostic
     */
    public function setHttpResponseHeaders(array $httpResponseHeaders): self
    {
        $this->httpResponseHeaders = $httpResponseHeaders;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getHttpResponseCode(): int|null|string
    {
        return $this->httpResponseCode;
    }

    /**
     * @param int|null|string $httpResponseCode
     * @return Diagnostic
     */
    public function setHttpResponseCode(int|null|string $httpResponseCode): self
    {
        $this->httpResponseCode = $httpResponseCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHttpResponseBody(): mixed
    {
        return $this->httpResponseBody;
    }

    /**
     * @param mixed $httpResponseBody
     * @return Diagnostic
     */
    public function setHttpResponseBody(mixed $httpResponseBody): self
    {
        $this->httpResponseBody = $httpResponseBody;
        return $this;
    }

    /**
     * @return int|float|string|null
     */
    public function getHttpResponseTime(): int|float|null|string
    {
        return $this->httpResponseTime;
    }

    /**
     * @param int|float $httpResponseTime
     * @return Diagnostic
     */
    public function setHttpResponseTime(int|float $httpResponseTime): self
    {
        $this->httpResponseTime = $httpResponseTime;
        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getVisitorContext(): ?array
    {
        return $this->visitorContext;
    }

    /**
     * @param array<mixed> $visitorContext
     * @return Diagnostic
     */
    public function setVisitorContext(array $visitorContext): self
    {
        $this->visitorContext = $visitorContext;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isVisitorConsent(): ?bool
    {
        return $this->visitorConsent;
    }

    /**
     * @param bool $visitorConsent
     * @return Diagnostic
     */
    public function setVisitorConsent(bool $visitorConsent): self
    {
        $this->visitorConsent = $visitorConsent;
        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getVisitorAssignmentHistory(): ?array
    {
        return $this->visitorAssignmentHistory;
    }

    /**
     * @param array<mixed> $visitorAssignmentHistory
     * @return Diagnostic
     */
    public function setVisitorAssignmentHistory(array $visitorAssignmentHistory): self
    {
        $this->visitorAssignmentHistory = $visitorAssignmentHistory;
        return $this;
    }

    /**
     * @return FlagDTO[]|null
     */
    public function getVisitorFlags(): ?array
    {
        return $this->visitorFlags;
    }

    /**
     * @param FlagDTO[] $visitorFlags
     * @return Diagnostic
     */
    public function setVisitorFlags(array $visitorFlags): self
    {
        $this->visitorFlags = $visitorFlags;
        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getVisitorCampaigns(): ?array
    {
        return $this->visitorCampaigns;
    }

    /**
     * @param array<mixed> $visitorCampaigns
     * @return Diagnostic
     */
    public function setVisitorCampaigns(array $visitorCampaigns): self
    {
        $this->visitorCampaigns = $visitorCampaigns;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isVisitorIsAuthenticated(): ?bool
    {
        return $this->visitorIsAuthenticated;
    }

    /**
     * @param bool $visitorIsAuthenticated
     * @return Diagnostic
     */
    public function setVisitorIsAuthenticated(bool $visitorIsAuthenticated): self
    {
        $this->visitorIsAuthenticated = $visitorIsAuthenticated;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagKey(): ?string
    {
        return $this->flagKey;
    }

    /**
     * @param string $flagKey
     * @return Diagnostic
     */
    public function setFlagKey(string $flagKey): self
    {
        $this->flagKey = $flagKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlagValue(): mixed
    {
        return $this->flagValue;
    }

    /**
     * @param mixed $flagValue
     * @return Diagnostic
     */
    public function setFlagValue(mixed $flagValue): self
    {
        $this->flagValue = $flagValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlagDefault(): mixed
    {
        return $this->flagDefault;
    }

    /**
     * @param mixed $flagDefault
     * @return Diagnostic
     */
    public function setFlagDefault(mixed $flagDefault): self
    {
        $this->flagDefault = $flagDefault;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isVisitorExposed(): ?bool
    {
        return $this->visitorExposed;
    }

    /**
     * @param bool $visitorExposed
     * @return Diagnostic
     */
    public function setVisitorExposed(bool $visitorExposed): self
    {
        $this->visitorExposed = $visitorExposed;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataCampaignId(): ?string
    {
        return $this->flagMetadataCampaignId;
    }

    /**
     * @param string $flagMetadataCampaignId
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignId(string $flagMetadataCampaignId): self
    {
        $this->flagMetadataCampaignId = $flagMetadataCampaignId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataVariationGroupId(): ?string
    {
        return $this->flagMetadataVariationGroupId;
    }

    /**
     * @param string $flagMetadataVariationGroupId
     * @return Diagnostic
     */
    public function setFlagMetadataVariationGroupId(string $flagMetadataVariationGroupId): self
    {
        $this->flagMetadataVariationGroupId = $flagMetadataVariationGroupId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataVariationId(): ?string
    {
        return $this->flagMetadataVariationId;
    }

    /**
     * @param string $flagMetadataVariationId
     * @return Diagnostic
     */
    public function setFlagMetadataVariationId(string $flagMetadataVariationId): self
    {
        $this->flagMetadataVariationId = $flagMetadataVariationId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataCampaignSlug(): ?string
    {
        return $this->flagMetadataCampaignSlug;
    }

    /**
     * @param string $flagMetadataCampaignSlug
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignSlug(string $flagMetadataCampaignSlug): self
    {
        $this->flagMetadataCampaignSlug = $flagMetadataCampaignSlug;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataCampaignType(): ?string
    {
        return $this->flagMetadataCampaignType;
    }

    /**
     * @param string $flagMetadataCampaignType
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignType(string $flagMetadataCampaignType): self
    {
        $this->flagMetadataCampaignType = $flagMetadataCampaignType;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isFlagMetadataCampaignIsReference(): ?bool
    {
        return $this->flagMetadataCampaignIsReference;
    }

    /**
     * @param bool $flagMetadataCampaignIsReference
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignIsReference(bool $flagMetadataCampaignIsReference): self
    {
        $this->flagMetadataCampaignIsReference = $flagMetadataCampaignIsReference;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataCampaignName(): ?string
    {
        return $this->flagMetadataCampaignName;
    }

    /**
     * @param string $flagMetadataCampaignName
     * @return Diagnostic
     */
    public function setFlagMetadataCampaignName(string $flagMetadataCampaignName): self
    {
        $this->flagMetadataCampaignName = $flagMetadataCampaignName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataVariationGroupName(): ?string
    {
        return $this->flagMetadataVariationGroupName;
    }

    /**
     * @param string $flagMetadataVariationGroupName
     * @return Diagnostic
     */
    public function setFlagMetadataVariationGroupName(string $flagMetadataVariationGroupName): self
    {
        $this->flagMetadataVariationGroupName = $flagMetadataVariationGroupName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFlagMetadataVariationName(): ?string
    {
        return $this->flagMetadataVariationName;
    }

    /**
     * @param string $flagMetadataVariationName
     * @return Diagnostic
     */
    public function setFlagMetadataVariationName(string $flagMetadataVariationName): self
    {
        $this->flagMetadataVariationName = $flagMetadataVariationName;
        return $this;
    }


    /**
     * @return array<string, mixed>|null
     */
    public function getHitContent(): ?array
    {
        return $this->hitContent;
    }

    /**
     * @param array<string, mixed> $hitContent
     * @return Diagnostic
     */
    public function setHitContent(array $hitContent): self
    {
        $this->hitContent = $hitContent;
        return $this;
    }

    /**
     * @return ?LogLevel
     */
    public function getSdkConfigLogLeve(): ?LogLevel
    {
        return $this->sdkConfigLogLeve;
    }

    /**
     * @param LogLevel $sdkConfigLogLeve
     * @return Diagnostic
     */
    public function setSdkConfigLogLevel(LogLevel $sdkConfigLogLeve): self
    {
        $this->sdkConfigLogLeve = $sdkConfigLogLeve;
        return $this;
    }



    /**
     * 
     * @return array<mixed>
     */
    public function toApiKeys(): array
    {
        $customVariable = [
            'version'       => $this->getVersion(),
            'logLevel'      => $this->getLogLevel()->name,
            'envId'         => $this->getConfig()?->getEnvId(),
            "timestamp"     => $this->getTimestamp(),
            'timeZone'      => $this->getTimeZone(),
            'label'         => $this->getLabel()->value,
            'stack.type'    => $this->getStackType(),
            'stack.name'    => $this->getStackName(),
            'stack.version' => $this->getStackVersion(),
        ];
        if (!empty($this->getVisitorId())) {
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
            $customVariable["sdk.status"] = $this->getSdkStatus()->name;
        }
        if ($this->getSdkConfigLogLeve() !== null) {
            $customVariable["sdk.config.logLevel"] = $this->getSdkConfigLogLeve()->name;
        }
        if ($this->getSdkConfigMode() !== null) {
            $customVariable["sdk.config.mode"] = $this->getSdkConfigMode()->name;
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
            $customVariable["sdk.config.trackingManager.strategy"] = $this->getSdkConfigTrackingManagerConfigStrategy()->name;
        }

        if ($this->getSdkConfigBucketingUrl() !== null) {
            $customVariable["sdk.config.bucketingUrl"] = $this->getSdkConfigBucketingUrl();
        }

        if ($this->getSdkConfigFetchThirdPartyData() !== null) {
            $customVariable["sdk.config.fetchThirdPartyData"] = json_encode($this->getSdkConfigFetchThirdPartyData());
        }

        if ($this->isSdkConfigUsingOnVisitorExposed() !== null) {
            $customVariable["sdk.config.usingOnVisitorExposed"] = json_encode($this->isSdkConfigUsingOnVisitorExposed());
        }

        if ($this->isSdkConfigUsingCustomHitCache() !== null) {
            $customVariable["sdk.config.usingCustomHitCache"] = json_encode($this->isSdkConfigUsingCustomHitCache());
        }

        if ($this->isSdkConfigUsingCustomVisitorCache() !== null) {
            $customVariable["sdk.config.usingCustomVisitorCache"] = json_encode($this->isSdkConfigUsingCustomVisitorCache());
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
                $customVariable["visitor.context.[$index]"] = is_string($item) ? $item : json_encode($item);
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
                $customVariable["$customVariableKey.value"] = is_string($visitorFlag->getValue()) ? $visitorFlag->getValue() : json_encode($visitorFlag->getValue());
                $customVariable["$customVariableKeyMetadata.variationId"] = $visitorFlag->getVariationId();
                $customVariable["$customVariableKeyMetadata.variationName"] = $visitorFlag->getVariationName();
                $customVariable["$customVariableKeyMetadata.variationGroupId"] = $visitorFlag->getVariationGroupId();
                $customVariable["$customVariableKeyMetadata.variationGroupName"] = $visitorFlag->getVariationGroupName();
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
            $customVariable["flag.value"] = is_string($this->getFlagValue()) ? $this->getFlagValue() : json_encode($this->getFlagValue());
        }
        if ($this->getFlagDefault() !== null) {
            $customVariable["flag.default"] = is_string($this->getFlagDefault()) ? $this->getFlagDefault() : json_encode($this->getFlagDefault());
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
                $customVariable["hit." . $key] = is_string($item) ? $item : json_encode($item);
            }
        }

        return [
            FlagshipConstant::VISITOR_ID_API_ITEM      => $this->visitorId,
            FlagshipConstant::DS_API_ITEM              => $this->getDs(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->getConfig()?->getEnvId(),
            FlagshipConstant::T_API_ITEM               => $this->getType()->value,
            'cv'                                       => $customVariable,
        ];
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return  "";
    }
}

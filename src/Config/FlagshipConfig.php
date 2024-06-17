<?php

namespace Flagship\Config;

use JsonSerializable;
use Flagship\Enum\LogLevel;
use Psr\Log\LoggerInterface;
use Flagship\Enum\FSSdkStatus;
use Flagship\Enum\DecisionMode;
use Flagship\Model\ExposedFlag;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\FlagshipField;
use Flagship\Model\ExposedVisitor;
use Flagship\Enum\FlagshipConstant;
use Flagship\Traits\ValidatorTrait;
use Flagship\Cache\IHitCacheImplementation;
use Flagship\Cache\IVisitorCacheImplementation;

/**
 * Flagship SDK configuration class to provide at initialization.
 *
 * @package Flagship
 */
abstract class FlagshipConfig implements JsonSerializable
{
    use ValidatorTrait;

    /**
     * @var string
     */
    private string $envId;
    /**
     * @var string
     */
    private string $apiKey;
    /**
     * @var DecisionMode
     */
    private DecisionMode $decisionMode = DecisionMode::DECISION_API;
    /**
     * @var int
     */
    private int $timeout = FlagshipConstant::REQUEST_TIME_OUT;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logManager;
    /**
     * @var LogLevel
     */
    private LogLevel $logLevel = LogLevel::ALL;

    /**
     * @var (callable(FSSdkStatus $status): void) | null
     */
    private $onSdkStatusChanged;

    /**
     * @var ?IVisitorCacheImplementation
     */
    private ?IVisitorCacheImplementation $visitorCacheImplementation;

    /**
     * @var ?IHitCacheImplementation
     */
    private ?IHitCacheImplementation $hitCacheImplementation;

    /**
     * @var CacheStrategy
     */
    protected CacheStrategy $cacheStrategy;

    /**
     * @var (callable(ExposedVisitor $exposedUser, ExposedFlag $exposedFlag): void) | null
     */
    protected $onVisitorExposed;

    /**
     * @var boolean
     */
    protected bool $disableDeveloperUsageTracking;

    /**
     * Create a new FlagshipConfig configuration.
     *
     * @param string|null $envId  Environment id provided by Flagship.
     * @param string|null $apiKey  Secure api key provided by Flagship.
     */
    public function __construct(string $envId = null, string $apiKey = null)
    {
        $this->envId = $envId;
        $this->apiKey = $apiKey;
        $this->cacheStrategy = CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE;
        $this->setDisableDeveloperUsageTracking(false);
    }

    /**
     * @return string|null
     */
    public function getEnvId(): ?string
    {
        return $this->envId;
    }

    /**
     * Specify the environment id provided by Flagship, to use.
     *
     * @param string $envId environment id.
     * @return $this
     */
    public function setEnvId(string $envId): static
    {
        $this->envId = $envId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Specify the secure api key provided by Flagship, to use.
     *
     * @param string $apiKey secure api key.
     * @return $this
     */
    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return DecisionMode
     */
    public function getDecisionMode(): DecisionMode
    {
        return $this->decisionMode;
    }

    /**
     * Specify the SDK running mode.
     *
     * @param DecisionMode $decisionMode decision mode value e.g DecisionMode::DECISION_API
     * @return $this
     */
    protected function setDecisionMode(DecisionMode $decisionMode): static
    {
        $this->decisionMode = $decisionMode;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout * 1000;
    }

    /**
     * Specify timeout for api request.
     *
     * @param int $timeout Milliseconds for connect and read timeouts. Default is 2000ms.
     * @return $this
     */
    public function setTimeout(int $timeout): static
    {
        if ($timeout < 0) {
            $this->logError($this, FlagshipConstant::TIMEOUT_TYPE_ERROR);
            return $this;
        }
        $this->timeout = $timeout / 1000;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogManager(): LoggerInterface
    {
        return $this->logManager;
    }

    /**
     * Specify a custom implementation of LogManager in order to receive logs from the SDK.
     *
     * @param LoggerInterface $logManager custom implementation of LogManager.
     * @return $this
     */
    public function setLogManager(LoggerInterface $logManager): static
    {
        $this->logManager = $logManager;
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
     * Set the maximum log level to display
     * @param LogLevel $logLevel
     * @return $this
     */
    public function setLogLevel(LogLevel $logLevel): static
    {
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * Return the strategy uses for hit caching with tracking manager
     * @return CacheStrategy
     */
    public function getCacheStrategy(): CacheStrategy
    {
        return $this->cacheStrategy;
    }

    /**
     * Define the strategy that will be used for hit caching with tracking manager
     * @param CacheStrategy $cacheStrategy
     * @return FlagshipConfig
     */
    public function setCacheStrategy(CacheStrategy $cacheStrategy): static
    {
        $this->cacheStrategy = $cacheStrategy;
        return $this;
    }

    /**
     * @return (callable(FSSdkStatus $status): void)|null
     */
    public function getOnSdkStatusChanged(): ?callable
    {
        return $this->onSdkStatusChanged;
    }

    /**
     * Define a callable in order to get callback when the SDK status has changed.
     * @param callable(FSSdkStatus $status): void $onSdkStatusChanged
     * @return $this
     */
    public function setOnSdkStatusChanged(callable $onSdkStatusChanged): static
    {
        if (is_callable($onSdkStatusChanged)) {
            $this->onSdkStatusChanged = $onSdkStatusChanged;
        } else {
            $this->logError(
                $this,
                sprintf(FlagshipConstant::IS_NOT_CALLABLE_ERROR, json_encode($onSdkStatusChanged)),
                [
                    FlagshipConstant::TAG => __FUNCTION__
                ]
            );
        }
        return $this;
    }

    /**
     * @return ?IVisitorCacheImplementation
     */
    public function getVisitorCacheImplementation(): ?IVisitorCacheImplementation
    {
        return $this->visitorCacheImplementation;
    }

    /**
     * Define an object that implement the interface IVisitorCacheImplementation, to handle the visitor cache.
     * @param IVisitorCacheImplementation $visitorCacheImplementation
     * @return FlagshipConfig
     */
    public function setVisitorCacheImplementation(IVisitorCacheImplementation $visitorCacheImplementation): static
    {
        $this->visitorCacheImplementation = $visitorCacheImplementation;
        return $this;
    }

    /**
     * @return ?IHitCacheImplementation
     */
    public function getHitCacheImplementation(): ?IHitCacheImplementation
    {
        return $this->hitCacheImplementation;
    }

    /**
     * @param IHitCacheImplementation $hitCacheImplementation
     * @return FlagshipConfig
     */
    public function setHitCacheImplementation(IHitCacheImplementation $hitCacheImplementation): static
    {
        $this->hitCacheImplementation = $hitCacheImplementation;
        return $this;
    }

    /**
     * @return callable(ExposedVisitor $exposedUser, ExposedFlag $exposedFlag): void|null
     */
    public function getOnVisitorExposed(): ?callable
    {
        return $this->onVisitorExposed;
    }

    /**
     * @param callable(ExposedVisitor $exposedUser, ExposedFlag $exposedFlag): void $onVisitorExposed
     * @return FlagshipConfig
     */
    public function setOnVisitorExposed(callable $onVisitorExposed): static
    {
        if (is_callable($onVisitorExposed)) {
            $this->onVisitorExposed = $onVisitorExposed;
        } else {
            $this->logError(
                $this,
                sprintf(FlagshipConstant::IS_NOT_CALLABLE_ERROR, json_encode($onVisitorExposed)),
                [
                    FlagshipConstant::TAG => __FUNCTION__
                ]
            );
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function disableDeveloperUsageTracking(): bool
    {
        return $this->disableDeveloperUsageTracking;
    }

    /**
     * @param bool $disableDeveloperUsageTracking
     * @return FlagshipConfig
     */
    public function setDisableDeveloperUsageTracking(bool $disableDeveloperUsageTracking): static
    {
        $this->disableDeveloperUsageTracking = $disableDeveloperUsageTracking;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return [
            FlagshipField::FIELD_ENVIRONMENT_ID => $this->getEnvId(),
            FlagshipField::FIELD_API_KEY => $this->getApiKey(),
            FlagshipField::FIELD_TIMEOUT => $this->getTimeout(),
            FlagshipField::FIELD_LOG_LEVEL => $this->getLogLevel()
        ];
    }

    public static function bucketing(string $bucketingUrl): BucketingConfig
    {
        return new BucketingConfig($bucketingUrl);
    }

    public static function decisionApi(): DecisionApiConfig
    {
        return new DecisionApiConfig();
    }
}

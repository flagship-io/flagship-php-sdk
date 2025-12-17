<?php

namespace Flagship\Visitor;

use JsonSerializable;
use Flagship\Flagship;
use Flagship\Traits\Guid;
use Flagship\Model\FlagDTO;
use Flagship\Enum\FSSdkStatus;
use Flagship\Utils\MurmurHash;
use Flagship\Model\CampaignDTO;
use Flagship\Hit\Troubleshooting;
use Flagship\Utils\ConfigManager;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\VisitorCacheDTO;
use Flagship\Traits\ValidatorTrait;
use Flagship\Enum\VisitorCacheStatus;
use Flagship\Utils\ContainerInterface;
use Flagship\Model\VisitorCacheDataDTO;
use Flagship\Model\FetchFlagsStatusInterface;

abstract class VisitorAbstract implements VisitorInterface, JsonSerializable, VisitorFlagInterface
{
    use ValidatorTrait;
    use Guid;

    /**
     * @var FlagshipConfig
     */
    protected FlagshipConfig $config;

    /**
     * @var string
     */
    private string $visitorId;

    /**
     * @var ?string
     */
    private ?string $anonymousId = null;

    /**
     * @var array<string, scalar>
     */
    public array $context = [];

    /**
     * @var FlagDTO[]
     */
    protected array $flagsDTO = [];

    /**
     * @var CampaignDTO[]
     */
    public array $campaigns = [];

    /**
     * @var ConfigManager
     */
    protected ConfigManager $configManager;

    /**
     * @var boolean
     */
    public bool $hasConsented = false;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $dependencyIContainer;

    /**
     * @var ?VisitorCacheDTO
     */
    public ?VisitorCacheDTO $visitorCache;

    /**
     * @var int|float|null
     */
    protected int|float|null $traffic = null;

    /**
     * @var string
     */
    protected string $instanceId;

    protected static ?FSSdkStatus $sdkStatus = null;

    protected ?string $flagshipInstanceId;

    /**
     * @var ?Troubleshooting
     */
    protected ?Troubleshooting $ConsentHitTroubleshooting = null;

    /**
     * @var ?callable
     */
    protected $onFetchFlagsStatusChanged;

    /**
     * The fetch status of the flags.
     *
     * @var FetchFlagsStatusInterface
     */
    protected FetchFlagsStatusInterface $fetchStatus;

    /**
     * 
     * @var array<string, mixed>
     */
    protected array $deDuplicationCache = [];

    protected bool $hasContextBeenUpdated = true;

    public function setHasContextBeenUpdated(bool $hasContextBeenUpdated): static
    {
        $this->hasContextBeenUpdated = $hasContextBeenUpdated;
        return $this;
    }

    public function getHasContextBeenUpdated(): bool
    {
        return $this->hasContextBeenUpdated;
    }

    protected visitorCacheStatus $visitorCacheStatus;

    public function getVisitorCacheStatus(): visitorCacheStatus
    {
        return $this->visitorCacheStatus;
    }

    public function setVisitorCacheStatus(visitorCacheStatus $visitorCacheStatus): static
    {
        $this->visitorCacheStatus = $visitorCacheStatus;
        return $this;
    }


    /**
     * @return ?callable
     */
    public function getOnFetchFlagsStatusChanged(): callable|null
    {
        return $this->onFetchFlagsStatusChanged;
    }

    /**
     * @param callable|null $onFetchFlagsStatusChanged
     * @return VisitorAbstract
     */
    public function setOnFetchFlagsStatusChanged(?callable $onFetchFlagsStatusChanged): self
    {
        $this->onFetchFlagsStatusChanged = $onFetchFlagsStatusChanged;
        return $this;
    }

    public function setFetchStatus(FetchFlagsStatusInterface $fetchStatus): static
    {
        if ($this->onFetchFlagsStatusChanged !== null) {
            call_user_func($this->onFetchFlagsStatusChanged, $fetchStatus);
        }
        $this->fetchStatus = $fetchStatus;
        return $this;
    }

    /**
     * @return FetchFlagsStatusInterface
     */
    public function getFetchStatus(): FetchFlagsStatusInterface
    {
        return $this->fetchStatus;
    }

    /**
     * @return Troubleshooting|null
     */
    public function getConsentHitTroubleshooting(): ?Troubleshooting
    {
        return $this->ConsentHitTroubleshooting;
    }

    /**
     * @param Troubleshooting|null $ConsentHitTroubleshooting
     * @return VisitorAbstract
     */
    public function setConsentHitTroubleshooting(?Troubleshooting $ConsentHitTroubleshooting): self
    {
        $this->ConsentHitTroubleshooting = $ConsentHitTroubleshooting;
        return $this;
    }

    /**
     * @return ?FSSdkStatus
     */
    public function getSdkStatus(): ?FSSdkStatus
    {
        return self::$sdkStatus;
    }

    /**
     * @return ?string
     */
    public function getFlagshipInstanceId(): ?string
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param ?string $flagshipInstanceId
     * @return VisitorAbstract
     */
    public function setFlagshipInstanceId(?string $flagshipInstanceId): self
    {
        $this->flagshipInstanceId = $flagshipInstanceId;
        return $this;
    }


    /**
     * @param FSSdkStatus $sdkStatus
     */
    public static function setSdkStatus(FSSdkStatus $sdkStatus): void
    {
        self::$sdkStatus = $sdkStatus;
    }

    public function __construct()
    {
        $this->visitorCache = null;
        $this->instanceId = $this->newGuid();
    }

    /**
     * @return string
     */
    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    /**
     * @return float|int|null
     */
    public function getTraffic(): float|int|null
    {
        return $this->traffic;
    }

    /**
     * @param float|int $traffic
     * @return VisitorAbstract
     */
    public function setTraffic(float|int $traffic): self
    {
        $this->traffic = $traffic;
        return $this;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    /**
     * @param FlagDTO[] $flagsDTO
     * @return VisitorAbstract
     */
    public function setFlagsDTO(array $flagsDTO): self
    {
        $this->flagsDTO = $flagsDTO;
        return $this;
    }


    /**
     * @return FlagDTO[]
     */
    public function getFlagsDTO(): array
    {
        return $this->flagsDTO;
    }

    /**
     * @param ConfigManager $configManager
     * @return VisitorAbstract
     */
    public function setConfigManager(ConfigManager $configManager): self
    {
        $this->configManager = $configManager;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisitorId(): string
    {
        return $this->visitorId;
    }


    /**
     * @param string $visitorId
     * @return VisitorAbstract
     */
    public function setVisitorId(string $visitorId): self
    {
        if (empty($visitorId)) {
            $this->logError(
                $this->config,
                FlagshipConstant::VISITOR_ID_ERROR,
                [FlagshipConstant::TAG => __FUNCTION__]
            );
        } else {
            $this->visitorId = $visitorId;
        }

        return $this;
    }


    /**
     * @return ?string
     */
    public function getAnonymousId(): ?string
    {
        return $this->anonymousId;
    }


    /**
     * @param string|null $anonymousId
     * @return VisitorAbstract
     */
    public function setAnonymousId(?string $anonymousId): self
    {
        $this->anonymousId = $anonymousId;
        return $this;
    }


    /**
     * @return array<string, scalar>
     */
    public function getContext(): array
    {
        return $this->context;
    }


    /**
     * Clear the current context and set a new context value
     *
     * @param array<string, scalar> $context collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = [];
        $this->updateContextCollection($context);
    }

    /**
     * 
     * 
     * @param array<string, mixed> $context
     * @return void
     */
    public function initialContext(array $context): void
    {
        $this->context = [];
        $this->getStrategy()->initialContext($context);
    }


    /**
     * @return FlagshipConfig
     */
    public function getConfig(): FlagshipConfig
    {
        return $this->config;
    }


    /**
     * @param FlagshipConfig $config
     * @return VisitorAbstract
     */
    public function setConfig(FlagshipConfig $config): self
    {
        $this->config = $config;
        return $this;
    }



    /**
     * @return StrategyAbstract
     */
    protected function getStrategy(): StrategyAbstract
    {
        $strategyClass = match (true) {
            Flagship::getStatus() === FSSdkStatus::SDK_NOT_INITIALIZED => NotReadyStrategy::class,
            Flagship::getStatus() === FSSdkStatus::SDK_PANIC => PanicStrategy::class,
            !$this->hasConsented() => NoConsentStrategy::class,
            default => DefaultStrategy::class,
        };

        /** @var StrategyAbstract $strategy */
        $strategy = $this->getDependencyIContainer()->get($strategyClass, [$this], true);

        $strategy->setMurmurHash(new MurmurHash());
        $strategy->setFlagshipInstanceId($this->getFlagshipInstanceId());

        return $strategy;
    }


    /**
     * Return True or False if the visitor has consented for private data usage.
     *
     * @return boolean
     */
    public function hasConsented(): bool
    {
        return $this->hasConsented;
    }


    /**
     * Set if visitor has consented for private data usage.
     *
     * @param  $hasConsented bool if the visitor has consented false otherwise.
     * @return void
     */
    public function setConsent(bool $hasConsented): void
    {
        $this->hasConsented = $hasConsented;
        $this->getStrategy()->setConsent($hasConsented);
    }


    /**
     * @return ContainerInterface
     */
    public function getDependencyIContainer(): ContainerInterface
    {
        return $this->dependencyIContainer;
    }


    /**
     * @param  ContainerInterface $dependencyIContainer
     * @return void
     */
    public function setDependencyIContainer(ContainerInterface $dependencyIContainer): void
    {
        $this->dependencyIContainer = $dependencyIContainer;
    }

    public function sendTroubleshootingHit(Troubleshooting $hit): void
    {
        $this->getStrategy()->sendTroubleshootingHit($hit);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'visitorId'  => $this->getVisitorId(),
            'context'    => $this->getContext(),
            'hasConsent' => $this->hasConsented(),
        ];
    }
}

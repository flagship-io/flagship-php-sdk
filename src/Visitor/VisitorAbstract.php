<?php

namespace Flagship\Visitor;

use JsonSerializable;
use Flagship\Flagship;
use Flagship\Traits\Guid;
use Flagship\Model\FlagDTO;
use Flagship\Enum\FSSdkStatus;
use Flagship\Utils\MurmurHash;
use Flagship\Hit\Troubleshooting;
use Flagship\Utils\ConfigManager;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\ContainerInterface;
use Flagship\Model\FetchFlagsStatusInterface;
use Flagship\Enum\visitorCacheStatus;

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
     * @var array
     */
    public array $context = [];

    /**
     * @var FlagDTO[]
     */
    protected array $flagsDTO = [];

    /**
     * @var array
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
     * @var ?array
     */
    public ?array $visitorCache;

    /**
     * @var string
     */
    private string $flagSyncStatus;

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
     * @var callable
     */
    protected $onFetchFlagsStatusChanged;

    /**
     * The fetch status of the flags.
     *
     * @var FetchFlagsStatusInterface
     */
    protected FetchFlagsStatusInterface $fetchStatus;

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
    protected array $deDuplicationCache = [];

    /**
     * @return callable
     */
    public function getOnFetchFlagsStatusChanged(): callable
    {
        return $this->onFetchFlagsStatusChanged;
    }

    /**
     * @param callable $onFetchFlagsStatusChanged
     * @return VisitorAbstract
     */
    public function setOnFetchFlagsStatusChanged(callable $onFetchFlagsStatusChanged): static
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
    public function setConsentHitTroubleshooting(?Troubleshooting $ConsentHitTroubleshooting): static
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
    public function setFlagshipInstanceId(?string $flagshipInstanceId): static
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
        $this->visitorCache = [];
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
    public function setTraffic(float|int $traffic): static
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
    public function setFlagsDTO(array $flagsDTO): static
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
    public function setConfigManager(ConfigManager $configManager): static
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
    public function setVisitorId(string $visitorId): static
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
    public function setAnonymousId(?string $anonymousId): static
    {
        $this->anonymousId = $anonymousId;
        return $this;
    }


    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }


    /**
     * Clear the current context and set a new context value
     *
     * @param array $context collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = [];
        $this->updateContextCollection($context);
    }

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
    public function setConfig(FlagshipConfig $config): static
    {
        $this->config = $config;
        return $this;
    }



    /**
     * @return StrategyAbstract
     */
    protected function getStrategy(): StrategyAbstract
    {
        if (Flagship::getStatus() === FSSdkStatus::SDK_NOT_INITIALIZED) {
            $strategy = $this->getDependencyIContainer()->get(NotReadyStrategy::class, [$this], true);
        } elseif (Flagship::getStatus() === FSSdkStatus::SDK_PANIC) {
            $strategy = $this->getDependencyIContainer()->get(PanicStrategy::class, [$this], true);
        } elseif (!$this->hasConsented()) {
            $strategy = $this->getDependencyIContainer()->get(NoConsentStrategy::class, [$this], true);
        } else {
            $strategy = $this->getDependencyIContainer()->get(DefaultStrategy::class, [$this], true);
        }
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

    public function getDeDuplicationCache(): array
    {
        return $this->deDuplicationCache;
    }

    public function setDeDuplicationCache(array $deDuplicationCache): void
    {
        $this->deDuplicationCache = $deDuplicationCache;
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

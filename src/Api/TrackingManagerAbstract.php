<?php

namespace Flagship\Api;

use Exception;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitCacheFields;
use Flagship\Enum\HitType;
use Flagship\Hit\Activate;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\Item;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Hit\Segment;
use Flagship\Hit\Transaction;
use Flagship\Model\TroubleshootingData;
use Flagship\Traits\LogTrait;
use Flagship\Utils\HttpClientInterface;

/**
 * Class TrackingManagerAbstract
 * @package Flagship\Api
 * @phpstan-import-type HitCacheDataArray from \Flagship\Model\Types
 */
abstract class TrackingManagerAbstract implements TrackingManagerInterface
{
    use LogTrait;

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * @var FlagshipConfig
     */
    protected FlagshipConfig $config;

    /**
     * @var BatchingCachingStrategyAbstract
     */
    protected BatchingCachingStrategyAbstract $strategy;

    protected ?string $flagshipInstanceId = null;

    /**
     * ApiManager constructor.
     *
     * @param FlagshipConfig $config
     * @param HttpClientInterface $httpClient
     * @param string|null $flagshipInstanceId
     */
    public function __construct(
        FlagshipConfig $config,
        HttpClientInterface $httpClient,
        ?string $flagshipInstanceId = null
    ) {
        $this->flagshipInstanceId = $flagshipInstanceId;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->strategy = $this->initStrategy();
        $this->lookupHits();
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig(): FlagshipConfig
    {
        return $this->config;
    }

    /**
     * @return BatchingCachingStrategyAbstract
     */
    public function getStrategy(): BatchingCachingStrategyAbstract
    {
        return $this->strategy;
    }

    /**
     * @return TroubleshootingData|null
     */
    public function getTroubleshootingData(): ?TroubleshootingData
    {
        return $this->getStrategy()->getTroubleshootingData();
    }

    /**
     * @param ?TroubleshootingData $troubleshootingData
     * @return void
     */
    public function setTroubleshootingData(?TroubleshootingData $troubleshootingData): void
    {
        $this->getStrategy()->setTroubleshootingData($troubleshootingData);
    }

    /**
     * @return BatchingCachingStrategyAbstract
     */
    public function initStrategy(): BatchingCachingStrategyAbstract
    {
        return match ($this->config->getCacheStrategy()) {
            CacheStrategy::NO_BATCHING_AND_CACHING_ON_FAILURE => new NoBatchingContinuousCachingStrategy(
                $this->config,
                $this->httpClient,
                $this->flagshipInstanceId
            ),
            default => new BatchingOnFailedCachingStrategy(
                $this->config,
                $this->httpClient,
                $this->flagshipInstanceId
            ),
        };
    }

    /**
     * @param array<mixed> $item
     * @return bool
     */
    protected function checkLookupHitData(array $item): bool
    {
        $data = $item[HitCacheFields::DATA] ?? null;
        if (is_null($data) || !is_array($data)) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_FORMAT_ERROR,
                [$item]
            );
            return false;
        }
        $content = $data[HitCacheFields::CONTENT] ?? null;
        $type = $data[HitCacheFields::TYPE] ?? null;
        $version = $item[HitCacheFields::VERSION] ?? null;
        if (is_null($content) || is_null($type) || is_null($version) || !is_array($content)) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_FORMAT_ERROR,
                [$item]
            );
            return false;
        }
        return true;
    }

    protected function checkHitTime(float $time): bool
    {
        $now = round(microtime(true) * 1000);
        return ($now - $time) <= FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
    }
    public function lookupHits(): void
    {
        try {
            $hitCacheImplementation = $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation) {
                return;
            }
            $hitsCache = $hitCacheImplementation->lookupHits();

            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_LOADED,
                [$hitsCache]
            );

            if (empty($hitsCache)) {
                return;
            }

            $hitKeysToRemove = [];


            foreach ($hitsCache as $key => $item) {
                $hitKeysToRemove[] = $key;

                if (!$this->checkLookupHitData($item)) {
                    continue;
                }

                $hitCacheData = $item[HitCacheFields::DATA];
                $hitTime = $hitCacheData[HitCacheFields::TIME] ?? 0;

                if (!$this->checkHitTime($hitTime)) {
                    continue;
                }

                $type = $item[HitCacheFields::DATA][HitCacheFields::TYPE];
                $content = $item[HitCacheFields::DATA][HitCacheFields::CONTENT];

                if (HitType::tryFrom($type) === HitType::ACTIVATE) {
                    $hit = HitAbstract::hydrate(Activate::class, $content);
                    $hit->setConfig($this->config);
                    $this->getStrategy()->hydrateActivatePoolQueue($hit->getKey(), $hit);
                    continue;
                }

                $hit = match (HitType::tryFrom($type)) {
                    HitType::EVENT => HitAbstract::hydrate(Event::class, $content),
                    HitType::ITEM => HitAbstract::hydrate(Item::class, $content),
                    HitType::PAGE_VIEW => HitAbstract::hydrate(Page::class, $content),
                    HitType::SCREEN_VIEW => HitAbstract::hydrate(Screen::class, $content),
                    HitType::SEGMENT => HitAbstract::hydrate(Segment::class, $content),
                    HitType::TRANSACTION => HitAbstract::hydrate(Transaction::class, $content),
                    default => null,
                };

                if (is_null($hit)) {
                    continue;
                }


                $hit->setConfig($this->config);
                $this->getStrategy()->hydrateHitsPoolQueue($hit->getKey(), $hit);
            }

            $this->getStrategy()->flushHits($hitKeysToRemove);
        } catch (Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                [
                    "lookupHits",
                    $exception->getMessage(),
                ]
            );
        }
    }
}

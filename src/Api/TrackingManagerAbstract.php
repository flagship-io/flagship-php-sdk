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

    protected function checkLookupHitData(array $item): bool
    {
        if (
            isset($item[HitCacheFields::DATA][HitCacheFields::CONTENT]) &&
            isset($item[HitCacheFields::DATA][HitCacheFields::TYPE]) &&
            isset($item[HitCacheFields::VERSION]) && $item[HitCacheFields::VERSION] == 1
        ) {
            return true;
        }
        $this->logErrorSprintf(
            $this->config,
            FlagshipConstant::PROCESS_CACHE,
            FlagshipConstant::HIT_CACHE_FORMAT_ERROR,
            [$item]
        );
        return  false;
    }

    protected function checkHitTime($time): bool
    {
        $now = round(microtime(true) * 1000);
        return ($now - $time) >= FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
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

            if (!is_array($hitsCache) || !count($hitsCache)) {
                return;
            }

            $hitKeysToRemove = [];

            foreach ($hitsCache as $key => $item) {
                $hitKeysToRemove [] = $key;
                if (
                    !$this->checkLookupHitData($item) ||
                    $this->checkHitTime($item[HitCacheFields::DATA][HitCacheFields::TIME])
                ) {
                    continue;
                }

                $type = $item[HitCacheFields::DATA][HitCacheFields::TYPE];
                $content = $item[HitCacheFields::DATA][HitCacheFields::CONTENT];

                switch ($type) {
                    case HitType::EVENT->value:
                        $hit = HitAbstract::hydrate(Event::getClassName(), $content);
                        break;
                    case HitType::ITEM->value:
                        $hit = HitAbstract::hydrate(Item::getClassName(), $content);
                        break;
                    case HitType::PAGE_VIEW->value:
                        $hit = HitAbstract::hydrate(Page::getClassName(), $content);
                        break;
                    case HitType::SCREEN_VIEW->value:
                        $hit = HitAbstract::hydrate(Screen::getClassName(), $content);
                        break;
                    case HitType::SEGMENT->value:
                        $hit = HitAbstract::hydrate(Segment::getClassName(), $content);
                        break;
                    case HitType::ACTIVATE->value:
                        $hit = HitAbstract::hydrate(Activate::getClassName(), $content);
                        $hit->setConfig($this->config);
                        $this->getStrategy()->hydrateActivatePoolQueue($hit->getKey(), $hit);
                        continue 2;
                    case HitType::TRANSACTION->value:
                        $hit = HitAbstract::hydrate(Transaction::getClassName(), $content);
                        break;
                    default:
                        continue 2;
                }

                $hit->setConfig($this->config);
                $this->getStrategy()->hydrateHitsPoolQueue($hit->getKey(), $hit);
            }

            $this->getStrategy()->flushHits($hitKeysToRemove);
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["lookupHits", $exception->getMessage()]
            );
        }
    }
}

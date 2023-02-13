<?php

namespace Flagship\Api;

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
    protected $httpClient;

    /**
     * @var FlagshipConfig
     */
    protected $config;

    /**
     * @var BatchingCachingStrategyAbstract
     */
    protected $strategy;

    /**
     * ApiManager constructor.
     *
     * @param FlagshipConfig $config
     * @param HttpClientInterface $httpClient
     */
    public function __construct(FlagshipConfig $config, HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->strategy = $this->initStrategy();
        $this->lookupHits();
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return BatchingCachingStrategyAbstract
     */
    public function getStrategy()
    {
        return $this->strategy;
    }



    /**
     * @return BatchingCachingStrategyAbstract
     */
    public function initStrategy()
    {
        switch ($this->config->getCacheStrategy()) {
            case CacheStrategy::CONTINUOUS_CACHING:
                $strategy = new BatchingContinuousCachingStrategy(
                    $this->config,
                    $this->httpClient
                );
                break;
            case CacheStrategy::PERIODIC_CACHING:
                $strategy = new BatchingPeriodicCachingStrategy(
                    $this->config,
                    $this->httpClient
                );
                break;
            default:
                $strategy = new NoBatchingContinuousCachingStrategy(
                    $this->config,
                    $this->httpClient
                );
                break;
        }
        return $strategy;
    }

    protected function checkLookupHitData(array $item)
    {
        if (
            isset($item[HitCacheFields::VERSION]) && $item[HitCacheFields::VERSION] == 1 &&
            isset($item[HitCacheFields::DATA]) && isset($item[HitCacheFields::DATA][HitCacheFields::TYPE]) &&
            isset($item[HitCacheFields::DATA][HitCacheFields::CONTENT])
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

    protected function checkHitTime($time)
    {
        $now = round(microtime(true) * 1000);
        return ($now - $time) >= FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
    }
    public function lookupHits()
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
                    case HitType::EVENT:
                        $hit = HitAbstract::hydrate(Event::getClassName(), $content);
                        break;
                    case HitType::ITEM:
                        $hit = HitAbstract::hydrate(Item::getClassName(), $content);
                        break;
                    case HitType::PAGE_VIEW:
                        $hit = HitAbstract::hydrate(Page::getClassName(), $content);
                        break;
                    case HitType::SCREEN_VIEW:
                        $hit = HitAbstract::hydrate(Screen::getClassName(), $content);
                        break;
                    case HitType::SEGMENT:
                        $hit = HitAbstract::hydrate(Segment::getClassName(), $content);
                        break;
                    case HitType::ACTIVATE:
                        $hit = HitAbstract::hydrate(Activate::getClassName(), $content);
                        $hit->setConfig($this->config);
                        $this->getStrategy()->hydrateActivatePoolQueue($hit->getKey(), $hit);
                        continue 2;
                    case HitType::TRANSACTION:
                        $hit = HitAbstract::hydrate(Transaction::getClassName(), $content);
                        break;
                    default:
                        continue 2;
                }
                $hit->setConfig($this->config);
                $this->getStrategy()->hydrateHitsPoolQueue($hit->getKey(), $hit);
            }

            $this->getStrategy()->flushHits($hitKeysToRemove);
        } catch (\Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["lookupHits", $exception->getMessage()]
            );
        }
    }
}

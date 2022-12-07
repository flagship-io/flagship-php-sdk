<?php

namespace Flagship\Api;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor\VisitorAbstract;

/**
 * Class TrackingManagerAbstract
 * @package Flagship\Api
 */
abstract class TrackingManagerAbstract implements TrackingManagerInterface
{
    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @var HitAbstract[]
     */
    protected $hitsPoolQueue;

    /**
     * @var Activate[]
     */
    protected $activatePoolQueue;
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
        $this->activatePoolQueue = [];
        $this->hitsPoolQueue = [];
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
     * @return BatchingCachingStrategyAbstract
     */
    public function initStrategy(){
        switch ($this->config->getCacheStrategy()){
            case CacheStrategy::CONTINUOUS_CACHING:
                $strategy = new BatchingContinuousCachingStrategy($this->config,$this->httpClient,
                    $this->hitsPoolQueue, $this->activatePoolQueue);
                break;
            case CacheStrategy::PERIODIC_CACHING:
                $strategy = new BatchingPeriodicCachingStrategy($this->config,$this->httpClient,
                    $this->hitsPoolQueue, $this->activatePoolQueue);
                break;
            default:
                $strategy = new NoBatchingContinuousCachingStrategy($this->config,$this->httpClient,
                    $this->hitsPoolQueue, $this->activatePoolQueue);
                break;
        }
        return $strategy;
    }


    public function lookupHits(){

    }
}

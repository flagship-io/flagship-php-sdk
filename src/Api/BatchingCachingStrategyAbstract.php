<?php

namespace Flagship\Api;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitCacheFields;
use Flagship\Hit\Activate;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\HitBatch;
use Flagship\Traits\Guid;
use Flagship\Traits\LogTrait;
use Flagship\Utils\HttpClientInterface;

abstract class BatchingCachingStrategyAbstract implements TrackingManagerCommonInterface
{
    use Guid, LogTrait;

    /**
     * @var HitAbstract[]
     */
    protected $hitsPoolQueue;

    /**
     * @var Activate[]
     */
    protected $activatePoolQueue;

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @var FlagshipConfig
     */
    protected $config;

    /**
     * @param FlagshipConfig $config
     * @param HttpClientInterface $httpClient
     * @param HitAbstract[] $hitsPoolQueue
     * @param Activate[] $activatePoolQueue
     */
    public function __construct(FlagshipConfig $config,HttpClientInterface $httpClient, array &$hitsPoolQueue, array &$activatePoolQueue)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->hitsPoolQueue = $hitsPoolQueue;
        $this->activatePoolQueue = $activatePoolQueue;
    }

    public function getNow(){
        return round(microtime(true) * 1000);
    }

    public function addHit(HitAbstract $hit){
        $hitKey = $hit->getVisitorId() . ":" . $this->newGuid();
        $hit->setKey($hitKey);

        $this->addHitInPoolQueue($hit);

        if (($hit instanceof Event) && $hit->getAction() === FlagshipConstant::FS_CONSENT &&
            $hit->getLabel() === FlagshipConstant::SDK_LANGUAGE.":false"){
            $this->notConsent($hit->getVisitorId());
        }

        $this->logDebugSprintf($this->config, FlagshipConstant::TRACKING_MANAGER, FlagshipConstant::HIT_ADDED_IN_QUEUE, [$hit->toArray()]);
    }

    public function activateFlag(Activate $hit){
        $hitKey = $hit->getVisitorId() . ":" . $this->newGuid();
        $hit->setKey($hitKey);

        $this->addActivateHitInPoolQueue($hit);

        $this->logDebugSprintf($this->config, FlagshipConstant::TRACKING_MANAGER, FlagshipConstant::ACTIVATE_HIT_ADDED_IN_QUEUE, [$hit->toArray()]);
    }

    abstract protected function notConsent ($visitorId);
    abstract protected function addHitInPoolQueue(HitAbstract $hit);
    abstract protected function addActivateHitInPoolQueue(Activate $hit);
    abstract protected function sendActivateHit();

    public function sendBatch(){
        if (count($this->activatePoolQueue)){
            $this->sendActivateHit();
        }

        $hits = [];
        $hitKeysToRemove = [];

        foreach ($this->hitsPoolQueue as $item) {
            $now = $this->getNow();
            $hitKeysToRemove[] = $item->getKey();
            if (($now - $item->getCreatedAt())>= FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS){
                continue;
            }
            $hits[] = $item;
        }

        $batchHit = new HitBatch($this->config, $hits);

        if (!count($hits)){
            return;
        }

        $header = [
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON
        ];

        $requestBody =  $batchHit->toArray();
        $now = $this->getNow();
        $url = FlagshipConstant::HIT_EVENT_URL;

        try {
            $this->httpClient->setTimeout($this->config->getTimeout());
            $this->httpClient->setHeaders($header);
           $this->httpClient->post($url,[], $requestBody);

           $this->logDebugSprintf($this->config, FlagshipConstant::TRACKING_MANAGER,
               FlagshipConstant::HIT_SENT_SUCCESS, [
                   FlagshipConstant::SEND_BATCH,
                   $this->getLogFormat(null, $url, $requestBody, $header,$this->getNow()  - $now) ]);

           $this->flushHits($hitKeysToRemove);
        }catch (\Exception $exception){
            foreach ($hits as $hit) {
                $this->hitsPoolQueue[$hit->getKey()] = $hit;
            }
            $this->logErrorSprintf($this->config, FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::TRACKING_MANAGER_ERROR, [FlagshipConstant::SEND_BATCH,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, $header, $this->getNow()  - $now)]);
        }
    }

    /**
     * @param HitAbstract[] $hits
     * @return void
     */
    public function cacheHit(array $hits){

        try {
            $hitCacheImplementation =  $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation){
                return;
            }

            $data = [];

            foreach ($hits as $hit) {
                $hitData = [
                    HitCacheFields::VISITOR_ID => 1,
                    HitCacheFields::DATA=>[
                        HitCacheFields::VISITOR_ID=> $hit->getVisitorId(),
                        HitCacheFields::ANONYMOUS_ID=> $hit->getAnonymousId(),
                        HitCacheFields::TYPE => $hit->getType(),
                        HitCacheFields::CONTENT => $hit->toArray(),
                        HitCacheFields::TIME => $this->getNow()
                    ]
                ];

                $data[$hit->getKey()] = $hitData;
            }

            $hitCacheImplementation->cacheHit($data);

            $this->logDebugSprintf($this->config, FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_SAVED,[$data]);
        }
        catch (\Exception $exception){
            $this->logErrorSprintf($this->config, FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR, ["cacheHit", $exception->getMessage()]);
        }
    }

    public  function flushHits(array $hitKeys){
        try {
            $hitCacheImplementation =  $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation){
                return;
            }
            $hitCacheImplementation->flushHits($hitKeys);

            $this->logDebugSprintf($this->config, FlagshipConstant::PROCESS_CACHE, FlagshipConstant::HIT_DATA_FLUSHED, [$hitKeys]);
        }catch (\Exception $exception){
            $this->logErrorSprintf($this->config, FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR, ["flushHits", $exception->getMessage()]);
        }
    }

    public  function flushAllHits(){
        try {
            $hitCacheImplementation =  $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation){
                return;
            }
            $hitCacheImplementation->flushAllHits();

            $this->logDebugSprintf($this->config, FlagshipConstant::PROCESS_CACHE, FlagshipConstant::ALL_HITS_FLUSHED);
        }catch (\Exception $exception){
            $this->logErrorSprintf($this->config, FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR, ["flushAllHits", $exception->getMessage()]);
        }
    }

}
<?php

namespace Flagship\Api;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitCacheFields;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
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
     */
    public function __construct(
        FlagshipConfig $config,
        HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->hitsPoolQueue = [];
        $this->activatePoolQueue = [];
    }

    /**
     * @return HitAbstract[]
     */
    public function getHitsPoolQueue()
    {
        return $this->hitsPoolQueue;
    }

    /**
     * @return Activate[]
     */
    public function getActivatePoolQueue()
    {
        return $this->activatePoolQueue;
    }



    /**
     * @param $key
     * @param HitAbstract $hit
     * @return void
     */
    public function hydrateHitsPoolQueue($key, HitAbstract $hit)
    {
        $this->hitsPoolQueue[$key] = $hit;
    }

    /**
     * @param $key
     * @param Activate $hit
     * @return void
     */
    public function hydrateActivatePoolQueue($key, Activate $hit)
    {
        $this->activatePoolQueue[$key] = $hit;
    }

    /**
     * @return float
     */
    public function getNow()
    {
        return round(microtime(true) * 1000);
    }

    /**
     * @return array
     */
    public function getActivateHeaders()
    {
        return [
            FlagshipConstant::HEADER_X_API_KEY => $this->config->getApiKey(),
            FlagshipConstant::HEADER_X_SDK_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON,
            FlagshipConstant::HEADER_X_SDK_CLIENT => FlagshipConstant::SDK_LANGUAGE,
        ];
    }

    /**
     * @param $visitorId
     * @return string
     */
    public function generateHitKey($visitorId)
    {
        return $visitorId . ":" . $this->newGuid();
    }

    /**
     * @param HitAbstract $hit
     * @return void
     */
    public function addHit(HitAbstract $hit)
    {
        $hitKey = $this->generateHitKey($hit->getVisitorId());
        $hit->setKey($hitKey);

        $this->addHitInPoolQueue($hit);

        if (($hit instanceof Event) && $hit->getAction() === FlagshipConstant::FS_CONSENT &&
            $hit->getLabel() === FlagshipConstant::SDK_LANGUAGE . ":false") {
            $this->notConsent($hit->getVisitorId());
        }

        $this->logDebugSprintf(
            $this->config,
            FlagshipConstant::TRACKING_MANAGER,
            FlagshipConstant::HIT_ADDED_IN_QUEUE,
            [$hit->toApiKeys()]
        );
    }

    /**
     * @param Activate $hit
     * @return void
     */
    public function activateFlag(Activate $hit)
    {
        $hitKey = $this->generateHitKey($hit->getVisitorId());

        $hit->setKey($hitKey);

        $this->addActivateHitInPoolQueue($hit);

        $this->logDebugSprintf($this->config, FlagshipConstant::TRACKING_MANAGER, FlagshipConstant::ACTIVATE_HIT_ADDED_IN_QUEUE, [$hit->toApiKeys()]);
    }

    /**
     * @param $visitorId
     * @return void
     */
    abstract protected function notConsent($visitorId);

    /**
     * @return void
     */
    protected function postPrecessSendBatch()
    {
        // Nothing to do
    }

    /**
     * @param HitAbstract $hit
     * @return void
     */
    protected function addHitInPoolQueue(HitAbstract $hit)
    {
        $this->hitsPoolQueue[$hit->getKey()] = $hit;
        $this->cacheHit([$hit]);
    }

    /**
     * @param Activate $hit
     * @return void
     */
    protected function addActivateHitInPoolQueue(Activate $hit)
    {
        $this->activatePoolQueue[$hit->getKey()] = $hit;
        $this->cacheHit([$hit]);
    }

    /**
     * @param string[] $hitKeysToRemove
     * @return void
     */
    protected function flushSentActivateHit(array $hitKeysToRemove)
    {
        $this->flushHits($hitKeysToRemove);
    }

    /**
     * @param string[] $hitKeysToRemove
     * @return void
     */
    protected function flushBatchedHits(array $hitKeysToRemove)
    {
        $this->flushHits($hitKeysToRemove);
    }

    /**
     * @return void
     */
    protected function sendActivateHit()
    {
        $headers = $this->getActivateHeaders();

        $activateBatch = new ActivateBatch($this->config, $this->activatePoolQueue);

        $requestBody = $activateBatch->toApiKeys();
        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;
        $now = $this->getNow();

        try {
            $this->httpClient->setTimeout($this->config->getTimeout());
            $this->httpClient->setHeaders($headers);

            $this->httpClient->post($url, [], $requestBody);

            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::HIT_SENT_SUCCESS,
                [
                    FlagshipConstant::SEND_ACTIVATE,
                $this->getLogFormat(null, $url, $requestBody, $headers, $this->getNow() - $now)]
            );

            $hitKeysToRemove = [];
            foreach ($this->activatePoolQueue as $item) {
                $hitKeysToRemove[] = $item->getKey();
            }

            $this->activatePoolQueue = [];

            $this->flushSentActivateHit($hitKeysToRemove);
        } catch (\Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::TRACKING_MANAGER_ERROR,
                [FlagshipConstant::SEND_ACTIVATE,
                $this->getLogFormat($exception->getMessage(), $url, $requestBody, $headers, $this->getNow() - $now)]
            );
        }
    }

    /**
     * @param $visitorId
     * @return string []
     */
    protected function commonNotConsent($visitorId)
    {
        $hitKeys = [];
        $keysToFlush = [];
        foreach ($this->hitsPoolQueue as $item) {
            if (($item instanceof Event && $item->getAction() === FlagshipConstant::FS_CONSENT) ||
                ($visitorId !== $item->getVisitorId() && $visitorId !== $item->getAnonymousId())) {
                continue;
            }
            $hitKeys[] = $item->getKey();
            $keysToFlush[] = $item->getKey();
        }

        $activateKeys = [];
        foreach ($this->activatePoolQueue as $item) {
            if ($visitorId !== $item->getVisitorId() && $visitorId !== $item->getAnonymousId()) {
                continue;
            }
            $activateKeys[] = $item->getKey();
            $keysToFlush[] = $item->getKey();
        }

        foreach ($hitKeys as $hitKey) {
            unset($this->hitsPoolQueue[$hitKey]);
        }

        foreach ($activateKeys as $activateKey) {
            unset($this->activatePoolQueue[$activateKey]);
        }

        return $keysToFlush;
    }

    /**
     * @return void
     */
    public function sendBatch()
    {
        if (count($this->activatePoolQueue)) {
            $this->sendActivateHit();
        }

        $hits = [];
        $hitKeysToRemove = [];

        foreach ($this->hitsPoolQueue as $item) {
            $now = $this->getNow();
            $hitKeysToRemove[] = $item->getKey();
            if (($now - $item->getCreatedAt()) >= FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS) {
                continue;
            }
            $hits[] = $item;
        }

        $batchHit = new HitBatch($this->config, $hits);

        if (!count($hits)) {
            return;
        }

        $header = [
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON
        ];

        $requestBody = $batchHit->toApiKeys();
        $now = $this->getNow();
        $url = FlagshipConstant::HIT_EVENT_URL;

        try {
            $this->httpClient->setTimeout($this->config->getTimeout());
            $this->httpClient->setHeaders($header);
            $this->httpClient->post($url, [], $requestBody);

            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::HIT_SENT_SUCCESS,
                [
                    FlagshipConstant::SEND_BATCH,
                $this->getLogFormat(null, $url, $requestBody, $header, $this->getNow() - $now)]
            );

            $this->hitsPoolQueue = [];
            $this->flushBatchedHits($hitKeysToRemove);
        } catch (\Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::TRACKING_MANAGER_ERROR,
                [FlagshipConstant::SEND_BATCH,
                $this->getLogFormat($exception->getMessage(), $url, $requestBody, $header, $this->getNow() - $now)]
            );
        }

        $this->postPrecessSendBatch();
    }

    /**
     * @param HitAbstract[] $hits
     * @return void
     */
    public function cacheHit(array $hits)
    {

        try {
            $hitCacheImplementation = $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation) {
                return;
            }

            $data = [];

            foreach ($hits as $hit) {
                $hitData = [
                    HitCacheFields::VERSION => 1,
                    HitCacheFields::DATA => [
                        HitCacheFields::VISITOR_ID => $hit->getVisitorId(),
                        HitCacheFields::ANONYMOUS_ID => $hit->getAnonymousId(),
                        HitCacheFields::TYPE => $hit->getType(),
                        HitCacheFields::CONTENT => $hit->toArray(),
                        HitCacheFields::TIME => $this->getNow()
                    ]
                ];

                $data[$hit->getKey()] = $hitData;
            }

            $hitCacheImplementation->cacheHit($data);

            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_SAVED,
                [$data]
            );
        } catch (\Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["cacheHit", $exception->getMessage()]
            );
        }
    }

    /**
     * @param array $hitKeys
     * @return void
     */
    public function flushHits(array $hitKeys)
    {
        try {
            $hitCacheImplementation = $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation) {
                return;
            }
            $hitCacheImplementation->flushHits($hitKeys);

            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_DATA_FLUSHED,
                [$hitKeys]
            );
        } catch (\Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["flushHits", $exception->getMessage()]
            );
        }
    }

    /**
     * @return void
     */
    public function flushAllHits()
    {
        try {
            $hitCacheImplementation = $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation) {
                return;
            }
            $hitCacheImplementation->flushAllHits();

            $this->logDebugSprintf($this->config, FlagshipConstant::PROCESS_CACHE, FlagshipConstant::ALL_HITS_FLUSHED);
        } catch (\Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                ["flushAllHits", $exception->getMessage()]
            );
        }
    }
}

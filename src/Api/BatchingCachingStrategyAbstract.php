<?php

namespace Flagship\Api;

use DateTime;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitCacheFields;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\HitBatch;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\ExposedFlag;
use Flagship\Model\ExposedVisitor;
use Flagship\Model\TroubleshootingData;
use Flagship\Traits\Guid;
use Flagship\Traits\Helper;
use Flagship\Traits\LogTrait;
use Flagship\Utils\HttpClientInterface;

abstract class BatchingCachingStrategyAbstract implements TrackingManagerCommonInterface
{
    use Guid;
    use LogTrait;
    use Helper;

    /**
     * @var HitAbstract[]
     */
    protected $hitsPoolQueue;

    /**
     * @var Activate[]
     */
    protected $activatePoolQueue;

    /**
     * @var Troubleshooting[]
     */
    protected $troubleshootingQueue;

    /**
     * @var TroubleshootingData
     */
    protected $troubleshootingData;

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
        $this->troubleshootingQueue = [];
    }

    /**
     * @return TroubleshootingData
     */
    public function getTroubleshootingData()
    {
        return $this->troubleshootingData;
    }

    /**
     * @param TroubleshootingData $troubleshootingData
     * @return BatchingCachingStrategyAbstract
     */
    public function setTroubleshootingData($troubleshootingData)
    {
        $this->troubleshootingData = $troubleshootingData;
        return $this;
    }

    /**
     * @return Troubleshooting[]
     */
    public function getTroubleshootingQueue()
    {
        return $this->troubleshootingQueue;
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

        if (
            ($hit instanceof Event) && $hit->getAction() === FlagshipConstant::FS_CONSENT &&
            $hit->getLabel() === FlagshipConstant::SDK_LANGUAGE . ":false"
        ) {
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

        $this->logDebugSprintf(
            $this->config,
            FlagshipConstant::TRACKING_MANAGER,
            FlagshipConstant::ACTIVATE_HIT_ADDED_IN_QUEUE,
            [$hit->toApiKeys()]
        );
    }

    /**
     * @param $visitorId
     * @return void
     */
    abstract protected function notConsent($visitorId);

    /**
     * @param HitAbstract $hit
     * @return void
     */
    protected function addHitInPoolQueue(HitAbstract $hit)
    {
        $this->hitsPoolQueue[$hit->getKey()] = $hit;
    }

    /**
     * @param Activate $hit
     * @return void
     */
    protected function addActivateHitInPoolQueue(Activate $hit)
    {
        $this->activatePoolQueue[$hit->getKey()] = $hit;
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
     * @param Activate $activate
     * @return void
     */
    protected function onVisitorExposed(Activate $activate)
    {
        $onUserExposed = $this->config->getOnVisitorExposed();
        if (!$onUserExposed) {
            return;
        }

        $exposedFlag = new ExposedFlag(
            $activate->getFlagKey(),
            $activate->getFlagValue(),
            $activate->getFlagDefaultValue(),
            $activate->getFlagMetadata()
        );
        $exposedUser = new ExposedVisitor(
            $activate->getVisitorId(),
            $activate->getAnonymousId(),
            $activate->getVisitorContext()
        );

        try {
            call_user_func($onUserExposed, $exposedUser, $exposedFlag);
        } catch (\Exception $exception) {
            $this->logErrorSprintf($this->config, __FUNCTION__, $exception->getMessage());
        }
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
                if ($item->getIsFromCache()) {
                    $hitKeysToRemove[] = $item->getKey();
                }
                $this->onVisitorExposed($item);
            }

            $this->activatePoolQueue = [];
            if (count($hitKeysToRemove) > 0) {
                $this->flushSentActivateHit($hitKeysToRemove);
            }
        } catch (\Exception $exception) {
            $this->cacheHit($this->activatePoolQueue);
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
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
            if (
                ($item instanceof Event && $item->getAction() === FlagshipConstant::FS_CONSENT) ||
                ($visitorId !== $item->getVisitorId() && $visitorId !== $item->getAnonymousId())
            ) {
                continue;
            }
            $hitKeys[] = $item->getKey();
            if ($item->getIsFromCache()) {
                $keysToFlush[] = $item->getKey();
            }
        }

        $activateKeys = [];
        foreach ($this->activatePoolQueue as $item) {
            if ($visitorId !== $item->getVisitorId() && $visitorId !== $item->getAnonymousId()) {
                continue;
            }
            $activateKeys[] = $item->getKey();

            if ($item->getIsFromCache()) {
                $keysToFlush[] = $item->getKey();
            }
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
            if ($item->getIsFromCache()) {
                $hitKeysToRemove[] = $item->getKey();
            }
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
            $this->httpClient->setHeaders($header);
            $this->httpClient->setTimeout($this->config->getTimeout());
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
            if (count($hitKeysToRemove) > 0) {
                $this->flushBatchedHits($hitKeysToRemove);
            }
        } catch (\Exception $exception) {
            $this->cacheHit($this->hitsPoolQueue);
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [FlagshipConstant::SEND_BATCH,
                $this->getLogFormat($exception->getMessage(), $url, $requestBody, $header, $this->getNow() - $now)]
            );
        }
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

    /**
     * @return boolean
     */
    public function isTroubleshootingActivated()
    {
        $troubleshootingData = $this->getTroubleshootingData();
        if (is_null($troubleshootingData)) {
            return false;
        }

        $now = $this->getNow();

        $isStarted = $now >= ($troubleshootingData->getStartDate()->getTimestamp()) * 1000;
        if (!$isStarted) {
            return false;
        }
        $isFinished = $now > ($troubleshootingData->getEndDate()->getTimestamp()) * 1000;
        if ($isFinished) {
            return  false;
        }
        return true;
    }

    public function addTroubleshootingHit(Troubleshooting $hit)
    {
        if (!$this->isTroubleshootingActivated()) {
            return;
        }
        $troubleshootingData = $this->getTroubleshootingData();
        if ($troubleshootingData->getTraffic() < $hit->getTraffic()) {
            return;
        }
        $hitKey = $this->generateHitKey($hit->getVisitorId());
        $hit->setKey($hitKey);
        $this->troubleshootingQueue[$hit->getKey()] = $hit;
        $this->logDebugSprintf(
            $this->config,
            FlagshipConstant::ADD_TROUBLESHOOTING_HIT,
            FlagshipConstant::TROUBLESHOOTING_HIT_ADDED_IN_QUEUE,
            [$hit->toApiKeys()]
        );
    }


    protected function sendTroubleshooting(Troubleshooting $hit)
    {
        $now = $this->getNow();
        $requestBody = $hit->toApiKeys();
        $url = FlagshipConstant::TROUBLESHOOTING_HIT_URL;
        try {
            $this->httpClient->setTimeout($this->config->getTimeout());

            $this->httpClient->post($url, [], $requestBody);
            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::SEND_TROUBLESHOOTING,
                FlagshipConstant::TROUBLESHOOTING_SENT_SUCCESS,
                [$this->getLogFormat(null, $url, $requestBody, [], $this->getNow() - $now)]
            );
        } catch (\Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::SEND_TROUBLESHOOTING,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [FlagshipConstant::SEND_TROUBLESHOOTING,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, [], $this->getNow() - $now)]
            );
        }
    }
    public function sendTroubleshootingQueue()
    {
        if (!$this->isTroubleshootingActivated() || count($this->troubleshootingQueue) === 0) {
            return;
        }
        foreach ($this->troubleshootingQueue as $key => $item) {
            $this->sendTroubleshooting($item);
        }
    }
}

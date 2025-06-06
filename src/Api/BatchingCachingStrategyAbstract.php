<?php

namespace Flagship\Api;

use Exception;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitCacheFields;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\UsageHit;
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
    protected array $hitsPoolQueue;

    /**
     * @var Activate[]
     */
    protected array $activatePoolQueue;

    /**
     * @var Troubleshooting[]
     */
    protected array $troubleshootingQueue;

    /**
     * @var UsageHit[]
     */
    protected array $usageHitQueue;

    /**
     * @var TroubleshootingData|null
     */
    protected ?TroubleshootingData $troubleshootingData;



    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * @var FlagshipConfig
     */
    protected FlagshipConfig $config;

    protected ?string $flagshipInstanceId;

    /**
     * @param FlagshipConfig $config
     * @param HttpClientInterface $httpClient
     * @param string|null $flagshipInstanceId
     */
    public function __construct(
        FlagshipConfig $config,
        HttpClientInterface $httpClient,
        ?string $flagshipInstanceId = null
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->hitsPoolQueue = [];
        $this->activatePoolQueue = [];
        $this->troubleshootingQueue = [];
        $this->usageHitQueue = [];
        $this->flagshipInstanceId = $flagshipInstanceId;
        $this->troubleshootingData = null;
    }

    /**
     * @return UsageHit []
     */
    public function getUsageHitQueue(): array
    {
        return $this->usageHitQueue;
    }

    /**
     * @return TroubleshootingData|null
     */
    public function getTroubleshootingData(): ?TroubleshootingData
    {
        return $this->troubleshootingData;
    }

    /**
     * @param ?TroubleshootingData $troubleshootingData
     * @return void
     */
    public function setTroubleshootingData(?TroubleshootingData $troubleshootingData): void
    {
        $this->troubleshootingData = $troubleshootingData;
    }

    /**
     * @return Troubleshooting[]
     */
    public function getTroubleshootingQueue(): array
    {
        return $this->troubleshootingQueue;
    }

    /**
     * @return HitAbstract[]
     */
    public function getHitsPoolQueue(): array
    {
        return $this->hitsPoolQueue;
    }

    /**
     * @return Activate[]
     */
    public function getActivatePoolQueue(): array
    {
        return $this->activatePoolQueue;
    }

    /**
     * @param $key
     * @param HitAbstract $hit
     * @return void
     */
    public function hydrateHitsPoolQueue($key, HitAbstract $hit): void
    {
        $this->hitsPoolQueue[$key] = $hit;
    }

    /**
     * @param $key
     * @param Activate $hit
     * @return void
     */
    public function hydrateActivatePoolQueue($key, Activate $hit): void
    {
        $this->activatePoolQueue[$key] = $hit;
    }


    /**
     * @return array
     */
    public function getActivateHeaders(): array
    {
        return [
            FlagshipConstant::HEADER_X_API_KEY     => $this->config->getApiKey(),
            FlagshipConstant::HEADER_X_SDK_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::HEADER_CONTENT_TYPE  => FlagshipConstant::HEADER_APPLICATION_JSON,
            FlagshipConstant::HEADER_X_SDK_CLIENT  => FlagshipConstant::SDK_LANGUAGE,
        ];
    }

    /**
     * @param $visitorId
     * @return string
     */
    public function generateHitKey($visitorId): string
    {
        return $visitorId . ":" . $this->newGuid();
    }

    /**
     * @param HitAbstract $hit
     * @return void
     */
    public function addHit(HitAbstract $hit): void
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
    public function activateFlag(Activate $hit): void
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
     * @param string $visitorId
     * @return void
     */
    abstract protected function notConsent(string $visitorId): void;

    /**
     * @param HitAbstract $hit
     * @return void
     */
    protected function addHitInPoolQueue(HitAbstract $hit): void
    {
        $this->hitsPoolQueue[$hit->getKey()] = $hit;
    }

    /**
     * @param Activate $hit
     * @return void
     */
    protected function addActivateHitInPoolQueue(Activate $hit): void
    {
        $this->activatePoolQueue[$hit->getKey()] = $hit;
    }

    /**
     * @param string[] $hitKeysToRemove
     * @return void
     */
    protected function flushSentActivateHit(array $hitKeysToRemove): void
    {
        $this->flushHits($hitKeysToRemove);
    }

    /**
     * @param string[] $hitKeysToRemove
     * @return void
     */
    protected function flushBatchedHits(array $hitKeysToRemove): void
    {
        $this->flushHits($hitKeysToRemove);
    }

    /**
     * @param Activate $activate
     * @return void
     */
    protected function onVisitorExposed(Activate $activate): void
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
        } catch (Exception $exception) {
            $this->logErrorSprintf($this->config, __FUNCTION__, $exception->getMessage());
        }
    }

    protected function sendActivateHitBatch(ActivateBatch $activateBatch)
    {
        $headers = $this->getActivateHeaders();
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
                    $this->getLogFormat(null, $url, $requestBody, $headers, $this->getNow() - $now),
                ]
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
        } catch (Exception $exception) {
            $this->cacheHit($this->activatePoolQueue);
            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::SEND_ACTIVATE_HIT_ROUTE_ERROR)
                ->setHttpRequestBody($requestBody)
                ->setHttpRequestHeaders($headers)
                ->setHttpRequestMethod("POST")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($exception->getMessage())
                ->setHttpResponseTime($this->getNow() - $now)
                ->setFlagshipInstanceId($this->flagshipInstanceId)
                ->setLogLevel(LogLevel::ERROR)
                ->setTraffic(100)->setConfig($this->config)
                ->setVisitorId($this->flagshipInstanceId);

            $this->addTroubleshootingHit($troubleshooting);
            $this->sendTroubleshootingQueue();
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [
                    FlagshipConstant::SEND_ACTIVATE,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, $headers, $this->getNow() - $now),
                ]
            );
        }
    }

    /**
     * @return void
     */
    protected function sendActivateHit(): void
    {
        $filteredItems = array_filter($this->activatePoolQueue, function ($item) {
            return $this->getNow() - $item->getCreatedAt() < FlagshipConstant::DEFAULT_HIT_CACHE_TIME_MS;
        });


        if (empty($filteredItems)) {
            return;
        }

        $batches = array_chunk($filteredItems, FlagshipConstant::MAX_ACTIVATE_HIT_PER_BATCH);

        foreach ($batches as $batch) {
            $activateBatch = new ActivateBatch($this->config, $batch);
            $this->sendActivateHitBatch($activateBatch);
        }
    }

    /**
     * @param string $visitorId
     * @return string []
     */
    protected function commonNotConsent(string $visitorId): array
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
    public function sendBatch(): void
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
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON,
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
                    $this->getLogFormat(null, $url, $requestBody, $header, $this->getNow() - $now),
                ]
            );

            $this->hitsPoolQueue = [];
            if (count($hitKeysToRemove) > 0) {
                $this->flushBatchedHits($hitKeysToRemove);
            }
        } catch (Exception $exception) {
            $this->cacheHit($this->hitsPoolQueue);
            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::SEND_BATCH_HIT_ROUTE_RESPONSE_ERROR)
                ->setLogLevel(LogLevel::ERROR)
                ->setFlagshipInstanceId($this->flagshipInstanceId)
                ->setHttpRequestBody($requestBody)
                ->setHttpRequestHeaders($header)
                ->setHttpRequestMethod("POST")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($exception->getMessage())
                ->setHttpResponseTime($this->getNow() - $now)
                ->setTraffic(100)->setConfig($this->config)
                ->setVisitorId($this->flagshipInstanceId);
            $this->addTroubleshootingHit($troubleshooting);
            $this->sendTroubleshootingQueue();
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [
                    FlagshipConstant::SEND_BATCH,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, $header, $this->getNow() - $now),
                ]
            );
        }
    }

    /**
     * @param HitAbstract[] $hits
     * @return void
     */
    public function cacheHit(array $hits): void
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
                    HitCacheFields::DATA    => [
                        HitCacheFields::VISITOR_ID   => $hit->getVisitorId(),
                        HitCacheFields::ANONYMOUS_ID => $hit->getAnonymousId(),
                        HitCacheFields::TYPE         => $hit->getType(),
                        HitCacheFields::CONTENT      => $hit->toArray(),
                        HitCacheFields::TIME         => $this->getNow(),
                    ],
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
        } catch (Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                [
                    "cacheHit",
                    $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * @param array $hitKeys
     * @return void
     */
    public function flushHits(array $hitKeys): void
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
        } catch (Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                [
                    "flushHits",
                    $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * @return void
     */
    public function flushAllHits(): void
    {
        try {
            $hitCacheImplementation = $this->config->getHitCacheImplementation();
            if (!$hitCacheImplementation) {
                return;
            }
            $hitCacheImplementation->flushAllHits();
            $this->logDebugSprintf($this->config, FlagshipConstant::PROCESS_CACHE, FlagshipConstant::ALL_HITS_FLUSHED);
        } catch (Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::PROCESS_CACHE,
                FlagshipConstant::HIT_CACHE_ERROR,
                [
                    "flushAllHits",
                    $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * @return boolean
     */
    public function isTroubleshootingActivated(): bool
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

    public function addTroubleshootingHit(Troubleshooting $hit): void
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


    protected function sendTroubleshooting(Troubleshooting $hit): void
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
        } catch (Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::SEND_TROUBLESHOOTING,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [
                    FlagshipConstant::SEND_TROUBLESHOOTING,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, [], $this->getNow() - $now),
                ]
            );
        }
    }
    public function sendTroubleshootingQueue(): void
    {
        if (!$this->isTroubleshootingActivated() || count($this->troubleshootingQueue) === 0) {
            return;
        }
        foreach ($this->troubleshootingQueue as $item) {
            $this->sendTroubleshooting($item);
        }
        $this->troubleshootingQueue = [];
    }

    public function addUsageHit(UsageHit $hit): void
    {
        $hitKey = $this->generateHitKey($hit->getVisitorId());
        $hit->setKey($hitKey);
        $this->usageHitQueue[$hit->getKey()] = $hit;
        $this->logDebugSprintf(
            $this->config,
            FlagshipConstant::ADD_USAGE_HIT,
            FlagshipConstant::USAGE_HIT_ADDED_IN_QUEUE,
            [$hit->toApiKeys()]
        );
    }
    public function sendUsageHit(UsageHit $hit): void
    {
        $now = $this->getNow();
        $requestBody = $hit->toApiKeys();
        $url = FlagshipConstant::ANALYTICS_HIT_URL;
        try {
            $this->httpClient->setTimeout($this->config->getTimeout());
            $this->httpClient->post($url, [], $requestBody);
            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::SEND_USAGE_HIT,
                FlagshipConstant::USAGE_HIT_HAS_BEEN_SENT_S,
                [$this->getLogFormat(null, $url, $requestBody, [], $this->getNow() - $now)]
            );
        } catch (Exception $exception) {
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::SEND_USAGE_HIT,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [
                    FlagshipConstant::SEND_USAGE_HIT,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, [], $this->getNow() - $now),
                ]
            );
        }
    }

    public function sendUsageHitQueue(): void
    {
        if (count($this->usageHitQueue) === 0) {
            return;
        }
        foreach ($this->usageHitQueue as $item) {
            $this->sendUsageHit($item);
        }
        $this->usageHitQueue = [];
    }
}

<?php

namespace Flagship\Api;

use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;

class NoBatchingContinuousCachingStrategy extends BatchingCachingStrategyAbstract
{
    /**
     * @var string[]
     */
    protected $cacheHitKeys = [];

    /**
     * @param HitAbstract $hit
     * @return void
     */
    public function addHit(HitAbstract $hit)
    {
        if (($hit instanceof Event) && $hit->getAction() === FlagshipConstant::FS_CONSENT &&
            $hit->getLabel() === FlagshipConstant::SDK_LANGUAGE . ":false") {
            $this->notConsent($hit->getVisitorId());
        }

        $this->sendHit($hit);
    }

    /**
     * @param HitAbstract $hit
     * @return void
     */
    protected function onError(HitAbstract $hit)
    {
        $hitKey = $this->generateHitKey($hit->getVisitorId());
        $hit->setKey($hitKey);
        $this->cacheHitKeys[]= $hitKey;
        $this->cacheHit([$hit]);
    }

    protected function sendHit(HitAbstract $hit)
    {
        $header = [
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON
        ];

        $requestBody = $hit->toApiKeys();
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
                    FlagshipConstant::SEND_HIT,
                    $this->getLogFormat(null, $url, $requestBody, $header, $this->getNow() - $now)]
            );
        } catch (\Exception $exception) {
            $this->onError($hit);
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::TRACKING_MANAGER_ERROR,
                [FlagshipConstant::SEND_HIT,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, $header, $this->getNow() - $now)]
            );
        }
    }

    /**
     * @param Activate $hit
     * @return void
     */
    public function activateFlag(Activate $hit)
    {
        $headers = $this->getActivateHeaders();

        $activateBatch = new ActivateBatch($this->config, [$hit]);

        $requestBody = $activateBatch->toApiKeys();

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $now = $this->getNow();

        try {
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($this->config->getTimeout());

            $this->httpClient->post($url, [], $requestBody);

            $this->logDebugSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::HIT_SENT_SUCCESS,
                [
                    FlagshipConstant::SEND_ACTIVATE,
                $this->getLogFormat(null, $url, $requestBody, $headers, $this->getNow() - $now)]
            );
        } catch (\Exception $exception) {
            $this->onError($hit);
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
     * @param string $visitorId
     * @return void
     */
    protected function notConsent($visitorId)
    {
        $keysToFlush = $this->commonNotConsent($visitorId);
        $mergedQueue = array_merge($keysToFlush, $this->cacheHitKeys);
        if (!count($mergedQueue)) {
            return;
        }
        $this->flushHits($mergedQueue);
        $this->cacheHitKeys = [];
    }
}

<?php

namespace Flagship\Api;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\UsageHit;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\Troubleshooting;

class NoBatchingContinuousCachingStrategy extends BatchingCachingStrategyAbstract
{
    /**
     * @var string[]
     */
    protected array $cacheHitKeys = [];

    /**
     * @param HitAbstract $hit
     * @return void
     */
    public function addHit(HitAbstract $hit): void
    {
        if (
            ($hit instanceof Event) && $hit->getAction() === FlagshipConstant::FS_CONSENT &&
            $hit->getLabel() === FlagshipConstant::SDK_LANGUAGE . ":false"
        ) {
            $this->notConsent($hit->getVisitorId());
        }

        $this->sendHit($hit);
    }

    /**
     * @param HitAbstract $hit
     * @return void
     */
    protected function onError(HitAbstract $hit): void
    {
        $hitKey = $this->generateHitKey($hit->getVisitorId());
        $hit->setKey($hitKey);
        $this->cacheHitKeys[] = $hitKey;
        $this->cacheHit([$hit]);
    }

    protected function sendHit(HitAbstract $hit): void
    {
        $header = [
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON,
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
                    $this->getLogFormat(null, $url, $requestBody, $header, $this->getNow() - $now),
                ]
            );
        } catch (Exception $exception) {
            $this->onError($hit);
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [
                    FlagshipConstant::SEND_HIT,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, $header, $this->getNow() - $now),
                ]
            );
            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::SEND_HIT_ROUTE_ERROR)
                ->setLogLevel(LogLevel::ERROR)
                ->setFlagshipInstanceId($this->flagshipInstanceId)
                ->setHttpRequestBody($requestBody)
                ->setHttpRequestHeaders($header)
                ->setHttpRequestMethod("POST")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($exception->getMessage())
                ->setHttpResponseTime($this->getNow() - $now)
                ->setTraffic(100)->setConfig($this->config)
                ->setVisitorId($this->flagshipInstanceId ?? "")
            ;
            $this->addTroubleshootingHit($troubleshooting);
            $this->sendTroubleshootingQueue();
        }
    }

    /**
     * @param Activate $hit
     * @return void
     */
    public function activateFlag(Activate $hit): void
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
                    $this->getLogFormat(null, $url, $requestBody, $headers, $this->getNow() - $now),
                ]
            );
        } catch (Exception $exception) {
            $this->onError($hit);
            $this->logErrorSprintf(
                $this->config,
                FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::UNEXPECTED_ERROR_OCCURRED,
                [
                    FlagshipConstant::SEND_ACTIVATE,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, $headers, $this->getNow() - $now),
                ]
            );

            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::SEND_ACTIVATE_HIT_ROUTE_ERROR)
                ->setFlagshipInstanceId($this->flagshipInstanceId)
                ->setHttpRequestBody($requestBody)
                ->setHttpRequestHeaders($headers)
                ->setHttpRequestMethod("POST")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($exception->getMessage())
                ->setHttpResponseTime($this->getNow() - $now)
                ->setLogLevel(LogLevel::ERROR)
                ->setTraffic(100)->setConfig($this->config)
                ->setVisitorId($this->flagshipInstanceId ?? "");
            $this->addTroubleshootingHit($troubleshooting);
            $this->sendTroubleshootingQueue();
        }
    }


    protected function notConsent(string $visitorId): void
    {
        $keysToFlush = $this->commonNotConsent($visitorId);
        $mergedQueue = array_merge($keysToFlush, $this->cacheHitKeys); 
        if (count($mergedQueue) === 0) {
            return;
        }
        $this->flushHits($mergedQueue);
        $this->cacheHitKeys = [];
    }

    public function addTroubleshootingHit(Troubleshooting $hit): void
    {
        if (!$this->isTroubleshootingActivated()) {
            return;
        }
        $this->sendTroubleshooting($hit);
    }

    public function addUsageHit(UsageHit $hit): void
    {
        $this->sendUsageHit($hit);
    }
}

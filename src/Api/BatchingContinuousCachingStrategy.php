<?php

namespace Flagship\Api;

use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\Activate;
use Flagship\Hit\ActivateBatch;
use Flagship\Hit\Event;
use Flagship\Hit\HitAbstract;

class a extends BatchingCachingStrategyAbstract
{

    protected function notConsent($visitorId)
    {
        $hitKeys = [];
        $keysToFlush = [];
        foreach ($this->hitsPoolQueue as $item) {
            if (($item instanceof Event && $item->getAction()=== FlagshipConstant::FS_CONSENT) ||
                ($visitorId!== $item->getVisitorId() && $visitorId !== $item->getAnonymousId())){
                continue;
            }
            $hitKeys[] = $item->getKey();
            $keysToFlush[]= $item->getKey();
        }

        $activateKeys = [];
        foreach ($this->activatePoolQueue as $item) {
            if ($visitorId!== $item->getVisitorId() && $visitorId!== $item->getAnonymousId()){
                continue;
            }
            $activateKeys[] = $item->getKey();
            $keysToFlush[]= $item->getKey();
        }

        foreach ($hitKeys as $hitKey) {
            unset($this->hitsPoolQueue[$hitKey]);
        }

        foreach ($activateKeys as $activateKey) {
            unset($this->activatePoolQueue[$activateKey]);
        }

        if (!count($keysToFlush)){
            return;
        }

        $this->flushHits($keysToFlush);

    }

    protected function addHitInPoolQueue(HitAbstract $hit)
    {
        $this->hitsPoolQueue[$hit->getKey()] = $hit;
        $this->cacheHit([$hit]);
    }

    protected function addActivateHitInPoolQueue(Activate $hit)
    {
        $this->activatePoolQueue[$hit->getKey()] = $hit;
        $this->cacheHit([$hit]);
    }

    protected function sendActivateHit()
    {

        $headers = [
            FlagshipConstant::HEADER_X_API_KEY => $this->config->getApiKey(),
            FlagshipConstant::HEADER_X_SDK_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON,
            FlagshipConstant::HEADER_X_SDK_CLIENT => FlagshipConstant::SDK_LANGUAGE,
        ];

        $activateBatch = new ActivateBatch($this->config, $this->activatePoolQueue);

        $requestBody = $activateBatch->toArray();
        $url = FlagshipConstant::BASE_API_URL . '/' .FlagshipConstant::URL_ACTIVATE_MODIFICATION;
        $now = $this->getNow();

        try {
            $this->httpClient->setTimeout($this->config->getTimeout());
            $this->httpClient->setHeaders($headers);

            $this->httpClient->post($url,[], $requestBody);

            $this->logDebugSprintf($this->config, FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::HIT_SENT_SUCCESS, [
                    FlagshipConstant::SEND_ACTIVATE,
                    $this->getLogFormat(null, $url, $requestBody, $headers,$this->getNow()  - $now) ]);

            $hitKeysToRemove = [];

            foreach ($this->activatePoolQueue as $item) {
                $hitKeysToRemove[] = $item->getKey();
            }

            $this->flushHits($hitKeysToRemove);

        }catch (\Exception $exception){
            $this->logErrorSprintf($this->config, FlagshipConstant::TRACKING_MANAGER,
                FlagshipConstant::TRACKING_MANAGER_ERROR, [FlagshipConstant::SEND_ACTIVATE,
                    $this->getLogFormat($exception->getMessage(), $url, $requestBody, $headers, $this->getNow()  - $now)]);
        }

    }
}
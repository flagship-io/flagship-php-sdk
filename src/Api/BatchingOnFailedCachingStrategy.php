<?php

namespace Flagship\Api;

class BatchingOnFailedCachingStrategy extends BatchingCachingStrategyAbstract
{
    /**
     * @inheritDoc
     */
    protected function notConsent($visitorId)
    {
        $keysToFlush = $this->commonNotConsent($visitorId);
        if (count($keysToFlush) === 0) {
            return;
        }

        $this->flushHits($keysToFlush);
    }
}
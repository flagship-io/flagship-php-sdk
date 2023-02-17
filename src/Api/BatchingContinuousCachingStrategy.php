<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;

class BatchingContinuousCachingStrategy extends BatchingCachingStrategyAbstract
{

    /**
     * @param $visitorId
     * @return void
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

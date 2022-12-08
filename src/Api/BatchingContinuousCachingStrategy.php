<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;

class BatchingContinuousCachingStrategy extends BatchingCachingStrategyAbstract
{

    protected function notConsent($visitorId)
    {
        $keysToFlush = $this->commonNotConsent($visitorId);
        if (!count($keysToFlush)){
            return;
        }

        $this->flushHits($keysToFlush);
    }

}
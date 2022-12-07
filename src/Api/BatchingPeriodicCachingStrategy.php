<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;

class BatchingPeriodicCachingStrategy extends BatchingCachingStrategyAbstract
{

    protected function notConsent($visitorId)
    {
        // TODO: Implement notConsent() method.
    }

    protected function addHitInPoolQueue(HitAbstract $hit)
    {
        // TODO: Implement addHitInPoolQueue() method.
    }

    protected function addActivateHitInPoolQueue(Activate $hit)
    {
        // TODO: Implement addActivateHitInPoolQueue() method.
    }

    protected function sendActivateHit()
    {
        // TODO: Implement sendActivateHit() method.
    }
}
<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;

class BatchingPeriodicCachingStrategy extends BatchingCachingStrategyAbstract
{

    protected function cacheMergedPoolQueue(){
        $mergedQueue = array_merge($this->hitsPoolQueue, $this->activatePoolQueue);
        $this->flushAllHits();
        $this->cacheHit($mergedQueue);
    }

    protected function notConsent($visitorId)
    {
        $keysToFlush = $this->commonNotConsent($visitorId);

        if (!count($keysToFlush)){
            return;
        }

        $this->cacheMergedPoolQueue();
    }

    protected function addHitInPoolQueue(HitAbstract $hit)
    {
        $this->hitsPoolQueue[$hit->getKey()] = $hit;
    }

    protected function addActivateHitInPoolQueue(Activate $hit)
    {
        $this->activatePoolQueue[$hit->getKey()] = $hit;
    }

    protected function postPrecessSendBatch()
    {
        $this->cacheMergedPoolQueue();
    }

    /**
     * @inheritDoc
     */
    protected function flushBatchedHits(array $hitKeysToRemove)
    {
        // Nothing to do
    }

    /**
     * @inheritDoc
     */
    protected function flushSentActivateHit(array $hitKeysToRemove)
    {
        // Nothing to do
    }
}
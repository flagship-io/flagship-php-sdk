<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;

/**
 * Class TrackingManager
 * @package Flagship\Api
 */
class TrackingManager extends TrackingManagerAbstract
{

    /**
     * @inheritDoc
     */
    public function addHit(HitAbstract $hit)
    {
        $this->strategy->addHit($hit);
    }

    /**
     * @inheritDoc
     */
    public function activateFlag(Activate $hit)
    {
        $this->strategy->activateFlag($hit);
    }

    /**
     * @inheritDoc
     */
    public function sendBatch()
    {
        $this->strategy->sendBatch();
    }
}

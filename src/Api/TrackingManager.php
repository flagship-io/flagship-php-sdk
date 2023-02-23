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
        $this->getStrategy()->addHit($hit);
    }

    /**
     * @inheritDoc
     */
    public function activateFlag(Activate $hit)
    {
        $this->getStrategy()->activateFlag($hit);
    }

    /**
     * @inheritDoc
     */
    public function sendBatch()
    {
        $this->getStrategy()->sendBatch();
    }
}

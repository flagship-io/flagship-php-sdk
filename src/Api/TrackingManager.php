<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\Troubleshooting;

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
        $this->getStrategy()->sendTroubleshootingQueue();
    }

    public function addTroubleshootingHit(Troubleshooting $hit)
    {
        $this->getStrategy()->addTroubleshootingHit($hit);
    }
}

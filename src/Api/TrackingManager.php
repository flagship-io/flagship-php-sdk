<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\Analytic;
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
        $strategy = $this->getStrategy();
        $strategy->sendBatch();
        $strategy->sendTroubleshootingQueue();
    }

    /**
     * @inheritDoc
     */
    public function addTroubleshootingHit(Troubleshooting $hit)
    {
        $this->getStrategy()->addTroubleshootingHit($hit);
    }

    /**
     * @inheritDoc
     */
    public function sendAnalyticsHit(Analytic $hit)
    {
        $this->getStrategy()->sendAnalyticsHit($hit);
    }
}

<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\UsageHit;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\TroubleshootingData;

interface TrackingManagerCommonInterface
{
    /**
     * @param HitAbstract $hit
     * @return void
     */
    public function addHit(HitAbstract $hit);

    /**
     * @param Activate $hit
     * @return void
     */
    public function activateFlag(Activate $hit);

    /**
     * @return void
     */
    public function addTroubleshootingHit(Troubleshooting $hit);

    /**
     * @return void
     */
    public function addUsageHit(UsageHit $hit);

    public function getTroubleshootingData();

    /**
     * @param TroubleshootingData $troubleshootingData
     * @return BatchingCachingStrategyAbstract
     */
    public function setTroubleshootingData($troubleshootingData);

}

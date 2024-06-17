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
    public function addHit(HitAbstract $hit): void;

    /**
     * @param Activate $hit
     * @return void
     */
    public function activateFlag(Activate $hit): void;

    /**
     * @param Troubleshooting $hit
     * @return void
     */
    public function addTroubleshootingHit(Troubleshooting $hit): void;

    /**
     * @param UsageHit $hit
     * @return void
     */
    public function addUsageHit(UsageHit $hit): void;

    public function getTroubleshootingData(): ?TroubleshootingData;

    /**
     * @param TroubleshootingData $troubleshootingData
     * @return void
     */
    public function setTroubleshootingData(TroubleshootingData $troubleshootingData): void;
}

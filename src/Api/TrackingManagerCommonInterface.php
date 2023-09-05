<?php

namespace Flagship\Api;

use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;
use Flagship\Hit\Troubleshooting;

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
}

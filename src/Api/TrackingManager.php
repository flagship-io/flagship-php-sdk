<?php

namespace Flagship\Api;

use Exception;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use Flagship\Hit\Activate;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
use Flagship\Traits\BuildApiTrait;
use Flagship\Traits\LogTrait;
use Flagship\Visitor\VisitorAbstract;

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

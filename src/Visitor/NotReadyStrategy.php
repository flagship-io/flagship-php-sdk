<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Hit\HitAbstract;
use Flagship\Traits\LogTrait;

/**
 * Visitor method strategy to use when the SDK status is not yet READY.
 * @package Flagship\Visitor
 */
class NotReadyStrategy extends DefaultStrategy
{
    use LogTrait;

    /**
     * @inheritDoc
     */
    public function synchronizeModifications()
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        $this->log(__FUNCTION__);
        return $defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function getModificationInfo($key)
    {
        $this->log(__FUNCTION__);
        return null;
    }

    /**
     * @inheritDoc
     */
    public function activateModification($key)
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $this->log(__FUNCTION__);
    }

    private function log($functionName)
    {
        $this->logError(
            $this->getVisitor()->getConfig(),
            sprintf(FlagshipConstant::METHOD_DEACTIVATED_ERROR, $functionName, FlagshipStatus::NOT_INITIALIZED),
            [FlagshipConstant::TAG => $functionName]
        );
    }
}

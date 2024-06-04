<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FSSdkStatus;
use Flagship\Flag\FSFlagMetadata;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
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
    public function sendHit(HitAbstract $hit)
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function fetchFlags()
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function getFlagValue($key, $defaultValue, FlagDTO $flag = null, $userExposed = true)
    {
        $this->log(__FUNCTION__);
        return $defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function visitorExposed($key, $defaultValue, FlagDTO $flag = null)
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function getFlagMetadata($key, FSFlagMetadata $metadata, $hasSameType)
    {
        $this->log(__FUNCTION__);
        return FSFlagMetadata::getEmpty();
    }

    /**
     * @inheritDoc
     */
    public function lookupVisitor()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function cacheVisitor()
    {
        //
    }

    /**
     * @param string $functionName
     * @return void
     */
    private function log($functionName)
    {
        $this->logError(
            $this->getVisitor()->getConfig(),
            sprintf(FlagshipConstant::METHOD_DEACTIVATED_ERROR, $functionName, FSSdkStatus::getStatusName(FSSdkStatus::SDK_NOT_INITIALIZED)),
            [FlagshipConstant::TAG => $functionName]
        );
    }
}

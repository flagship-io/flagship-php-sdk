<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
use Flagship\Traits\LogTrait;

/**
 * Visitor method strategy to use when the SDK status is READY_PANIC_ON.
 */
class PanicStrategy extends DefaultStrategy
{
    use LogTrait;

    /**
     * @inheritDoc
     */
    public function setConsent($hasConsented)
    {
        $this->visitor->hasConsented = $hasConsented;
        $this->logError(
            $this->getVisitor()->getConfig(),
            sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_SEND_CONSENT_ERROR,
                FlagshipStatus::getStatusName(FlagshipStatus::READY_PANIC_ON)
            ),
            [FlagshipConstant::TAG => __FUNCTION__]
        );
    }

    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function clearContext()
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
    public function userExposed($key, $defaultValue, FlagDTO $flag = null)
    {
        $this->log(__FUNCTION__);
    }

    public function getFlagMetadata($key, FlagMetadata $metadata, $hasSameType)
    {
        $this->log(__FUNCTION__);
        return FlagMetadata::getEmpty();
    }

    private function log($functionName)
    {
        $this->logError(
            $this->getVisitor()->getConfig(),
            sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FlagshipStatus::getStatusName(FlagshipStatus::READY_PANIC_ON)
            ),
            [FlagshipConstant::TAG => $functionName]
        );
    }
}

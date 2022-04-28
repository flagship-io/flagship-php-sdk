<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;

/**
 * Visitor method strategy to use when the SDK status is READY_PANIC_ON.
 * @package Flagship\Visitor
 */
class NoConsentStrategy extends DefaultStrategy
{
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
    public function userExposed($key, $defaultValue, FlagDTO $flag = null)
    {
        $this->log(__FUNCTION__);
    }

    private function log($functionName)
    {
        $this->logError(
            $this->getVisitor()->getConfig(),
            sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_CONSENT_ERROR,
                $functionName,
                $this->getVisitor()->getVisitorId()
            ),
            [FlagshipConstant::TAG => $functionName]
        );
    }
}

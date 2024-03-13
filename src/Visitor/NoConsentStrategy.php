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
    public function sendHit(HitAbstract $hit)
    {
        $this->log(__FUNCTION__);
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
     * @inheritDoc
     */
    protected function fetchVisitorCampaigns(VisitorAbstract $visitor)
    {
        return [];
    }

    /**
     * @param string $functionName
     * @return void
     */
    private function log($functionName)
    {
        $this->logInfo(
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

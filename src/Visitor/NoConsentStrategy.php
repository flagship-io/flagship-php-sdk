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
    public function sendHit(HitAbstract $hit): void
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function visitorExposed(
        $key,
        float|array|bool|int|string $defaultValue,
        FlagDTO $flag = null,
        bool $hasGetValueBeenCalled = false
    ): void {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function lookupVisitor(): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function cacheVisitor(): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    protected function fetchVisitorCampaigns(VisitorAbstract $visitor): array
    {
        return [];
    }

    /**
     * @param string $functionName
     * @return void
     */
    private function log(string $functionName): void
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

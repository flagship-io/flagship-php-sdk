<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FSSdkStatus;
use Flagship\Flag\FSFlagMetadata;
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
    public function setConsent(bool $hasConsented): void
    {
        $this->visitor->hasConsented = $hasConsented;
        $this->logInfo(
            $this->getVisitor()->getConfig(),
            sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_SEND_CONSENT_ERROR,
                FSSdkStatus::SDK_PANIC->name
            ),
            [FlagshipConstant::TAG => __FUNCTION__]
        );
    }

    /**
     * @inheritDoc
     */
    public function updateContext(string $key, float|bool|int|string|null $value): void
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context): void
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function clearContext(): void
    {
        $this->log(__FUNCTION__);
    }

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
    public function getFlagValue(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO|null $flag = null,
        bool $userExposed = true
    ): float|int|bool|array|string|null {
        $this->log(__FUNCTION__);
        return $defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function visitorExposed(
        $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO|null $flag = null,
        bool $hasGetValueBeenCalled = false
    ): void {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function getFlagMetadata(string $key, FlagDTO $flag = null): FSFlagMetadata
    {
        $this->log(__FUNCTION__);
        return FSFlagMetadata::getEmpty();
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
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FSSdkStatus::SDK_PANIC->name
            ),
            [FlagshipConstant::TAG => $functionName]
        );
    }
}

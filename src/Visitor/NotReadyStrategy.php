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
    public function sendHit(HitAbstract $hit): void
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function fetchFlags(): void
    {
        $this->log(__FUNCTION__);
    }

    /**
     * @inheritDoc
     */
    public function getFlagValue(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO $flag = null,
        bool $userExposed = true
    ): float|array|bool|int|string {
        $this->log(__FUNCTION__);
        return $defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function visitorExposed(
        $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO $flag = null,
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
     * @param string $functionName
     * @return void
     */
    private function log(string $functionName): void
    {
        $this->logError(
            $this->getVisitor()->getConfig(),
            sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FSSdkStatus::SDK_NOT_INITIALIZED->name
            ),
            [FlagshipConstant::TAG => $functionName]
        );
    }
}

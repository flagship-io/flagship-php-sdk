<?php

namespace Flagship\Flag;

use Flagship\Model\FlagDTO;
use Flagship\Enum\FSFlagStatus;
use Flagship\Enum\FSFetchStatus;
use Flagship\Traits\HasSameTypeTrait;
use Flagship\Visitor\VisitorAbstract;

class FSFlag implements FSFlagInterface
{
    use HasSameTypeTrait;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var VisitorAbstract|null
     */
    private ?VisitorAbstract $visitorDelegate;

    /**
     * @var array|bool|float|int|string
     */
    private string|array|bool|int|null|float $defaultValue;

    private bool $hasGetValueBeenCalled;


    /***
     * @param string $key
     * @param VisitorAbstract|null $visitorDelegate
     */
    public function __construct(
        string $key,
        VisitorAbstract $visitorDelegate = null
    ) {
        $this->key = $key;
        $this->visitorDelegate = $visitorDelegate;
        $this->defaultValue = null;
        $this->hasGetValueBeenCalled = false;
    }

    /**
     * @param string $key
     * @return FlagDTO|null
     */
    protected function findFlagDTO(string $key): ?FlagDTO
    {
        if (!$this->visitorDelegate) {
            return null;
        }

        foreach ($this->visitorDelegate->getFlagsDTO() as $flagDTO) {
            if ($flagDTO->getKey() === $key) {
                return $flagDTO;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getValue(
        float|array|bool|int|string|null $defaultValue,
        bool $visitorExposed = true
    ): float|array|bool|int|string|null {
        $flagDTO = $this->findFlagDTO($this->key);
        $this->defaultValue = $defaultValue;
        $this->hasGetValueBeenCalled = true;

        if (!$this->visitorDelegate) {
            return $defaultValue;
        }

        return $this->visitorDelegate->getFlagValue($this->key, $defaultValue, $flagDTO, $visitorExposed);
    }

    /**
     * @inheritDoc
     */
    public function exists(): bool
    {
        $flagDTO = $this->findFlagDTO($this->key);
        return $flagDTO && $flagDTO->getCampaignId() && $flagDTO->getVariationId() && $flagDTO->getVariationGroupId();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): FSFlagMetadataInterface
    {
        if (!$this->visitorDelegate) {
            return FSFlagMetadata::getEmpty();
        }

        $flagDTO = $this->findFlagDTO($this->key);

        return $this->visitorDelegate->getFlagMetadata($this->key, $flagDTO);
    }

    /**
     * @inheritDoc
     */
    public function visitorExposed(): void
    {
        if (!$this->visitorDelegate) {
            return;
        }

        $flagDTO = $this->findFlagDTO($this->key);
        $this->visitorDelegate->visitorExposed(
            $this->key,
            $this->defaultValue,
            $flagDTO,
            $this->hasGetValueBeenCalled
        );
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): FSFlagStatus
    {
        if (!$this->visitorDelegate) {
            return FSFlagStatus::NOT_FOUND;
        }

        $fetchStatus = $this->visitorDelegate->getFetchStatus();
        if ($fetchStatus->getStatus() === FSFetchStatus::PANIC) {
            return FSFlagStatus::PANIC;
        }

        if (!$this->exists()) {
            return FSFlagStatus::NOT_FOUND;
        }

        if (
            $fetchStatus->getStatus() === FSFetchStatus::FETCH_REQUIRED ||
            $fetchStatus->getStatus() === FSFetchStatus::FETCHING
        ) {
            return FSFlagStatus::FETCH_REQUIRED;
        }

        return FSFlagStatus::FETCHED;
    }
}

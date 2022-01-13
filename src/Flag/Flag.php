<?php

namespace Flagship\Flag;

use Flagship\Model\FlagDTO;
use Flagship\Traits\HasSameTypeTrait;
use Flagship\Visitor\VisitorAbstract;

class Flag implements FlagInterface
{
    use HasSameTypeTrait;

    /**
     * @var string
     */
    private $key;
    /**
     * @var VisitorAbstract
     */
    private $visitorDelegate;
    /**
     * @var FlagDTO
     */
    private $flagDTO;
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var FlagMetadata
     */
    private $metadata;

    /***
     * @param string $key
     * @param VisitorAbstract $visitorDelegate
     * @param FlagDTO $flagDTO
     * @param mixed $defaultValue
     * @param FlagMetadata $flagMetadata
     */
    public function __construct(
        $key,
        VisitorAbstract $visitorDelegate,
        $defaultValue,
        FlagMetadata $flagMetadata,
        FlagDTO $flagDTO = null
    ) {
        $this->key = $key;
        $this->visitorDelegate = $visitorDelegate;
        $this->flagDTO = $flagDTO;
        $this->defaultValue = $defaultValue;
        $this->metadata = $flagMetadata;
    }

    /**
     * @inheritDoc
     */
    public function value($userExposed = true)
    {
        return $this->visitorDelegate->getFlagValue($this->key, $this->defaultValue, $this->flagDTO, $userExposed);
    }

    /**
     * @inheritDoc
     */
    public function exists()
    {
        return $this->flagDTO && $this->hasSameType($this->flagDTO->getValue(), $this->defaultValue);
    }

    /**
     * @inheritDoc
     */
    public function userExposed()
    {
        $this->visitorDelegate->userExposed(
            $this->key,
            $this->hasSameType($this->flagDTO->getValue(), $this->defaultValue),
            $this->flagDTO
        );
    }

    /**
     * @inheritDoc
     */
    public function getMetadata()
    {
        if (!$this->flagDTO) {
            return $this->metadata;
        }

        return  $this->visitorDelegate->getFlagMetadata(
            $this->key,
            $this->metadata,
            !$this->flagDTO->getValue() || $this->hasSameType($this->flagDTO->getValue(), $this->defaultValue)
        );
    }
}

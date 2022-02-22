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
     * @param mixed $defaultValue
     */
    public function __construct(
        $key,
        VisitorAbstract $visitorDelegate,
        $defaultValue
    ) {
        $this->key = $key;
        $this->visitorDelegate = $visitorDelegate;
        $flagDTO = $this->findFlagDTO($key);

        $this->defaultValue = $defaultValue;
        $this->metadata = new FlagMetadata(
            $flagDTO ? $flagDTO->getCampaignId() : "",
            $flagDTO ? $flagDTO->getVariationGroupId() : "",
            $flagDTO ? $flagDTO->getVariationId() : "",
            $flagDTO ? $flagDTO->getIsReference() : false,
            $flagDTO ? $flagDTO->getCampaignType() : ""
        );
    }

    protected function findFlagDTO($key)
    {
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
    public function getValue($userExposed = true)
    {
        $flagDTO = $this->findFlagDTO($this->key);
        return $this->visitorDelegate->getFlagValue($this->key, $this->defaultValue, $flagDTO, $userExposed);
    }

    /**
     * @inheritDoc
     */
    public function exists()
    {
        $flagDTO = $this->findFlagDTO($this->key);
        return $flagDTO && $this->hasSameType($flagDTO->getValue(), $this->defaultValue);
    }

    /**
     * @inheritDoc
     */
    public function userExposed()
    {
        $flagDTO = $this->findFlagDTO($this->key);
        $this->visitorDelegate->userExposed(
            $this->key,
            $flagDTO && $this->hasSameType($flagDTO->getValue(), $this->defaultValue),
            $flagDTO
        );
    }

    /**
     * @inheritDoc
     */
    public function getMetadata()
    {
        $flagDTO = $this->findFlagDTO($this->key);
        if (!$flagDTO) {
            return $this->metadata;
        }

        return  $this->visitorDelegate->getFlagMetadata(
            $this->key,
            $this->metadata,
            !$flagDTO->getValue() || $this->hasSameType($flagDTO->getValue(), $this->defaultValue)
        );
    }
}

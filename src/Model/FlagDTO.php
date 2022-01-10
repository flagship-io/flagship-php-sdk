<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;
use JsonSerializable;

/**
 * This class is representation of modification to display
 *
 * @package Flagship\Model
 */
class FlagDTO implements JsonSerializable
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $campaignId;
    /**
     * @var string
     */
    private $variationGroupId;
    /**
     * @var string
     */
    private $variationId;
    /**
     * @var string
     */
    private $isReference;
    /**
     * @var string|bool|numeric
     */
    private $value;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  string $key
     * @return FlagDTO
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @param  string $campaignId
     * @return FlagDTO
     */
    public function setCampaignId($campaignId)
    {
        $this->campaignId = $campaignId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationGroupId()
    {
        return $this->variationGroupId;
    }

    /**
     * @param  string $variationGroupId
     * @return FlagDTO
     */
    public function setVariationGroupId($variationGroupId)
    {
        $this->variationGroupId = $variationGroupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @param  string $variationId
     * @return FlagDTO
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getIsReference()
    {
        return $this->isReference;
    }

    /**
     * @param  string $isReference
     * @return FlagDTO
     */
    public function setIsReference($isReference)
    {
        $this->isReference = $isReference;
        return $this;
    }

    /**
     * @return string|bool|numeric
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param  string|bool|numeric $value
     * @return FlagDTO
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            FlagshipField::FIELD_KEY => $this->getKey(),
            FlagshipField::FIELD_CAMPAIGN_ID => $this->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $this->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $this->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $this->getIsReference(),
            FlagshipField::FIELD_VALUE => $this->getValue()
        ];
    }
}

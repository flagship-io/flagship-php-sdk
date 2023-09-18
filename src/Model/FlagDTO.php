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
    private $campaignName;
    /**
     * @var string
     */
    private $variationGroupId;

    /**
     * @var string
     */
    private $variationGroupName;

    /**
     * @var string
     */
    private $variationId;

    /**
     * @var string
     */
    private $variationName;
    /**
     * @var bool
     */
    private $isReference;
    /**
     * @var string|bool|numeric
     */
    private $value;

    /**
     * @var string
     */
    private $campaignType;

    /**
     * @var string
     */
    private $slug;

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
     * @return bool
     */
    public function getIsReference()
    {
        return $this->isReference;
    }

    /**
     * @param  bool $isReference
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
     * @return string
     */
    public function getCampaignType()
    {
        return $this->campaignType;
    }

    /**
     * @param string $campaignType
     * @return FlagDTO
     */
    public function setCampaignType($campaignType)
    {
        $this->campaignType = $campaignType;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return FlagDTO
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getCampaignName()
    {
        return $this->campaignName;
    }

    /**
     * @param string $campaignName
     * @return FlagDTO
     */
    public function setCampaignName($campaignName)
    {
        $this->campaignName = $campaignName;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationGroupName()
    {
        return $this->variationGroupName;
    }

    /**
     * @param string $variationGroupName
     * @return FlagDTO
     */
    public function setVariationGroupName($variationGroupName)
    {
        $this->variationGroupName = $variationGroupName;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationName()
    {
        return $this->variationName;
    }

    /**
     * @param string $variationName
     * @return FlagDTO
     */
    public function setVariationName($variationName)
    {
        $this->variationName = $variationName;
        return $this;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            FlagshipField::FIELD_KEY => $this->getKey(),
            FlagshipField::FIELD_CAMPAIGN_ID => $this->getCampaignId(),
            FlagshipField::FIELD_CAMPAIGN_NAME => $this->getCampaignName(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $this->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_GROUP_NAME => $this->getVariationGroupName(),
            FlagshipField::FIELD_VARIATION_ID => $this->getVariationId(),
            FlagshipField::FIELD_VARIATION_NAME => $this->getVariationName(),
            FlagshipField::FIELD_IS_REFERENCE => $this->getIsReference(),
            FlagshipField::FIELD_VALUE => $this->getValue(),
            FlagshipField::FIELD_SLUG => $this->getSlug()
        ];
    }
}

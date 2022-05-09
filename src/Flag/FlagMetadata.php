<?php

namespace Flagship\Flag;

use Flagship\Enum\FlagshipConstant;
use JsonSerializable;

class FlagMetadata implements JsonSerializable
{
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
     * @var bool
     */
    private $isReference;
    /**
     * @var string
     */
    private $campaignType;

    /**
     * @var string
     */
    private $slug;

    /**
     * @param string $campaignId
     * @param string $variationGroupId
     * @param string $variationId
     * @param bool $isReference
     * @param string $campaignType
     */
    public function __construct($campaignId, $variationGroupId, $variationId, $isReference, $campaignType, $slug)
    {
        $this->campaignId = $campaignId;
        $this->variationGroupId = $variationGroupId;
        $this->variationId = $variationId;
        $this->isReference = $isReference;
        $this->campaignType = $campaignType;
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }


    /**
     * @return string
     */
    public function getVariationGroupId()
    {
        return $this->variationGroupId;
    }


    /**
     * @return string
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @return bool
     */
    public function isReference()
    {
        return $this->isReference;
    }

    /**
     * @return string
     */
    public function getCampaignType()
    {
        return $this->campaignType;
    }

    public static function getEmpty()
    {
        return new FlagMetadata("", "", "", false, "", null);
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
     * @return FlagMetadata
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }



    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "campaignId" => $this->getCampaignId(),
            "variationGroupId" => $this->getVariationGroupId(),
            "variationId" => $this->getVariationId(),
            "isReference" => $this->isReference(),
            "campaignType" => $this->getCampaignType(),
            "slug" => $this->getSlug()
        ];
    }
}

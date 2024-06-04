<?php

namespace Flagship\Flag;

use JsonSerializable;

class FSFlagMetadata implements JsonSerializable, FSFlagMetadataInterface
{
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
    public function __construct(
        $campaignId,
        $variationGroupId,
        $variationId,
        $isReference,
        $campaignType,
        $slug,
        $campaignName,
        $variationGroupName,
        $variationName
    ) {
        $this->campaignId = $campaignId;
        $this->variationGroupId = $variationGroupId;
        $this->variationId = $variationId;
        $this->isReference = $isReference;
        $this->campaignType = $campaignType;
        $this->slug = $slug;
        $this->variationGroupName = $variationGroupName;
        $this->campaignName = $campaignName;
        $this->variationName = $variationName;
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

    /**
     * @return string
     */
    public function getCampaignName()
    {
        return $this->campaignName;
    }

    /**
     * @return string
     */
    public function getVariationGroupName()
    {
        return $this->variationGroupName;
    }

    /**
     * @return string
     */
    public function getVariationName()
    {
        return $this->variationName;
    }

    public static function getEmpty()
    {
        return new FSFlagMetadata(
            "",
            "",
            "",
            false,
            "",
            null,
            "",
            "",
            ""
        );
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "campaignId" => $this->getCampaignId(),
            "campaignName" => $this->getCampaignName(),
            "variationGroupId" => $this->getVariationGroupId(),
            "variationGroupName" => $this->getVariationGroupName(),
            "variationId" => $this->getVariationId(),
            "variationName" => $this->getVariationName(),
            "isReference" => $this->isReference(),
            "campaignType" => $this->getCampaignType(),
            "slug" => $this->getSlug()
        ];
    }
}

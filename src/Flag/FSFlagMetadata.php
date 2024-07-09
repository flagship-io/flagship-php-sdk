<?php

namespace Flagship\Flag;

class FSFlagMetadata implements FSFlagMetadataInterface
{
    /**
     * @var string
     */
    private string $campaignId;

    /**
     * @var string
     */
    private string $campaignName;
    /**
     * @var string
     */
    private string $variationGroupId;

    /**
     * @var string
     */
    private string $variationGroupName;

    /**
     * @var string
     */
    private string $variationId;

    /**
     * @var string
     */
    private string $variationName;
    /**
     * @var bool
     */
    private bool $isReference;
    /**
     * @var string
     */
    private string $campaignType;

    /**
     * @var ?string
     */
    private ?string $slug;

    /**
     * @param string $campaignId
     * @param string $variationGroupId
     * @param string $variationId
     * @param bool $isReference
     * @param string $campaignType
     * @param string|null $slug
     * @param string $campaignName
     * @param string $variationGroupName
     * @param string $variationName
     */
    public function __construct(
        string $campaignId,
        string $variationGroupId,
        string $variationId,
        bool $isReference,
        string $campaignType,
        ?string $slug,
        string $campaignName,
        string $variationGroupName,
        string $variationName
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
    public function getCampaignId(): string
    {
        return $this->campaignId;
    }


    /**
     * @return string
     */
    public function getVariationGroupId(): string
    {
        return $this->variationGroupId;
    }


    /**
     * @return string
     */
    public function getVariationId(): string
    {
        return $this->variationId;
    }

    /**
     * @return bool
     */
    public function isReference(): bool
    {
        return $this->isReference;
    }

    /**
     * @return string
     */
    public function getCampaignType(): string
    {
        return $this->campaignType;
    }

    /**
     * @return string
     */
    public function getCampaignName(): string
    {
        return $this->campaignName;
    }

    /**
     * @return string
     */
    public function getVariationGroupName(): string
    {
        return $this->variationGroupName;
    }

    /**
     * @return string
     */
    public function getVariationName(): string
    {
        return $this->variationName;
    }

    public static function getEmpty(): FSFlagMetadata
    {
        return new FSFlagMetadata(
            "",
            "",
            "",
            false,
            "",
            "",
            "",
            "",
            ""
        );
    }

    /**
     * @return ?string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @inheritDoc
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function jsonSerialize(): mixed
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

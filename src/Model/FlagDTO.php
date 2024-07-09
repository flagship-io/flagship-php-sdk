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
    private string $key;
    /**
     * @var string
     */
    private string $campaignId;

    /**
     * @var ?string
     */
    private ?string $campaignName = null;
    /**
     * @var string
     */
    private string $variationGroupId;

    /**
     * @var ?string
     */
    private ?string $variationGroupName = null;

    /**
     * @var string
     */
    private string $variationId;

    /**
     * @var ?string
     */
    private ?string $variationName = null;
    /**
     * @var bool
     */
    private bool $isReference;
    /**
     * @var string|bool|numeric
     */
    private string|int|bool|float|null|array $value;

    /**
     * @var string
     */
    private string $campaignType;

    /**
     * @var ?string
     */
    private ?string $slug = null;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return FlagDTO
     */
    public function setKey(string $key): static
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    /**
     * @param string $campaignId
     * @return FlagDTO
     */
    public function setCampaignId(string $campaignId): static
    {
        $this->campaignId = $campaignId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationGroupId(): string
    {
        return $this->variationGroupId;
    }

    /**
     * @param string $variationGroupId
     * @return FlagDTO
     */
    public function setVariationGroupId(string $variationGroupId): static
    {
        $this->variationGroupId = $variationGroupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationId(): string
    {
        return $this->variationId;
    }

    /**
     * @param string $variationId
     * @return FlagDTO
     */
    public function setVariationId(string $variationId): static
    {
        $this->variationId = $variationId;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsReference(): bool
    {
        return $this->isReference;
    }

    /**
     * @param bool $isReference
     * @return FlagDTO
     */
    public function setIsReference(bool $isReference): static
    {
        $this->isReference = $isReference;
        return $this;
    }

    /**
     * @return float|bool|int|string|array|null
     */
    public function getValue(): float|bool|int|string|null|array
    {
        return $this->value;
    }

    /**
     * @param bool|string|numeric $value
     * @return FlagDTO
     */
    public function setValue(float|bool|int|string|null|array $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getCampaignType(): string
    {
        return $this->campaignType;
    }

    /**
     * @param string $campaignType
     * @return FlagDTO
     */
    public function setCampaignType(string $campaignType): static
    {
        $this->campaignType = $campaignType;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return FlagDTO
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getCampaignName(): ?string
    {
        return $this->campaignName;
    }

    /**
     * @param string $campaignName
     * @return FlagDTO
     */
    public function setCampaignName(string $campaignName): static
    {
        $this->campaignName = $campaignName;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getVariationGroupName(): ?string
    {
        return $this->variationGroupName;
    }

    /**
     * @param string $variationGroupName
     * @return FlagDTO
     */
    public function setVariationGroupName(string $variationGroupName): static
    {
        $this->variationGroupName = $variationGroupName;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getVariationName(): ?string
    {
        return $this->variationName;
    }

    /**
     * @param string $variationName
     * @return FlagDTO
     */
    public function setVariationName(string $variationName): static
    {
        $this->variationName = $variationName;
        return $this;
    }

    public function jsonSerialize(): mixed
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

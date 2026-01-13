<?php

namespace Flagship\Model;

use JsonSerializable;
use Flagship\Enum\FlagshipField;

/**
 * The Campaign Data Transfer Object.
 * @phpstan-import-type CampaignArray from Types
 */
class CampaignDTO implements JsonSerializable
{
    private string $id;

    private ?string $name = null;

    private ?string $slug = null;

    private string $variationGroupId;

    private ?string $variationGroupName = null;

    private VariationDTO $variation;

    private ?string $type = null;

    public function __construct(
        string $id,
        string $variationGroupId,
        VariationDTO $variation
    ) {
        $this->id = $id;
        $this->variationGroupId = $variationGroupId;
        $this->variation = $variation;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getVariationGroupId(): string
    {
        return $this->variationGroupId;
    }

    public function setVariationGroupId(string $variationGroupId): self
    {
        $this->variationGroupId = $variationGroupId;
        return $this;
    }

    public function getVariationGroupName(): ?string
    {
        return $this->variationGroupName;
    }

    public function setVariationGroupName(?string $variationGroupName): self
    {
        $this->variationGroupName = $variationGroupName;
        return $this;
    }

    public function getVariation(): VariationDTO
    {
        return $this->variation;
    }

    public function setVariation(VariationDTO $variation): self
    {
        $this->variation = $variation;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $variationData = $data[FlagshipField::FIELD_VARIATION] ?? [];
        $variation = is_array($variationData)
            ? VariationDTO::fromArray($variationData)
            : new VariationDTO('', 
            new ModificationsDTO('', []));

        $id = $data[FlagshipField::FIELD_ID] ?? '';
        $variationGroupId = $data[FlagshipField::FIELD_VARIATION_GROUP_ID] ?? '';

        $instance = new self(
            is_string($id) ? $id : '',
            is_string($variationGroupId) ? $variationGroupId : '',
            $variation
        );

        if (isset($data[FlagshipField::FIELD_NANE]) && is_string($data[FlagshipField::FIELD_NANE])) {
            $instance->setName($data[FlagshipField::FIELD_NANE]);
        }

        if (isset($data[FlagshipField::FIELD_SLUG]) && is_string($data[FlagshipField::FIELD_SLUG])) {
            $instance->setSlug($data[FlagshipField::FIELD_SLUG]);
        }

        if (isset($data[FlagshipField::FIELD_VARIATION_GROUP_NAME]) && is_string($data[FlagshipField::FIELD_VARIATION_GROUP_NAME])) {
            $instance->setVariationGroupName($data[FlagshipField::FIELD_VARIATION_GROUP_NAME]);
        }

        if (isset($data[FlagshipField::FIELD_CAMPAIGN_TYPE]) && is_string($data[FlagshipField::FIELD_CAMPAIGN_TYPE])) {
            $instance->setType($data[FlagshipField::FIELD_CAMPAIGN_TYPE]);
        }

        return $instance;
    }

    /**
     * @return CampaignArray
     */
    public function toArray(): array
    {
        return [
            FlagshipField::FIELD_ID => $this->id,
            FlagshipField::FIELD_NANE => $this->name,
            FlagshipField::FIELD_SLUG => $this->slug,
            FlagshipField::FIELD_VARIATION_GROUP_ID => $this->variationGroupId,
            FlagshipField::FIELD_VARIATION_GROUP_NAME => $this->variationGroupName,
            FlagshipField::FIELD_VARIATION => $this->variation->toArray(),
            FlagshipField::FIELD_CAMPAIGN_TYPE => $this->type,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}

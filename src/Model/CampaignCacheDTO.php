<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;
use Flagship\Visitor\StrategyAbstract;

/**
 * @phpstan-import-type CampaignCacheArray from Types
 */
class CampaignCacheDTO
{
    private string $campaignId;

    private string $variationGroupId;

    private string $variationId;

    private ?string $type = null;

    private ?string $slug = null;

    private ?string $name = null;

    private ?bool $isReference = null;

    private ?bool $activated = null;

    /** @var ModificationsDTO */
    private ModificationsDTO $flags;

    public function __construct(
        string $campaignId,
        string $variationGroupId,
        string $variationId,
        ModificationsDTO $flags
    ) {
        $this->campaignId = $campaignId;
        $this->variationGroupId = $variationGroupId;
        $this->variationId = $variationId;
        $this->flags = $flags;
    }

    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    public function setCampaignId(string $campaignId): self
    {
        $this->campaignId = $campaignId;
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

    public function getVariationId(): string
    {
        return $this->variationId;
    }

    public function setVariationId(string $variationId): self
    {
        $this->variationId = $variationId;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
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

    public function getIsReference(): ?bool
    {
        return $this->isReference;
    }

    public function setIsReference(?bool $isReference): self
    {
        $this->isReference = $isReference;
        return $this;
    }

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(?bool $activated): self
    {
        $this->activated = $activated;
        return $this;
    }

    /**
     * @return  ModificationsDTO
     */
    public function getFlags(): ModificationsDTO
    {
        return $this->flags;
    }

    /**
     * @param ModificationsDTO $flags
     */
    public function setFlags(ModificationsDTO $flags): self
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $campaignId = $data[StrategyAbstract::CAMPAIGN_ID] ?? '';
        $variationGroupId = $data[StrategyAbstract::VARIATION_GROUP_ID] ?? '';
        $variationId = $data[StrategyAbstract::VARIATION_ID] ?? '';

        $flags = null;

        if (isset($data[FlagshipField::FIELD_FLAGS]) && is_array($data[FlagshipField::FIELD_FLAGS])) {
            $flags = ModificationsDTO::fromArray($data[FlagshipField::FIELD_FLAGS]);
        }

        $instance = new self(
            is_string($campaignId) ? $campaignId : '',
            is_string($variationGroupId) ? $variationGroupId : '',
            is_string($variationId) ? $variationId : '',
            $flags ?? new ModificationsDTO('', [])
        );

        if (isset($data[FlagshipField::FIELD_CAMPAIGN_TYPE]) && is_string($data[FlagshipField::FIELD_CAMPAIGN_TYPE])) {
            $instance->setType($data[FlagshipField::FIELD_CAMPAIGN_TYPE]);
        }

        if (isset($data[FlagshipField::FIELD_SLUG]) && is_string($data[FlagshipField::FIELD_SLUG])) {
            $instance->setSlug(FlagshipField::FIELD_SLUG);
        }

        if (isset($data[FlagshipField::FIELD_CAMPAIGN_NAME]) && is_string($data[FlagshipField::FIELD_CAMPAIGN_NAME])) {
            $instance->setName($data[FlagshipField::FIELD_CAMPAIGN_NAME]);
        }

        if (isset($data[FlagshipField::FIELD_IS_REFERENCE]) && is_bool($data[FlagshipField::FIELD_IS_REFERENCE])) {
            $instance->setIsReference($data[FlagshipField::FIELD_IS_REFERENCE]);
        }

        if (isset($data[FlagshipField::FIELD_ACTIVATED]) && is_bool($data[FlagshipField::FIELD_ACTIVATED])) {
            $instance->setActivated($data[FlagshipField::FIELD_ACTIVATED]);
        }



        return $instance;
    }

    /**
     * @return CampaignCacheArray
     */
    public function toArray(): array
    {
        $result = [
            StrategyAbstract::CAMPAIGN_ID => $this->campaignId,
            StrategyAbstract::VARIATION_GROUP_ID => $this->variationGroupId,
            StrategyAbstract::VARIATION_ID => $this->variationId,
            FlagshipField::FIELD_CAMPAIGN_TYPE => $this->type,
            FlagshipField::FIELD_FLAGS => $this->flags->toArray(),
        ];

        if ($this->slug !== null) {
            $result[FlagshipField::FIELD_SLUG] = $this->slug;
        }

        if ($this->name !== null) {
            $result[FlagshipField::FIELD_CAMPAIGN_NAME] = $this->name;
        }

        if ($this->isReference !== null) {
            $result[FlagshipField::FIELD_IS_REFERENCE] = $this->isReference;
        }

        if ($this->activated !== null) {
            $result[FlagshipField::FIELD_ACTIVATED] = $this->activated;
        }

        return $result;
    }
}

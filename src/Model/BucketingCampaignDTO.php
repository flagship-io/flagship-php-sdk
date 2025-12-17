<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type BucketingCampaignArray from Types
 */
class BucketingCampaignDTO
{
    private string $id;

    private ?string $name = null;

    private string $type;

    private ?string $slug = null;

    /** @var array<VariationGroupDTO> */
    private array $variationGroups;

    /**
     * @param string $id
     * @param string $type
     * @param array<VariationGroupDTO> $variationGroups
     */
    public function __construct(string $id, string $type, array $variationGroups)
    {
        $this->id = $id;
        $this->type = $type;
        $this->variationGroups = $variationGroups;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
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

    /**
     * @return array<VariationGroupDTO>
     */
    public function getVariationGroups(): array
    {
        return $this->variationGroups;
    }

    /**
     * @param array<VariationGroupDTO> $variationGroups
     */
    public function setVariationGroups(array $variationGroups): self
    {
        $this->variationGroups = $variationGroups;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        /**
         * @var array<string, array<string,mixed>>|null $variationGroupsData
         */
        $variationGroupsData = $data[FlagshipField::FIELD_VARIATION_GROUPS] ?? null;
        $variationGroups = [];

        if (is_array($variationGroupsData)) {
            $variationGroups = array_map(
                VariationGroupDTO::fromArray(...),
                $variationGroupsData
            );
        }

        $id = $data[FlagshipField::FIELD_ID] ?? '';
        $type = $data[FlagshipField::FIELD_CAMPAIGN_TYPE] ?? '';

        $instance = new self(
            is_string($id) ? $id : '',
            is_string($type) ? $type : '',
            $variationGroups
        );

        if (isset($data[FlagshipField::FIELD_NANE]) && is_string($data[FlagshipField::FIELD_NANE])) {
            $instance->setName($data[FlagshipField::FIELD_NANE]);
        }

        if (array_key_exists(FlagshipField::FIELD_SLUG, $data)) {
            $slug = $data[FlagshipField::FIELD_SLUG];
            $instance->setSlug(is_string($slug) ? $slug : null);
        }

        return $instance;
    }

    /**
     * @return BucketingCampaignArray
     */
    public function toArray(): array
    {
        $result = [
            FlagshipField::FIELD_ID => $this->id,
            FlagshipField::FIELD_CAMPAIGN_TYPE => $this->type,
            FlagshipField::FIELD_VARIATION_GROUPS => array_map(
                fn(VariationGroupDTO $group) => $group->toArray(),
                $this->variationGroups
            ),
        ];

        if ($this->name !== null) {
            $result[FlagshipField::FIELD_NANE] = $this->name;
        }

        if ($this->slug !== null) {
            $result[FlagshipField::FIELD_SLUG] = $this->slug;
        }

        return $result;
    }
}

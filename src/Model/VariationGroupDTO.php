<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type VariationGroupArray from Types
 */
class VariationGroupDTO
{
    private string $id;

    private ?string $name = null;

    private TargetingDTO $targeting;

    /** @var array<BucketingVariationDTO> */
    private array $variations;

    /**
     * @param string $id
     * @param TargetingDTO $targeting
     * @param array<BucketingVariationDTO> $variations
     */
    public function __construct(string $id, TargetingDTO $targeting, array $variations)
    {
        $this->id = $id;
        $this->targeting = $targeting;
        $this->variations = $variations;
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

    public function getTargeting(): TargetingDTO
    {
        return $this->targeting;
    }

    public function setTargeting(TargetingDTO $targeting): self
    {
        $this->targeting = $targeting;
        return $this;
    }

    /**
     * @return array<BucketingVariationDTO>
     */
    public function getVariations(): array
    {
        return $this->variations;
    }

    /**
     * @param array<BucketingVariationDTO> $variations
     */
    public function setVariations(array $variations): self
    {
        $this->variations = $variations;
        return $this;
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        /**
         * @var array<string, mixed>| null $targetingData 
         */
        $targetingData = $data[FlagshipField::FIELD_TARGETING] ?? null;

        $targeting = is_array($targetingData)
            ? TargetingDTO::fromArray($targetingData)
            : new TargetingDTO([]);

        /**
         * @var array<array<string, mixed>>|null $variationsData
         */
        $variationsData = $data[FlagshipField::FIELD_VARIATIONS] ?? null;
        $variations = [];
        if (is_array($variationsData)) {
            $variations = array_map(
                BucketingVariationDTO::fromArray(...),
                $variationsData
            );
        }

        $id = $data[FlagshipField::FIELD_ID] ?? '';
        $instance = new self(
            is_string($id) ? $id : '',
            $targeting,
            $variations
        );

        if (isset($data[FlagshipField::FIELD_NANE]) && is_string($data[FlagshipField::FIELD_NANE])) {
            $instance->setName($data[FlagshipField::FIELD_NANE]);
        }

        return $instance;
    }

    /**
     * @return VariationGroupArray
     */
    public function toArray(): array
    {
        $result = [
            FlagshipField::FIELD_ID => $this->id,
            FlagshipField::FIELD_TARGETING => $this->targeting->toArray(),
            FlagshipField::FIELD_VARIATIONS => array_map(
                fn(BucketingVariationDTO $variation) => $variation->toArray(),
                $this->variations
            ),
        ];

        if ($this->name !== null) {
            $result[FlagshipField::FIELD_NANE] = $this->name;
        }

        return $result;
    }
}

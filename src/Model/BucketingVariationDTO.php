<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type BucketingVariationArray from Types
 */
class BucketingVariationDTO extends VariationDTO
{
    private ?float $allocation = null;

    public function getAllocation(): ?float
    {
        return $this->allocation;
    }

    public function setAllocation(?float $allocation): self
    {
        $this->allocation = $allocation;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {

        $modificationsData = $data[FlagshipField::FIELD_MODIFICATIONS] ?? null;
        $modifications = is_array($modificationsData)
            ? ModificationsDTO::fromArray($modificationsData)
            : new ModificationsDTO('', []);

        $id = $data[FlagshipField::FIELD_ID] ?? '';

        $instance = new self(is_string($id) ? $id : '', $modifications);

        if (isset($data[FlagshipField::FIELD_NANE]) && is_string($data[FlagshipField::FIELD_NANE])) {
            $instance->setName($data[FlagshipField::FIELD_NANE]);
        }

        if (isset($data[FlagshipField::FIELD_REFERENCE]) && is_bool($data[FlagshipField::FIELD_REFERENCE])) {
            $instance->setReference($data[FlagshipField::FIELD_REFERENCE]);
        }

        if (isset($data[FlagshipField::FIELD_ALLOCATION])) {
            $allocation = $data[FlagshipField::FIELD_ALLOCATION];
            $instance->setAllocation(is_numeric($allocation) ? (float)$allocation : null);
        }

        return $instance;
    }

    /**
     * @return BucketingVariationArray
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        $result[FlagshipField::FIELD_ALLOCATION] = $this->allocation;

        return $result;
    }
}

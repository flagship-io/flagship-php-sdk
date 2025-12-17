<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type ModificationsArray from Types
 * @phpstan-import-type FlagValue from Types
 */
class ModificationsDTO
{
    private string $type;

    /**
     * 
     * @var FlagValue
     */
    private array $value;

    /**
     * 
     * @param string $type
     * @param FlagValue $value
     */
    public function __construct(string $type, array $value)
    {
        $this->type = $type;
        $this->value = $value;
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

    /**
     * 
     * @return FlagValue
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * 
     * @param FlagValue $value
     * @return self
     */
    public function setValue(array $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $type = $data[FlagshipField::FIELD_CAMPAIGN_TYPE] ?? '';

        /** @var FlagValue|null $value */
        $value = $data[FlagshipField::FIELD_VALUE] ?? null;

        return new self(
            is_string($type) ? $type : '',
            is_array($value) ? $value : []
        );
    }

    /**
     * @return ModificationsArray
     */
    public function toArray(): array
    {
        return [
            FlagshipField::FIELD_CAMPAIGN_TYPE => $this->type,
            FlagshipField::FIELD_VALUE => $this->value,
        ];
    }
}

<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type VariationArray from Types
 */
class VariationDTO
{
    private string $id;

    private ?string $name = null;

    private ?bool $reference = null;

    private ModificationsDTO $modifications;

    public function __construct(string $id, ModificationsDTO $modifications)
    {
        $this->id = $id;
        $this->modifications = $modifications;
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

    public function getReference(): ?bool
    {
        return $this->reference;
    }

    public function setReference(?bool $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getModifications(): ModificationsDTO
    {
        return $this->modifications;
    }

    public function setModifications(ModificationsDTO $modifications): self
    {
        $this->modifications = $modifications;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $modificationsData = $data[FlagshipField::FIELD_MODIFICATIONS]?? null;
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

        return $instance;
    }

    /**
     * @return VariationArray
     */
    public function toArray(): array
    {
        return [
            FlagshipField::FIELD_ID => $this->id,
            FlagshipField::FIELD_NANE => $this->name,
            FlagshipField::FIELD_REFERENCE => $this->reference,
            FlagshipField::FIELD_MODIFICATIONS => $this->modifications->toArray(),
        ];
    }
}

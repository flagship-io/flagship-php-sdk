<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type TargetingGroupArray from Types
 */
class TargetingGroupDTO
{
    /** @var array<TargetingsDTO> */
    private array $targetings;

    /**
     * @param array<TargetingsDTO> $targetings
     */
    public function __construct(array $targetings)
    {
        $this->targetings = $targetings;
    }

    /**
     * @return array<TargetingsDTO>
     */
    public function getTargetings(): array
    {
        return $this->targetings;
    }

    /**
     * @param array<TargetingsDTO> $targetings
     */
    public function setTargetings(array $targetings): self
    {
        $this->targetings = $targetings;
        return $this;
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        /**
         * @var array<array<string, mixed>> | null $targetingsData
         */
        $targetingsData = $data[FlagshipField::FIELD_TARGETINGS] ?? [];
        $targetings = [];

        if (is_array($targetingsData)) {
            $targetings = array_map(
                fn($targeting) => TargetingsDTO::fromArray($targeting),
                $targetingsData
            );
        }

        return new self($targetings);
    }

    /**
     * @return TargetingGroupArray
     */
    public function toArray(): array
    {
        return [
            FlagshipField::FIELD_TARGETINGS => array_map(
                fn(TargetingsDTO $targeting) => $targeting->toArray(),
                $this->targetings
            ),
        ];
    }
}

<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type TargetingArray from Types
 */
class TargetingDTO
{
    /** @var array<TargetingGroupDTO> */
    private array $targetingGroups;

    /**
     * @param array<TargetingGroupDTO> $targetingGroups
     */
    public function __construct(array $targetingGroups)
    {
        $this->targetingGroups = $targetingGroups;
    }

    /**
     * @return array<TargetingGroupDTO>
     */
    public function getTargetingGroups(): array
    {
        return $this->targetingGroups;
    }

    /**
     * @param array<TargetingGroupDTO> $targetingGroups
     */
    public function setTargetingGroups(array $targetingGroups): self
    {
        $this->targetingGroups = $targetingGroups;
        return $this;
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        /**
         * @var array<array<string, mixed>> | null $targetingGroupsData
         */
        $targetingGroupsData = $data[FlagshipField::FIELD_TARGETING_GROUPS] ?? [];

        $targetingGroups = [];

        if (is_array($targetingGroupsData)) {
            $targetingGroups = array_map(
                TargetingGroupDTO::fromArray(...),
                $targetingGroupsData
            );
        }

        return new self(array_values($targetingGroups));
    }

    /**
     * @return TargetingArray
     */
    public function toArray(): array
    {
        return [
            FlagshipField::FIELD_TARGETING_GROUPS => array_map(
                fn(TargetingGroupDTO $group) => $group->toArray(),
                $this->targetingGroups
            ),
        ];
    }
}

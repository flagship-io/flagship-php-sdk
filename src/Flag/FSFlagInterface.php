<?php

namespace Flagship\Flag;

use Flagship\Enum\FSFlagStatus;


interface FSFlagInterface
{
    /**
     * Returns the value from the assigned campaign variation or the Flag default value if the Flag does not exist,
     * or if types are different.
     * @phpstan-template T of scalar|array<mixed>|null  
     * @param T $defaultValue
     * @param boolean $visitorExposed
     * @return T
     */
    public function getValue(
        float|array|bool|int|string|null $defaultValue,
        bool $visitorExposed = true
    ): float|array|bool|int|string|null;

    /**
     * This method will return true if a Flag exists in Flagship.
     * @return bool
     */
    public function exists(): bool;

    /**
     * Tells Flagship the visitor have been exposed and have seen this flag.
     * @return void
     */
    public function visitorExposed(): void;

    /**
     * Returns the metadata of the flag.
     * @return FSFlagMetadataInterface
     */
    public function getMetadata(): FSFlagMetadataInterface;

    /**
     * Returns the status of the flag.
     * @return FSFlagStatus
     */
    public function getStatus(): FSFlagStatus;
}

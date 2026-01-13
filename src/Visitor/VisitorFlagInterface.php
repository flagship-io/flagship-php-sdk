<?php

namespace Flagship\Visitor;

use Flagship\Flag\FSFlagMetadata;
use Flagship\Model\FlagDTO;

interface VisitorFlagInterface
{
    /**
     * Returns the value from the assigned campaign variation or the Flag default value if the Flag does not exist,
     * or if types are different.
     * @param string $key
     * @param mixed[]|bool|string|numeric $defaultValue
     * @param FlagDTO|null $flag
     * @param bool $hasGetValueBeenCalled
     * @return void
     */
    public function visitorExposed(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        ?FlagDTO $flag = null,
        bool $hasGetValueBeenCalled = false
    ): void;

    /**
     * 
     * @phpstan-template T of scalar|array<mixed>|null
     * @param string $key
     * @param T $defaultValue
     * @param FlagDTO|null $flag
     * @param bool $userExposed
     * @return T
     */
    public function getFlagValue(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        ?FlagDTO $flag = null,
        bool $userExposed = true
    ): float|array|bool|int|string|null;

    /**
     * @param string $key
     * @param FlagDTO|null $flag
     * @return FSFlagMetadata
     */
    public function getFlagMetadata(string $key, ?FlagDTO $flag = null): FSFlagMetadata;
}

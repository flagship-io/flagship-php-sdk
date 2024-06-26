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
     * @param array|bool|string|numeric $defaultValue
     * @param FlagDTO|null $flag
     * @param bool $hasGetValueBeenCalled
     * @return void
     */
    public function visitorExposed(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO $flag = null,
        bool $hasGetValueBeenCalled = false
    ): void;

    /**
     * @param string $key
     * @param array|bool|string|numeric $defaultValue
     * @param FlagDTO|null $flag
     * @param bool $userExposed
     * @return float|array|bool|int|string|null
     */
    public function getFlagValue(
        string $key,
        float|array|bool|int|string|null $defaultValue,
        FlagDTO $flag = null,
        bool $userExposed = true
    ): float|array|bool|int|string|null;

    /**
     * @param string $key
     * @param FlagDTO|null $flag
     * @return FSFlagMetadata
     */
    public function getFlagMetadata(string $key, FlagDTO $flag = null): FSFlagMetadata;
}

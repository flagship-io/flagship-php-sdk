<?php

namespace Flagship\Visitor;

use Flagship\Flag\FSFlagMetadata;
use Flagship\Model\FlagDTO;

interface VisitorFlagInterface
{

    /**
     * Returns the value from the assigned campaign variation or the Flag default value if the Flag does not exist,
     * or if types are different.
     * @param string|numeric|bool|array $defaultValue
     * @param FlagDTO $flag
     * @param bool $hasGetValueBeenCalled
     * @return string|numeric|bool|array
     */
    public function visitorExposed($key, $defaultValue, FlagDTO $flag = null, $hasGetValueBeenCalled = false);

    /**
     * @param string $key
     * @param string|numeric|bool|array $defaultValue
     * @param FlagDTO $flag
     * @param bool $userExposed
     * @return string|numeric|bool|array
     */
    public function getFlagValue($key, $defaultValue, FlagDTO $flag = null, $userExposed = true);

    /**
     * @param string $key
     * @param FlagDTO $flag
     * @return FSFlagMetadata
     */
    public function getFlagMetadata($key, FlagDTO $flag = null);
}

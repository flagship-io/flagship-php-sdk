<?php

namespace Flagship\Visitor;

use Flagship\Flag\FlagMetadata;
use Flagship\Model\FlagDTO;

interface VisitorFlagInterface
{
    /**
     * @param string $key
     * @param FlagDTO $flag
     * @param bool $hasSameType
     * @return void
     */
    public function userExposed($key, FlagDTO $flag, $hasSameType);

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
     * @param FlagMetadata $metadata
     * @param bool $hasSameType
     * @return FlagMetadata
     */
    public function getFlagMetadata($key, FlagMetadata $metadata, $hasSameType);
}

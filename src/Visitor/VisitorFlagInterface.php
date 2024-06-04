<?php

namespace Flagship\Visitor;

use Flagship\Flag\FSFlagMetadata;
use Flagship\Model\FlagDTO;

interface VisitorFlagInterface
{
    /**
     * @param string $key
     * @param mixed $defaultValue
     * @param FlagDTO $flag
     * @return void
     */
    public function visitorExposed($key, $defaultValue, FlagDTO $flag = null);

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
     * @param FSFlagMetadata $metadata
     * @param bool $hasSameType
     * @return FSFlagMetadata
     */
    public function getFlagMetadata($key, FSFlagMetadata $metadata, $hasSameType);
}

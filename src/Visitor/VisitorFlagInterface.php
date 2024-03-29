<?php

namespace Flagship\Visitor;

use Flagship\Flag\FlagMetadata;
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
     * @param FlagMetadata $metadata
     * @param bool $hasSameType
     * @return FlagMetadata
     */
    public function getFlagMetadata($key, FlagMetadata $metadata, $hasSameType);
}

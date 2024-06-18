<?php

namespace Flagship\Model;

use Flagship\Flag\FSFlagMetadataInterface;

interface ExposedFlagInterface
{
    /**
     * Return the key of flag
     * @return string
     */
    public function getKey(): string;

    /**
     * Return the value of flag
     * @return float|array|bool|int|string|null
     */
    public function getValue(): float|array|bool|int|string|null;

    /**
     * Return the metadata of flag
     * @return FSFlagMetadataInterface
     */
    public function getMetadata(): FSFlagMetadataInterface;

    /**
     * Return the default value of flag
     * @return float|array|bool|int|string|null
     */
    public function getDefaultValue(): float|array|bool|int|string|null;
}

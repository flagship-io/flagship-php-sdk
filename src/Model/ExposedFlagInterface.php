<?php

namespace Flagship\Model;

use Flagship\Flag\FlagMetadataInterface;

interface ExposedFlagInterface
{
    /**
     * Return the key of flag
     * @return string
     */
    public function getKey();

    /**
     * Return the value of flag
     * @return bool|numeric|string|array
     */
    public function getValue();

    /**
     * Return the metadata of flag
     * @return FlagMetadataInterface
     */
    public function getMetadata();

    /**
     * Return the default value of flag
     * @return bool|numeric|string|array
     */
    public function getDefaultValue();
}
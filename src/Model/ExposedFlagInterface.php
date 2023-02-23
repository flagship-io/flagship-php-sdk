<?php

namespace Flagship\Model;

use Flagship\Flag\FlagMetadataInterface;

interface ExposedFlagInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return bool|numeric|string|array
     */
    public function getValue();

    /**
     * @return FlagMetadataInterface
     */
    public function getMetadata();
}
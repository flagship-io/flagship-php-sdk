<?php

namespace Flagship\Model;

use Flagship\Flag\FlagMetadataInterface;

class ExposedFlag implements ExposedFlagInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var bool|numeric|string|array
     */
    private $value;


    /**
     * @var FlagMetadataInterface
     */
    private $metadata;

    /**
     * @param string $key
     * @param mixed $value
     * @param FlagMetadataInterface $metadata
     */
    public function __construct($key, $value, FlagMetadataInterface $metadata)
    {
        $this->key = $key;
        $this->value = $value;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return bool|numeric|string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return FlagMetadataInterface
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
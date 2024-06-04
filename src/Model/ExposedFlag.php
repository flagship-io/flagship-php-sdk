<?php

namespace Flagship\Model;

use Flagship\Flag\FSFlagMetadataInterface;

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
     * @var FSFlagMetadataInterface
     */
    private $metadata;

    /**
     * @var bool|numeric|string|array
     */
    private $defaultValue;

    /**
     * @param string $key
     * @param bool|numeric|string|array $value
     * @param bool|numeric|string|array $defaultValue
     * @param FSFlagMetadataInterface $metadata
     */
    public function __construct($key, $value, $defaultValue, FSFlagMetadataInterface $metadata)
    {
        $this->key = $key;
        $this->value = $value;
        $this->metadata = $metadata;
        $this->defaultValue = $defaultValue;
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
     * @return FSFlagMetadataInterface
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}

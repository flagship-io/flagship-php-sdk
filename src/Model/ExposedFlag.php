<?php

namespace Flagship\Model;

use Flagship\Flag\FSFlagMetadataInterface;

class ExposedFlag implements ExposedFlagInterface
{
    /**
     * @var string
     */
    private string $key;

    /**
     * @var bool|numeric|string|array
     */
    private string|array|bool|int|float $value;

    /**
     * @var FSFlagMetadataInterface
     */
    private FSFlagMetadataInterface $metadata;

    /**
     * @var bool|numeric|string|array
     */
    private string|array|bool|int|float $defaultValue;

    /**
     * @param string $key
     * @param array|bool|string|numeric $value
     * @param array|bool|string|numeric $defaultValue
     * @param FSFlagMetadataInterface $metadata
     */
    public function __construct(
        string $key,
        float|array|bool|int|string $value,
        float|array|bool|int|string $defaultValue,
        FSFlagMetadataInterface $metadata
    ) {
        $this->key = $key;
        $this->value = $value;
        $this->metadata = $metadata;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return bool|numeric|string|array
     */
    public function getValue(): float|int|bool|array|string
    {
        return $this->value;
    }

    /**
     * @return FSFlagMetadataInterface
     */
    public function getMetadata(): FSFlagMetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultValue(): float|array|bool|int|string
    {
        return $this->defaultValue;
    }
}

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
     * @var scalar|array<mixed>|null
     */
    private string|array|bool|int|float|null $value;

    /**
     * @var FSFlagMetadataInterface
     */
    private FSFlagMetadataInterface $metadata;

    /**
     * @var scalar|array<mixed>|null
     */
    private string|array|bool|int|float|null $defaultValue;

    /**
     * @param string $key
     * @param scalar|array<mixed>|null $value
     * @param scalar|array<mixed>|null $defaultValue
     * @param FSFlagMetadataInterface $metadata
     */
    public function __construct(
        string $key,
        float|array|bool|int|string|null $value,
        float|array|bool|int|string|null $defaultValue,
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
     * @inheritDoc
     */
    public function getValue(): float|int|bool|array|string|null
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
    public function getDefaultValue(): float|array|bool|int|string|null
    {
        return $this->defaultValue;
    }
}

<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;
use Flagship\Enum\TargetingOperator;

/**
 * @phpstan-import-type TargetingsArray from Types
 */
class TargetingsDTO
{
    private TargetingOperator $operator;
    private string $key;
    /**
     * 
     * @var mixed
     */
    private mixed $value;

    public function getOperator(): TargetingOperator
    {
        return $this->operator;
    }

    public function setOperator(TargetingOperator $operator): self
    {
        $this->operator = $operator;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * 
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * 
     * @param TargetingOperator $operator
     * @param string $key
     * @param mixed $value
     */
    public function __construct(TargetingOperator $operator, string $key, mixed $value)
    {
        $this->operator = $operator;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $operatorValue = $data[FlagshipField::FIELD_OPERATOR] ?? '';
        $operator = is_string($operatorValue)
            ? (TargetingOperator::tryFrom($operatorValue) ?? TargetingOperator::EQUALS)
            : TargetingOperator::EQUALS;

        $key = $data[FlagshipField::FIELD_KEY] ?? '';

        /** @var mixed|null $value */
        $value = $data[FlagshipField::FIELD_VALUE] ?? null;

        return new self(
            $operator,
            is_string($key) ? $key : '',
            $value,
        );
    }

    /**
     * @return TargetingsArray
     */
    public function toArray(): array
    {
        return [
            FlagshipField::FIELD_OPERATOR => $this->operator->value,
            FlagshipField::FIELD_KEY => $this->key,
            FlagshipField::FIELD_VALUE => $this->value,
        ];
    }
}

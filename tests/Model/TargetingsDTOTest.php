<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\TargetingOperator;

class TargetingsDTOTest extends TestCase
{
    public function testConstructor()
    {
        $operator = TargetingOperator::EQUALS;
        $key = 'browser';
        $value = 'chrome';
        $dto = new TargetingsDTO($operator, $key, $value);

        $this->assertSame($operator, $dto->getOperator());
        $this->assertSame($key, $dto->getKey());
        $this->assertSame($value, $dto->getValue());
    }

    public function testGettersAndSetters()
    {
        $dto = new TargetingsDTO(TargetingOperator::EQUALS, 'key1', 'value1');

        $instance1 = $dto->setOperator(TargetingOperator::NOT_EQUALS);
        $this->assertSame(TargetingOperator::NOT_EQUALS, $dto->getOperator());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setKey('newKey');
        $this->assertSame('newKey', $dto->getKey());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setValue(['array', 'value']);
        $this->assertSame(['array', 'value'], $dto->getValue());
        $this->assertSame($dto, $instance3);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_OPERATOR => 'EQUALS',
            FlagshipField::FIELD_KEY => 'country',
            FlagshipField::FIELD_VALUE => 'US'
        ];

        $dto = TargetingsDTO::fromArray($data);

        $this->assertSame(TargetingOperator::EQUALS, $dto->getOperator());
        $this->assertSame('country', $dto->getKey());
        $this->assertSame('US', $dto->getValue());
    }

    public function testFromArrayWithInvalidOperator()
    {
        $data = [
            FlagshipField::FIELD_OPERATOR => 'INVALID_OPERATOR',
            FlagshipField::FIELD_KEY => 'key',
            FlagshipField::FIELD_VALUE => 'value'
        ];

        $dto = TargetingsDTO::fromArray($data);

        $this->assertSame(TargetingOperator::EQUALS, $dto->getOperator());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = TargetingsDTO::fromArray([]);

        $this->assertSame(TargetingOperator::EQUALS, $dto->getOperator());
        $this->assertSame('', $dto->getKey());
        $this->assertNull($dto->getValue());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            FlagshipField::FIELD_OPERATOR => 123,
            FlagshipField::FIELD_KEY => [],
            FlagshipField::FIELD_VALUE => 'any value'
        ];

        $dto = TargetingsDTO::fromArray($data);

        $this->assertSame(TargetingOperator::EQUALS, $dto->getOperator());
        $this->assertSame('', $dto->getKey());
        $this->assertSame('any value', $dto->getValue());
    }

    public function testToArray()
    {
        $dto = new TargetingsDTO(TargetingOperator::CONTAINS, 'tags', ['premium', 'vip']);

        $array = $dto->toArray();

        $this->assertSame('CONTAINS', $array[FlagshipField::FIELD_OPERATOR]);
        $this->assertSame('tags', $array[FlagshipField::FIELD_KEY]);
        $this->assertSame(['premium', 'vip'], $array[FlagshipField::FIELD_VALUE]);
    }

    public function testWithDifferentOperators()
    {
        $operators = [
            TargetingOperator::EQUALS,
            TargetingOperator::NOT_EQUALS,
            TargetingOperator::CONTAINS,
        ];

        foreach ($operators as $operator) {
            $dto = new TargetingsDTO($operator, 'key', 'value');
            $this->assertSame($operator, $dto->getOperator());

            $array = $dto->toArray();
            $this->assertSame($operator->value, $array[FlagshipField::FIELD_OPERATOR]);
        }
    }

    public function testWithDifferentValueTypes()
    {
        $values = [
            'string value',
            123,
            45.67,
            true,
            ['array', 'value'],
            null
        ];

        foreach ($values as $value) {
            $dto = new TargetingsDTO(TargetingOperator::EQUALS, 'key', $value);
            $this->assertSame($value, $dto->getValue());
        }
    }
}

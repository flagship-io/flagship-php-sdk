<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class ModificationsDTOTest extends TestCase
{
    public function testConstructor()
    {
        $type = 'ab';
        $value = ['key1' => 'value1', 'key2' => 123];
        $dto = new ModificationsDTO($type, $value);

        $this->assertSame($type, $dto->getType());
        $this->assertSame($value, $dto->getValue());
    }

    public function testGettersAndSetters()
    {
        $dto = new ModificationsDTO('ab', []);

        $instance1 = $dto->setType('toggle');
        $this->assertSame('toggle', $dto->getType());
        $this->assertSame($dto, $instance1);

        $value = ['key' => 'value', 'number' => 42];
        $instance2 = $dto->setValue($value);
        $this->assertSame($value, $dto->getValue());
        $this->assertSame($dto, $instance2);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
            FlagshipField::FIELD_VALUE => ['key1' => 'value1', 'key2' => true]
        ];

        $dto = ModificationsDTO::fromArray($data);

        $this->assertSame('ab', $dto->getType());
        $this->assertSame(['key1' => 'value1', 'key2' => true], $dto->getValue());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = ModificationsDTO::fromArray([]);

        $this->assertSame('', $dto->getType());
        $this->assertSame([], $dto->getValue());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            FlagshipField::FIELD_CAMPAIGN_TYPE => 123,
            FlagshipField::FIELD_VALUE => 'not an array'
        ];

        $dto = ModificationsDTO::fromArray($data);

        $this->assertSame('', $dto->getType());
        $this->assertSame([], $dto->getValue());
    }

    public function testToArray()
    {
        $type = 'toggle';
        $value = ['feature1' => true, 'feature2' => false];
        $dto = new ModificationsDTO($type, $value);

        $array = $dto->toArray();

        $this->assertSame($type, $array[FlagshipField::FIELD_CAMPAIGN_TYPE]);
        $this->assertSame($value, $array[FlagshipField::FIELD_VALUE]);
    }

    public function testToArrayWithEmptyValue()
    {
        $dto = new ModificationsDTO('ab', []);
        $array = $dto->toArray();

        $this->assertSame('ab', $array[FlagshipField::FIELD_CAMPAIGN_TYPE]);
        $this->assertSame([], $array[FlagshipField::FIELD_VALUE]);
    }
}

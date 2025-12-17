<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class VariationDTOTest extends TestCase
{
    public function testConstructor()
    {
        $modifications = new ModificationsDTO('ab', ['key1' => 'value1']);
        $dto = new VariationDTO('var123', $modifications);

        $this->assertSame('var123', $dto->getId());
        $this->assertSame($modifications, $dto->getModifications());
    }

    public function testGettersAndSetters()
    {
        $modifications = new ModificationsDTO('ab', []);
        $dto = new VariationDTO('var1', $modifications);

        $instance1 = $dto->setId('var2');
        $this->assertSame('var2', $dto->getId());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setName('Variation Name');
        $this->assertSame('Variation Name', $dto->getName());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setReference(true);
        $this->assertTrue($dto->getReference());
        $this->assertSame($dto, $instance3);

        $newModifications = new ModificationsDTO('toggle', ['key' => 'value']);
        $instance4 = $dto->setModifications($newModifications);
        $this->assertSame($newModifications, $dto->getModifications());
        $this->assertSame($dto, $instance4);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_ID => 'var123',
            FlagshipField::FIELD_NANE => 'Control',
            FlagshipField::FIELD_REFERENCE => true,
            FlagshipField::FIELD_MODIFICATIONS => [
                FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                FlagshipField::FIELD_VALUE => ['feature1' => true]
            ]
        ];

        $dto = VariationDTO::fromArray($data);

        $this->assertSame('var123', $dto->getId());
        $this->assertSame('Control', $dto->getName());
        $this->assertTrue($dto->getReference());
        $this->assertInstanceOf(ModificationsDTO::class, $dto->getModifications());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = VariationDTO::fromArray([]);

        $this->assertSame('', $dto->getId());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getReference());
        $this->assertInstanceOf(ModificationsDTO::class, $dto->getModifications());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            FlagshipField::FIELD_ID => 123,
            FlagshipField::FIELD_NANE => [],
            FlagshipField::FIELD_REFERENCE => 'true',
            FlagshipField::FIELD_MODIFICATIONS => 'not an array'
        ];

        $dto = VariationDTO::fromArray($data);

        $this->assertSame('', $dto->getId());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getReference());
    }

    public function testToArray()
    {
        $modifications = new ModificationsDTO('ab', ['key1' => 'value1']);
        $dto = new VariationDTO('var123', $modifications);
        $dto->setName('Variation A');
        $dto->setReference(false);

        $array = $dto->toArray();

        $this->assertSame('var123', $array[FlagshipField::FIELD_ID]);
        $this->assertSame('Variation A', $array[FlagshipField::FIELD_NANE]);
        $this->assertFalse($array[FlagshipField::FIELD_REFERENCE]);
        $this->assertIsArray($array[FlagshipField::FIELD_MODIFICATIONS]);
    }

    public function testToArrayWithNullValues()
    {
        $modifications = new ModificationsDTO('ab', []);
        $dto = new VariationDTO('var123', $modifications);

        $array = $dto->toArray();

        $this->assertSame('var123', $array[FlagshipField::FIELD_ID]);
        $this->assertArrayHasKey(FlagshipField::FIELD_MODIFICATIONS, $array);
    }
}

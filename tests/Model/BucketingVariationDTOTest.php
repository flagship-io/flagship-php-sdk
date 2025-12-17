<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class BucketingVariationDTOTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $modifications = new ModificationsDTO('ab', ['key1' => 'value1']);
        $dto = new BucketingVariationDTO('var123', $modifications);

        $this->assertNull($dto->getAllocation());

        $instance = $dto->setAllocation(50.5);
        $this->assertSame(50.5, $dto->getAllocation());
        $this->assertSame($dto, $instance);

        $dto->setAllocation(null);
        $this->assertNull($dto->getAllocation());
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_ID => 'var123',
            FlagshipField::FIELD_NANE => 'Variation Name',
            FlagshipField::FIELD_REFERENCE => true,
            FlagshipField::FIELD_ALLOCATION => 75.5,
            FlagshipField::FIELD_MODIFICATIONS => [
                FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                FlagshipField::FIELD_VALUE => ['key1' => 'value1']
            ]
        ];

        $dto = BucketingVariationDTO::fromArray($data);

        $this->assertSame('var123', $dto->getId());
        $this->assertSame('Variation Name', $dto->getName());
        $this->assertTrue($dto->getReference());
        $this->assertSame(75.5, $dto->getAllocation());
        $this->assertInstanceOf(ModificationsDTO::class, $dto->getModifications());
    }

    public function testFromArrayWithStringAllocation()
    {
        $data = [
            FlagshipField::FIELD_ID => 'var123',
            FlagshipField::FIELD_ALLOCATION => '50',
            FlagshipField::FIELD_MODIFICATIONS => [
                FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                FlagshipField::FIELD_VALUE => []
            ]
        ];

        $dto = BucketingVariationDTO::fromArray($data);
        $this->assertSame(50.0, $dto->getAllocation());
    }

    public function testFromArrayWithInvalidAllocation()
    {
        $data = [
            FlagshipField::FIELD_ID => 'var123',
            FlagshipField::FIELD_ALLOCATION => 'invalid',
            FlagshipField::FIELD_MODIFICATIONS => [
                FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                FlagshipField::FIELD_VALUE => []
            ]
        ];

        $dto = BucketingVariationDTO::fromArray($data);
        $this->assertNull($dto->getAllocation());
    }

    public function testToArray()
    {
        $modifications = new ModificationsDTO('ab', ['key1' => 'value1']);
        $dto = new BucketingVariationDTO('var123', $modifications);
        $dto->setName('Variation Name');
        $dto->setReference(false);
        $dto->setAllocation(25.0);

        $array = $dto->toArray();

        $this->assertSame('var123', $array[FlagshipField::FIELD_ID]);
        $this->assertSame(25.0, $array[FlagshipField::FIELD_ALLOCATION]);
        $this->assertIsArray($array[FlagshipField::FIELD_MODIFICATIONS]);
    }

    public function testInheritsFromVariationDTO()
    {
        $modifications = new ModificationsDTO('ab', []);
        $dto = new BucketingVariationDTO('var123', $modifications);

        $this->assertInstanceOf(VariationDTO::class, $dto);
    }
}

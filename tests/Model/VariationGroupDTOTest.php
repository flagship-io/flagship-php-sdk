<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class VariationGroupDTOTest extends TestCase
{
    public function testConstructor()
    {
        $targeting = new TargetingDTO([]);
        $variations = [new BucketingVariationDTO('v1', new ModificationsDTO('ab', []))];
        $dto = new VariationGroupDTO('vg123', $targeting, $variations);

        $this->assertSame('vg123', $dto->getId());
        $this->assertSame($targeting, $dto->getTargeting());
        $this->assertSame($variations, $dto->getVariations());
    }

    public function testGettersAndSetters()
    {
        $targeting = new TargetingDTO([]);
        $dto = new VariationGroupDTO('vg1', $targeting, []);

        $instance1 = $dto->setId('vg2');
        $this->assertSame('vg2', $dto->getId());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setName('VG Name');
        $this->assertSame('VG Name', $dto->getName());
        $this->assertSame($dto, $instance2);

        $newTargeting = new TargetingDTO([new TargetingGroupDTO([])]);
        $instance3 = $dto->setTargeting($newTargeting);
        $this->assertSame($newTargeting, $dto->getTargeting());
        $this->assertSame($dto, $instance3);

        $variations = [
            new BucketingVariationDTO('v1', new ModificationsDTO('ab', [])),
            new BucketingVariationDTO('v2', new ModificationsDTO('ab', []))
        ];
        $instance4 = $dto->setVariations($variations);
        $this->assertSame($variations, $dto->getVariations());
        $this->assertSame($dto, $instance4);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_ID => 'vg123',
            FlagshipField::FIELD_NANE => 'Group Name',
            FlagshipField::FIELD_TARGETING => [
                FlagshipField::FIELD_TARGETING_GROUPS => []
            ],
            FlagshipField::FIELD_VARIATIONS => [
                [
                    FlagshipField::FIELD_ID => 'v1',
                    FlagshipField::FIELD_ALLOCATION => 50.0,
                    FlagshipField::FIELD_MODIFICATIONS => [
                        FlagshipField::FIELD_CAMPAIGN_TYPE => 'ab',
                        FlagshipField::FIELD_VALUE => []
                    ]
                ]
            ]
        ];

        $dto = VariationGroupDTO::fromArray($data);

        $this->assertSame('vg123', $dto->getId());
        $this->assertSame('Group Name', $dto->getName());
        $this->assertInstanceOf(TargetingDTO::class, $dto->getTargeting());
        $this->assertIsArray($dto->getVariations());
        $this->assertCount(1, $dto->getVariations());
        $this->assertContainsOnlyInstancesOf(BucketingVariationDTO::class, $dto->getVariations());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = VariationGroupDTO::fromArray([]);

        $this->assertSame('', $dto->getId());
        $this->assertNull($dto->getName());
        $this->assertInstanceOf(TargetingDTO::class, $dto->getTargeting());
        $this->assertIsArray($dto->getVariations());
        $this->assertEmpty($dto->getVariations());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            FlagshipField::FIELD_ID => 123,
            FlagshipField::FIELD_NANE => [],
            FlagshipField::FIELD_TARGETING => 'not an array',
            FlagshipField::FIELD_VARIATIONS => 'not an array'
        ];

        $dto = VariationGroupDTO::fromArray($data);

        $this->assertSame('', $dto->getId());
        $this->assertNull($dto->getName());
        $this->assertInstanceOf(TargetingDTO::class, $dto->getTargeting());
    }

    public function testToArray()
    {
        $targeting = new TargetingDTO([]);
        $variations = [
            new BucketingVariationDTO('v1', new ModificationsDTO('ab', [])),
            new BucketingVariationDTO('v2', new ModificationsDTO('ab', []))
        ];
        $dto = new VariationGroupDTO('vg123', $targeting, $variations);
        $dto->setName('My Group');

        $array = $dto->toArray();

        $this->assertSame('vg123', $array[FlagshipField::FIELD_ID]);
        $this->assertSame('My Group', $array[FlagshipField::FIELD_NANE]);
        $this->assertIsArray($array[FlagshipField::FIELD_TARGETING]);
        $this->assertIsArray($array[FlagshipField::FIELD_VARIATIONS]);
        $this->assertCount(2, $array[FlagshipField::FIELD_VARIATIONS]);
    }

    public function testToArrayWithNullName()
    {
        $targeting = new TargetingDTO([]);
        $dto = new VariationGroupDTO('vg123', $targeting, []);

        $array = $dto->toArray();

        $this->assertArrayNotHasKey(FlagshipField::FIELD_NANE, $array);
    }
}

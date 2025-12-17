<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class TargetingDTOTest extends TestCase
{
    public function testConstructor()
    {
        $targetingGroups = [
            new TargetingGroupDTO([])
        ];
        $dto = new TargetingDTO($targetingGroups);

        $this->assertSame($targetingGroups, $dto->getTargetingGroups());
    }

    public function testGettersAndSetters()
    {
        $dto = new TargetingDTO([]);

        $targetingGroups = [
            new TargetingGroupDTO([]),
            new TargetingGroupDTO([])
        ];
        $instance = $dto->setTargetingGroups($targetingGroups);

        $this->assertSame($targetingGroups, $dto->getTargetingGroups());
        $this->assertSame($dto, $instance);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_TARGETING_GROUPS => [
                [
                    FlagshipField::FIELD_TARGETINGS => []
                ],
                [
                    FlagshipField::FIELD_TARGETINGS => []
                ]
            ]
        ];

        $dto = TargetingDTO::fromArray($data);

        $this->assertIsArray($dto->getTargetingGroups());
        $this->assertCount(2, $dto->getTargetingGroups());
        $this->assertContainsOnlyInstancesOf(TargetingGroupDTO::class, $dto->getTargetingGroups());
    }

    public function testFromArrayWithEmptyData()
    {
        $dto = TargetingDTO::fromArray([]);

        $this->assertIsArray($dto->getTargetingGroups());
        $this->assertEmpty($dto->getTargetingGroups());
    }

    public function testFromArrayWithInvalidData()
    {
        $data = [
            FlagshipField::FIELD_TARGETING_GROUPS => 'not an array'
        ];

        $dto = TargetingDTO::fromArray($data);

        $this->assertIsArray($dto->getTargetingGroups());
        $this->assertEmpty($dto->getTargetingGroups());
    }

    public function testToArray()
    {
        $targetingGroups = [
            new TargetingGroupDTO([]),
            new TargetingGroupDTO([])
        ];
        $dto = new TargetingDTO($targetingGroups);

        $array = $dto->toArray();

        $this->assertArrayHasKey(FlagshipField::FIELD_TARGETING_GROUPS, $array);
        $this->assertIsArray($array[FlagshipField::FIELD_TARGETING_GROUPS]);
        $this->assertCount(2, $array[FlagshipField::FIELD_TARGETING_GROUPS]);
    }

    public function testToArrayWithEmptyGroups()
    {
        $dto = new TargetingDTO([]);
        $array = $dto->toArray();

        $this->assertArrayHasKey(FlagshipField::FIELD_TARGETING_GROUPS, $array);
        $this->assertIsArray($array[FlagshipField::FIELD_TARGETING_GROUPS]);
        $this->assertEmpty($array[FlagshipField::FIELD_TARGETING_GROUPS]);
    }
}

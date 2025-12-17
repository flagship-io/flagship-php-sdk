<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class TargetingGroupDTOTest extends TestCase
{
    public function testConstructor()
    {
        $targetings = [
            new TargetingsDTO(\Flagship\Enum\TargetingOperator::EQUALS, 'key1', 'value1')
        ];
        $dto = new TargetingGroupDTO($targetings);

        $this->assertSame($targetings, $dto->getTargetings());
    }

    public function testGettersAndSetters()
    {
        $dto = new TargetingGroupDTO([]);

        $targetings = [
            new TargetingsDTO(\Flagship\Enum\TargetingOperator::EQUALS, 'key1', 'value1'),
            new TargetingsDTO(\Flagship\Enum\TargetingOperator::NOT_EQUALS, 'key2', 'value2')
        ];
        $instance = $dto->setTargetings($targetings);

        $this->assertSame($targetings, $dto->getTargetings());
        $this->assertSame($dto, $instance);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::FIELD_TARGETINGS => [
                [
                    FlagshipField::FIELD_OPERATOR => 'EQUALS',
                    FlagshipField::FIELD_KEY => 'browser',
                    FlagshipField::FIELD_VALUE => 'chrome'
                ],
                [
                    FlagshipField::FIELD_OPERATOR => 'NOT_EQUALS',
                    FlagshipField::FIELD_KEY => 'country',
                    FlagshipField::FIELD_VALUE => 'US'
                ]
            ]
        ];

        $dto = TargetingGroupDTO::fromArray($data);

        $this->assertIsArray($dto->getTargetings());
        $this->assertCount(2, $dto->getTargetings());
        $this->assertContainsOnlyInstancesOf(TargetingsDTO::class, $dto->getTargetings());
    }

    public function testFromArrayWithEmptyData()
    {
        $dto = TargetingGroupDTO::fromArray([]);

        $this->assertIsArray($dto->getTargetings());
        $this->assertEmpty($dto->getTargetings());
    }

    public function testFromArrayWithInvalidData()
    {
        $data = [
            FlagshipField::FIELD_TARGETINGS => 'not an array'
        ];

        $dto = TargetingGroupDTO::fromArray($data);

        $this->assertIsArray($dto->getTargetings());
        $this->assertEmpty($dto->getTargetings());
    }

    public function testToArray()
    {
        $targetings = [
            new TargetingsDTO(\Flagship\Enum\TargetingOperator::EQUALS, 'key1', 'value1'),
            new TargetingsDTO(\Flagship\Enum\TargetingOperator::CONTAINS, 'key2', ['a', 'b'])
        ];
        $dto = new TargetingGroupDTO($targetings);

        $array = $dto->toArray();

        $this->assertArrayHasKey(FlagshipField::FIELD_TARGETINGS, $array);
        $this->assertIsArray($array[FlagshipField::FIELD_TARGETINGS]);
        $this->assertCount(2, $array[FlagshipField::FIELD_TARGETINGS]);
    }

    public function testToArrayWithEmptyTargetings()
    {
        $dto = new TargetingGroupDTO([]);
        $array = $dto->toArray();

        $this->assertArrayHasKey(FlagshipField::FIELD_TARGETINGS, $array);
        $this->assertIsArray($array[FlagshipField::FIELD_TARGETINGS]);
        $this->assertEmpty($array[FlagshipField::FIELD_TARGETINGS]);
    }
}

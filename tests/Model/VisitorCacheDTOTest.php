<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Visitor\StrategyAbstract;

class VisitorCacheDTOTest extends TestCase
{
    public function testConstructor()
    {
        $data = new VisitorCacheDataDTO('visitor123', null);
        $dto = new VisitorCacheDTO(1, $data);

        $this->assertSame(1, $dto->getVersion());
        $this->assertSame($data, $dto->getData());
    }

    public function testGettersAndSetters()
    {
        $data = new VisitorCacheDataDTO('visitor1', null);
        $dto = new VisitorCacheDTO(1, $data);

        $instance1 = $dto->setVersion(2);
        $this->assertSame(2, $dto->getVersion());
        $this->assertSame($dto, $instance1);

        $newData = new VisitorCacheDataDTO('visitor2', 'anon123');
        $instance2 = $dto->setData($newData);
        $this->assertSame($newData, $dto->getData());
        $this->assertSame($dto, $instance2);
    }

    public function testFromArray()
    {
        $array = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => 'visitor123',
                StrategyAbstract::ANONYMOUS_ID => 'anon456',
                StrategyAbstract::CONSENT => true
            ]
        ];

        $dto = VisitorCacheDTO::fromArray($array);

        $this->assertSame(1, $dto->getVersion());
        $this->assertInstanceOf(VisitorCacheDataDTO::class, $dto->getData());
        $this->assertSame('visitor123', $dto->getData()->getVisitorId());
    }

    public function testFromArrayWithMissingVersion()
    {
        $array = [
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => 'visitor123',
                StrategyAbstract::ANONYMOUS_ID => null
            ]
        ];

        $dto = VisitorCacheDTO::fromArray($array);

        $this->assertSame(StrategyAbstract::CURRENT_VERSION, $dto->getVersion());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $array = [
            StrategyAbstract::VERSION => 'not an int',
            StrategyAbstract::DATA => 'not an array'
        ];

        $dto = VisitorCacheDTO::fromArray($array);

        $this->assertSame(StrategyAbstract::CURRENT_VERSION, $dto->getVersion());
        $this->assertInstanceOf(VisitorCacheDataDTO::class, $dto->getData());
    }

    public function testToArray()
    {
        $data = new VisitorCacheDataDTO('visitor123', 'anon456');
        $data->setConsent(true);
        $dto = new VisitorCacheDTO(1, $data);

        $array = $dto->toArray();

        $this->assertSame(1, $array[StrategyAbstract::VERSION]);
        $this->assertIsArray($array[StrategyAbstract::DATA]);
        $this->assertArrayHasKey(StrategyAbstract::VISITOR_ID, $array[StrategyAbstract::DATA]);
    }

    public function testRoundTripConversion()
    {
        $originalArray = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => 'visitor123',
                StrategyAbstract::ANONYMOUS_ID => 'anon456',
                StrategyAbstract::CONSENT => true,
                StrategyAbstract::CONTEXT => ['key' => 'value']
            ]
        ];

        $dto = VisitorCacheDTO::fromArray($originalArray);
        $resultArray = $dto->toArray();

        $this->assertSame($originalArray[StrategyAbstract::VERSION], $resultArray[StrategyAbstract::VERSION]);
    }
}

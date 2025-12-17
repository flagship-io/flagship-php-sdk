<?php

declare(strict_types=1);

namespace Flagship\Model;

use DateTime;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;

class TroubleshootingDTOTest extends TestCase
{
    public function testConstructor()
    {
        $startDate = '2025-01-01T00:00:00Z';
        $endDate = '2025-12-31T23:59:59Z';
        $traffic = 75.5;
        $timezone = 'UTC';

        $dto = new TroubleshootingDTO($startDate, $endDate, $traffic, $timezone);

        $this->assertSame($startDate, $dto->getStartDate());
        $this->assertSame($endDate, $dto->getEndDate());
        $this->assertSame($traffic, $dto->getTraffic());
        $this->assertSame($timezone, $dto->getTimezone());
    }

    public function testGettersAndSetters()
    {
        $dto = new TroubleshootingDTO('2025-01-01', '2025-12-31', 50.0, 'UTC');

        $instance1 = $dto->setStartDate('2025-02-01');
        $this->assertSame('2025-02-01', $dto->getStartDate());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setEndDate('2025-11-30');
        $this->assertSame('2025-11-30', $dto->getEndDate());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setTraffic(100.0);
        $this->assertSame(100.0, $dto->getTraffic());
        $this->assertSame($dto, $instance3);

        $instance4 = $dto->setTimezone('America/New_York');
        $this->assertSame('America/New_York', $dto->getTimezone());
        $this->assertSame($dto, $instance4);
    }

    public function testGetStartDateAsDateTime()
    {
        $dateString = '2025-06-15T10:30:00Z';
        $dto = new TroubleshootingDTO($dateString, '2025-12-31', 50.0, 'UTC');

        $dateTime = $dto->getStartDateAsDateTime();

        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertSame('2025-06-15', $dateTime->format('Y-m-d'));
    }

    public function testGetStartDateAsDateTimeWithInvalidDate()
    {
        $dto = new TroubleshootingDTO('invalid-date', '2025-12-31', 50.0, 'UTC');

        $dateTime = $dto->getStartDateAsDateTime();

        $this->assertNull($dateTime);
    }

    public function testGetEndDateAsDateTime()
    {
        $dateString = '2025-12-31T23:59:59Z';
        $dto = new TroubleshootingDTO('2025-01-01', $dateString, 50.0, 'UTC');

        $dateTime = $dto->getEndDateAsDateTime();

        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertSame('2025-12-31', $dateTime->format('Y-m-d'));
    }

    public function testGetEndDateAsDateTimeWithInvalidDate()
    {
        $dto = new TroubleshootingDTO('2025-01-01', 'not-a-date', 50.0, 'UTC');

        $dateTime = $dto->getEndDateAsDateTime();

        $this->assertNull($dateTime);
    }

    public function testFromArray()
    {
        $data = [
            FlagshipField::START_DATE => '2025-03-01',
            FlagshipField::END_DATE => '2025-09-30',
            FlagshipField::TRAFFIC => 85.5,
            FlagshipField::TIMEZONE => 'Europe/Paris'
        ];

        $dto = TroubleshootingDTO::fromArray($data);

        $this->assertSame('2025-03-01', $dto->getStartDate());
        $this->assertSame('2025-09-30', $dto->getEndDate());
        $this->assertSame(85.5, $dto->getTraffic());
        $this->assertSame('Europe/Paris', $dto->getTimezone());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = TroubleshootingDTO::fromArray([]);

        $this->assertSame('', $dto->getStartDate());
        $this->assertSame('', $dto->getEndDate());
        $this->assertSame(0.0, $dto->getTraffic());
        $this->assertSame('', $dto->getTimezone());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $data = [
            FlagshipField::START_DATE => 123,
            FlagshipField::END_DATE => [],
            FlagshipField::TRAFFIC => 'not a number',
            FlagshipField::TIMEZONE => null
        ];

        $dto = TroubleshootingDTO::fromArray($data);

        $this->assertSame('', $dto->getStartDate());
        $this->assertSame('', $dto->getEndDate());
        $this->assertSame(0.0, $dto->getTraffic());
        $this->assertSame('', $dto->getTimezone());
    }

    public function testFromArrayWithStringTraffic()
    {
        $data = [
            FlagshipField::START_DATE => '2025-01-01',
            FlagshipField::END_DATE => '2025-12-31',
            FlagshipField::TRAFFIC => '50',
            FlagshipField::TIMEZONE => 'UTC'
        ];

        $dto = TroubleshootingDTO::fromArray($data);
        $this->assertSame(50.0, $dto->getTraffic());
    }

    public function testToArray()
    {
        $dto = new TroubleshootingDTO('2025-01-01', '2025-12-31', 60.0, 'Asia/Tokyo');

        $array = $dto->toArray();

        $this->assertSame('2025-01-01', $array[FlagshipField::START_DATE]);
        $this->assertSame('2025-12-31', $array[FlagshipField::END_DATE]);
        $this->assertSame(60.0, $array[FlagshipField::TRAFFIC]);
        $this->assertSame('Asia/Tokyo', $array[FlagshipField::TIMEZONE]);
    }

    public function testToTroubleshootingData()
    {
        $dto = new TroubleshootingDTO('2025-01-01', '2025-12-31', 75.0, 'UTC');

        $data = $dto->toTroubleshootingData();

        $this->assertInstanceOf(TroubleshootingData::class, $data);
        $this->assertInstanceOf(DateTime::class, $data->getStartDate());
        $this->assertInstanceOf(DateTime::class, $data->getEndDate());
        $this->assertSame(75.0, $data->getTraffic());
        $this->assertSame('UTC', $data->getTimezone());
    }

    public function testToTroubleshootingDataWithInvalidDates()
    {
        $dto = new TroubleshootingDTO('invalid', 'dates', 50.0, 'UTC');

        $data = $dto->toTroubleshootingData();

        $this->assertNull($data);
    }
}

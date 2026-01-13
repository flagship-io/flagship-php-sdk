<?php

declare(strict_types=1);

namespace Flagship\Model;

use DateTime;
use PHPUnit\Framework\TestCase;

class TroubleshootingDataTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $data = new TroubleshootingData();

        $startDate = new DateTime('2025-01-01');
        $instance1 = $data->setStartDate($startDate);
        $this->assertSame($startDate, $data->getStartDate());
        $this->assertSame($data, $instance1);

        $endDate = new DateTime('2025-12-31');
        $instance2 = $data->setEndDate($endDate);
        $this->assertSame($endDate, $data->getEndDate());
        $this->assertSame($data, $instance2);

        $instance3 = $data->setTraffic(75.5);
        $this->assertSame(75.5, $data->getTraffic());
        $this->assertSame($data, $instance3);

        $instance4 = $data->setTimezone('UTC');
        $this->assertSame('UTC', $data->getTimezone());
        $this->assertSame($data, $instance4);
    }

    public function testSetTrafficWithInt()
    {
        $data = new TroubleshootingData();
        $data->setTraffic(100);

        $this->assertSame(100, $data->getTraffic());
    }

    public function testSetTrafficWithFloat()
    {
        $data = new TroubleshootingData();
        $data->setTraffic(50.5);

        $this->assertSame(50.5, $data->getTraffic());
    }

    public function testChainingSetter()
    {
        $data = new TroubleshootingData();
        $startDate = new DateTime('2025-01-01');
        $endDate = new DateTime('2025-12-31');

        $result = $data
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setTraffic(80.0)
            ->setTimezone('America/New_York');

        $this->assertSame($data, $result);
        $this->assertSame($startDate, $data->getStartDate());
        $this->assertSame($endDate, $data->getEndDate());
        $this->assertSame(80.0, $data->getTraffic());
        $this->assertSame('America/New_York', $data->getTimezone());
    }

    public function testDateTimeObjects()
    {
        $data = new TroubleshootingData();
        $startDate = new DateTime('2025-06-15 10:30:00');
        $endDate = new DateTime('2025-12-25 23:59:59');

        $data->setStartDate($startDate);
        $data->setEndDate($endDate);

        $this->assertSame('2025-06-15', $data->getStartDate()->format('Y-m-d'));
        $this->assertSame('10:30:00', $data->getStartDate()->format('H:i:s'));
        $this->assertSame('2025-12-25', $data->getEndDate()->format('Y-m-d'));
        $this->assertSame('23:59:59', $data->getEndDate()->format('H:i:s'));
    }
}

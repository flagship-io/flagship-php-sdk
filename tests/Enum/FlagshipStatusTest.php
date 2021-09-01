<?php

namespace Flagship\Enum;

use PHPUnit\Framework\TestCase;

class FlagshipStatusTest extends TestCase
{

    public function testGetStatusName()
    {
        $this->assertEquals("NOT_INITIALIZED", FlagshipStatus::getStatusName(FlagshipStatus::NOT_INITIALIZED));
        $this->assertEquals("NOT_INITIALIZED", FlagshipStatus::getStatusName(FlagshipStatus::NOT_READY));
        $this->assertEquals("STARTING", FlagshipStatus::getStatusName(FlagshipStatus::STARTING));
        $this->assertEquals("POLLING", FlagshipStatus::getStatusName(FlagshipStatus::POLLING));
        $this->assertEquals("READY_PANIC_ON", FlagshipStatus::getStatusName(FlagshipStatus::READY_PANIC_ON));
        $this->assertEquals("READY", FlagshipStatus::getStatusName(FlagshipStatus::READY));

        $this->assertNull(FlagshipStatus::getStatusName(""));
        $this->assertNull(FlagshipStatus::getStatusName(-1));
        $this->assertNull(FlagshipStatus::getStatusName(5));
    }
}

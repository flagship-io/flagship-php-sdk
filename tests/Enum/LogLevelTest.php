<?php

namespace Flagship\Enum;

use PHPUnit\Framework\TestCase;

class LogLevelTest extends TestCase
{
    public function testGetLogName()
    {
        $this->assertEquals("NONE", LogLevel::getLogName(LogLevel::NONE));
        $this->assertEquals("EMERGENCY", LogLevel::getLogName(LogLevel::EMERGENCY));
        $this->assertEquals("ALERT", LogLevel::getLogName(LogLevel::ALERT));
        $this->assertEquals("CRITICAL", LogLevel::getLogName(LogLevel::CRITICAL));
        $this->assertEquals("ERROR", LogLevel::getLogName(LogLevel::ERROR));
        $this->assertEquals("WARNING", LogLevel::getLogName(LogLevel::WARNING));
        $this->assertEquals("NOTICE", LogLevel::getLogName(LogLevel::NOTICE));
        $this->assertEquals("INFO", LogLevel::getLogName(LogLevel::INFO));
        $this->assertEquals("DEBUG", LogLevel::getLogName(LogLevel::DEBUG));
        $this->assertEquals("ALL", LogLevel::getLogName(LogLevel::ALL));

        $this->assertSame("", LogLevel::getLogName(""));
        $this->assertSame("", LogLevel::getLogName(-1));
        $this->assertSame("", LogLevel::getLogName(10));
    }
}

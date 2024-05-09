<?php

namespace Flagship\Enum;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\EnumStatusBase;

class EnumStatusBaseTest extends TestCase
{
    public function testGetStatusName()
    {
        $this->assertEquals("SDK_INITIALIZED", FSSdkStatus::getStatusName(FSSdkStatus::SDK_INITIALIZED));
        $this->assertEquals("SDK_INITIALIZING", FSSdkStatus::getStatusName(FSSdkStatus::SDK_INITIALIZING));
        $this->assertEquals("SDK_NOT_INITIALIZED", FSSdkStatus::getStatusName(FSSdkStatus::SDK_NOT_INITIALIZED));
        $this->assertEquals("SDK_PANIC", FSSdkStatus::getStatusName(FSSdkStatus::SDK_PANIC));
        $this->assertEquals(null, EnumStatusBase::getStatusName(4));
    }
}
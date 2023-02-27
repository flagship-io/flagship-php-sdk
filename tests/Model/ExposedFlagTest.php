<?php

namespace Flagship\Model;

use Flagship\Flag\FlagMetadata;
use PHPUnit\Framework\TestCase;

class ExposedFlagTest extends TestCase
{
    public function testConstruct()
    {
        $key = "key";
        $flagValue = "value";
        $flagMetadata = new FlagMetadata("campaignId",
            "VarGrId", "varId",
            true, "ab", null);
        $exposedFlag = new ExposedFlag($key, $flagValue, $flagMetadata);
        $this->assertSame($key, $exposedFlag->getKey());
        $this->assertSame($flagValue, $exposedFlag->getValue());
        $this->assertSame($flagMetadata, $exposedFlag->getMetadata());
    }
}

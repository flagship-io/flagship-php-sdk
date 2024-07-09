<?php

namespace Flagship\Model;

use Flagship\Flag\FSFlagMetadata;
use PHPUnit\Framework\TestCase;

class ExposedFlagTest extends TestCase
{
    public function testConstruct()
    {
        $key = "key";
        $flagValue = "value";
        $flagDefaultValue = "defaultValue";
        $flagMetadata = new FSFlagMetadata(
            "campaignId",
            "VarGrId",
            "varId",
            true,
            "ab",
            null,
            "campaignName",
            "variationGroupName",
            "variationName"
        );
        $exposedFlag = new ExposedFlag($key, $flagValue, $flagDefaultValue, $flagMetadata);
        $this->assertSame($key, $exposedFlag->getKey());
        $this->assertSame($flagValue, $exposedFlag->getValue());
        $this->assertSame($flagMetadata, $exposedFlag->getMetadata());
    }
}

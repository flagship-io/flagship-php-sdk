<?php

namespace Flagship\Enum;

use PHPUnit\Framework\TestCase;

class CacheStrategyTest extends TestCase
{
    public function testGetCacheStrategyName()
    {
        $strategy = CacheStrategy::getCacheStrategyName(3);
        $this->assertSame("", $strategy);

        $strategy = CacheStrategy::getCacheStrategyName(0);
        $this->assertSame("", $strategy);

        $strategy = CacheStrategy::getCacheStrategyName("test");
        $this->assertSame("", $strategy);

        $strategy = CacheStrategy::getCacheStrategyName(CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE);
        $this->assertSame("BATCHING_AND_CACHING_ON_FAILURE", $strategy);
    }
}

<?php

namespace Flagship\Enum;

class CacheStrategy
{
    const BATCHING_AND_CACHING_ON_FAILURE = 1;
    const NO_BATCHING_AND_CACHING_ON_FAILURE = 2;

    private static $listCacheStrategy = ["BATCHING_AND_CACHING_ON_FAILURE", "NO_BATCHING_AND_CACHING_ON_FAILURE"];

    /**
     * @param int $strategy
     * @return string
     */
    public static function getCacheStrategyName($strategy)
    {
        if (!is_int($strategy) || $strategy < 1 || $strategy > 2) {
            return "";
        }
        return self::$listCacheStrategy[$strategy];
    }
}

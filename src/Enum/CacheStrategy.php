<?php

namespace Flagship\Enum;

/**
 * CacheStrategy Enum
 */
enum CacheStrategy: int
{
    /**
     * Batching and caching on failure
     */
    case BATCHING_AND_CACHING_ON_FAILURE = 1;

    /**
     * No batching and caching on failure
     */
    case NO_BATCHING_AND_CACHING_ON_FAILURE = 2;
}

<?php

namespace Flagship\Enum;

/**
 * FSFlagStatus Enum
 */
enum FSFlagStatus: int
{
    /**
     * The flags have been successfully fetched from the API or re-evaluated in bucketing mode.
     */
    case FETCHED = 0;

    /**
     * The flags need to be re-fetched due to a change in context, or because the flags were loaded from cache or XPC.
     */
    case FETCH_REQUIRED = 1;

    /**
     * The flag was not found or do not exist.
     */
    case NOT_FOUND = 2;

    /**
     * The SDK is in PANIC mode: All features are disabled except for the one to fetch flags.
     */
    case PANIC = 3;
}

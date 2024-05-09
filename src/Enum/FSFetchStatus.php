<?php

namespace Flagship\Enum;

class FSFetchStatus extends EnumBase
{
    /**
     * The flags have been successfully fetched from the API or re-evaluated in bucketing mode.
     */
    const FETCHED = 0;

    /**
     * The flags are currently being fetched from the API or re-evaluated in bucketing mode.
     */
    const FETCHING = 1;

    /**
     * The flags need to be re-fetched due to a change in context, or because the flags were loaded from cache or XPC.
     */
    const FETCH_REQUIRED = 2;

    /**
     * The SDK is in PANIC mode: All features are disabled except for the one to fetch flags.
     */
    const PANIC = 3;
}
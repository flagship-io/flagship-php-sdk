<?php

namespace Flagship\Enum;

/**
 * Enum FSSdkStatus
 */
enum FSSdkStatus: int
{
    /**
     * It is the default initial status. This status remains until the sdk has been initialized successfully.
     */
    case SDK_NOT_INITIALIZED = 0;

    /**
     * The SDK is currently initializing.
     */
    case SDK_INITIALIZING = 1;

    /**
     * Flagship SDK is ready but is running in Panic mode:
     * All features are disabled except the one which refresh this status.
     */
    case SDK_PANIC = 2;

    /**
     * The Initialization is done, and Flagship SDK is ready to use.
     */
    case SDK_INITIALIZED = 3;
}

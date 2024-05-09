<?php

namespace Flagship\Enum;

class FSSdkStatus extends EnumStatusBase
{
    /**
     * It is the default initial status. This status remains until the sdk has been initialized successfully.
     */
    const SDK_NOT_INITIALIZED = 0;

    /**
     * The SDK is currently initializing.
     */
    const SDK_INITIALIZING = 1;

    /**
     * Flagship SDK is ready but is running in Panic mode: All features are disabled except the one which refresh this status.
     */
    const SDK_PANIC = 2;

    /**
     * The Initialization is done, and Flagship SDK is ready to use.
     */
    const SDK_INITIALIZED = 3;
}
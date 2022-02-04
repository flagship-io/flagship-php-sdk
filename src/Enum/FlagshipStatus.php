<?php

namespace Flagship\Enum;

/**
 * Class FlagshipStatus
 * @package Flagship\Enum
 */
class FlagshipStatus
{
    /**
     * It is the default initial status. This status remains until the sdk has been initialized successfully.
     * Flagship SDK has not been started or initialized successfully.
     * @deprecated in v2, use FlagshipStatus::NOT_INITIALIZED instead of
     * @var int
     */
    const NOT_READY = 0;

    /**
     * It is the default initial status. This status remains until the sdk has been initialized successfully.
     * @var int
     */
    const NOT_INITIALIZED = 0;

    /**
     * Flagship SDK is starting.
     */
    const STARTING = 1;
    /**
     * Flagship SDK has been started successfully but is still polling campaigns.
     * @var int
     */
    const POLLING = 2;
    /**
     * Flagship SDK is ready but is running in Panic mode: All features are disabled except the one which refresh this status.
     * @var int
     */
    const READY_PANIC_ON = 3;
    /**
     * Flagship SDK is ready to use.
     * @var int
     */
    const READY = 4 ;

    private static $listStatus = ["NOT_INITIALIZED","STARTING", "POLLING", "READY_PANIC_ON", "READY"];

    /**
     * @param int $status
     * @return string|null
     */
    public static function getStatusName($status)
    {
        if (!is_int($status) || $status < 0 || $status > 4) {
            return null;
        }
        return self::$listStatus[$status];
    }
}

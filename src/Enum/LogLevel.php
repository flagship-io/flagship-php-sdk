<?php

namespace Flagship\Enum;

class LogLevel
{
    /**
     * NONE = 0: Logging will be disabled.
     */
    const NONE      = 0;
    /**
     * EMERGENCY = 1: Only emergencies will be logged.
     */
    const EMERGENCY = 1;
    /**
     * ALERT = 2: Only alerts and above will be logged.
     */
    const ALERT = 2;
    /**
     * CRITICAL = 3: Only critical and above will be logged.
     */
    const CRITICAL = 3;
    /**
     * ERROR = 4: Only errors and above will be logged.
     */
    const ERROR = 4;
    /**
     * WARNING = 5: Only warnings and above will be logged.
     */
    const WARNING = 5;
    /**
     * NOTICE = 6: Only notices and above will be logged.
     */
    const NOTICE  = 6;
    /**
     * INFO = 7: Only info logs and above will be logged.
     */
    const INFO = 7;
    /**
     * DEBUG = 8: Only debug logs and above will be logged.
     */
    const DEBUG = 8;

    /**
     * ALL = 9: All logs will be logged.
     */
    const ALL = 9;

    public static function getLog($loglevelInt)
    {
        $loglevel = "";
        switch ($loglevelInt) {
            case self::EMERGENCY:
                $loglevel = "EMERGENCY";
                break;
            case self::ALERT:
                $loglevel = "ALERT";
                break;
            case self::CRITICAL:
                $loglevel = "CRITICAL";
                break;
            case self::ERROR:
                $loglevel = "ERROR";
                break;
            case self::WARNING:
                $loglevel = "WARNING";
                break;
            case self::NOTICE:
                $loglevel = "NOTICE";
                break;
            case self::INFO:
                $loglevel = "INFO";
                break;
            case self::DEBUG:
                $loglevel = "DEBUG";
                break;
        }
        return $loglevel;
    }
}

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

    /**
     * @param int $loglevel
     * @return string
     */
    public static function getLogName($value)
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());
    
        return isset($constants[$value]) ? $constants[$value] : null;
    }
}

<?php

namespace Flagship\Enum;

enum LogLevel: int
{
    /**
     * Logging will be disabled.
     */
    case NONE = 0;

    /**
     * Only emergencies will be logged.
     */
    case EMERGENCY = 1;

    /**
     * Only alerts and above will be logged.
     */
    case ALERT = 2;

    /**
     * Only critical and above will be logged.
     */
    case CRITICAL = 3;

    /**
     * Only errors and above will be logged.
     */
    case ERROR = 4;

    /**
     * Only warnings and above will be logged.
     */
    case WARNING = 5;

    /**
     * Only notices and above will be logged.
     */
    case NOTICE = 6;

    /**
     * Only info logs and above will be logged.
     */
    case INFO = 7;

    /**
     * Only debug logs and above will be logged.
     */
    case DEBUG = 8;

    /**
     * All logs will be logged.
     */
    case ALL = 9;
}

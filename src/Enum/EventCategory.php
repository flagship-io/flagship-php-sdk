<?php

namespace Flagship\Enum;

/**
 * EventCategory Enum
 */
enum EventCategory: string
{
    /**
     * Represents action tracking event category.
     */
    case ACTION_TRACKING = "ACTION_TRACKING";

    /**
     * Represents user engagement event category.
     */
    case USER_ENGAGEMENT = "USER_ENGAGEMENT";
}

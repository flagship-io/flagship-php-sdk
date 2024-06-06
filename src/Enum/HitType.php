<?php

namespace Flagship\Enum;

/**
 * HitType Enum
 */
enum HitType: string
{
    /**
     * User has seen a URL
     */
    case PAGE_VIEW = "PAGEVIEW";

    /**
     * User has seen a screen.
     */
    case SCREEN_VIEW = "SCREENVIEW";

    /**
     * User has made a transaction.
     */
    case TRANSACTION = "TRANSACTION";

    /**
     * Item bought in a transaction.
     */
    case ITEM = "ITEM";

    /**
     * User has made a specific action.
     */
    case EVENT = "EVENT";

    /**
     * User has activated something.
     */
    case ACTIVATE = "ACTIVATE";

    /**
     * User has given consent.
     */
    case CONSENT = "CONSENT";

    /**
     * User has been segmented.
     */
    case SEGMENT = "SEGMENT";

    /**
     * User is troubleshooting.
     */
    case TROUBLESHOOTING = "TROUBLESHOOTING";

    /**
     * User is using something.
     */
    case USAGE = "USAGE";
    case BATCH = "BATCH";
}

<?php


namespace Flagship\Enum;


class HitType
{
    /**
     * User has seen a URL
     *
     * @var string
     */
    const PAGE_VIEW="PAGEVIEW";

    /**
     * User has seen a screen.
     *
     * @var string
     */
    const SCREEN_VIEW = "SCREENVIEW";

    /**
     * User has made a transaction.
     *
     * @var string
     */
    const TRANSACTION = "TRANSACTION";

    /**
     * Item bought in a transaction.
     *
     * @var string
     */
    const ITEM = "ITEM";

    /**
     * User has made a specific action.
     *
     * @var string
     */
    const EVENT = "EVENT";
}

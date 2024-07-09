<?php

namespace Flagship\Model;

use Flagship\Enum\FSFetchReason;
use Flagship\Enum\FSFetchStatus;

/**
 * Represents the status of visitor fetch for flag data.
 */
interface FetchFlagsStatusInterface
{
    /**
     * The new status of the flags fetch.
     *
     * @return FSFetchStatus
     */
    public function getStatus(): FSFetchStatus;

    /**
     * The reason for the status change
     *
     * @return FSFetchReason
     */
    public function getReason(): FSFetchReason;
}

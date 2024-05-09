<?php

namespace Flagship\Model;

/**
 * Represents the status of visitor fetch for flag data.
 */
interface FetchFlagsStatusInterface
{
    /**
     * The new status of the flags fetch. 
     * See: FSFetchStatus
     *
     * @return int
     */
    public function getStatus();

    /**
     * The reason for the status change
     * See: FSFetchReason
     * @return int
     */
    public function getReason();
}
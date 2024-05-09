<?php

namespace Flagship\Model;

/**
 * Represents the status of visitor fetch for flag data.
 */
interface FetchFlagsStatusInterface
{
    /**
     * The new status of the flags fetch. 
     * @see \Flagship\Enum\FSFetchStatus for possible values.
     *
     * @return int
     */
    public function getStatus();

    /**
     * The reason for the status change
     * @see \Flagship\Enum\FSFetchReason For possible values.
     * 
     * @return int
     */
    public function getReason();
}
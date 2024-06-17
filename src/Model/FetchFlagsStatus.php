<?php

namespace Flagship\Model;

use Flagship\Enum\FSFetchReason;
use Flagship\Enum\FSFetchStatus;

class FetchFlagsStatus implements FetchFlagsStatusInterface
{
    /**
     * @var FSFetchStatus
     */
    private FSFetchStatus $status;
    /**
     * @var FSFetchReason
     */
    private FSFetchReason $reason;

    /**
     * @param FSFetchStatus $status
     * @param FSFetchReason $reason
     */
    public function __construct(FSFetchStatus $status, FSFetchReason $reason)
    {
        $this->status = $status;
        $this->reason = $reason;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): FSFetchStatus
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function getReason(): FSFetchReason
    {
        return $this->reason;
    }
}

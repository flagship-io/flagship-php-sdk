<?php

namespace Flagship\Model;

class FetchFlagsStatus implements FetchFlagsStatusInterface
{
    /**
     * @var int
     */
    private $status;
    /**
     * @var int
     */
    private $reason;

    /**
     * @param int $status
     * @param int $reason
     */
    public function __construct($status, $reason)
    {
        $this->status = $status;
        $this->reason = $reason;
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function getReason()
    {
        return $this->reason;
    }
}

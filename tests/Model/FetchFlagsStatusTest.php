<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Enum\FSFetchStatus;
use Flagship\Enum\FSFetchReason;

class FetchFlagsStatusTest extends TestCase
{
    public function testConstructor()
    {
        $status = FSFetchStatus::FETCHED;
        $reason = FSFetchReason::NONE;
        $fetchStatus = new FetchFlagsStatus($status, $reason);

        $this->assertSame($status, $fetchStatus->getStatus());
        $this->assertSame($reason, $fetchStatus->getReason());
    }

    public function testGetStatus()
    {
        $status = FSFetchStatus::FETCH_REQUIRED;
        $reason = FSFetchReason::VISITOR_CREATED;
        $fetchStatus = new FetchFlagsStatus($status, $reason);

        $this->assertSame($status, $fetchStatus->getStatus());
    }

    public function testGetReason()
    {
        $status = FSFetchStatus::PANIC;
        $reason = FSFetchReason::NONE;
        $fetchStatus = new FetchFlagsStatus($status, $reason);

        $this->assertSame($reason, $fetchStatus->getReason());
    }

    public function testImplementsInterface()
    {
        $fetchStatus = new FetchFlagsStatus(FSFetchStatus::FETCHED, FSFetchReason::NONE);
        $this->assertInstanceOf(FetchFlagsStatusInterface::class, $fetchStatus);
    }

    public function testWithDifferentStatusCombinations()
    {
        $combinations = [
            [FSFetchStatus::FETCHED, FSFetchReason::NONE],
            [FSFetchStatus::FETCH_REQUIRED, FSFetchReason::VISITOR_CREATED],
            [FSFetchStatus::PANIC, FSFetchReason::NONE],
        ];

        foreach ($combinations as [$status, $reason]) {
            $fetchStatus = new FetchFlagsStatus($status, $reason);
            $this->assertSame($status, $fetchStatus->getStatus());
            $this->assertSame($reason, $fetchStatus->getReason());
        }
    }
}

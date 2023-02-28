<?php

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;

class ExposedUserTest extends TestCase
{
    public function testConstruct()
    {
        $visitorId = 'visitorId';
        $visitorContext = ["key" => "value"];
        $anonymousId = "anonymousId";
        $exposedUser = new ExposedUser($visitorId, $anonymousId, $visitorContext);

        $this->assertSame($visitorId, $exposedUser->getVisitorId());
        $this->assertSame($anonymousId, $exposedUser->getAnonymousId());
        $this->assertSame($visitorContext, $exposedUser->getContext());
    }
}

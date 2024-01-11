<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use PHP_CodeSniffer\Config;
use PHPUnit\Framework\TestCase;

class UsageHitTest extends TestCase
{
    public function testToApiKeys()
    {
        $config = new DecisionApiConfig();
        $analyticHit = new UsageHit();
        $analyticHit->setVisitorId("visitor")
        ->setConfig($config);

        $this->assertSame('USAGE', $analyticHit->toApiKeys()['t']);
    }
}

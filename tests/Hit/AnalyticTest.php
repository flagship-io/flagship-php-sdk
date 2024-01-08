<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use PHP_CodeSniffer\Config;
use PHPUnit\Framework\TestCase;

class AnalyticTest extends TestCase
{
    public function testToApiKeys()
    {
        $config = new DecisionApiConfig();
        $analyticHit = new Analytic();
        $analyticHit->setVisitorId("visitor")
        ->setConfig($config);

        $this->assertSame('USAGE', $analyticHit->toApiKeys()['t']);
        $this->assertNull($analyticHit->getVisitorId());
    }
}

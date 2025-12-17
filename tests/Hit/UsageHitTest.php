<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\LogLevel;
use Flagship\Enum\TroubleshootingLabel;
use PHP_CodeSniffer\Config;
use PHPUnit\Framework\TestCase;

class UsageHitTest extends TestCase
{
    public function testToApiKeys()
    {
        $config = new DecisionApiConfig();
        $analyticHit = new UsageHit();
        $analyticHit->setLogLevel(LogLevel::INFO)
            ->setLabel(TroubleshootingLabel::FLAG_VALUE_NOT_CALLED)
            ->setConfig($config)->setVisitorId("visitor");

        $this->assertSame('USAGE', $analyticHit->toApiKeys()['t']);
    }
}

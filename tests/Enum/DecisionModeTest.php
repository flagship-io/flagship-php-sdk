<?php

namespace Flagship\Enum;

use PHPUnit\Framework\TestCase;

class DecisionModeTest extends TestCase
{

    public function testIsDecisionMode()
    {
        $this->assertTrue(DecisionMode::isDecisionMode(DecisionMode::DECISION_API));
        $this->assertTrue(DecisionMode::isDecisionMode(DecisionMode::BUCKETING));
        $this->assertFalse(DecisionMode::isDecisionMode(450));
        $this->assertFalse(DecisionMode::isDecisionMode("anything"));
        $this->assertFalse(DecisionMode::isDecisionMode([]));
    }
}

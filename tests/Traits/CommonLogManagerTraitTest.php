<?php

namespace Flagship\Traits;

use PHPUnit\Framework\TestCase;

class CommonLogManagerTraitTest extends TestCase
{
    public function testGetDateTime()
    {
        $logManagerTraitMock = $this->getMockForTrait("Flagship\Traits\CommonLogManagerTrait");
        $value =  $logManagerTraitMock->getDateTime();

        if (method_exists($this, "assertMatchesRegularExpression")) {
            $this->assertMatchesRegularExpression("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+/", $value);
        } else {
            $this->assertRegExp("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+/", $value);
        }
    }
}

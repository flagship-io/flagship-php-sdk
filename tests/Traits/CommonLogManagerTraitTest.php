<?php

namespace Flagship\Traits;

use PHPUnit\Framework\TestCase;

class CommonLogManagerTraitTest extends TestCase
{
    public function testGetDateTime()
    {
        $logManagerTraitMock = $this->getMockForTrait("Flagship\Traits\CommonLogManagerTrait");
        $value = $logManagerTraitMock->getDateTime();
        $this->assertMatchesRegularExpression("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+/", $value);
    }
}

<?php

namespace Flagship;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Flagship\Utils\FlagshipLogManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class BaseTestCase extends TestCase
{
    use PHPMock;

    protected array $capturedLogs = [];
    protected function mockRoundFunction()
    {
        $round = $this->getFunctionMock("Flagship\Traits", 'round');
        $round->expects($this->any())->willReturn(0);
        return $round;
    }

    protected function mockErrorLog(?InvocationOrder $invocationRule = null): void
    {
        $invocationRule = $invocationRule ?? $this->once();
        $errorLog = $this->getFunctionMock('Flagship\Traits', 'error_log');
        $errorLog->expects($invocationRule)
            ->willReturnCallback(function ($message) {
                $this->capturedLogs[] = $message;
                return true;
            });
    }

    /**
     * 
     * @param array<string> $methods
     * @return FlagshipLogManager|MockObject
     */
    protected function mockLoggerManager(array $methods = []): MockObject
    {
        return $this->getMockBuilder(
            FlagshipLogManager::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(
                $methods
            )->getMock();
    }
}

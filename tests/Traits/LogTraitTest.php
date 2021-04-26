<?php

namespace Flagship\Traits;

use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class LogTraitTest extends TestCase
{
    public function testLoginError()
    {
        $logTraitMock = $this->getMockForTrait(
            'Flagship\Traits\LogTrait',
            [],
            "",
            false,
            true,
            true
        );
        $logManagerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            [],
            "",
            false,
            true,
            true,
            ['error']
        );
        $message = "hello";
        $context = ['exception' => 'hello Exception'];

        $logManagerMock->expects($this->once())->method('error')
            ->with(
                $message,
                $context
            );
        $logError = Utils::getMethod($logTraitMock, "logError");
        $logError->invokeArgs($logTraitMock, [$logManagerMock, $message, $context]);
    }

    public function testLoginInfo()
    {
        $logTraitMock = $this->getMockForTrait(
            'Flagship\Traits\LogTrait',
            [],
            "",
            false,
            true,
            true
        );
        $logManagerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            [],
            "",
            false,
            true,
            true,
            ['info']
        );
        $message = "hello";
        $context = ['exception' => 'hello Exception'];

        $logManagerMock->expects($this->once())->method('info')
            ->with(
                $message,
                $context
            );
        $loginInfo = Utils::getMethod($logTraitMock, "logInfo");
        $loginInfo->invokeArgs($logTraitMock, [$logManagerMock, $message, $context]);
    }
}

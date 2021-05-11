<?php

namespace Flagship\Traits;

use Flagship\Enum\FlagshipConstant;
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
            'Psr\Log\LoggerInterface',
            [],
            "",
            false,
            true,
            true,
            ['error']
        );
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $message = "hello";
        $context = ['exception' => 'hello Exception'];

        $logManagerMock->expects($this->once())->method('error')
            ->with(
                "[$flagshipSdk] " . $message,
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
            'Psr\Log\LoggerInterface',
            [],
            "",
            false,
            true,
            true,
            ['info']
        );
        $message = "hello";
        $context = ['exception' => 'hello Exception'];
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $logManagerMock->expects($this->once())->method('info')
            ->with(
                "[$flagshipSdk] " . $message,
                $context
            );
        $loginInfo = Utils::getMethod($logTraitMock, "logInfo");
        $loginInfo->invokeArgs($logTraitMock, [$logManagerMock, $message, $context]);
        $loginInfo->invokeArgs($logTraitMock, [null, $message, $context]);
    }
}

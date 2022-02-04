<?php

namespace Flagship\Traits;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;
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

        $logManagerMock->expects($this->exactly(3))->method('error')
            ->with(
                "[$flagshipSdk] " . $message,
                $context
            );

        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);
        $logError = Utils::getMethod($logTraitMock, "logError");
        $logError->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::CRITICAL);
        $logError->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::ERROR);
        $logError->invokeArgs($logTraitMock, [$config, $message, $context]);

        $logError->invokeArgs($logTraitMock, [null, $message, $context]);

        $config->setLogLevel(LogLevel::WARNING);
        $logError->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config = new DecisionApiConfig();
        $config->setLogLevel(LogLevel::INFO);
        $logError->invokeArgs($logTraitMock, [$config, $message, $context]);
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

        $logManagerMock->expects($this->exactly(3))->method('info')
            ->with(
                "[$flagshipSdk] " . $message,
                $context
            );
        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);

        $logInfo = Utils::getMethod($logTraitMock, "logInfo");
        $logInfo->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::DEBUG);
        $logInfo->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::INFO);
        $logInfo->invokeArgs($logTraitMock, [$config, $message, $context]);

        $logInfo->invokeArgs($logTraitMock, [null, $message, $context]);

        $config->setLogLevel(LogLevel::NOTICE);
        $logInfo->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config = new DecisionApiConfig();
        $config->setLogLevel(LogLevel::INFO);
        $logInfo->invokeArgs($logTraitMock, [$config, $message, $context]);
    }
}

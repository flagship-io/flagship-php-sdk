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
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello";
        $context = ['exception' => 'hello Exception'];

        $logManagerMock->expects($this->exactly(3))->method('error')
            ->with(
                $message,
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

    public function testLogErrorSprintf()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello";
        $tag = __FUNCTION__;
        $context = [FlagshipConstant::TAG => $tag];

        $logManagerMock->expects($this->exactly(2))->method('error')
            ->with(
                $message,
                $context
            );

        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);

        $logError = Utils::getMethod($logTraitMock, "logErrorSprintf");

        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, []]);

        $config->setLogLevel(LogLevel::CRITICAL);
        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, []]);

        $config->setLogLevel(LogLevel::ERROR);
        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, []]);
    }


    public function testLoginInfo()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello";
        $context = ['exception' => 'hello Exception'];

        $logManagerMock->expects($this->exactly(3))->method('info')
            ->with(
                $message,
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

    public function testLogInfoSprintf()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello";
        $tag = __FUNCTION__;
        $context = [FlagshipConstant::TAG => $tag];

        $logManagerMock->expects($this->exactly(2))->method('info')
            ->with(
                $message,
                $context
            );

        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);

        $logError = Utils::getMethod($logTraitMock, "logInfoSprintf");

        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, []]);

        $config->setLogLevel(LogLevel::CRITICAL);
        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, []]);

        $config->setLogLevel(LogLevel::INFO);
        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, []]);
    }
    public function testWarning()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello";
        $context = ['exception' => 'hello Exception'];

        $logManagerMock->expects($this->exactly(3))->method('warning')
            ->with(
                $message,
                $context
            );

        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);

        $logWarning = Utils::getMethod($logTraitMock, "logWarning");
        $logWarning->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::DEBUG);
        $logWarning->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::WARNING);
        $logWarning->invokeArgs($logTraitMock, [$config, $message, $context]);

        $logWarning->invokeArgs($logTraitMock, [null, $message, $context]);

        $config->setLogLevel(LogLevel::CRITICAL);
        $logWarning->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config = new DecisionApiConfig();
        $config->setLogLevel(LogLevel::WARNING);
        $logWarning->invokeArgs($logTraitMock, [$config, $message, $context]);
    }

    public function testWarningSprintf()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello";
        $tag = __FUNCTION__;
        $context = [FlagshipConstant::TAG => $tag];

        $logManagerMock->expects($this->exactly(2))->method('warning')
            ->with(
                $message,
                $context
            );

        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);

        $logWarning = Utils::getMethod($logTraitMock, "logWarningSprintf");

        $logWarning->invokeArgs($logTraitMock, [$config, $tag, $message, []]);

        $config->setLogLevel(LogLevel::CRITICAL);
        $logWarning->invokeArgs($logTraitMock, [$config, $tag, $message, []]);

        $config->setLogLevel(LogLevel::WARNING);
        $logWarning->invokeArgs($logTraitMock, [$config, $tag, $message, []]);
    }
    public function testLogDebug()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello";
        $context = ['exception' => 'hello Exception'];

        $logManagerMock->expects($this->exactly(2))->method('debug')
            ->with(
                $message,
                $context
            );

        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);

        $logDebug = Utils::getMethod($logTraitMock, "logDebug");
        $logDebug->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::DEBUG);
        $logDebug->invokeArgs($logTraitMock, [$config, $message, $context]);

        $config->setLogLevel(LogLevel::INFO);
        $logDebug->invokeArgs($logTraitMock, [$config, $message, $context]);

        $logDebug->invokeArgs($logTraitMock, [null, $message, $context]);
    }

    public function testLogDebugSprintf()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $logManagerMock = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $message = "hello %s %s";
        $tag = __FUNCTION__;
        $args = ["there", ["key" => "value"]];
        $context = [FlagshipConstant::TAG => $tag];

        $logArgs = [$args[0], json_encode($args[1])];

        $logManagerMock->expects($this->exactly(2))->method('debug')
            ->with(
                vsprintf($message, $logArgs),
                $context
            );

        $config = new DecisionApiConfig();
        $config->setLogManager($logManagerMock);

        $logError = Utils::getMethod($logTraitMock, "logDebugSprintf");

        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, $args]);

        $config->setLogLevel(LogLevel::CRITICAL);
        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, $args]);

        $config->setLogLevel(LogLevel::DEBUG);
        $logError->invokeArgs($logTraitMock, [$config, $tag, $message, $args]);
    }

    public function testGetLogFormat()
    {
        $logTraitMock = $this->getMockForTrait('Flagship\Traits\LogTrait');
        $getLogFormat = Utils::getMethod($logTraitMock, "getLogFormat");


        $message = "message";
        $url = "http://localhost";
        $requestBody = [
            "key" => "value"
        ];
        $headers = ["key" => "value"];
        $duration = 300;
        $value = $getLogFormat->invokeArgs($logTraitMock, [$message, $url, $requestBody, $headers, $duration]);
        $expectedValue = [
            FlagshipConstant::LOG_FORMAT_MESSAGE => $message,
            FlagshipConstant::LOG_FORMAT_URL => $url,
            FlagshipConstant::LOG_FORMAT_BODY => $requestBody,
            FlagshipConstant::LOG_FORMAT_HEADERS => $headers,
            FlagshipConstant::LOG_FORMAT_DURATION => $duration
        ];

        $this->assertSame($expectedValue, $value);
    }
}

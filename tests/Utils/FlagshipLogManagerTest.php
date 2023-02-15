<?php

namespace Flagship\Utils;

require_once __DIR__ . "/../Traits/ErrorLog.php";

use Flagship\Enum\FlagshipConstant;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Flagship\Traits\ErrorLog;

class FlagshipLogManagerTest extends TestCase
{
    public function contextDataProvider()
    {
        return
                [
                    'flagshipSdk' => FlagshipConstant::FLAGSHIP_SDK,
                    'context' => ['process' => 'testError', 'context2' => 'value 2'],
                    'contextString' => '[process => testError, context2 => value 2]' ];
    }

    public function testError()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);

        $logManager->error($message, $data['context']);
        $level = LogLevel::ERROR;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testInfo()
    {
        $data = $this->contextDataProvider();
        $message = 'Test info';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->info($message, $data['context']);
        $level = LogLevel::INFO;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testAlert()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->alert($message, $data['context']);
        $level = LogLevel::ALERT;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testEmergency()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->emergency($message, $data['context']);
        $level = LogLevel::EMERGENCY;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testLog()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $level = LogLevel::EMERGENCY;
        $logManager->log($level, $message, $data['context']);
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testWarning()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->warning($message, $data['context']);
        $level = LogLevel::WARNING;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testCritical()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->critical($message, $data['context']);
        $level = LogLevel::CRITICAL;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testNotice()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Notice';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->notice($message, $data['context']);
        $level = LogLevel::NOTICE;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testDebug()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Debug';
        $logManager = $this->getMockBuilder("Flagship\Utils\FlagshipLogManager")
            ->setMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->debug($message, $data['context']);
        $level = LogLevel::DEBUG;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$formatDate] [$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }
}

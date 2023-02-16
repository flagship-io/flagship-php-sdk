<?php

namespace Flagship\Utils;

require_once __DIR__ . "/../Traits/ErrorLog.php";

use Flagship\Enum\FlagshipConstant;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Flagship\Traits\ErrorLog;

class FlagshipLogManager8Test extends TestCase
{
    public function contextDataProvider()
    {
        return
            [
                'flagshipSdk' => FlagshipConstant::FLAGSHIP_SDK,
                'context' => ['process' => 'testError', 'context2' => 'value 2'],
                'contextString' => '[process => testError, context2 => value 2]' ];
    }

    public function getMessageError($formatDate,$level, $message, $tag ){
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        return "[$formatDate] [$flagshipSdk] [$level] {$tag} $message";
    }

    public function testError()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);

        $logManager->error($message, $data['context']);
        $level = LogLevel::ERROR;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testInfo()
    {
        $data = $this->contextDataProvider();
        $message = 'Test info';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->info($message, $data['context']);
        $level = LogLevel::INFO;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testAlert()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->alert($message, $data['context']);
        $level = LogLevel::ALERT;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testEmergency()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->emergency($message, $data['context']);
        $level = LogLevel::EMERGENCY;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testLog()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $level = LogLevel::EMERGENCY;
        $logManager->log($level, $message, $data['context']);
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testWarning()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->warning($message, $data['context']);
        $level = LogLevel::WARNING;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testCritical()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->critical($message, $data['context']);
        $level = LogLevel::CRITICAL;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testNotice()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Notice';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->notice($message, $data['context']);
        $level = LogLevel::NOTICE;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testDebug()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Debug';
        $logManager = $this->getMockBuilder(FlagshipLogManager8::class)
            ->onlyMethods(["getDateTime"])
            ->getMock();
        $formatDate = "2023-02-15 11:08:10.455";
        $logManager->expects($this->once())
            ->method("getDateTime")
            ->willReturn($formatDate);
        $logManager->debug($message, $data['context']);
        $level = LogLevel::DEBUG;
        $messageError = $this->getMessageError($formatDate, $level, $message, $data['contextString']);
        $this->assertSame($messageError, ErrorLog::$error);
    }
}

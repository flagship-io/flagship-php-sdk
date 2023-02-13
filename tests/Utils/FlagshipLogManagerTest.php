<?php

namespace Flagship\Utils;

require_once __dir__ . '/../Assets/ErrorLog.php';

use Flagship\Assets\ErrorLog;
use Flagship\Enum\FlagshipConstant;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

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
        $logManager = new FlagshipLogManager();
        $logManager->error($message, $data['context']);
        $level = LogLevel::ERROR;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testInfo()
    {
        $data = $this->contextDataProvider();
        $message = 'Test info';
        $logManager = new FlagshipLogManager();
        $logManager->info($message, $data['context']);
        $level = LogLevel::INFO;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testAlert()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->alert($message, $data['context']);
        $level = LogLevel::ALERT;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testEmergency()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->emergency($message, $data['context']);
        $level = LogLevel::EMERGENCY;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testLog()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $level = LogLevel::EMERGENCY;
        $logManager->log($level, $message, $data['context']);
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testWarning()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->warning($message, $data['context']);
        $level = LogLevel::WARNING;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testCritical()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->critical($message, $data['context']);
        $level = LogLevel::CRITICAL;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    public function testNotice()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Notice';
        $logManager = new FlagshipLogManager();
        $logManager->notice($message, $data['context']);
        $level = LogLevel::NOTICE;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }


    public function testDebug()
    {
        $data = $this->contextDataProvider();
        $message = 'Test Debug';
        $logManager = new FlagshipLogManager();
        $logManager->debug($message, $data['context']);
        $level = LogLevel::DEBUG;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }
}

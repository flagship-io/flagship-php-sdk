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
        return [
            [
                [
                    'flagshipSdk' => FlagshipConstant::FLAGSHIP_SDK,
                    'context' => ['process' => 'testError', 'context2' => 'value 2'],
                    'contextString' => '[process => testError, context2 => value 2]'
                ]
            ]
        ];
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testError($data)
    {
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->error($message, $data['context']);
        $level = LogLevel::ERROR;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testInfo($data)
    {
        $message = 'Test info';
        $logManager = new FlagshipLogManager();
        $logManager->info($message, $data['context']);
        $level = LogLevel::INFO;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testAlert($data)
    {
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->alert($message, $data['context']);
        $level = LogLevel::ALERT;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testEmergency($data)
    {
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->emergency($message, $data['context']);
        $level = LogLevel::EMERGENCY;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testLog($data)
    {
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $level = LogLevel::EMERGENCY;
        $logManager->log($level, $message, $data['context']);
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testWarning($data)
    {
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->warning($message, $data['context']);
        $level = LogLevel::WARNING;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testCritical($data)
    {
        $message = 'Test Error';
        $logManager = new FlagshipLogManager();
        $logManager->critical($message, $data['context']);
        $level = LogLevel::CRITICAL;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testNotice($data)
    {
        $message = 'Test Notice';
        $logManager = new FlagshipLogManager();
        $logManager->notice($message, $data['context']);
        $level = LogLevel::NOTICE;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testDebug($data)
    {
        $message = 'Test Debug';
        $logManager = new FlagshipLogManager();
        $logManager->debug($message, $data['context']);
        $level = LogLevel::DEBUG;
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $messageError = "[$flagshipSdk] [$level] $message {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }
}

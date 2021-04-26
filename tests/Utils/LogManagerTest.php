<?php

namespace Flagship\Utils;

require_once __dir__ . '/../Assets/ErrorLog.php';

use Flagship\Assets\ErrorLog;
use Flagship\Enum\LogLevel;
use PHPUnit\Framework\TestCase;

class LogManagerTest extends TestCase
{
    public function contextDataProvider()
    {
        return [
            [
                [
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
        $logManager = new LogManager();
        $logManager->error($message, $data['context']);
        $level = LogLevel::ERROR;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testInfo($data)
    {
        $message = 'Test info';
        $logManager = new LogManager();
        $logManager->info($message, $data['context']);
        $level = LogLevel::INFO;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testAlert($data)
    {
        $message = 'Test Error';
        $logManager = new LogManager();
        $logManager->alert($message, $data['context']);
        $level = LogLevel::ALERT;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testEmergency($data)
    {
        $message = 'Test Error';
        $logManager = new LogManager();
        $logManager->emergency($message, $data['context']);
        $level = LogLevel::EMERGENCY;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testLog($data)
    {
        $message = 'Test Error';
        $logManager = new LogManager();
        $level = LogLevel::EMERGENCY;
        $logManager->log($level, $message, $data['context']);
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testWarning($data)
    {
        $message = 'Test Error';
        $logManager = new LogManager();
        $logManager->warning($message, $data['context']);
        $level = LogLevel::WARNING;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testCritical($data)
    {
        $message = 'Test Error';
        $logManager = new LogManager();
        $logManager->critical($message, $data['context']);
        $level = LogLevel::CRITICAL;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testNotice($data)
    {
        $message = 'Test Notice';
        $logManager = new LogManager();
        $logManager->notice($message, $data['context']);
        $level = LogLevel::NOTICE;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }

    /**
     * @dataProvider contextDataProvider
     */
    public function testDebug($data)
    {
        $message = 'Test Debug';
        $logManager = new LogManager();
        $logManager->debug($message, $data['context']);
        $level = LogLevel::DEBUG;
        $messageError = "[{$level}] {$message} {$data['contextString']}";
        $this->assertSame($messageError, ErrorLog::$error);
    }
}

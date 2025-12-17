<?php

namespace Flagship\Utils;

use Psr\Log\LogLevel;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipConstant;

class StringableTestObject
{
    public function __toString(): string
    {
        return 'stringable-test';
    }
}

class FlagshipLogManagerTest extends TestCase
{
    use PHPMock;

    private FlagshipLogManager $logManager;
    private array $capturedLogs = [];

    protected function setUp(): void
    {
        $this->logManager = new FlagshipLogManager();
        $this->capturedLogs = [];
    }

    private function mockErrorLog(): void
    {
        $errorLog = $this->getFunctionMock('Flagship\Traits', 'error_log');
        $errorLog->expects($this->once())
            ->willReturnCallback(function ($message) {
                $this->capturedLogs[] = $message;
                return true;
            });
    }

    public function testEmergencyLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Emergency message';
        $this->logManager->emergency($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::EMERGENCY, '/') . '\].*Emergency message/',
            $this->capturedLogs[0]
        );
    }

    public function testAlertLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Alert message';
        $this->logManager->alert($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::ALERT, '/') . '\].*Alert message/',
            $this->capturedLogs[0]
        );
    }

    public function testCriticalLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Critical message';
        $this->logManager->critical($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::CRITICAL, '/') . '\].*Critical message/',
            $this->capturedLogs[0]
        );
    }

    public function testErrorLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Error message';
        $this->logManager->error($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::ERROR, '/') . '\].*Error message/',
            $this->capturedLogs[0]
        );
    }

    public function testWarningLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Warning message';
        $this->logManager->warning($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::WARNING, '/') . '\].*Warning message/',
            $this->capturedLogs[0]
        );
    }

    public function testNoticeLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Notice message';
        $this->logManager->notice($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::NOTICE, '/') . '\].*Notice message/',
            $this->capturedLogs[0]
        );
    }

    public function testInfoLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Info message';
        $this->logManager->info($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::INFO, '/') . '\].*Info message/',
            $this->capturedLogs[0]
        );
    }

    public function testDebugLogsWithCorrectLevel(): void
    {
        $this->mockErrorLog();

        $message = 'Debug message';
        $this->logManager->debug($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::DEBUG, '/') . '\].*Debug message/',
            $this->capturedLogs[0]
        );
    }

    public function testLogWithStringableMessage(): void
    {
        $this->mockErrorLog();

        $message = new StringableTestObject();
        $this->logManager->info($message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertStringContainsString('stringable-test', $this->capturedLogs[0]);
    }

    public function testLogWithContext(): void
    {
        $this->mockErrorLog();

        $message = 'Test with context';
        $context = ['user_id' => 123, 'action' => 'login'];

        $this->logManager->info($message, $context);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[user_id => 123, action => login\].*Test with context/',
            $this->capturedLogs[0]
        );
    }

    public function testLogWithComplexContext(): void
    {
        $this->mockErrorLog();

        $message = 'Complex context';
        $context = [
            'string' => 'value',
            'number' => 42,
            'bool' => true,
            'array' => ['nested' => 'data'],
            'stringable' => new StringableTestObject()
        ];

        $this->logManager->debug($message, $context);

        $this->assertCount(1, $this->capturedLogs);
        $log = $this->capturedLogs[0];
        $this->assertStringContainsString('string => value', $log);
        $this->assertStringContainsString('number => 42', $log);
        $this->assertStringContainsString('bool => 1', $log);
        $this->assertStringContainsString('stringable => stringable-test', $log);
    }

    public function testLogMethodDirectly(): void
    {
        $this->mockErrorLog();

        $level = LogLevel::WARNING;
        $message = 'Direct log call';

        $this->logManager->log($level, $message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(LogLevel::WARNING, '/') . '\].*Direct log call/',
            $this->capturedLogs[0]
        );
    }

    public function testLogFormatIncludesFlagshipSDK(): void
    {
        $this->mockErrorLog();

        $this->logManager->info('Test message');

        $this->assertCount(1, $this->capturedLogs);
        $this->assertStringContainsString('[' . FlagshipConstant::FLAGSHIP_SDK . ']', $this->capturedLogs[0]);
    }

    public function testLogFormatIncludesTimestamp(): void
    {
        $this->mockErrorLog();

        $this->logManager->info('Test message');

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}\]/',
            $this->capturedLogs[0]
        );
    }

    public function testLogWithEmptyMessage(): void
    {
        $this->mockErrorLog();

        $this->logManager->info('');

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(FlagshipConstant::FLAGSHIP_SDK, '/') . '\] \[info\]/',
            $this->capturedLogs[0]
        );
    }

    public function testLogWithEmptyContext(): void
    {
        $this->mockErrorLog();

        $this->logManager->warning('Warning', []);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertStringContainsString('Warning', $this->capturedLogs[0]);
        $this->assertStringNotContainsString('[]', $this->capturedLogs[0]);
    }

    public function testLogWithCustomLevel(): void
    {
        $this->mockErrorLog();

        $this->logManager->log('CUSTOM_LEVEL', 'Custom message');

        $this->assertCount(1, $this->capturedLogs);
        $this->assertStringContainsString('[CUSTOM_LEVEL]', $this->capturedLogs[0]);
        $this->assertStringContainsString('Custom message', $this->capturedLogs[0]);
    }
}

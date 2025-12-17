<?php

namespace Flagship\Traits;

use DateTime;
use Flagship\BaseTestCase;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipConstant;
use phpmock\phpunit\PHPMock;

class ConcreteLogManager
{
    use CommonLogManagerTrait;
}

class StringableObject
{
    public function __toString(): string
    {
        return 'stringable';
    }
}

class CommonLogManagerTraitTest extends BaseTestCase
{
    use PHPMock;

    private ConcreteLogManager $logManager;


    protected function setUp(): void
    {
        $this->logManager = new ConcreteLogManager();
        $this->capturedLogs = [];
    }



    public function testGetDateTimeReturnsCorrectFormat(): void
    {
        $dateTime = $this->logManager->getDateTime();

        // Verify format: Y-m-d H:i:s.u (e.g., 2024-01-15 14:30:45.123456)
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/',
            $dateTime
        );
    }

    public function testCustomLogWithStringLevel(): void
    {
        $this->mockErrorLog();

        $level = 'INFO';
        $message = 'Test message';

        $this->logManager->customLog($level, $message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[.*\] \[' . preg_quote(FlagshipConstant::FLAGSHIP_SDK, '/') . '\] \[INFO\]  Test message/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithNumericLevel(): void
    {
        $this->mockErrorLog();

        $level = 200;
        $message = 'Numeric level test';

        $this->logManager->customLog($level, $message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[.*\] \[' . preg_quote(FlagshipConstant::FLAGSHIP_SDK, '/') . '\] \[200\]  Numeric level test/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithStringableObject(): void
    {
        $this->mockErrorLog();

        $level = new StringableObject();
        $message = 'Stringable object test';

        $this->logManager->customLog($level, $message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[.*\] \[' . preg_quote(FlagshipConstant::FLAGSHIP_SDK, '/') . '\] \[stringable\]  Stringable object test/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithNonStringableObject(): void
    {
        $this->mockErrorLog();

        $level = new \stdClass();
        $message = 'Non-stringable object test';

        $this->logManager->customLog($level, $message);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[.*\] \[' . preg_quote(FlagshipConstant::FLAGSHIP_SDK, '/') . '\] \[object\]  Non-stringable object test/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithEmptyContext(): void
    {
        $this->mockErrorLog();

        $level = 'DEBUG';
        $message = 'Empty context test';

        $this->logManager->customLog($level, $message, []);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[.*\] \[' . preg_quote(FlagshipConstant::FLAGSHIP_SDK, '/') . '\] \[DEBUG\]  Empty context test/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithScalarContext(): void
    {
        $this->mockErrorLog();

        $level = 'INFO';
        $message = 'Context test';
        $context = ['key1' => 'value1', 'key2' => 42, 'key3' => true];

        $this->logManager->customLog($level, $message, $context);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[key1 => value1, key2 => 42, key3 => 1\] Context test/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithArrayInContext(): void
    {
        $this->mockErrorLog();

        $level = 'WARNING';
        $message = 'Array context test';
        $context = ['data' => ['nested' => 'value']];

        $this->logManager->customLog($level, $message, $context);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[data => \{"nested":"value"\}\] Array context test/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithStringableObjectInContext(): void
    {
        $this->mockErrorLog();

        $level = 'ERROR';
        $message = 'Stringable in context';
        $context = ['obj' => new StringableObject()];

        $this->logManager->customLog($level, $message, $context);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[obj => stringable\] Stringable in context/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogWithMixedContext(): void
    {
        $this->mockErrorLog();

        $level = 'INFO';
        $message = 'Mixed context';
        $context = [
            'string' => 'test',
            'number' => 123,
            'array' => [1, 2, 3],
            'stringable' => new StringableObject()
        ];

        $this->logManager->customLog($level, $message, $context);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[string => test, number => 123, array => \[1,2,3\], stringable => stringable\] Mixed context/',
            $this->capturedLogs[0]
        );
    }

    public function testCustomLogFormatStructure(): void
    {
        $this->mockErrorLog();

        $level = 'INFO';
        $message = 'Structure test';

        $this->logManager->customLog($level, $message);

        $this->assertCount(1, $this->capturedLogs);
        $output = $this->capturedLogs[0];

        // Verify the log contains all required components
        $this->assertStringContainsString('[' . FlagshipConstant::FLAGSHIP_SDK . ']', $output);
        $this->assertStringContainsString('[INFO]', $output);
        $this->assertStringContainsString('Structure test', $output);

        // Verify date format is present
        $this->assertMatchesRegularExpression('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}\]/', $output);
    }

    public function testCustomLogWithSingleContextItem(): void
    {
        $this->mockErrorLog();

        $level = 'DEBUG';
        $message = 'Single item';
        $context = ['key' => 'value'];

        $this->logManager->customLog($level, $message, $context);

        $this->assertCount(1, $this->capturedLogs);
        $this->assertMatchesRegularExpression(
            '/\[key => value\] Single item/',
            $this->capturedLogs[0]
        );
    }
}

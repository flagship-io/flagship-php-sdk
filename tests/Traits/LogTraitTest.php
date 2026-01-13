<?php

namespace Flagship\Traits;

use Flagship\Enum\LogLevel;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\DecisionApiConfig;
use PHPUnit\Framework\MockObject\MockObject;

// Test helper classes
class ConcreteLogTraitClass
{
    use LogTrait;

    public function publicFormatArgs(array $args = []): array
    {
        return $this->formatArgs($args);
    }

    public function publicGetLogFormat(
        ?string $message,
        string $url,
        ?array $requestBody,
        ?array $headers,
        float|int|null $duration,
        $responseHeader = null,
        $responseBody = null,
        $responseStatus = null
    ): array {
        return $this->getLogFormat(
            $message,
            $url,
            $requestBody,
            $headers,
            $duration,
            $responseHeader,
            $responseBody,
            $responseStatus
        );
    }

    // Expose protected log methods as public for testing
    public function publicLogDebugSprintf(?FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        $this->logDebugSprintf($config, $tag, $message, $args);
    }

    public function publicLogDebug(FlagshipConfig $config, string $message, array $context = []): void
    {
        $this->logDebug($config, $message, $context);
    }

    public function publicLogErrorSprintf(FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        $this->logErrorSprintf($config, $tag, $message, $args);
    }

    public function publicLogError(?FlagshipConfig $config, string $message, array $context = []): void
    {
        $this->logError($config, $message, $context);
    }

    public function publicLogInfoSprintf(FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        $this->logInfoSprintf($config, $tag, $message, $args);
    }

    public function publicLogInfo(FlagshipConfig $config, string $message, array $context = []): void
    {
        $this->logInfo($config, $message, $context);
    }

    public function publicLogWarningSprintf(?FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        $this->logWarningSprintf($config, $tag, $message, $args);
    }

    public function publicLogWarning(FlagshipConfig $config, string $message, array $context = []): void
    {
        $this->logWarning($config, $message, $context);
    }
}

class StringableTestObject
{
    public function __toString(): string
    {
        return 'stringable-object';
    }
}

class JsonSerializableTestObject implements \JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return ['key' => 'value', 'number' => 42];
    }
}

class PlainTestObject
{
    public string $property = 'value';
}

class LogTraitTest extends TestCase
{
    private ConcreteLogTraitClass $logTrait;
    private FlagshipConfig $config;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->logTrait = new ConcreteLogTraitClass();
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            '',
            false,
            true, true,
            ['debug', 'info', 'warning', 'error']
        );
        $this->config = DecisionApiConfig::decisionApi()
            ->setLogManager($this->logger)
            ->setLogLevel(LogLevel::ALL);
    }

    // formatArgs() tests
    public function testFormatArgsWithEmptyArray(): void
    {
        $result = $this->logTrait->publicFormatArgs([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFormatArgsWithScalarValues(): void
    {
        $args = ['string', 42, 3.14, true, false, null];
        $result = $this->logTrait->publicFormatArgs($args);

        $this->assertEquals('string', $result[0]);
        $this->assertEquals(42, $result[1]);
        $this->assertEquals(3.14, $result[2]);
        $this->assertTrue($result[3]);
        $this->assertFalse($result[4]);
        $this->assertNull($result[5]);
    }

    public function testFormatArgsWithArray(): void
    {
        $args = [['key' => 'value', 'number' => 123]];
        $result = $this->logTrait->publicFormatArgs($args);

        $this->assertIsString($result[0]);
        $decoded = json_decode($result[0], true);
        $this->assertEquals('value', $decoded['key']);
        $this->assertEquals(123, $decoded['number']);
    }

    public function testFormatArgsWithStringableObject(): void
    {
        $args = [new StringableTestObject()];
        $result = $this->logTrait->publicFormatArgs($args);

        $this->assertEquals('stringable-object', $result[0]);
    }

    public function testFormatArgsWithJsonSerializable(): void
    {
        $args = [new JsonSerializableTestObject()];
        $result = $this->logTrait->publicFormatArgs($args);

        $this->assertIsString($result[0]);
        $decoded = json_decode($result[0], true);
        $this->assertEquals('value', $decoded['key']);
        $this->assertEquals(42, $decoded['number']);
    }

    public function testFormatArgsWithPlainObject(): void
    {
        $args = [new PlainTestObject()];
        $result = $this->logTrait->publicFormatArgs($args);

        $this->assertEquals(PlainTestObject::class, $result[0]);
    }

    public function testFormatArgsWithResource(): void
    {
        $resource = fopen('php://memory', 'r');
        $args = [$resource];
        $result = $this->logTrait->publicFormatArgs($args);

        $this->assertStringContainsString('[Resource:', $result[0]);
        $this->assertStringContainsString('stream', $result[0]);

        fclose($resource);
    }

    public function testFormatArgsWithMixedTypes(): void
    {
        $args = [
            'text',
            123,
            ['nested' => 'array'],
            new StringableTestObject(),
            true,
            null
        ];
        $result = $this->logTrait->publicFormatArgs($args);

        $this->assertCount(6, $result);
        $this->assertEquals('text', $result[0]);
        $this->assertEquals(123, $result[1]);
        $this->assertIsString($result[2]);
        $this->assertEquals('stringable-object', $result[3]);
        $this->assertTrue($result[4]);
        $this->assertNull($result[5]);
    }

    public function testFormatArgsWithNestedArrays(): void
    {
        $args = [
            [
                'level1' => [
                    'level2' => [
                        'level3' => 'deep value'
                    ]
                ]
            ]
        ];
        $result = $this->logTrait->publicFormatArgs($args);

        $decoded = json_decode($result[0], true);
        $this->assertEquals('deep value', $decoded['level1']['level2']['level3']);
    }

    // logDebugSprintf() tests
    public function testLogDebugSprintfCallsLoggerWhenLevelIsDebug(): void
    {
        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->equalTo('Test message: value1, 42'),
                $this->equalTo([FlagshipConstant::TAG => 'TEST_TAG'])
            );

        $this->logTrait->publicLogDebugSprintf(
            $this->config,
            'TEST_TAG',
            'Test message: %s, %d',
            ['value1', 42]
        );
    }

    public function testLogDebugSprintfDoesNotLogWhenLevelIsTooHigh(): void
    {
        $config = DecisionApiConfig::decisionApi()
            ->setLogManager($this->logger)
            ->setLogLevel(LogLevel::INFO);

        $this->logger->expects($this->never())->method('debug');

        $this->logTrait->publicLogDebugSprintf($config, 'TAG', 'Message', []);
    }

    public function testLogDebugSprintfDoesNotLogWhenLogManagerIsNull(): void
    {
        $config = DecisionApiConfig::decisionApi()
            ->setLogLevel(LogLevel::DEBUG);

        // Should not throw exception
        $this->logTrait->publicLogDebugSprintf($config, 'TAG', 'Message', []);
        $this->assertTrue(true);
    }

    public function testLogDebugSprintfWithNullConfig(): void
    {
        // Should not throw exception
        $this->logTrait->publicLogDebugSprintf(null, 'TAG', 'Message', []);
        $this->assertTrue(true);
    }

    // logDebug() tests
    public function testLogDebugCallsLogger(): void
    {
        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->equalTo('Debug message'),
                $this->equalTo(['context' => 'value'])
            );

        $this->logTrait->publicLogDebug($this->config, 'Debug message', ['context' => 'value']);
    }

    public function testLogDebugWithEmptyContext(): void
    {
        $this->logger->expects($this->once())
            ->method('debug')
            ->with($this->equalTo('Message'), $this->equalTo([]));

        $this->logTrait->publicLogDebug($this->config, 'Message');
    }

    public function testLogDebugDoesNotLogWhenLevelIsTooHigh(): void
    {
        $config = DecisionApiConfig::decisionApi()
            ->setLogManager($this->logger)
            ->setLogLevel(LogLevel::INFO);

        $this->logger->expects($this->never())->method('debug');

        $this->logTrait->publicLogDebug($config, 'Message', []);
    }

    // logErrorSprintf() tests
    public function testLogErrorSprintfCallsLogger(): void
    {
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->equalTo('Error: 404'),
                $this->equalTo([FlagshipConstant::TAG => 'ERROR_TAG'])
            );

        $this->logTrait->publicLogErrorSprintf($this->config, 'ERROR_TAG', 'Error: %d', [404]);
    }

    public function testLogErrorSprintfDoesNotLogWhenLevelIsTooHigh(): void
    {
        $config = DecisionApiConfig::decisionApi()
            ->setLogManager($this->logger)
            ->setLogLevel(LogLevel::NONE);

        $this->logger->expects($this->never())->method('error');

        $this->logTrait->publicLogErrorSprintf($config, 'TAG', 'Message', []);
    }

    // logError() tests
    public function testLogErrorCallsLogger(): void
    {
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->equalTo('Error occurred'),
                $this->equalTo(['code' => 500])
            );

        $this->logTrait->publicLogError($this->config, 'Error occurred', ['code' => 500]);
    }

    public function testLogErrorWithNullConfig(): void
    {
        // Should not throw exception
        $this->logTrait->publicLogError(null, 'Message', []);
        $this->assertTrue(true);
    }

    // logInfoSprintf() tests
    public function testLogInfoSprintfCallsLogger(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Info: user123'),
                $this->equalTo([FlagshipConstant::TAG => 'INFO_TAG'])
            );

        $this->logTrait->publicLogInfoSprintf($this->config, 'INFO_TAG', 'Info: %s', ['user123']);
    }

    public function testLogInfoSprintfDoesNotLogWhenLevelIsTooHigh(): void
    {
        $config = DecisionApiConfig::decisionApi()
            ->setLogManager($this->logger)
            ->setLogLevel(LogLevel::WARNING);

        $this->logger->expects($this->never())->method('info');

        $this->logTrait->publicLogInfoSprintf($config, 'TAG', 'Message', []);
    }

    // logInfo() tests
    public function testLogInfoCallsLogger(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Information'),
                $this->equalTo(['data' => 'test'])
            );

        $this->logTrait->publicLogInfo($this->config, 'Information', ['data' => 'test']);
    }

    public function testLogInfoDoesNotLogWhenLevelIsTooHigh(): void
    {
        $config = DecisionApiConfig::decisionApi()
            ->setLogManager($this->logger)
            ->setLogLevel(LogLevel::ERROR);

        $this->logger->expects($this->never())->method('info');

        $this->logTrait->publicLogInfo($config, 'Message', []);
    }

    // logWarningSprintf() tests
    public function testLogWarningSprintfCallsLogger(): void
    {
        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->equalTo('Warning: timeout 30s'),
                $this->equalTo([FlagshipConstant::TAG => 'WARN_TAG'])
            );

        $this->logTrait->publicLogWarningSprintf(
            $this->config,
            'WARN_TAG',
            'Warning: timeout %ds',
            [30]
        );
    }

    public function testLogWarningSprintfWithNullConfig(): void
    {
        // Should not throw exception
        $this->logTrait->publicLogWarningSprintf(null, 'TAG', 'Message', []);
        $this->assertTrue(true);
    }

    // logWarning() tests
    public function testLogWarningCallsLogger(): void
    {
        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->equalTo('Warning message'),
                $this->equalTo(['type' => 'timeout'])
            );

        $this->logTrait->publicLogWarning($this->config, 'Warning message', ['type' => 'timeout']);
    }

    public function testLogWarningDoesNotLogWhenLevelIsTooHigh(): void
    {
        $config = DecisionApiConfig::decisionApi()
            ->setLogManager($this->logger)
            ->setLogLevel(LogLevel::ERROR);

        $this->logger->expects($this->never())->method('warning');

        $this->logTrait->publicLogWarning($config, 'Message', []);
    }

    // getLogFormat() tests
    public function testGetLogFormatWithAllParameters(): void
    {
        $result = $this->logTrait->publicGetLogFormat(
            'Test message',
            'https://api.example.com',
            ['key' => 'value'],
            ['Authorization' => 'Bearer token'],
            123.45,
            ['Content-Type' => 'application/json'],
            ['result' => 'success'],
            200
        );

        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_MESSAGE, $result);
        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_URL, $result);
        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_REQUEST_BODY, $result);
        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_REQUEST_HEADERS, $result);
        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_DURATION, $result);
        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_RESPONSE_BODY, $result);
        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_RESPONSE_STATUS, $result);

        $this->assertEquals('Test message', $result[FlagshipConstant::LOG_FORMAT_MESSAGE]);
        $this->assertEquals('https://api.example.com', $result[FlagshipConstant::LOG_FORMAT_URL]);
        $this->assertEquals(123.45, $result[FlagshipConstant::LOG_FORMAT_DURATION]);
        $this->assertEquals(200, $result[FlagshipConstant::LOG_FORMAT_RESPONSE_STATUS]);
    }

    public function testGetLogFormatWithMinimalParameters(): void
    {
        $result = $this->logTrait->publicGetLogFormat(
            null,
            'https://api.example.com',
            null,
            null,
            null
        );

        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_URL, $result);
        $this->assertArrayNotHasKey(FlagshipConstant::LOG_FORMAT_MESSAGE, $result);
        $this->assertArrayNotHasKey(FlagshipConstant::LOG_FORMAT_REQUEST_BODY, $result);
        $this->assertArrayNotHasKey(FlagshipConstant::LOG_FORMAT_DURATION, $result);
    }

    public function testGetLogFormatWithEmptyMessage(): void
    {
        $result = $this->logTrait->publicGetLogFormat(
            '',
            'https://api.example.com',
            null,
            null,
            null
        );

        $this->assertArrayNotHasKey(FlagshipConstant::LOG_FORMAT_MESSAGE, $result);
    }

    public function testGetLogFormatWithZeroDuration(): void
    {
        $result = $this->logTrait->publicGetLogFormat(
            null,
            'https://api.example.com',
            null,
            null,
            0
        );

        $this->assertArrayNotHasKey(FlagshipConstant::LOG_FORMAT_DURATION, $result);
    }

    public function testGetLogFormatWithEmptyArrays(): void
    {
        $result = $this->logTrait->publicGetLogFormat(
            'Message',
            'https://api.example.com',
            [],
            [],
            null
        );

        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_REQUEST_BODY, $result);
        $this->assertArrayHasKey(FlagshipConstant::LOG_FORMAT_REQUEST_HEADERS, $result);
        $this->assertEmpty($result[FlagshipConstant::LOG_FORMAT_REQUEST_BODY]);
        $this->assertEmpty($result[FlagshipConstant::LOG_FORMAT_REQUEST_HEADERS]);
    }

    // Integration tests
    public function testCompleteLoggingWorkflow(): void
    {
        $this->logger->expects($this->exactly(4))
            ->method($this->anything());

        $this->logTrait->publicLogDebug($this->config, 'Debug message');
        $this->logTrait->publicLogInfo($this->config, 'Info message');
        $this->logTrait->publicLogWarning($this->config, 'Warning message');
        $this->logTrait->publicLogError($this->config, 'Error message');
    }

    public function testSprintfMethodsFormatCorrectly(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('User user123 performed action with status 200'),
                $this->anything()
            );

        $this->logTrait->publicLogInfoSprintf(
            $this->config,
            'TAG',
            'User %s performed %s with status %d',
            ['user123', 'action', 200]
        );
    }

    public function testFormatArgsWithComplexObjectGraph(): void
    {
        $complex = [
            'string' => 'value',
            'number' => 42,
            'nested' => [
                'object' => new StringableTestObject(),
                'array' => [1, 2, 3]
            ],
            'json' => new JsonSerializableTestObject()
        ];

        $result = $this->logTrait->publicFormatArgs([$complex]);
        $this->assertIsString($result[0]);

        $decoded = json_decode($result[0], true);
        $this->assertEquals('value', $decoded['string']);
        $this->assertEquals(42, $decoded['number']);
    }
}

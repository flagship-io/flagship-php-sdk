<?php

namespace Flagship\Traits;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;

trait LogTrait
{
    /**
     * Format arguments for logging by converting them to scalar values
     * 
     * @param array<mixed> $args Arguments to format
     * @return array<scalar|null> Formatted arguments safe for logging
     */
    protected function formatArgs(array $args = []): array
    {
        $formatted = [];

        foreach ($args as $arg) {
            $formatted[] = $this->convertToScalar($arg);
        }

        return $formatted;
    }

    /**
     * Convert any value to a scalar or null for safe logging
     * 
     * @param mixed $value The value to convert
     * @return scalar|null Scalar representation of the value
     */
    private function convertToScalar(mixed $value): string|int|float|bool|null
    {

        return match (true) {
            $value === null => null,
            is_bool($value) => $value,
            is_int($value) => $value,
            is_float($value) => $value,
            is_string($value) => $value,
            is_array($value) => $this->serializeToJson($value),
            is_object($value) => $this->convertObjectToString($value),
            is_resource($value) => $this->formatResource($value),
            default => $this->serializeToJson($value),
        };
    }

    /**
     * Convert an object to its string representation
     * 
     * @param object $object The object to convert
     * @return string String representation
     */
    private function convertObjectToString(object $object): string
    {


        if (method_exists($object, '__toString')) {
            return (string) $object;
        }

        if ($object instanceof \JsonSerializable) {
            return $this->serializeToJson($object);
        }


        return get_class($object);
    }

    /**
     * Serialize a value to JSON with error handling
     * 
     * @param mixed $value Value to serialize
     * @return string JSON string or error message
     */
    private function serializeToJson(mixed $value): string
    {
        try {
            $json = json_encode(
                $value
            );
            return $json ?: '{}';
            // @phpstan-ignore catch.neverThrown
        } catch (\JsonException $e) {
            return sprintf('[JSON Error: %s]', $e->getMessage());
        }
    }

    /**
     * Format a resource for logging
     * 
     * @param resource $resource The resource to format
     * @return string Resource description
     */
    private function formatResource($resource): string
    {
        $type = get_resource_type($resource);
        return sprintf('[Resource: %s]', $type);
    }

    /**
     * Format error when __toString fails
     * 
     * @param object $object The object that failed
     * @return string Error description
     */
    private function formatToStringError(object $object, \Throwable $error): string
    {
        $class = get_class($object);
        return sprintf('[%s __toString error: %s]', $class, $error->getMessage());
    }

    /**
     * @param ?FlagshipConfig $config
     * @param string $tag
     * @param string $message
     * @param array<mixed> $args
     * @return void
     */
    protected function logDebugSprintf(?FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        if (
            $config?->getLogLevel()->value < LogLevel::DEBUG->value ||
            is_null($config->getLogManager())
        ) {
            return;
        }
        $customMessage = vsprintf($message, $this->formatArgs($args));
        $this->logDebug($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param array<mixed> $context
     *@return void
     */
    protected function logDebug(FlagshipConfig $config, string $message, array $context = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::DEBUG->value || is_null($config->getLogManager())) {
            return;
        }
        $config->getLogManager()->debug($message, $context);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $tag
     * @param string $message
     * @param array<mixed> $args
     * @return void
     */
    protected function logErrorSprintf(FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::ERROR->value || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = vsprintf($message, $this->formatArgs($args));
        $this->logError($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }
    /**
     * @param ?FlagshipConfig $config
     * @param string $message
     * @param array<mixed> $context
     * @return void
     */
    protected function logError(?FlagshipConfig $config, string $message, array $context = []): void
    {
        if ($config?->getLogLevel()->value < LogLevel::ERROR->value || is_null($config->getLogManager())) {
            return;
        }
        $config->getLogManager()->error($message, $context);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $tag
     * @param string $message
     * @param array<mixed> $args
     * @return void
     */
    protected function logInfoSprintf(FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::INFO->value || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = vsprintf($message, $this->formatArgs($args));
        $this->logInfo($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }
    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param  array<mixed> $context
     * @return void
     */
    protected function logInfo(FlagshipConfig $config, string $message, array $context = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::INFO->value || is_null($config->getLogManager())) {
            return;
        }
        $config->getLogManager()->info($message, $context);
    }

    /**
     * @param FlagshipConfig|null $config
     * @param string $tag
     * @param string $message
     * @param array< mixed> $args
     * @return void
     */
    protected function logWarningSprintf(FlagshipConfig|null $config, string $tag, string $message, array $args = []): void
    {
        if ($config?->getLogLevel()->value < LogLevel::WARNING->value || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = vsprintf($message, $this->formatArgs($args));
        $this->logWarning($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logWarning(FlagshipConfig $config, string $message, array $context = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::WARNING->value || is_null($config->getLogManager())) {
            return;
        }
        $config->getLogManager()->warning($message, $context);
    }

    /**
     * @param string|null $message
     * @param string $url
     * @param ?array<mixed> $requestBody
     * @param ?array<string, string> $headers
     * @param float|int|null $duration
     * @param mixed $responseHeader
     * @param mixed $responseBody
     * @param mixed $responseStatus
     * @return array<string, mixed>
     */
    protected function getLogFormat(
        string|null $message,
        string $url,
        ?array $requestBody,
        ?array $headers,
        float|int|null $duration,
        $responseHeader = null,
        $responseBody = null,
        $responseStatus = null
    ): array {
        $format = [];
        if ($message) {
            $format[FlagshipConstant::LOG_FORMAT_MESSAGE] = $message;
        }
        if ($url) {
            $format[FlagshipConstant::LOG_FORMAT_URL] = $url;
        }
        if ($requestBody !== null) {
            $format[FlagshipConstant::LOG_FORMAT_REQUEST_BODY] = $requestBody;
        }
        if ($headers !== null) {
            $format[FlagshipConstant::LOG_FORMAT_REQUEST_HEADERS] = $headers;
        }
        if ($duration) {
            $format[FlagshipConstant::LOG_FORMAT_DURATION] = $duration;
        }
        if ($responseHeader !== null) {
            $format[FlagshipConstant::LOG_FORMAT_REQUEST_HEADERS] = $responseHeader;
        }
        if ($responseBody !== null) {
            $format[FlagshipConstant::LOG_FORMAT_RESPONSE_BODY] = $responseBody;
        }
        if ($responseStatus !== null) {
            $format[FlagshipConstant::LOG_FORMAT_RESPONSE_STATUS] = $responseStatus;
        }
        return  $format;
    }
}

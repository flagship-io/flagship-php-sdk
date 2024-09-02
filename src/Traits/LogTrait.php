<?php

namespace Flagship\Traits;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;

trait LogTrait
{
    /**
     * @param array $args
     * @return array
     */
    protected function formatArgs($args = []): array
    {
        $formatArgs = [];
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $arg = json_encode($arg);
            }
            $formatArgs[] = $arg;
        }
        return $formatArgs;
    }

    /**
     * @param FlagshipConfig $config
     * @param string $tag
     * @param string $message
     * @param array $args
     * @return void
     */
    protected function logDebugSprintf(FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::DEBUG->value || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = vsprintf($message, $this->formatArgs($args));
        $this->logDebug($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param array $context
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
     * @param array ...$args
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
     * @param FlagshipConfig $config
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(FlagshipConfig $config, string $message, array $context = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::ERROR->value || is_null($config->getLogManager())) {
            return;
        }
        $config->getLogManager()->error($message, $context);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $tag
     * @param string $message
     * @param array $args
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
     * @param array $context
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
     * @param FlagshipConfig $config
     * @param string $tag
     * @param string $message
     * @param array $args
     * @return void
     */
    protected function logWarningSprintf(FlagshipConfig $config, string $tag, string $message, array $args = []): void
    {
        if ($config->getLogLevel()->value < LogLevel::WARNING->value || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = vsprintf($message, $this->formatArgs($args));
        $this->logWarning($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param array $context
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
     * @param string $message
     * @param string $url
     * @param array $requestBody
     * @param array $headers
     * @param string $duration
     * @param mixed $responseHeader
     * @param mixed $responseBody
     * @param mixed $responseStatus
     * @return array
     */
    protected function getLogFormat(
        $message,
        $url,
        $requestBody,
        $headers,
        $duration,
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

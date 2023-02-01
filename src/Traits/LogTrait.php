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
    protected function formatArgs($args = [])
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
    protected function logDebugSprintf(FlagshipConfig $config, $tag, $message, $args = [])
    {
        if ($config->getLogLevel() < LogLevel::DEBUG || is_null($config->getLogManager())) {
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
    protected function logDebug($config, $message, $context = [])
    {
        if (!$config || $config->getLogLevel() < LogLevel::DEBUG || is_null($config->getLogManager())) {
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
    protected function logErrorSprintf(FlagshipConfig $config, $tag, $message, $args = [])
    {
        if ($config->getLogLevel() < LogLevel::ERROR || is_null($config->getLogManager())) {
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
    protected function logError($config, $message, $context = [])
    {
        if (!$config || $config->getLogLevel() < LogLevel::ERROR || is_null($config->getLogManager())) {
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
    protected function logInfoSprintf(FlagshipConfig $config, $tag, $message, $args = [])
    {
        if ($config->getLogLevel() < LogLevel::INFO || is_null($config->getLogManager())) {
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
    protected function logInfo($config, $message, $context = [])
    {
        if (!$config || $config->getLogLevel() < LogLevel::INFO || is_null($config->getLogManager())) {
            return;
        }
        $config->getLogManager()->info($message, $context);
    }

    /**
     * @param string $message
     * @param string $url
     * @param array $requestBody
     * @param array $headers
     * @param string $duration
     * @return array
     */
    protected function getLogFormat($message, $url, $requestBody, $headers, $duration)
    {
        $format = [];
        if ($message) {
            $format["message"] = $message;
        }
        if ($url) {
            $format["url"] = $url;
        }
        if ($requestBody) {
            $format['body'] = $requestBody;
        }
        if ($headers) {
            $format['headers'] = $headers;
        }
        if ($duration) {
            $format['duration'] = $duration;
        }
        return  $format;
    }
}

<?php

namespace Flagship\Traits;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;

trait LogTrait
{
    /**
     * @param FlagshipConfig $config
     * @param $tag
     * @param string $message
     * @param mixed ...$args
     */
    protected function logDebugSprintf(FlagshipConfig $config, $tag, $message, $args=[])
    {
        if ($config->getLogLevel() < LogLevel::DEBUG || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = sprintf($message, ...$args);
        $this->logDebug($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param array $context
     */
    protected function logDebug($config, $message, $context = [])
    {
        if (!$config || $config->getLogLevel() < LogLevel::DEBUG || is_null($config->getLogManager())) {
            return;
        }
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $config->getLogManager()->debug("[$flagshipSdk] $message", $context);
    }

    /**
     * @param FlagshipConfig $config
     * @param string $tag
     * @param string $message
     * @param mixed ...$args
     */
    protected function logErrorSprintf( FlagshipConfig $config, $tag, $message, $args=[]){
        if ($config->getLogLevel() < LogLevel::ERROR || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = sprintf($message, ...$args);
        $this->logError($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }
    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param array $context
     */
    protected function logError($config, $message, $context = [])
    {
        if (!$config || $config->getLogLevel() < LogLevel::ERROR || is_null($config->getLogManager())) {
            return;
        }
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $config->getLogManager()->error("[$flagshipSdk] $message", $context);
    }

    protected function logInfoSprintf( FlagshipConfig $config, $tag, $message, $args=[]){
        if ($config->getLogLevel() < LogLevel::INFO || is_null($config->getLogManager())) {
            return;
        }
        $customMessage = sprintf($message, ...$args);
        $this->logInfo($config, $customMessage, [FlagshipConstant::TAG => $tag]);
    }
    /**
     * @param FlagshipConfig $config
     * @param string $message
     * @param array $context
     */
    protected function logInfo($config, $message, $context = [])
    {
        if (!$config || $config->getLogLevel() < LogLevel::INFO || is_null($config->getLogManager())) {
            return;
        }
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $config->getLogManager()->info("[$flagshipSdk] $message", $context);
    }

    protected function getLogFormat($message, $url, $requestBody, $headers, $duration){
        $format = [];
        if ($message){
            $format["message"] = $message;
        }
        if ($url){
            $format["url"] = $url;
        }
        if ($requestBody){
            $format['body'] = $requestBody;
        }
        if ($headers){
            $format['headers'] = $headers;
        }
        if ($duration){
            $format['duration'] = $duration;
        }
        return  $format;
    }
}

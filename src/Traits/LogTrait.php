<?php

namespace Flagship\Traits;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;

trait LogTrait
{
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
}

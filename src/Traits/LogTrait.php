<?php

namespace Flagship\Traits;

use Flagship\Enum\FlagshipConstant;
use Psr\Log\LoggerInterface;

trait LogTrait
{
    /**
     * @param LoggerInterface $logManager
     * @param $message
     * @param array               $context
     */
    protected function logError($logManager, $message, $context = [])
    {
        if (is_null($logManager)) {
            return;
        }
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManager->error("[$flagshipSdk] $message", $context);
    }

    /**
     * @param LoggerInterface $logManager
     * @param $message
     * @param array               $context
     */
    protected function logInfo($logManager, $message, $context = [])
    {
        if (is_null($logManager)) {
            return;
        }
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManager->info("[$flagshipSdk] $message", $context);
    }
}

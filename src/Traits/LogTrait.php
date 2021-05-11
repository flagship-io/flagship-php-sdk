<?php

namespace Flagship\Traits;

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
        $logManager->error($message, $context);
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
        $logManager->info($message, $context);
    }
}

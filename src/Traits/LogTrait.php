<?php

namespace Flagship\Traits;

use Flagship\Utils\LogManagerInterface;

trait LogTrait
{
    /**
     * @param LogManagerInterface $logManager
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
     * @param LogManagerInterface $logManager
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

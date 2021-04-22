<?php

namespace Flagship\Traits;

use Flagship\Interfaces\LogManagerInterface;

trait LogTrait
{
    /**
     * @param LogManagerInterface $logManager
     * @param $message
     * @param array $context
     */
    public function logError($logManager, $message, $context = [])
    {
        if (!is_null($logManager)) {
            $logManager->error($message, $context);
        }
    }
}

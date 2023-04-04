<?php

namespace App\Traits;

trait ErrorFormatTrait
{
    /**
     * @param string $message
     * @return array
     */
    protected function formatError($message)
    {
        return ["ok" => true, 'error' => $message];
    }
}

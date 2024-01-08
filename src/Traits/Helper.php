<?php

namespace Flagship\Traits;

use DateTime;

trait Helper
{
    /**
     * @return float
     */
    public function getNow()
    {
        return round(microtime(true) * 1000);
    }

    public function getCurrentDateTime()
    {
        return new DateTime();
    }
}

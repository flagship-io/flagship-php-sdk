<?php

namespace Flagship\Traits;

trait Helper
{
    /**
     * @return float
     */
    public function getNow()
    {
        return round(microtime(true) * 1000);
    }//end getNow()
}
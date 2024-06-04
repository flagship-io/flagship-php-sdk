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

    /**
     * convert value to hex
     * @param $value
     * @return string
     */
    public function valueToHex($value)
    {
        $jsonString = json_encode($value);
        $hex = '';
        for ($i = 0; $i < strlen($jsonString); $i++) {
            $hex .= dechex(ord($jsonString[$i]));
        }
        return $hex;
    }
}

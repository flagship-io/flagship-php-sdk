<?php

namespace Flagship\Traits;

use DateTime;

trait Helper
{
    /**
     * @return float
     */
    public function getNow(): float
    {
        return round(microtime(true) * 1000);
    }

    public function getCurrentDateTime(): DateTime
    {
        return new DateTime();
    }

    /**
     * convert value to hex
     * @param mixed $value
     * @return string
     */
    public function valueToHex(mixed $value): string
    {
        $jsonString = json_encode($value);
        $hex = '';
        for ($i = 0; $i < strlen($jsonString); $i++) {
            $hex .= dechex(ord($jsonString[$i]));
        }
        return $hex;
    }
}

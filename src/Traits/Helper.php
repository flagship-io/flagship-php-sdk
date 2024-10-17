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

    function arraysAreEqual(array $array1, array $array2): bool
    {
        if (count($array1) !== count($array2)) {
            return false;
        }

        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                return false;
            }

            if (is_array($value)) {
                if (!is_array($array2[$key]) || !$this->arraysAreEqual($value, $array2[$key])) {
                    return false;
                }
            } else {
                if ($value !== $array2[$key]) {
                    return false;
                }
            }
        }

        return true;
    }
}

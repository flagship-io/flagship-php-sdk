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
        if ($jsonString === false) {
            return '';
        }
        $hex = '';
        for ($i = 0; $i < strlen($jsonString); $i++) {
            $hex .= dechex(ord($jsonString[$i]));
        }
        return $hex;
    }

    /**
     * Compare two arrays for equality, including nested arrays.
     *
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     * @return bool
     */
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

    /**
     * 
     * @param array<mixed> $array
     * @param callable $callback
     * @return bool
     */
    protected function array_all(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if (!$callback($value, $key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @param array<mixed> $array
     * @param callable $callback
     * @return bool
     */
    protected function array_any(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @phpstan-template T of mixed
     * @param array<T> $array
     * @param callable $callback
     * @return T|null
     */
    protected function array_find(array $array, callable $callback): mixed
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return null;
    }
}

<?php

namespace Flagship\Traits;

trait ValidatorTrait
{

    /**
     * Return true if key is not empty and is a string, otherwise return false
     *
     * @param  mixed $key Context key
     * @return bool
     */
    public static function isKeyValid($key)
    {
        return !empty($key) && is_string($key);
    }

    /**
     * Return true if value is not empty and is a number or a boolean or a string,
     * otherwise return false
     *
     * @param  $value
     * @return bool
     */
    public static function isValueValid($value)
    {
        return (!empty($value) && (is_numeric($value) || is_bool($value) || is_string($value)));
    }
}

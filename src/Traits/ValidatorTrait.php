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
    public function isKeyValid($key)
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
    public function isValueValid($value)
    {
        return (is_numeric($value) || is_bool($value) || (is_string($value) && !empty($value)));
    }
}

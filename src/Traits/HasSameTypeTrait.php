<?php

namespace Flagship\Traits;

trait HasSameTypeTrait
{
    /**
     * @param mixed $flagValue
     * @param mixed $defaultValue
     * @return bool
     */
    protected function hasSameType($flagValue, $defaultValue)
    {
        return gettype($flagValue) === getType($defaultValue);
    }
}

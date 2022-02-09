<?php

namespace Flagship\Traits;

trait HasSameTypeTrait
{
    protected function hasSameType($flagValue, $defaultValue)
    {
        return gettype($flagValue) === getType($defaultValue);
    }
}

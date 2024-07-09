<?php

namespace Flagship\Traits;

trait HasSameTypeTrait
{
    /**
     * @param mixed $flagValue
     * @param mixed $defaultValue
     * @return bool
     */
    protected function hasSameType(mixed $flagValue, mixed $defaultValue): bool
    {
        return gettype($flagValue) === getType($defaultValue);
    }
}

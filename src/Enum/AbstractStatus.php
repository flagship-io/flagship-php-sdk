<?php

namespace Flagship\Enum;

abstract class AbstractStatus
{
    /**
     * Get the status name for the given value.
     *
     * @param mixed $value The value to get the status name for.
     * @return string|null The status name if found, null otherwise.
     */
    public static function getConstantName($value)
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());
    
        return $constants[$value];
    }
}
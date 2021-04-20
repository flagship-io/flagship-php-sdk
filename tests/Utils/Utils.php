<?php

namespace Abtasty\FlagshipPhpSdk\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Utils
 * @package Abtasty\FlagshipPhpSdk\Utils
 */
class Utils
{
    /**
     * Get a class protect or private method
     * @param string $class the name of class
     * @param string $name the method's name to reflect
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function getMethod($class, $name)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}

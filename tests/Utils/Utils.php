<?php

namespace Flagship\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Utils
 *
 * @package Flagship\Utils
 */
class Utils
{
    /**
     * Get a class protect or private method
     *
     * @param string $class the name of class
     * @param string $name  the method's name to reflect
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function getMethod(mixed $class, string $name): ReflectionMethod
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @throws ReflectionException
     */
    public static function setPrivateProperty($objet, $propertyName, $value, $className = null): void
    {
        $class = new ReflectionClass($className ?: $objet);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($objet, $value);
        $property->setAccessible(false);
    }

    /**
     * @throws ReflectionException
     */
    public static function getProperty($class, $name): \ReflectionProperty
    {
        $class = new ReflectionClass($class);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }
}

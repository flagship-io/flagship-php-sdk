<?php

namespace Flagship\Utils;

use Exception;

/**
 * Exception thrown when container operations fail
 */
class ContainerException extends Exception
{
    /**
     * Create exception for unresolvable class
     * 
     * @param class-string $className
     * @return self
     */
    public static function unresolvable(string $className): self
    {
        return new self(sprintf('Unable to resolve class "%s"', $className));
    }

    /**
     * Create exception for circular dependency
     * 
     * @param array<class-string> $chain
     * @return self
     */
    public static function circularDependency(array $chain): self
    {
        return new self(
            sprintf('Circular dependency detected: %s', implode(' -> ', $chain))
        );
    }

    /**
     * Create exception for non-instantiable class
     * 
     * @param class-string $className
     * @return self
     */
    public static function notInstantiable(string $className): self
    {
        return new self(
            sprintf('Class "%s" is not instantiable', $className)
        );
    }
}
<?php

namespace Flagship\Utils;

use ReflectionException;

/**
 * Dependency Injection Container Interface
 */
interface ContainerInterface
{
    /**
     * Bind an alias to a concrete class
     * 
     * @param class-string $alias
     * @param class-string $className
     * @return self
     */
    public function bind(string $alias, string $className): self;

    /**
     * Resolve and return an instance
     * 
     * @param class-string $id
     * @param array<mixed>|null $args
     * @param bool $isFactory
     * @return object
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function get(string $id, ?array $args = null, bool $isFactory = false): object;

    /**
     * Check if a binding exists
     * 
     * @param class-string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Register a factory callback
     * 
     * @param class-string $id
     * @param callable $factory
     * @return self
     */
    public function factory(string $id, callable $factory): self;

    /**
     * Bind a singleton instance
     * 
     * @param class-string $id
     * @param object $instance
     * @return self
     */
    public function instance(string $id, object $instance): self;

    /**
     * Clear all cached instances
     * 
     * @return self
     */
    public function flush(): self;
}
<?php

namespace Flagship\Utils;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * Dependency Injection Container
 * 
 * Provides automatic dependency resolution and singleton management
 * for class instantiation throughout the SDK.
 */
class Container implements ContainerInterface
{
    /**
     * Singleton instances cache
     * @var array<class-string, object>
     */
    private array $instances = [];

    /**
     * Interface to implementation bindings
     * @var array<class-string, class-string>
     */
    private array $bindings = [];

    /**
     * Factory callbacks for custom instantiation
     * @var array<class-string, callable>
     */
    private array $factories = [];

    /**
     * Bind an alias to a concrete class implementation
     * 
     * @template T of object
     * @param class-string<T> $alias The interface or abstract class
     * @param class-string<T> $className The concrete implementation
     * @return self
     * @throws ContainerException If alias already exists
     */
    public function bind(string $alias, string $className): self
    {
        if (isset($this->bindings[$alias])) {
            throw new ContainerException(
                sprintf('Alias "%s" is already bound to "%s"', $alias, $this->bindings[$alias])
            );
        }

        if (!class_exists($className) && !interface_exists($className)) {
            throw new ContainerException(
                sprintf('Class "%s" does not exist', $className)
            );
        }

        $this->bindings[$alias] = $className;
        return $this;
    }

    /**
     * Register a factory callback for custom instantiation
     * 
     * @template T of object
     * @param class-string<T> $id The class identifier
     * @param callable(self, array<mixed>|null): T $factory Factory function that returns the instance
     * @return self
     */
    public function factory(string $id, callable $factory): self
    {
        $this->factories[$id] = $factory;
        return $this;
    }

    /**
     * Bind a singleton instance directly
     * 
     * @template T of object
     * @param class-string<T> $id The class identifier
     * @param T $instance The instance to register
     * @return self
     */
    public function instance(string $id, object $instance): self
    {
        $this->instances[$id] = $instance;
        return $this;
    }

    /**
     * Check if a binding exists
     * 
     * @param class-string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->bindings[$id])
            || isset($this->factories[$id])
            || class_exists($id);
    }

    /**
     * Resolve and return an instance
     * 
     * @template T of object
     * @param class-string<T> $id The class identifier
     * @param array<mixed>|null $args Constructor arguments (optional)
     * @param bool $isFactory Whether to create a new instance each time
     * @return T
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function get(string $id, ?array $args = null, bool $isFactory = false): object
    {
        // Always create new instance if factory mode
        if ($isFactory) {
            return $this->resolve($id, $args);
        }

        // Return existing singleton if available
        if (isset($this->instances[$id])) {
            /** @var T */
            return $this->instances[$id];
        }

        // Create and cache new singleton
        $instance = $this->resolve($id, $args);
        $this->instances[$id] = $instance;

        /** @var T */
        return $instance;
    }

    /**
     * Create a new instance (alias for factory mode)
     * 
     * @template T of object
     * @param class-string<T> $id
     * @param array<mixed>|null $args
     * @return T
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function make(string $id, ?array $args = null): object
    {
        return $this->resolve($id, $args);
    }

    /**
     * Resolve a class to an instance
     * 
     * @template T of object
     * @param class-string<T> $id The class identifier
     * @param array<mixed>|null $args Constructor arguments
     * @return T
     * @throws ContainerException
     * @throws ReflectionException
     */
    private function resolve(string $id, ?array $args = null): object
    {
        // Use factory if registered
        if (isset($this->factories[$id])) {
            return $this->invokeFactory($id, $args);
        }

        // Resolve bound implementation
        $className = $this->getConcreteClassName($id);

        // Create reflection
        $reflectionClass = $this->createReflection($className);

        // Validate instantiability
        $this->validateInstantiable($reflectionClass);

        // Get constructor
        $constructor = $reflectionClass->getConstructor();

        // No constructor - simple instantiation
        if ($constructor === null) {
            /** @var T */
            return $reflectionClass->newInstance();
        }

        // Resolve constructor parameters
        $parameters = $this->resolveConstructorParameters($constructor, $args);

        /** @var T */
        return $reflectionClass->newInstanceArgs($parameters);
    }

    /**
     * Invoke a registered factory
     * 
     * @template T of object
     * @param class-string<T> $id
     * @param array<mixed>|null $args
     * @return T
     * @throws ContainerException
     */
    private function invokeFactory(string $id, ?array $args = null): object
    {
        $factory = $this->factories[$id];
        $instance = $factory($this, $args);

        if (!is_object($instance)) {
            throw new ContainerException(
                sprintf('Factory for "%s" must return an object', $id)
            );
        }

        /** @var T */
        return $instance;
    }

    /**
     * Get the concrete class name from binding or use the id
     * 
     * @template T of object
     * @param class-string<T> $id
     * @return class-string<T>
     */
    private function getConcreteClassName(string $id): string
    {
        /** @var class-string<T> */
        return $this->bindings[$id] ?? $id;
    }

    /**
     * Create a reflection class with error handling
     * 
     * @template T of object
     * @param class-string<T> $className
     * @return ReflectionClass<T>
     * @throws ContainerException
     * @phpstan-ignore throws.unusedType
     */
    private function createReflection(string $className): ReflectionClass
    {
        try {
            return new ReflectionClass($className);
        // @phpstan-ignore catch.neverThrown
        } catch (ReflectionException $e) {
            throw new ContainerException(
                sprintf('Class "%s" does not exist', $className),
                0,
                $e
            );
        }
    }

    /**
     * Validate that a class is instantiable
     * 
     * @param ReflectionClass<object> $reflection
     * @return void
     * @throws ContainerException
     */
    private function validateInstantiable(ReflectionClass $reflection): void
    {
        if (!$reflection->isInstantiable()) {
            throw new ContainerException(
                sprintf(
                    'Class "%s" is not instantiable (abstract, interface, or trait)',
                    $reflection->getName()
                )
            );
        }
    }

    /**
     * Resolve constructor parameters
     * 
     * @param \ReflectionMethod $constructor
     * @param array<mixed>|null $args
     * @return array<mixed>
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function resolveConstructorParameters(
        \ReflectionMethod $constructor,
        ?array $args = null
    ): array {
        // Use provided args if available
        if (is_array($args) && !empty($args)) {
            return $args;
        }

        // Auto-resolve dependencies
        $parameters = $constructor->getParameters();
        return $this->buildParameterList($parameters);
    }

    /**
     * Build parameter list from reflection parameters
     * 
     * @param array<ReflectionParameter> $parameters
     * @return array<mixed>
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function buildParameterList(array $parameters): array
    {
        $resolved = [];

        foreach ($parameters as $parameter) {
            $resolved[] = $this->resolveParameter($parameter);
        }

        return $resolved;
    }

    /**
     * Resolve a single parameter
     * 
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function resolveParameter(ReflectionParameter $parameter): mixed
    {
        // Use default value if available
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // Get parameter type
        $type = $parameter->getType();

        // No type hint - return null if nullable
        if ($type === null) {
            return null;
        }

        // Handle union types (PHP 8.0+)
        if ($type instanceof ReflectionUnionType) {
            return $this->resolveUnionType($type, $parameter);
        }

        // Handle named types
        if ($type instanceof ReflectionNamedType) {
            return $this->resolveNamedType($type, $parameter);
        }

        return null;
    }

    /**
     * Resolve a union type parameter
     * 
     * @param ReflectionUnionType $type
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function resolveUnionType(ReflectionUnionType $type, ReflectionParameter $parameter): mixed
    {
        $types = $type->getTypes();

        // Try to resolve the first non-builtin type
        foreach ($types as $unionType) {
            if ($unionType instanceof ReflectionNamedType && !$unionType->isBuiltin()) {
                $className = $unionType->getName();
                /** @var class-string $className */
                return $this->get($className);
            }
        }

        // Fallback to null if allowed
        if ($type->allowsNull()) {
            return null;
        }

        // Try to get default for first type
        $defaultValue = $this->getDefaultForFirstType($types);

        if ($defaultValue !== null) {
            return $defaultValue;
        }

        // If we can't resolve any type, throw exception
        throw new ContainerException(
            sprintf(
                'Cannot resolve union type parameter "$%s" in class "%s" - no resolvable types found',
                $parameter->getName(),
                $parameter->getDeclaringClass()?->getName() ?? 'unknown'
            )
        );
    }

    /**
     * Resolve a named type parameter
     * 
     * @param ReflectionNamedType $type
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws ReflectionException
     * @throws ContainerException
     */
    private function resolveNamedType(ReflectionNamedType $type, ReflectionParameter $parameter): mixed
    {
        $typeName = $type->getName();
        $allowsNull = $type->allowsNull();

        // Built-in types (string, int, bool, etc.)
        if ($type->isBuiltin()) {
            return $this->resolveBuiltInType($typeName, $allowsNull);
        }

        // Try to resolve from container
        try {
            /** @var class-string $typeName */
            return $this->get($typeName);
        } catch (ContainerException $e) {
            if ($allowsNull) {
                return null;
            }
            throw new ContainerException(
                sprintf(
                    'Cannot resolve parameter "$%s" of type "%s": %s',
                    $parameter->getName(),
                    $typeName,
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }

    /**
     * Resolve built-in type with default value
     * 
     * @param string $typeName
     * @param bool $allowsNull
     * @return scalar|array<mixed>|null
     */
    private function resolveBuiltInType(string $typeName, bool $allowsNull): float|bool|array|int|string|null
    {
        if ($allowsNull) {
            return null;
        }

        return $this->getDefaultForType($typeName);
    }

    /**
     * Get default value for a built-in type
     * 
     * @param string $typeName
     * @return scalar|array<mixed>
     */
    private function getDefaultForType(string $typeName): float|bool|array|int|string
    {
        return match ($typeName) {
            'string' => '',
            'int' => 0,
            'bool' => false,
            'float' => 0.0,
            'array' => [],
            default => '',
        };
    }

    /**
     * Get default for first type in union
     * 
     * @param array<\ReflectionType> $types
     * @return mixed
     */
    private function getDefaultForFirstType(array $types): mixed
    {
        $firstType = $types[0] ?? null;

        if ($firstType instanceof ReflectionNamedType && $firstType->isBuiltin()) {
            return $this->getDefaultForType($firstType->getName());
        }

        return null;
    }

    /**
     * Clear all cached instances
     * 
     * @return self
     */
    public function flush(): self
    {
        $this->instances = [];
        return $this;
    }

    /**
     * Clear specific cached instance
     * 
     * @param class-string $id
     * @return self
     */
    public function forget(string $id): self
    {
        unset($this->instances[$id]);
        return $this;
    }

    /**
     * Get all registered bindings
     * 
     * @return array<class-string, class-string>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}

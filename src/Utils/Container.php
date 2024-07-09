<?php

namespace Flagship\Utils;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

class Container implements ContainerInterface
{
    private array $instances = [];
    private array $bindings = [];

    /**
     * @param string $alias
     * @param string $className
     * @return $this
     * @throws Exception
     */
    public function bind(string $alias, string $className): static
    {
        if (isset($this->bindings[$alias])) {
            throw new Exception('alias ' . $alias . ' already exist');
        }
        $this->bindings[$alias] = $className;
        return $this;
    }

    /**
     * @param string $id
     * @param null $args
     * @param bool $isFactory
     * @return mixed|object|null
     * @throws ReflectionException
     */
    public function get(string $id, $args = null, bool $isFactory = false): mixed
    {
        if ($isFactory) {
            return $this->resolve($id, $args);
        }
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        return $this->instances[$id] = $this->resolve($id, $args);
    }

    /**
     * @param string $id
     * @param array|null $args
     * @return object|null
     * @throws ReflectionException
     * @throws Exception
     */
    private function resolve(string $id, array $args = null): ?object
    {
        $className = $id;
        if (isset($this->bindings[$id])) {
            $className = $this->bindings[$id];
        }
        $reflectedClass = new ReflectionClass($className);

        if (!$reflectedClass->isInstantiable()) {
            throw new Exception($className . "not an instantiable Class");
        }

        $constructor = $reflectedClass->getConstructor();

        if (!$constructor) {
            return $reflectedClass->newInstance();
        }

        if (is_array($args)) {
            $constructorParameters = $args;
        } else {
            $parameters = $constructor->getParameters();
            $constructorParameters = $this->extractConstructorParam($parameters);
        }
        return $reflectedClass->newInstanceArgs($constructorParameters);
    }

    private function getDefaultForType(string $typeName): float|bool|array|int|string|null
    {
        return match ($typeName) {
            'string' => '',
            'int' => 0,
            'bool' => false,
            'float' => 0.0,
            'array' => [],
            default => null,
        };
    }


    private function resolveBuiltInType(string $typeName, bool $allowsNull): float|bool|array|int|string|null
    {
        return $allowsNull ? null : $this->getDefaultForType($typeName);
    }

    /**
     * @throws ReflectionException
     */
    private function resolveParameter(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $type = $parameter->getType();
        if (!$type) {
            return null;
        }

        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string)$type;
        $allowsNull = $type->allowsNull();

        if ($type->isBuiltin()) {
            return $this->resolveBuiltInType($typeName, $allowsNull);
        }
        return $this->get($typeName);
    }

    /**
     * @throws ReflectionException
     */
    private function extractConstructorParam(array $parameters): array
    {
        $constructorParameters = [];
        foreach ($parameters as $parameter) {
            $constructorParameters[] = $this->resolveParameter($parameter);
        }
        return $constructorParameters;
    }
}

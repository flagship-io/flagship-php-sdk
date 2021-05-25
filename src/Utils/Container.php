<?php

namespace Flagship\Utils;

use Exception;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    private $instances = [];
    private $bindings = [];

    /**
     * @param  $alias
     * @param  $className
     * @return $this
     * @throws Exception
     */
    public function bind($alias, $className)
    {
        if (isset($this->bindings[$alias])) {
            throw new Exception('alias ' . $alias . ' already exist');
        }
        $this->bindings[$alias] = $className;
        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function get($id, $args = null, $isFactory = false)
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
     * @throws ReflectionException
     * @throws Exception
     */
    private function resolve($id, $args = null)
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

    private function extractConstructorParam($parameters)
    {
        $constructorParameters = [];
        foreach ($parameters as $parameter) {
            $isPhp5 = version_compare(phpversion(), '7', '<');
            $typeName = $isPhp5 ? $parameter->getClass() : $parameter->getType();
            if ($typeName) {
                $constructorParameters[] = $this->get($typeName->getName());
            } else {
                $constructorParameters[] = $parameter->isDefaultValueAvailable() ?
                    $parameter->getDefaultValue() : null;
            }
        }
        return $constructorParameters;
    }
}

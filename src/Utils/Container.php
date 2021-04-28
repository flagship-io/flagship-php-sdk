<?php

namespace Flagship\Utils;

use Exception;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    private $instances = [];
    private $bins = [];

    public function bind($alias, $className)
    {
        $this->bins[$alias] = $className;
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

    public function has($id)
    {
        // TODO: Implement has() method.
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function resolve($id, $args = null)
    {
        $className = $id;
        if (isset($this->bins[$id])) {
            $className = $this->bins[$id];
        }
        $reflectedClass = new ReflectionClass($className);
        if ($reflectedClass->isInstantiable()) {
            $constructor = $reflectedClass->getConstructor();
            if ($constructor) {
                $constructorParameters = [];
                if ($args) {
                    $constructorParameters = $args;
                } else {
                    $parameters = $constructor->getParameters();
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
                }
                return $reflectedClass->newInstanceArgs($constructorParameters);
            } else {
                return $reflectedClass->newInstance();
            }
        } else {
            throw new Exception($id . "not an instantiable Class");
        }
    }
}

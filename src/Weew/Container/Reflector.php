<?php

namespace Weew\Container;

use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Weew\Container\Exceptions\ClassNotFoundException;
use Weew\Container\Exceptions\DebugInfoException;
use Weew\Container\Exceptions\InterfaceIsNotInstantiableException;
use Weew\Container\Exceptions\MissingArgumentException;

class Reflector implements IReflector {
    /**
     * @param IContainer $container
     * @param $className
     * @param array $args
     *
     * @return object
     * @throws ClassNotFoundException
     * @throws InterfaceIsNotInstantiableException
     * @throws MissingArgumentException
     */
    public function resolveClass(IContainer $container, $className, array $args = []) {
        if (class_exists($className)) {
            $class = new ReflectionClass($className);

            return $this->resolveConstructor($container, $class, $args);
        }

        if (interface_exists($className)) {
            throw new InterfaceIsNotInstantiableException($className);
        }

        throw new ClassNotFoundException($className);
    }

    /**
     * @param IContainer $container
     * @param ReflectionClass $class
     * @param array $args
     *
     * @return object
     * @throws DebugInfoException
     */
    public function resolveConstructor(IContainer $container, ReflectionClass $class, array $args = []) {
        $constructor = $class->getConstructor();

        if ($constructor !== null) {
            try {
                $arguments = $this->buildArgumentsFromParameters(
                    $container, $constructor->getParameters(), $args
                );
            } catch (DebugInfoException $ex) {
                $ex->setClassName($class->getName());
                $ex->setMethodName($constructor->getName());

                throw $ex;
            }

            return $class->newInstanceArgs($arguments);
        }

        return $class->newInstance();
    }

    /**
     * @param IContainer $container
     * @param $instance
     * @param $methodName
     * @param array $args
     *
     * @return mixed
     * @throws DebugInfoException
     */
    public function resolveMethod(IContainer $container, $instance, $methodName, array $args = []) {
        $class = new ReflectionClass($instance);
        $method = $class->getMethod($methodName);

        try {
            $arguments = $this->buildArgumentsFromParameters(
                $container, $method->getParameters(), $args
            );
        } catch (DebugInfoException $ex) {
            $ex->setClassName($class->getName());
            $ex->setMethodName($method->getName());

            throw $ex;
        }

        if ($method->isStatic()) {
            $instance = null;
        }

        return $method->invokeArgs($instance, $arguments);
    }

    /**
     * @param IContainer $container
     * @param $functionName
     * @param array $args
     *
     * @return mixed
     * @throws DebugInfoException
     */
    public function resolveFunction(IContainer $container, $functionName, array $args = []) {
        $function = new ReflectionFunction($functionName);

        try {
            $arguments = $this->buildArgumentsFromParameters(
                $container, $function->getParameters(), $args
            );
        } catch (DebugInfoException $ex) {
            $ex->setFunctionName(
                $function->isClosure() ? 'Closure' : $function->getName()
            );

            throw $ex;
        }

        return $function->invokeArgs($arguments);
    }

    /**
     * @param IContainer $container
     * @param array $parameters
     * @param array $args
     *
     * @return array
     * @throws DebugInfoException
     */
    protected function buildArgumentsFromParameters(IContainer $container, array $parameters, array $args) {
        $arguments = [];

        foreach ($parameters as $index => $parameter) {
            try {
                $arguments[] = $this->getParameterValue($container, $parameter, $args);
            } catch (DebugInfoException $ex) {
                $ex->setArgumentIndex($index);

                throw $ex;
            }
        }

        return $arguments;
    }

    /**
     * @param IContainer $container
     * @param ReflectionParameter $parameter
     * @param array $args
     *
     * @return mixed
     * @throws MissingArgumentException
     */
    protected function getParameterValue(IContainer $container, ReflectionParameter $parameter, array $args) {
        $parameterName = $parameter->getName();
        $parameterClass = $parameter->getClass();

        if (isset($args[$parameterName])) {
            return $args[$parameterName];
        }

        if ($parameterClass !== null) {
            $concrete = $container->get($parameterClass->getName());

            return $concrete;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $ex = new MissingArgumentException('Missing argument.');
        $ex->setArgumentName($parameterName);

        throw $ex;
    }
}

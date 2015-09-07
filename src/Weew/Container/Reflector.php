<?php

namespace Weew\Container;

use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Weew\Container\Exceptions\InterfaceImplementationNotFoundException;
use Weew\Container\Exceptions\UnresolveableArgumentException;
use Weew\Container\Exceptions\ValueNotFoundException;

class Reflector implements IReflector {
    /**
     * @param IContainer $container
     * @param $className
     * @param array $args
     *
     * @return object
     * @throws InterfaceIsNotInstantiableException
     */
    public function resolveClass(IContainer $container, $className, array $args = []) {
        return $this->resolveConstructor($container, $className, $args);
    }

    /**
     * @param IContainer $container
     * @param $instance
     * @param $methodName
     * @param array $args
     *
     * @return mixed
     * @throws Exception
     * @throws UnresolveableArgumentException
     */
    public function resolveMethod(IContainer $container, $instance, $methodName, array $args = []) {
        $class = new ReflectionClass($instance);
        $method = $class->getMethod($methodName);

        try {
            $arguments = $this->buildArgumentsFromParameters(
                $container, $method->getParameters(), $args
            );
        } catch (UnresolveableArgumentException $ex) {
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
     * @throws Exception
     * @throws UnresolveableArgumentException
     */
    public function resolveFunction(IContainer $container, $functionName, array $args = []) {
        $function = new ReflectionFunction($functionName);

        try {
            $arguments = $this->buildArgumentsFromParameters(
                $container, $function->getParameters(), $args
            );
        } catch (UnresolveableArgumentException $ex) {
            $ex->setFunctionName($function->isClosure() ? 'Closure' : $function->getName());

            throw $ex;
        }

        return $function->invokeArgs($arguments);
    }

    /**
     * @param IContainer $container
     * @param $className
     * @param array $args
     *
     * @return object
     * @throws Exception
     * @throws UnresolveableArgumentException
     */
    protected function resolveConstructor(IContainer $container, $className, array $args = []) {
        $class = new ReflectionClass($className);
        $constructor = $class->getConstructor();

        if ($constructor !== null) {
            try {
                $arguments = $this->buildArgumentsFromParameters(
                    $container, $constructor->getParameters(), $args
                );
            } catch (UnresolveableArgumentException $ex) {
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
     * @param ReflectionParameter[] $parameters
     * @param array $args
     *
     * @return array
     * @throws Exception
     * @throws UnresolveableArgumentException
     */
    protected function buildArgumentsFromParameters(IContainer $container, array $parameters, array $args) {
        $arguments = [];

        foreach ($parameters as $index => $parameter) {
            try {
                $arguments[] = $this->getParameterValue($container, $parameter, $args);
            } catch (UnresolveableArgumentException $ex) {
                $ex->setArgumentName($parameter->getName());
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
     * @throws Exception
     */
    protected function getParameterValue(IContainer $container, ReflectionParameter $parameter, array $args) {
        $parameterName = $parameter->getName();
        $parameterClass = $parameter->getClass();

        if (array_has($args, $parameterName)) {
            return $args[$parameterName];
        }

        if ($parameterClass !== null) {
            try {
                return $container->get($parameterClass->getName());
            } catch (Exception $ex) {
                $ignoreException = (
                    $ex instanceof ValueNotFoundException ||
                    $ex instanceof InterfaceImplementationNotFoundException
                );

                if ( ! ($ignoreException && $parameter->isDefaultValueAvailable())) {
                    throw $ex;
                }
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new UnresolveableArgumentException(
            s('Value not found for argument %s.', $parameterName)
        );
    }
}

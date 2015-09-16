<?php

namespace Weew\Container;

use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\Exceptions\InvalidCallableFormatException;
use Weew\Container\Exceptions\UnresolveableArgumentException;
use Weew\Container\Exceptions\ValueNotFoundException;

class Reflector {
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
            $arguments = $this->resolveMethodArguments($container, $class, $constructor, $args);

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
     * @throws Exception
     * @throws UnresolveableArgumentException
     */
    public function resolveMethod(IContainer $container, $instance, $methodName, array $args = []) {
        $class = new ReflectionClass($instance);
        $method = $class->getMethod($methodName);
        $arguments = $this->resolveMethodArguments($container, $class, $method, $args);

        if ($method->isStatic()) {
            $instance = null;
        } else if (is_string($instance)) {
            $instance = $container->get($instance);
        }

        return $method->invokeArgs($instance, $arguments);
    }

    /**
     * @param IContainer $container
     * @param ReflectionClass $class
     * @param ReflectionMethod $method
     * @param array $args
     *
     * @return array
     * @throws Exception
     * @throws UnresolveableArgumentException
     */
    protected function resolveMethodArguments(
        IContainer $container,
        ReflectionClass $class,
        ReflectionMethod $method,
        array $args = []
    ) {
        try {
            return $this->buildArgumentsFromParameters(
                $container, $method->getParameters(), $args
            );
        } catch (UnresolveableArgumentException $ex) {
            $ex->setClassName($class->getName());
            $ex->setMethodName($method->getName());

            throw $ex;
        }
    }

    /**
     * @param IContainer $container
     * @param $callable
     * @param array $args
     *
     * @return mixed
     * @throws Exception
     * @throws UnresolveableArgumentException
     */
    public function resolveCallable(IContainer $container, $callable, array $args = []) {
        if (is_string($callable) && function_exists($callable) || is_object($callable)) {
            return $this->resolveFunction($container, $callable, $args);
        } else if (is_array($callable) && is_callable($callable)) {
            return $this->resolveMethod($container, $callable[0], $callable[1], $args);
        } else {
            throw new InvalidCallableFormatException('Invalid callable given.');
        }
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
                $ex->setArgumentIndex($index + 1);

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

        if (array_has($args, $parameterName)) {
            return $args[$parameterName];
        }

        if ($parameter->getClass() !== null) {
            return $this->getParameterFromContainer($container, $parameter);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new UnresolveableArgumentException(
            s('Value not found for argument %s.', $parameterName)
        );
    }

    /**
     * @param IContainer $container
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     * @throws Exception
     * @throws ValueNotFoundException
     */
    protected function getParameterFromContainer(IContainer $container, ReflectionParameter $parameter) {
        try {
            return $container->get($parameter->getClass()->getName());
        } catch (Exception $ex) {
            $ignoreException = (
                $ex instanceof ValueNotFoundException ||
                $ex instanceof ImplementationNotFoundException
            );

            if ( ! ($ignoreException && $parameter->isDefaultValueAvailable())) {
                throw $ex;
            }
        }
    }
}

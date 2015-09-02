<?php
namespace Weew\Container;

use ReflectionClass;

interface IReflector {
    /**
     * @param IContainer $container
     * @param $className
     * @param array $args
     *
     * @return object
     */
    function resolveClass(IContainer $container, $className, array $args = []);

    /**
     * @param IContainer $container
     * @param ReflectionClass $class
     * @param array $args
     *
     * @return object
     */
    function resolveConstructor(IContainer $container, ReflectionClass $class, array $args = []);

    /**
     * @param IContainer $container
     * @param $instance
     * @param $methodName
     * @param array $args
     *
     * @return mixed
     */
    function resolveMethod(IContainer $container, $instance, $methodName, array $args = []);

    /**
     * @param IContainer $container
     * @param $functionName
     * @param array $args
     *
     * @return mixed
     */
    function resolveFunction(IContainer $container, $functionName, array $args = []);
}

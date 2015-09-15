<?php
namespace Weew\Container;

interface IReflector {
    /**
     * @param IContainer $container
     * @param $className
     * @param array $args
     *
     * @return mixed
     */
    function resolveClass(IContainer $container, $className, array $args = []);

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

    /**
     * @param IContainer $container
     * @param $callable
     * @param array $args
     *
     * @return mixed
     */
    function resolveCallable(IContainer $container, $callable, array $args = []);
}

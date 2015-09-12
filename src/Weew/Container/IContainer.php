<?php
namespace Weew\Container;

interface IContainer {
    /**
     * @param string $id
     * @param array $args
     *
     * @return mixed
     */
    function get($id, array $args = []);

    /**
     * @param string $id
     * @param $value
     *
     * @return IDefinition
     */
    function set($id, $value = null);

    /**
     * @param string $id
     *
     * @return bool
     */
    function has($id);

    /**
     * @param string $id
     */
    function remove($id);

    /**
     * @param $function
     * @param array $args
     *
     * @return mixed
     */
    function call($function, array $args = []);

    /**
     * @param $instance
     * @param $method
     * @param array $args
     *
     * @return mixed
     */
    function callMethod($instance, $method, array $args = []);

    /**
     * @param $class
     * @param $method
     * @param array $args
     *
     * @return mixed
     */
    function callStaticMethod($class, $method, array $args = []);
}

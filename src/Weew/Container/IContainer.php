<?php
namespace Weew\Container;

use ArrayAccess;
use Countable;

interface IContainer extends ArrayAccess, Countable {
    /**
     * @param $id
     * @param array $args
     *
     * @return mixed
     */
    function get($id, array $args = []);

    /**
     * @param $id
     * @param $abstract
     *
     * @return IContainer
     */
    function set($id, $abstract = null);

    /**
     * @param $id
     *
     * @return bool
     */
    function has($id);

    /**
     * @param $id
     */
    function remove($id);

    /**
     * @param $function
     * @param array $args
     *
     * @return mixed
     * @throws Exceptions\MissingArgumentException
     */
    function call($function, array $args = []);

    /**
     * @param $instance
     * @param $method
     * @param array $args
     *
     * @return mixed
     * @throws Exceptions\MissingArgumentException
     */
    function callMethod($instance, $method, array $args = []);

    /**
     * @param $class
     * @param $method
     * @param array $args
     *
     * @return mixed
     * @throws Exceptions\MissingArgumentException
     */
    function callStaticMethod($class, $method, array $args = []);
}

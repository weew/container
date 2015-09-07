<?php

namespace Weew\Container;

use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\Exceptions\InterfaceIsNotInstantiableException;

class Container implements IContainer {
    /**
     * @var IReflector
     */
    protected $reflector;

    /**
     * @var array
     */
    protected $container = [];

    /**
     * @var array
     */
    protected $shared = [];

    /**
     * @param IReflector|null $reflector
     */
    public function __construct(IReflector $reflector = null) {
        if ( ! $reflector instanceof IReflector) {
            $reflector = $this->createReflector();
        }

        $this->reflector = $reflector;
        $this->registerContainerInstance();
    }

    /**
     * @param $id
     * @param array $args
     *
     * @return mixed|object
     * @throws ImplementationNotFoundException
     */
    public function get($id, array $args = []) {
        $concrete = null;

        if (array_has($this->container, $id)) {
            $item = $this->container[$id];

            if (is_callable($item)) {
                $concrete = $this->call($item, $args);
            } else if (is_string($item) && class_exists($item)) {
                $concrete = $this->get($item, $args);
            } else {
                $concrete = $item;
            }
        } else {
            $concrete = $this->getClass($id, $args);
        }

        $this->shareInstance($id, $concrete);

        return $concrete;
    }

    /**
     * @param $id
     * @param $abstract
     *
     * @return $this
     */
    public function set($id, $abstract = null) {
        if ($abstract === null && is_object($id)) {
            $this->container[get_class($id)] = $id;
        } else {
            $this->container[$id] = $abstract;
        }

        return $this;
    }

    /**
     * @param $id
     * @param null $abstract
     *
     * @return $this
     */
    public function share($id, $abstract = null) {
        if ($abstract !== null) {
            $this->set($id, $abstract);
        }

        $this->shared[$id] = true;

        return $this;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function has($id) {
        return array_has($this->container, $id);
    }

    /**
     * @param $id
     */
    public function remove($id) {
        if ($this->has($id)) {
            unset($this->container[$id]);
        }
    }

    /**
     * @param $function
     * @param array $args
     *
     * @return mixed
     * @throws Exceptions\MissingArgumentException
     */
    public function call($function, array $args = []) {
        return $this->reflector
            ->resolveFunction($this, $function, $args);
    }

    /**
     * @param $instance
     * @param $method
     * @param array $args
     *
     * @return mixed
     * @throws Exceptions\MissingArgumentException
     */
    public function callMethod($instance, $method, array $args = []) {
        return $this->reflector
            ->resolveMethod($this, $instance, $method, $args);
    }

    /**
     * @param $class
     * @param $method
     * @param array $args
     *
     * @return mixed
     * @throws Exceptions\MissingArgumentException
     */
    public function callStaticMethod($class, $method, array $args = []) {
        return $this->reflector
            ->resolveMethod($this, $class, $method, $args);
    }

    /**
     * @return IReflector
     */
    protected function createReflector() {
        return new Reflector();
    }

    /**
     * @param $class
     * @param array $args
     *
     * @return object
     * @throws ImplementationNotFoundException
     */
    protected function getClass($class, array $args) {
        try {
            return $this->reflector
                ->resolveClass($this, $class, $args);
        } catch (InterfaceIsNotInstantiableException $ex) {
            throw new ImplementationNotFoundException(
                s('No implementation found for interface %s.', $ex->getInterface())
            );
        }
    }

    /**
     * Register this container instance in the container.
     */
    protected function registerContainerInstance() {
        $this->set(static::class, $this);
        $this->set(IContainer::class, $this);
    }

    /**
     * @param $id
     * @param $instance
     */
    protected function shareInstance($id, $instance) {
        if (array_has($this->shared, $id)) {
            $this->set($id, $instance);
        }
    }

    /**
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset) {
        return array_has($this->container, $offset);
    }

    /**
     * @param $offset
     *
     * @return mixed
     */
    public function offsetGet($offset) {
        return array_get($this->container, $offset);
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset) {
        array_remove($this->container, $offset);
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->container);
    }
}

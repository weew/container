<?php

namespace Weew\Container;

use Weew\Container\Definitions\ClassDefinition;
use Weew\Container\Definitions\InterfaceDefinition;
use Weew\Container\Definitions\ValueDefinition;
use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\Exceptions\TypeMismatchException;
use Weew\Container\Exceptions\UnresolveableArgumentException;
use Weew\Container\Exceptions\ValueNotFoundException;

class Container implements IContainer {
    /**
     * @var IDefinition[]
     */
    protected $definitions = [];

    /**
     * @var IReflector
     */
    protected $reflector;

    /**
     * @param IReflector|null $reflector
     */
    public function __construct(IReflector $reflector = null) {
        if ( ! $reflector instanceof IReflector) {
            $reflector = $this->createReflector();
        }

        $this->reflector = $reflector;
        $this->shareContainerInstance();
    }

    /**
     * @param string $id
     * @param array $args
     *
     * @return mixed
     * @throws ImplementationNotFoundException
     * @throws TypeMismatchException
     * @throws UnresolveableArgumentException
     * @throws ValueNotFoundException
     * @throws \Exception
     */
    public function get($id, array $args = []) {
        return $this->catchExceptions(function () use ($id, $args) {
            $value = null;

            if (array_has($this->definitions, $id)) {
                $definition = array_get($this->definitions, $id);
                $value = $this->resolveDefinition($definition, $args);
            }

            if ($value === null) {
                return $this->resolveWithoutDefinition($id, $args);
            }

            return $value;
        });
    }

    /**
     * @param string $id
     * @param $value
     *
     * @return IDefinition
     */
    public function set($id, $value = null) {
        if ($value === null) {
            $value = $id;

            if (is_object($id)) {
                $id = get_class($id);
            }
        }

        if (class_exists($id)) {
            $definition = new ClassDefinition($id, $value);
        } else if (interface_exists($id)) {
            $definition = new InterfaceDefinition($id, $value);
        } else {
            $definition = new ValueDefinition($id, $value);
        }

        $this->setDefinition($definition);

        return $definition;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id) {
        return array_has($this->definitions, $id);
    }

    /**
     * @param string $id
     */
    public function remove($id) {
        if ($this->has($id)) {
            array_remove($this->definitions, $id);
        }
    }

    /**
     * @param $function
     * @param array $args
     *
     * @return mixed
     */
    public function call($function, array $args = []) {
        return $this->catchExceptions(function () use ($function, $args) {
            return $this->reflector
                ->resolveFunction($this, $function, $args);
        });
    }

    /**
     * @param $instance
     * @param $method
     * @param array $args
     *
     * @return mixed
     */
    public function callMethod($instance, $method, array $args = []) {
        return $this->catchExceptions(function () use ($instance, $method, $args) {
            return $this->reflector
                ->resolveMethod($this, $instance, $method, $args);
        });
    }

    /**
     * @param $class
     * @param $method
     * @param array $args
     *
     * @return mixed
     */
    public function callStaticMethod($class, $method, array $args = []) {
        return $this->catchExceptions(function () use ($class, $method, $args) {
            return $this->reflector
                ->resolveMethod($this, $class, $method, $args);
        });
    }

    /**
     * @param callable $callable
     *
     * @return mixed
     * @throws ImplementationNotFoundException
     * @throws TypeMismatchException
     * @throws UnresolveableArgumentException
     * @throws ValueNotFoundException
     * @throws \Exception
     */
    protected function catchExceptions(callable $callable) {
        try {
            return $callable();
        } catch (ImplementationNotFoundException $ex) {
            throw new ImplementationNotFoundException($ex->getMessage());
        } catch (TypeMismatchException $ex) {
            throw new TypeMismatchException($ex->getMessage());
        } catch (UnresolveableArgumentException $ex) {
            throw new UnresolveableArgumentException($ex->getMessage());
        } catch (ValueNotFoundException $ex) {
            throw new ValueNotFoundException($ex->getMessage());
        }
    }

    /**
     * @return Reflector
     */
    protected function createReflector() {
        return new Reflector();
    }

    /**
     * Put current container instance in the container.
     */
    protected function shareContainerInstance() {
        $this->set(IContainer::class, $this);
        $this->set(static::class, $this);
    }

    /**
     * @param string $id
     * @param array $args
     *
     * @return mixed
     * @throws ImplementationNotFoundException
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    protected function resolveWithoutDefinition($id, array $args = []) {
        if (class_exists($id)) {
            return $this->getClass(new ClassDefinition($id, null), $args);
        }

        if (interface_exists($id)) {
            throw new ImplementationNotFoundException(
                s('No implementation found in container for interface %s.', $id)
            );
        }

        throw new ValueNotFoundException(
            s('No value found in container for id %s.', $id)
        );
    }

    /**
     * @param IDefinition $definition
     * @param array $args
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    protected function resolveDefinition(IDefinition $definition, array $args = []) {
        $value = null;

        if ($definition instanceof InterfaceDefinition) {
            $value = $this->getInterface($definition, $args);
        } else if ($definition instanceof ClassDefinition) {
            $value = $this->getClass($definition, $args);
        } else {
            $value = $definition->getValue();
        }

        $this->processSingletonDefinition($definition, $value);

        return $value;
    }

    /**
     * @param ClassDefinition $definition
     * @param array $args
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    protected function getClass(ClassDefinition $definition, array $args = []) {
        $abstract = $definition->getValue();
        $class = $definition->getId();

        if (is_callable($abstract)) {
            $instance = $this->call($abstract, $args);
        } else if (is_object($abstract)) {
            $instance = $abstract;
        } else {
            if ($abstract === null) {
                $abstract = $class;
            }

            $instance = $this->reflector
                ->resolveClass($this, $abstract, $args);
        }

        $this->matchClassType($class, $instance);

        return $instance;
    }

    /**
     * @param InterfaceDefinition $definition
     * @param array $args
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    protected function getInterface(InterfaceDefinition $definition, array $args = []) {
        $abstract = $definition->getValue();
        $interface = $definition->getId();
        $instance = null;

        if (is_callable($abstract)) {
            $instance = $this->call($abstract, $args);
        } else if (is_object($abstract)) {
            $instance = $abstract;
        } else if (class_exists($abstract)) {
            $instance = $this->getClass(new ClassDefinition($abstract, null), $args);
        }

        $this->matchClassType($interface, $instance);

        return $instance;
    }

    /**
     * @param IDefinition $definition
     * @param $value
     */
    protected function processSingletonDefinition(IDefinition $definition, $value) {
        if ($definition->isSingleton() && ! $definition instanceof ValueDefinition) {
            $newDefinition = new ValueDefinition($definition->getId(), $value);
            $this->setDefinition($newDefinition);
        }
    }

    /**
     * @param IDefinition $definition
     */
    protected function setDefinition(IDefinition $definition) {
        $this->definitions[$definition->getId()] = $definition;
    }

    /**
     * @param $class
     * @param $instance
     *
     * @throws TypeMismatchException
     */
    protected function matchClassType($class, $instance) {
        if ( ! $instance instanceof $class) {
            throw new TypeMismatchException(
                s(
                    'Container expects an implementation of type %s, %s given.',
                    $class, get_type($instance)
                )
            );
        }
    }
}

<?php

namespace Weew\Container;

use Weew\Container\Definitions\ClassDefinition;
use Weew\Container\Definitions\InterfaceDefinition;
use Weew\Container\Definitions\WildcardDefinition;
use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\Exceptions\TypeMismatchException;
use Weew\Container\Exceptions\ValueNotFoundException;

class Resolver {
    /**
     * @var IContainer
     */
    protected $container;

    /**
     * @var Reflector
     */
    protected $reflector;

    /**
     * @param IContainer $container
     * @param Reflector $reflector
     */
    public function __construct(
        IContainer $container,
        Reflector $reflector
    ) {
        $this->container = $container;
        $this->reflector = $reflector;
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
    public function resolveWithoutDefinition($id, array $args = []) {
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
     * @param $id
     * @param array $args
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    public function resolveDefinition(IDefinition $definition, $id, array $args = []) {
        $value = null;

        if ($definition instanceof WildcardDefinition) {
            $value = $this->getWildcard($definition, $id, $args);
        } else if ($definition instanceof InterfaceDefinition) {
            $value = $this->getInterface($definition, $args);
        } else if ($definition instanceof ClassDefinition) {
            $value = $this->getClass($definition, $args);
        } else {
            $value = $definition->getValue();
        }

        return $value;
    }

    /**
     * @param ClassDefinition $definition
     * @param array $args
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    public function getClass(ClassDefinition $definition, array $args = []) {
        $abstract = $definition->getValue();
        $class = $definition->getId();

        if (is_callable($abstract)) {
            $instance = $this->container->call($abstract, $args);
        } else if (is_object($abstract)) {
            $instance = $abstract;
        } else {
            if ($abstract === null) {
                $abstract = $class;
            }

            $instance = $this->reflector
                ->resolveClass($this->container, $abstract, $args);
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
    public function getInterface(InterfaceDefinition $definition, array $args = []) {
        $abstract = $definition->getValue();
        $interface = $definition->getId();
        $instance = null;

        if (is_callable($abstract)) {
            $instance = $this->container->call($abstract, $args);
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
     * @param $id
     * @param $args
     *
     * @return mixed|null
     * @throws TypeMismatchException
     */
    public function getWildcard(IDefinition $definition, $id, $args) {
        $abstract = $definition->getValue();
        $instance = null;

        $args['abstract'] = $id;

        if (is_callable($abstract)) {
            $instance = $this->container->call($abstract, $args);
        } else if (is_object($abstract)) {
            $instance = $abstract;
        } else if (class_exists($abstract)) {
            $instance = $this->getClass(new ClassDefinition($abstract, null), $args);
        }

        $this->matchClassType($id, $instance);

        return $instance;
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
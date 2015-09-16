<?php

namespace Weew\Container;

use Weew\Container\Definitions\ClassDefinition;
use Weew\Container\Definitions\InterfaceDefinition;
use Weew\Container\Definitions\ValueDefinition;
use Weew\Container\Definitions\WildcardDefinition;
use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\Exceptions\InvalidCallableFormatException;
use Weew\Container\Exceptions\TypeMismatchException;
use Weew\Container\Exceptions\UnresolveableArgumentException;
use Weew\Container\Exceptions\ValueNotFoundException;

class DefinitionRegistry {
    /**
     * @var IDefinition[]
     */
    protected $definitions = [];

    /**
     * @var IContainer
     */
    protected $container;

    /**
     * @var IReflector
     */
    protected $reflector;

    /**
     * @param IContainer $container
     * @param IReflector $reflector
     */
    public function __construct(
        IContainer $container,
        IReflector $reflector
    ) {
        $this->container = $container;
        $this->reflector = $reflector;
    }

    /**
     * @param $id
     * @param array $args
     *
     * @return null
     */
    public function get($id, array $args = []) {
        $value = null;
        $definition = $this->getDefinition($id);

        if ($definition instanceof IDefinition) {
            $value = $this->resolveDefinition($definition, $id, $args);
        }

        if ($value === null) {
            return $this->resolveWithoutDefinition($id, $args);
        }

        return $value;
    }

    /**
     * @param $id
     * @param null $value
     *
     * @return IDefinition
     */
    public function set($id, $value = null) {
        $args = func_get_args();

        if (count($args) > 2) {
            array_shift($args);
            $value = $args;
        }

        if ($value === null) {
            $value = $id;

            if (is_object($id)) {
                $id = get_class($id);
            }
        }

        if ($this->isRegexPattern($id)) {
            $definition = new WildcardDefinition($id, $value);
        } else if (class_exists($id)) {
            $definition = new ClassDefinition($id, $value);
        } else if (interface_exists($id)) {
            $definition = new InterfaceDefinition($id, $value);
        } else {
            $definition = new ValueDefinition($id, $value);
        }

        $this->addDefinition($definition);

        return $definition;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function has($id) {
        return $this->getDefinition($id) !== null;
    }

    public function remove($id) {
        $index = $this->getDefinitionIndex($id);

        if ($index !== null) {
            array_remove($this->definitions, $index);
        }
    }


    /**
     * @param $id
     *
     * @return IDefinition
     */
    public function getDefinition($id) {
        foreach ($this->definitions as $definition) {
            if ($definition instanceof WildcardDefinition) {
                if ($this->matchRegexPattern($id, $definition->getId())) {
                    return $definition;
                }
            } else if ($definition->getId() == $id) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @param $id
     *
     * @return int|null|string
     */
    public function getDefinitionIndex($id) {
        $definition = $this->getDefinition($id);

        foreach ($this->definitions as $index => $item) {
            if ($item === $definition) {
                return $index;
            }
        }
    }

    /**
     * @param IDefinition $definition
     */
    public function addDefinition(IDefinition $definition) {
        $this->remove($definition->getId());
        array_unshift($this->definitions, $definition);
    }

    /**
     * @param $string
     *
     * @return bool
     */
    protected function isRegexPattern($string) {
        return str_starts_with($string, '/') &&
        str_ends_with($string, '/') ||
        str_starts_with($string, '#') &&
        str_ends_with($string, '#');
    }

    /**
     * @param $string
     * @param $pattern
     *
     * @return bool
     */
    protected function matchRegexPattern($string, $pattern) {
        return preg_match($pattern, $string) == 1;
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
     * @param $id
     * @param array $args
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    protected function resolveDefinition(IDefinition $definition, $id, array $args = []) {
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

        $this->processSingletonDefinition($definition, $id, $value);

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
    protected function getInterface(InterfaceDefinition $definition, array $args = []) {
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
    protected function getWildcard(IDefinition $definition, $id, $args) {
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
     * @param IDefinition $definition
     * @param $id
     * @param $value
     */
    protected function processSingletonDefinition(IDefinition $definition, $id, $value) {
        if ($definition->isSingleton() && ! $definition instanceof ValueDefinition) {
            $newDefinition = new ValueDefinition($id, $value);
            $this->addDefinition($newDefinition);
        }
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

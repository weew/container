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
        return $this->rethrowExceptions(function () use ($id, $args) {
            $value = null;
            $definition = $this->getDefinition($id);

            if ($definition instanceof IDefinition) {
                $value = $this->resolveDefinition($definition, $id, $args);
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
     * @param string $id
     *
     * @return bool
     */
    public function has($id) {
        return $this->getDefinition($id) !== null;
    }

    /**
     * @param string $id
     */
    public function remove($id) {
        $index = $this->getDefinitionIndex($id);

        if ($index !== null) {
            array_remove($this->definitions, $index);
        }
    }

    /**
     * @param $callable
     * @param array $args
     *
     * @return mixed
     */
    public function call($callable, array $args = []) {
        return $this->rethrowExceptions(function () use ($callable, $args) {
            return $this->reflector
                ->resolveCallable($this, $callable, $args);
        });
    }

    /**
     * @param $function
     * @param array $args
     *
     * @return mixed
     * @throws ImplementationNotFoundException
     * @throws TypeMismatchException
     * @throws UnresolveableArgumentException
     * @throws ValueNotFoundException
     */
    public function callFunction($function, array $args = []) {
        return $this->rethrowExceptions(function () use ($function, $args) {
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
        return $this->rethrowExceptions(function () use ($instance, $method, $args) {
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
        return $this->rethrowExceptions(function () use ($class, $method, $args) {
            return $this->reflector
                ->resolveMethod($this, $class, $method, $args);
        });
    }

    /**
     * Rethrow controlled exceptions, those that get thrown by the container
     * or the resolver, to shorten the call stack and make it more readable.
     *
     * @param callable $callable
     *
     * @return mixed
     * @throws ImplementationNotFoundException
     * @throws TypeMismatchException
     * @throws UnresolveableArgumentException
     * @throws ValueNotFoundException
     * @throws \Exception
     */
    protected function rethrowExceptions(callable $callable) {
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
        } catch (InvalidCallableFormatException $ex) {
            throw new InvalidCallableFormatException($ex->getMessage());
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
            $instance = $this->call($abstract, $args);
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
     * @param $id
     *
     * @return IDefinition
     */
    protected function getDefinition($id) {
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
    protected function getDefinitionIndex($id) {
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
    protected function addDefinition(IDefinition $definition) {
        $this->remove($definition->getId());
        array_unshift($this->definitions, $definition);
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
}

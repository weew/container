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
     * @var IReflector
     */
    protected $reflector;

    /**
     * @var DefinitionRegistry
     */
    protected $registry;

    /**
     * @param IReflector|null $reflector
     */
    public function __construct(IReflector $reflector = null) {
        if ( ! $reflector instanceof IReflector) {
            $reflector = $this->createReflector();
        }

        $this->reflector = $reflector;
        $this->registry = $this->createRegistry();
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
     */
    public function get($id, array $args = []) {
        return $this->rethrowExceptions(function () use ($id, $args) {
            return $this->registry->get($id, $args);
        });
    }

    /**
     * @param string $id
     * @param $value
     *
     * @return IDefinition
     */
    public function set($id, $value = null) {
        return call_user_func_array([$this->registry, 'set'], func_get_args());
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id) {
        return $this->registry->has($id);
    }

    /**
     * @param string $id
     */
    public function remove($id) {
        $this->registry->remove($id);
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
     * @return DefinitionRegistry
     */
    protected function createRegistry() {
        return new DefinitionRegistry($this, $this->reflector);
    }

    /**
     * Put current container instance in the container.
     */
    protected function shareContainerInstance() {
        $this->set(IContainer::class, $this);
        $this->set(static::class, $this);
    }
}

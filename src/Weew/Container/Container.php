<?php

namespace Weew\Container;

use Weew\Container\Definitions\ValueDefinition;
use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\Exceptions\InvalidCallableFormatException;
use Weew\Container\Exceptions\MissingDefinitionIdentifierException;
use Weew\Container\Exceptions\MissingDefinitionValueException;
use Weew\Container\Exceptions\TypeMismatchException;
use Weew\Container\Exceptions\UnresolveableArgumentException;
use Weew\Container\Exceptions\ValueNotFoundException;

class Container implements IContainer {
    /**
     * @var Reflector
     */
    protected $reflector;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * Container constructor.
     */
    public function __construct() {
        $this->reflector = $this->createReflector();
        $this->registry = $this->createRegistry();
        $this->resolver = $this->createResolver();

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
            $value = null;
            $definition =  $this->registry->getDefinition($id);

            if ($definition instanceof IDefinition) {
                $value = $this->resolver->resolveDefinition($definition, $id, $args);
                $this->processSingletonDefinition($definition, $id, $value);
            }

            if ($value === null) {
                return $this->resolver->resolveWithoutDefinition($id, $args);
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
        $args = func_get_args();

        return $this->rethrowExceptions(function() use ($args) {
            return call_user_func_array([$this->registry, 'createDefinition'], $args);
        });
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id) {
        return $this->registry->hasDefinition($id);
    }

    /**
     * @param string $id
     */
    public function remove($id) {
        $this->registry->removeDefinition($id);
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
        } catch (MissingDefinitionIdentifierException $ex) {
            throw new MissingDefinitionIdentifierException($ex->getMessage());
        } catch (MissingDefinitionValueException $ex) {
            throw new MissingDefinitionValueException($ex->getMessage());
        }
    }

    /**
     * @param IDefinition $definition
     * @param $id
     * @param $value
     */
    protected function processSingletonDefinition(IDefinition $definition, $id, $value) {
        if ($definition->isSingleton() && ! $definition instanceof ValueDefinition) {
            $newDefinition = new ValueDefinition($id, $value);
            $this->registry->addDefinition($newDefinition);

            foreach ($definition->getAliases() as $alias) {
                $alias->setValue($newDefinition);
            }
        }
    }

    /**
     * @return Reflector
     */
    protected function createReflector() {
        return new Reflector();
    }

    /**
     * @return Registry
     */
    protected function createRegistry() {
        return new Registry();
    }

    /**
     * @return Resolver
     */
    protected function createResolver() {
        return new Resolver($this, $this->reflector);
    }

    /**
     * Put current container instance in the container.
     */
    protected function shareContainerInstance() {
        $this->set([IContainer::class, static::class], $this);
    }
}

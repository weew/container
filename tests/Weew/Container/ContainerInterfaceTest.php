<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\SimpleImplementation;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\SimpleClass;
use Weew\Container\Container;
use Weew\Container\Exceptions\InterfaceImplementationNotFoundException;
use Weew\Container\Exceptions\TypeMismatchException;

class ContainerInterfaceTest extends PHPUnit_Framework_TestCase {
    public function test_get_interface_without_definition() {
        $container = new Container();
        $this->setExpectedException(InterfaceImplementationNotFoundException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_interface_with_invalid_definition() {
        $container = new Container();
        $container->set(IImplementation::class, SimpleClass::class);
        $this->setExpectedException(TypeMismatchException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_interface() {
        $container = new Container();
        $container->set(IImplementation::class, SimpleImplementation::class);
        $value = $container->get(IImplementation::class);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_interface_with_factory_that_returns_an_invalid_value() {
        $container = new Container();
        $container->set(IImplementation::class, function() {return 1;});
        $this->setExpectedException(TypeMismatchException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_interface_with_factory() {
        $container = new Container();
        $container->set(IImplementation::class, function() {return new SimpleImplementation();});
        $value = $container->get(IImplementation::class);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_interface_with_invalid_instance() {
        $container = new Container();
        $container->set(IImplementation::class, new SimpleClass());
        $this->setExpectedException(TypeMismatchException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_interface_with_instance() {
        $container = new Container();
        $instance = new SimpleImplementation();
        $container->set(IImplementation::class, $instance);
        $value = $container->get(IImplementation::class);
        $this->assertTrue($value === $instance);
    }
}

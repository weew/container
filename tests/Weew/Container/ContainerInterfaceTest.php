<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\SimpleImplementation;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\SimpleClass;
use Weew\Container\Container;
use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\Exceptions\TypeMismatchException;

class ContainerInterfaceTest extends PHPUnit_Framework_TestCase {
    public function test_get_interface_without_definition() {
        $container = new Container();
        $this->setExpectedException(ImplementationNotFoundException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_interface_with_invalid_definition() {
        $container = new Container();
        $container->set(IImplementation::class, SimpleClass::class);
        $this->setExpectedException(TypeMismatchException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_interface_with_invalid_definition_without_strict_mode() {
        $container = new Container(false);
        $container->set(IImplementation::class, SimpleClass::class);
        $this->assertTrue($container->get(IImplementation::class) instanceof SimpleClass);
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

    public function test_get_interface_with_factory_that_returns_an_invalid_value_without_strict_mode() {
        $container = new Container(false);
        $container->set(IImplementation::class, function() {return 1;});
        $this->assertEquals(1, $container->get(IImplementation::class));
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

    public function test_get_interface_with_invalid_instance_without_strict_mode() {
        $container = new Container(false);
        $instance = new SimpleClass();
        $container->set(IImplementation::class, $instance);
        $this->assertTrue($container->get(IImplementation::class) === $instance);
    }

    public function test_get_interface_with_instance() {
        $container = new Container();
        $instance = new SimpleImplementation();
        $container->set(IImplementation::class, $instance);
        $value = $container->get(IImplementation::class);
        $this->assertTrue($value === $instance);
    }

    public function test_get_interface_with_interface() {
        $container = new Container();
        $container->set(IImplementation::class, IImplementation::class);
        $this->setExpectedException(TypeMismatchException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_interface_with_interface_without_strict_mode() {
        $container = new Container(false);
        $container->set(IImplementation::class, IImplementation::class);
        $this->setExpectedException(ImplementationNotFoundException::class);
        $container->get(IImplementation::class);
    }
}

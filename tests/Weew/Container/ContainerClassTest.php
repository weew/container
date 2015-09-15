<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\ComplexClass;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\InterfaceClass;
use Tests\Weew\Container\Stubs\NullableClass;
use Tests\Weew\Container\Stubs\SimpleImplementation;
use Tests\Weew\Container\Stubs\SimpleClass;
use Weew\Container\Container;
use Weew\Container\Exceptions\TypeMismatchException;
use Weew\Container\Exceptions\UnresolveableArgumentException;
use Weew\Container\Exceptions\ValueNotFoundException;

class ContainerClassTest extends PHPUnit_Framework_TestCase {
    public function test_get_invalid_class_without_definition() {
        $container = new Container();
        $this->setExpectedException(ValueNotFoundException::class);
        $container->get('foo');
    }

    public function test_get_class_without_definition() {
        $container = new Container();
        $value = $container->get(SimpleClass::class);
        $this->assertTrue($value instanceof SimpleClass);
    }

    public function test_get_class_with_factory_that_returns_an_invalid_value() {
        $container = new Container();
        $container->set(SimpleClass::class, function() {return 1;});
        $this->setExpectedException(TypeMismatchException::class);
        $container->get(SimpleClass::class);
    }

    public function test_get_class_with_factory() {
        $container = new Container();
        $container->set(SimpleClass::class, function() {return new SimpleClass();});
        $value = $container->get(SimpleClass::class);
        $this->assertTrue($value instanceof SimpleClass);
    }

    public function test_get_class_with_invalid_instance() {
        $container = new Container();
        $container->set(SimpleClass::class, new SimpleImplementation());
        $this->setExpectedException(TypeMismatchException::class);
        $container->get(SimpleClass::class);
    }

    public function test_get_class_with_instance() {
        $container = new Container();
        $instance = new SimpleClass();
        $container->set(SimpleClass::class, $instance);
        $value = $container->get(SimpleClass::class);
        $this->assertTrue($value === $instance);
    }

    public function test_get_class_with_dependencies() {
        $container = new Container();
        $value = $container->get(ComplexClass::class, ['foo' => 'bar']);
        $this->assertTrue($value instanceof ComplexClass);
        $this->assertEquals('bar', $value->foo);
    }

    public function test_get_class_with_missing_unresolvable_dependencies() {
        $container = new Container();
        $this->setExpectedException(
            UnresolveableArgumentException::class,
            'Container could not resolve argument 2 for Tests\Weew\Container\Stubs\ComplexClass::__construct.'
        );
        $container->get(ComplexClass::class);
    }

    public function test_get_class_with_interface_dependencies() {
        $container = new Container();
        $container->set(IImplementation::class, SimpleImplementation::class);
        $value = $container->get(InterfaceClass::class);
        $this->assertTrue($value instanceof InterfaceClass);
    }

    public function test_get_class_with_nullable_dependencies() {
        $container = new Container();
        $value = $container->get(NullableClass::class);
        $this->assertTrue($value instanceof NullableClass);
        $this->assertNull($value->interface);
        $this->assertNull($value->implementation);
        $this->assertEquals(1, $value->foo);
    }

    public function test_get_class_with_some_nullable_dependencies() {
        $container = new Container();
        $container->set(IImplementation::class, SimpleImplementation::class);
        $value = $container->get(NullableClass::class);
        $this->assertTrue($value instanceof NullableClass);
        $this->assertTrue($value->interface instanceof InterfaceClass);
        $this->assertTrue($value->implementation instanceof SimpleImplementation);
        $this->assertEquals(1, $value->foo);
    }

    public function test_set_by_class_only() {
        $container = new Container();
        $container->set(SimpleImplementation::class)->singleton();
        $value = $container->get(SimpleImplementation::class);
        $value2 = $container->get(SimpleImplementation::class);

        $this->assertTrue($value === $value2);
    }

    public function test_set_definition_by_instance() {
        $container = new Container();
        $value = new SimpleImplementation();
        $container->set($value);
        $value2 = $container->get(SimpleImplementation::class);

        $this->assertTrue($value === $value2);
    }
}

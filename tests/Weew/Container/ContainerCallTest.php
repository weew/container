<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\InterfaceClass;
use Tests\Weew\Container\Stubs\MethodClass;
use Tests\Weew\Container\Stubs\SimpleClass;
use Weew\Container\Container;
use Weew\Container\Exceptions\UnresolveableArgumentException;

class ContainerCallTest extends PHPUnit_Framework_TestCase {
    public function test_call_function() {
        $container = new Container();
        $callable = function(array $array) {return array_pop($array);};
        $this->assertEquals(1, $container->call($callable, ['array' => [1]]));
    }

    public function test_call_function_with_dependencies() {
        $container = new Container();
        $callable = function(SimpleClass $instance, array $array) {return array_pop($array);};
        $this->assertEquals(1, $container->call($callable, ['array' => [1]]));
    }

    public function test_call_function_with_nullable_dependencies() {
        $container = new Container();
        $callable = function(InterfaceClass $class = null, array $array, $foo = 1) {return $foo;};
        $this->assertEquals(1, $container->call($callable, ['array' => []]));
    }

    public function test_call_function_with_missing_unresolvable_dependencies() {
        $container = new Container();
        $callable = function($foo) {};
        $this->setExpectedException(UnresolveableArgumentException::class);
        $container->call($callable);
    }

    public function test_call_method() {
        $container = new Container();
        $instance = new MethodClass();
        $this->assertEquals(1, $container->callMethod($instance, 'method', ['array' => [1]]));
    }

    public function test_call_method_with_dependencies() {
        $container = new Container();
        $instance = new MethodClass();
        $this->assertEquals(1, $container->callMethod($instance, 'complexMethod', ['array' => [1]]));
    }

    public function test_call_method_with_nullable_dependencies() {
        $container = new Container();
        $instance = new MethodClass();
        $this->assertEquals(1, $container->callMethod($instance, 'complexNullableMethod', ['array' => []]));
    }

    public function test_call_method_with_missing_unresolvable_dependencies() {
        $container = new Container();
        $instance = new MethodClass();
        $this->setExpectedException(UnresolveableArgumentException::class);
        $this->assertEquals(1, $container->callMethod($instance, 'complexMethod'));
    }

    public function test_call_static_method() {
        $container = new Container();
        $this->assertEquals(1, $container->callStaticMethod(MethodClass::class, 'staticMethod', ['array' => [1]]));
    }

    public function test_call_static_method_with_dependencies() {
        $container = new Container();
        $instance = new MethodClass();
        $this->assertEquals(1, $container->callStaticMethod($instance, 'complexStaticMethod', ['array' => [1]]));
    }

    public function test_call_static_method_with_nullable_dependencies() {
        $container = new Container();
        $instance = new MethodClass();
        $this->assertEquals(1, $container->callStaticMethod($instance, 'complexNullableStaticMethod', ['array' => []]));
    }

    public function test_call_static_method_with_missing_unresolvable_dependencies() {
        $container = new Container();
        $instance = new MethodClass();
        $this->setExpectedException(UnresolveableArgumentException::class);
        $this->assertEquals(1, $container->callStaticMethod($instance, 'complexStaticMethod'));
    }
}

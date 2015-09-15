<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\InterfaceClass;
use Tests\Weew\Container\Stubs\MethodClass;
use Tests\Weew\Container\Stubs\SimpleClass;
use Weew\Container\Container;
use Weew\Container\Exceptions\InvalidCallableFormatException;
use Weew\Container\Exceptions\UnresolveableArgumentException;

class ContainerCallTest extends PHPUnit_Framework_TestCase {
    public function test_call_function() {
        $container = new Container();
        $callable = function(array $array) {return array_pop($array);};
        $this->assertEquals(1, $container->callFunction($callable, ['array' => [1]]));
    }

    public function test_call_function_with_dependencies() {
        $container = new Container();
        $callable = function(SimpleClass $instance, array $array) {return array_pop($array);};
        $this->assertEquals(1, $container->callFunction($callable, ['array' => [1]]));
    }

    public function test_call_function_with_nullable_dependencies() {
        $container = new Container();
        $callable = function(InterfaceClass $class = null, array $array, $foo = 1) {return $foo;};
        $this->assertEquals(1, $container->callFunction($callable, ['array' => []]));
    }

    public function test_call_function_with_missing_unresolvable_dependencies() {
        $container = new Container();
        $callable = function($foo) {};
        $this->setExpectedException(UnresolveableArgumentException::class);
        $container->callFunction($callable);
    }

    public function test_call_method() {
        $container = new Container();
        $instance = new MethodClass();
        $this->assertEquals(1, $container->callMethod($instance, 'method', ['array' => [1]]));
    }

    public function test_call_method_with_class_name() {
        $container = new Container();
        $this->assertEquals(1, $container->callMethod(MethodClass::class, 'method', ['array' => [1]]));
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

    public function test_call() {
        $container = new Container();
        $callable = function(array $array) {return array_pop($array);};
        $this->assertEquals(1, $container->callFunction($callable, ['array' => [1]]));
    }

    public function test_call_method_with_array_syntax() {
        $container = new Container();
        $this->assertEquals(
            1, $container->call([MethodClass::class, 'method'], ['array' => [1]])
        );
    }

    public function test_call_static_method_with_array_syntax() {
        $container = new Container();
        $this->assertEquals(
            1, $container->call([MethodClass::class, 'staticMethod'], ['array' => [1]])
        );
    }

    public function test_call_with_invalid_callable() {
        $container = new Container();
        $this->setExpectedException(InvalidCallableFormatException::class);
        $this->assertEquals(
            1, $container->call(['foo'], ['array' => [1]])
        );
    }
}

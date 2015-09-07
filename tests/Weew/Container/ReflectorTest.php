<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\MethodClass;
use Tests\Weew\Container\Stubs\NoConstructor;
use Tests\Weew\Container\Stubs\SharedClass;
use Tests\Weew\Container\Stubs\SharedConstructor;
use Tests\Weew\Container\Stubs\SimpleConstructor;
use Tests\Weew\Container\Stubs\ComplexConstructor;
use Weew\Container\Exceptions\ClassNotFoundException;
use Weew\Container\Reflector;
use Weew\Container\Container;
use Weew\Container\Exceptions\MissingArgumentException;

class ReflectorTest extends PHPUnit_Framework_TestCase {
    public function test_resolve_no_constructor() {
        $container = new Container();
        $reflector = new Reflector();
        $this->assertTrue($reflector->resolveClass($container, NoConstructor::class) instanceof NoConstructor);
    }

    public function test_resolve_bad_class() {
        $container = new Container();
        $reflector = new Reflector();
        $this->setExpectedException(ClassNotFoundException::class);
        $reflector->resolveClass($container, 'Foo');
    }

    public function test_resolve_simple_constructor() {
        $container = new Container();
        $reflector = new Reflector();
        $concrete = $reflector->resolveClass($container, SimpleConstructor::class, ['x' => 1, 'z' => 3]);
        $this->assertTrue($concrete instanceof SimpleConstructor);
        $this->assertEquals(1, $concrete->x);
        $this->assertEquals(2, $concrete->y);
        $this->assertEquals(3, $concrete->z);
    }

    public function test_resolve_complex_constructor() {
        $container = new Container();
        $reflector = new Reflector();
        $concrete = $reflector->resolveClass($container, ComplexConstructor::class, ['a' => 2]);
        $this->assertTrue($concrete instanceof ComplexConstructor);
        $this->assertTrue($concrete->b instanceof NoConstructor);
        $this->assertEquals(1, $concrete->c);
    }

    public function test_missing_argument_exception_gets_thrown() {
        $container = new Container();
        $reflector = new Reflector();
        $this->setExpectedException(
            MissingArgumentException::class,
            'Missing argument 0 for Tests\Weew\Container\Stubs\ComplexConstructor::__construct.'
        );
        $concrete = $reflector->resolveClass($container, ComplexConstructor::class);
        $this->assertTrue($concrete instanceof ComplexConstructor);
        $this->assertTrue($concrete->b instanceof NoConstructor);
        $this->assertEquals(1, $concrete->c);
    }

    public function test_with_shared_classes() {
        $container = new Container();
        $reflector = new Reflector();

        $concrete = $reflector->resolveClass($container, SharedConstructor::class, ['x' => 9]);
        $this->assertEquals(9, $concrete->x);
        $this->assertEquals(1, $concrete->shared->x);

        $shared = new SharedClass();
        $shared->x = 123;
        $container->set(SharedClass::class, $shared);

        $concrete = $reflector->resolveClass($container, SharedConstructor::class, ['x' => 9]);
        $this->assertEquals(9, $concrete->x);
        $this->assertEquals(123, $concrete->shared->x);
    }

    public function test_resolve_function() {
        $container = new Container();
        $reflector = new Reflector();
        $test = $this;
        $callable = function(NoConstructor $cls, $foo = 2, $bar) use ($test) {
            $test->assertTrue($cls instanceof NoConstructor);
            $test->assertEquals(2, $foo);
            $test->assertEquals(1, $bar);
        };

        $reflector->resolveFunction($container, $callable, ['bar' => 1]);
    }

    public function test_resolve_method() {
        $container = new Container();
        $reflector = new Reflector();

        $instance = new MethodClass();
        $result = $reflector->resolveMethod($container, $instance, 'foo', ['y' => 2]);
        $this->assertEquals(8, $result);
    }

    public function test_resolve_static_method() {
        $container = new Container();
        $reflector = new Reflector();

        $result = $reflector->resolveMethod($container, MethodClass::class, 'bar', ['y' => 2]);
        $this->assertEquals(8, $result);
    }

    public function test_resolve_method_exception_gets_thrown() {
        $container = new Container();
        $reflector = new Reflector();

        $instance = new MethodClass();
        $this->setExpectedException(MissingArgumentException::class);
        $reflector->resolveMethod($container, $instance, 'foo');
    }

    public function test_resolve_function_throws_exception() {
        $container = new Container();
        $reflector = new Reflector();
        $callable = function($foo) {};
        $this->setExpectedException(MissingArgumentException::class);
        $reflector->resolveFunction($container, $callable);
    }
}

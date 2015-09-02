<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tests\Weew\Container\Stubs\BarImplementation;
use Tests\Weew\Container\Stubs\FooImplementation;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\InterfaceConstructor;
use Tests\Weew\Container\Stubs\MethodClass;
use Tests\Weew\Container\Stubs\NoConstructor;
use Tests\Weew\Container\Stubs\SharedClass;
use Tests\Weew\Container\Stubs\SharedConstructor;
use Weew\Container\Container;
use Weew\Container\Exceptions\ClassNotFoundException;
use Weew\Container\Exceptions\ImplementationNotFoundException;
use Weew\Container\IContainer;

class ContainerTest extends PHPUnit_Framework_TestCase {
    public function test_set() {
        $container = new Container();
        $container->set('foo', 'bar');
        $this->assertEquals('bar', $container->get('foo'));

        $obj = new stdClass();
        $obj->foo = 'bar';
        $container->set('bar', $obj);
        $this->assertTrue($container->get('bar') === $obj);
    }

    public function test_set_instance() {
        $container = new Container();
        $implementation = new BarImplementation();

        $container->set($implementation);
        $this->assertTrue($container->get(BarImplementation::class) === $implementation);
    }

    public function test_get() {
        $container = new Container();
        $shared = $container->get(SharedConstructor::class, ['x' => 2]);
        $this->assertEquals(2, $shared->x);
        $this->assertEquals(1, $shared->shared->x);
    }

    public function test_call() {
        $container = new Container();
        $test = $this;
        $container->call(function(NoConstructor $cls, $foo = 'bar') use ($test) {
            $test->assertTrue($cls instanceof NoConstructor);
            $test->assertEquals('bar', $foo);
        });
    }

    public function test_call_method() {
        $container = new Container();

        $instance = new MethodClass();
        $result = $container->callMethod($instance, 'foo', ['y' => 2]);
        $this->assertEquals(8, $result);
    }

    public function test_call_static_method() {
        $container = new Container();
        $result = $container->callStaticMethod(MethodClass::class, 'bar', ['y' => 2]);
        $this->assertEquals(8, $result);
    }

    public function test_factory() {
        $container = new Container();
        $container->set('foo', function(SharedClass $shared) {
            return $shared->x + 2;
        });
        $result = $container->get('foo');
        $this->assertEquals(3, $result);
    }

    public function test_get_interface_with_factory() {
        $container = new Container();

        $container->set(IImplementation::class, function() {
            $implementation = new BarImplementation();
            $implementation->x = 99;

            return $implementation;
        });

        $concrete = $container->get(InterfaceConstructor::class);
        $this->assertTrue($concrete->implementation instanceof BarImplementation);
        $this->assertEquals(99, $concrete->implementation->x);
    }

    public function test_get_interface_with_implementation_factory() {
        $container = new Container();

        $container->set(BarImplementation::class, function() {
            $implementation = new BarImplementation();
            $implementation->x = 22;

            return $implementation;
        });
        $container->set(IImplementation::class, BarImplementation::class);

        $concrete = $container->get(InterfaceConstructor::class);
        $this->assertTrue($concrete->implementation instanceof BarImplementation);
        $this->assertEquals(22, $concrete->implementation->x);
    }

    public function test_get_with_interface_argument() {
        $container = new Container();

        $this->setExpectedException(ImplementationNotFoundException::class);
        $container->get(InterfaceConstructor::class);

        $container->set(IImplementation::class, new FooImplementation());
        $concrete = $container->get(InterfaceConstructor::class);
        $this->assertTrue($concrete->implementation instanceof FooImplementation);
    }

    public function test_get_with_type_hinted_interface_argument() {
        $container = new Container();

        $container->set(IImplementation::class, FooImplementation::class);
        $concrete = $container->get(InterfaceConstructor::class);
        $this->assertTrue($concrete->implementation instanceof FooImplementation);
    }

    public function test_has() {
        $container = new Container();
        $this->assertFalse($container->has('foo'));
        $container->set('foo', 'bar');
        $this->assertTrue($container->has('foo'));
        $this->assertEquals('bar', $container->get('foo'));
    }

    public function test_remove() {
        $container = new Container();
        $container->set('foo', 'bar');
        $this->assertTrue($container->has('foo'));
        $container->remove('foo');
        $this->assertFalse($container->has('foo'));
    }

    public function test_container_registers_itself() {
        $container = new Container();
        $container->set('foo', 'bar');

        $c = $container->get(Container::class);
        $this->assertTrue($c->has('foo'));
        $this->assertEquals('bar', $c->get('foo'));

        $c = $container->get(IContainer::class);
        $this->assertTrue($c->has('foo'));
        $this->assertEquals('bar', $c->get('foo'));
    }

    public function test_complete_functionality() {
        $container = new Container();

        $container->set('foo', 'bar');
        $this->assertEquals('bar', $container->get('foo'));

        $instance = $container->get(NoConstructor::class);
        $this->assertTrue($instance instanceof NoConstructor);

        $container->set(NoConstructor::class, $instance);
        $this->assertTrue(
            $container->get(NoConstructor::class) === $instance
        );

        $container->set($instance);
        $this->assertTrue(
            $container->get(NoConstructor::class) === $instance
        );

        $container->set(SharedClass::class, function() {
            $instance = new SharedClass();
            $instance->x = 99;

            return $instance;
        });
        $instance = $container->get(SharedClass::class);
        $this->assertEquals(99, $instance->x);

        $container->set(IImplementation::class, BarImplementation::class);
        $this->assertTrue(
            $container->get(IImplementation::class) instanceof BarImplementation
        );

        $container->set(IImplementation::class, function(IContainer $container) {
            $instance = new FooImplementation();
            $container->set(IImplementation::class, $instance);

            return $instance;
        });
        $instance = $container->get(IImplementation::class);
        $this->assertTrue(
            $instance instanceof FooImplementation
        );
        $this->assertTrue(
            $container->get(IImplementation::class) === $instance
        );

//        get(function)
//        get(closure)
//        get(instance, method)
//        get(class, method)
    }

    public function test_array_access_and_count() {
        $container = new Container();
        $count = count($container);

        $this->assertFalse(isset($container['foo']));
        $container->set('foo', 'bar');
        $this->assertEquals('bar', $container['foo']);
        $container['bar'] = 'baz';
        $this->assertEquals('baz', $container->get('bar'));

        $this->assertEquals($count + 2, count($container));

        unset($container['bar']);
        $this->assertFalse(isset($container['bar']));
    }
}

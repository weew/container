<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\SimpleImplementation;
use Weew\Container\Container;

class ContainerSingletonTest extends PHPUnit_Framework_TestCase {
    public function test_get_value_as_singleton() {
        $container = new Container();
        $instance = new stdClass();

        $container->set('foo', $instance)->singleton();
        $value = $container->get('foo');
        $this->assertTrue($value === $instance);
    }

    public function test_get_class_as_singleton() {
        $container = new Container();

        $this->assertTrue(
            $container->get(SimpleImplementation::class) !== $container->get(SimpleImplementation::class)
        );

        $container->set(SimpleImplementation::class, SimpleImplementation::class)->singleton();
        $this->assertTrue(
            $container->get(SimpleImplementation::class) === $container->get(SimpleImplementation::class)
        );
    }

    public function test_get_class_factory_as_singleton() {
        $container = new Container();

        $container->set(SimpleImplementation::class, function() {
            return new SimpleImplementation();
        });
        $this->assertTrue(
            $container->get(SimpleImplementation::class) !== $container->get(SimpleImplementation::class)
        );

        $container->set(SimpleImplementation::class, function() {
            return new SimpleImplementation();
        })->singleton();
        $this->assertTrue(
            $container->get(SimpleImplementation::class) === $container->get(SimpleImplementation::class)
        );
    }

    public function test_get_class_instance_as_singleton() {
        $container = new Container();

        $container->set(SimpleImplementation::class, new SimpleImplementation());
        $this->assertTrue(
            $container->get(SimpleImplementation::class) === $container->get(SimpleImplementation::class)
        );

        $container->set(SimpleImplementation::class, new SimpleImplementation())->singleton();
        $this->assertTrue(
            $container->get(SimpleImplementation::class) === $container->get(SimpleImplementation::class)
        );
    }

    public function test_get_interface_with_class_as_singleton() {
        $container = new Container();

        $container->set(IImplementation::class, SimpleImplementation::class);
        $this->assertTrue(
            $container->get(IImplementation::class) !== $container->get(IImplementation::class)
        );

        $container->set(IImplementation::class, SimpleImplementation::class)->singleton();
        $this->assertTrue(
            $container->get(IImplementation::class) === $container->get(IImplementation::class)
        );
    }

    public function test_get_interface_with_factory_as_singleton() {
        $container = new Container();

        $container->set(IImplementation::class, function() {
            return new SimpleImplementation();
        });
        $this->assertTrue(
            $container->get(IImplementation::class) !== $container->get(IImplementation::class)
        );

        $container->set(IImplementation::class, function() {
            return new SimpleImplementation();
        })->singleton();
        $this->assertTrue(
            $container->get(IImplementation::class) === $container->get(IImplementation::class)
        );
    }

    public function test_get_interface_with_instance_as_singleton() {
        $container = new Container();

        $container->set(IImplementation::class, new SimpleImplementation());
        $this->assertTrue(
            $container->get(IImplementation::class) === $container->get(IImplementation::class)
        );

        $container->set(IImplementation::class, new SimpleImplementation())->singleton();
        $this->assertTrue(
            $container->get(IImplementation::class) === $container->get(IImplementation::class)
        );
    }
}

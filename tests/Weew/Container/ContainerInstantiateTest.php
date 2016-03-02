<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\ComplexClass;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\SimpleClass;
use Weew\Container\Container;
use Weew\Container\Exceptions\ClassNotFoundException;

class ContainerInstantiateTest extends PHPUnit_Framework_TestCase {
    public function test_instantiate_checks_class_existence() {
        $container = new Container();
        $this->setExpectedException(ClassNotFoundException::class);
        $container->instantiate(IImplementation::class);
    }

    public function test_instantiate_ignores_factories() {
        $container = new Container();
        $simple = new SimpleClass();
        $container->set(ComplexClass::class, function() use ($simple) {
            return new ComplexClass($simple, 'factory');
        });

        $instance1 = $container->get(ComplexClass::class);
        $instance2 = $container->get(ComplexClass::class);

        $this->assertEquals('factory', $instance1->foo);
        $this->assertEquals('factory', $instance2->foo);
        $this->assertTrue($instance1->simple === $instance2->simple);

        $instance3 = $container->instantiate(ComplexClass::class, ['foo' => 'instance']);
        $this->assertEquals('instance', $instance3->foo);
        $this->assertFalse($instance3->simple === $instance1->simple);
    }

    public function test_instantiate_ignores_singletons() {
        $container = new Container();
        $simple = new SimpleClass();
        $container->set(ComplexClass::class, function() use ($simple) {
            return new ComplexClass($simple, 'factory');
        })->singleton();

        $instance1 = $container->get(ComplexClass::class);
        $instance2 = $container->get(ComplexClass::class);

        $this->assertTrue($instance1 === $instance2);
        $this->assertEquals('factory', $instance1->foo);
        $this->assertEquals('factory', $instance2->foo);
        $this->assertTrue($instance1->simple === $instance2->simple);

        $instance3 = $container->instantiate(ComplexClass::class, ['foo' => 'instance']);
        $this->assertEquals('instance', $instance3->foo);
        $this->assertFalse($instance3 === $instance1);
        $this->assertFalse($instance3->simple === $instance1->simple);
    }
}

<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\SimpleClass;
use Tests\Weew\Container\Stubs\SimpleImplementation;
use Weew\Container\Container;

class ContainerFactoryTest extends PHPUnit_Framework_TestCase {
    public function test_class_factory_gets_arguments_injected() {
        $container = new Container();
        $container->set(SimpleImplementation::class, function(SimpleClass $class) {
            return new SimpleImplementation();
        });
        $value = $container->get(SimpleImplementation::class);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_interface_factory_gets_arguments_injected() {
        $container = new Container();
        $container->set(IImplementation::class, function(SimpleClass $class) {
            return new SimpleImplementation();
        });
        $value = $container->get(SimpleImplementation::class);
        $this->assertTrue($value instanceof SimpleImplementation);
    }
}

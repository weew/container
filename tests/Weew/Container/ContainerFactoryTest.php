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

    public function test_factory_with_instance_and_method() {
        $container = new Container();
        $container->set(IImplementation::class, $this, 'methodFactory');
        $value = $container->get(IImplementation::class, ['foo' => 'bar']);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_factory_with_class_and_method() {
        $container = new Container();
        $container->set(IImplementation::class, self::class, 'methodFactory');
        $value = $container->get(IImplementation::class, ['foo' => 'bar']);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_factory_with_with_static_method() {
        $container = new Container();
        $container->set(IImplementation::class, self::class, 'staticMethodFactory');
        $value = $container->get(IImplementation::class, ['foo' => 'bar']);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_factory_with_array_syntax_with_instance_and_method() {
        $container = new Container();
        $container->set(IImplementation::class, [$this, 'methodFactory']);
        $value = $container->get(IImplementation::class, ['foo' => 'bar']);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_factory_with_array_syntax_with_class_and_method() {
        $container = new Container();
        $container->set(IImplementation::class, [self::class, 'methodFactory']);
        $value = $container->get(IImplementation::class, ['foo' => 'bar']);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_factory_with_array_syntax_with_static_method() {
        $container = new Container();
        $container->set(IImplementation::class, [self::class, 'staticMethodFactory']);
        $value = $container->get(IImplementation::class, ['foo' => 'bar']);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function methodFactory(SimpleClass $class, $foo) {
        return new SimpleImplementation();
    }

    public static function staticMethodFactory(SimpleClass $class, $foo) {
        return new SimpleImplementation();
    }
}

<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Container\Stubs\BaseImplementation;
use Tests\Weew\Container\Stubs\ComplexClass;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\SimpleClass;
use Tests\Weew\Container\Stubs\SimpleImplementation;
use Tests\Weew\Container\Stubs\SpecialImplementation;
use Weew\Container\Container;
use Weew\Container\Exceptions\TypeMismatchException;

class ContainerWildcardTest extends PHPUnit_Framework_TestCase {
    public function test_get_interface_with_wildcard() {
        $container = new Container();
        $container->set('/Implementation$/', function() {
            return new SimpleImplementation();
        });
        $value = $container->get(IImplementation::class);

        $this->assertTrue($value instanceof IImplementation);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_interface_singleton_with_wildcard() {
        $container = new Container();
        $container->set('/Implementation$/', function() {
            return new SimpleImplementation();
        })->singleton();

        $value1 = $container->get(IImplementation::class);
        $value2 = $container->get(IImplementation::class);

        $this->assertTrue($value1 instanceof IImplementation);
        $this->assertTrue($value1 instanceof SimpleImplementation);
        $this->assertTrue($value1 === $value2);
    }

    public function test_get_interface_with_wildcard_with_instance() {
        $container = new Container();
        $container->set('/Implementation$/', new SimpleImplementation());
        $value = $container->get(IImplementation::class);

        $this->assertTrue($value instanceof IImplementation);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_interface_with_wildcard_with_class_name() {
        $container = new Container();
        $container->set('/Implementation$/', SimpleImplementation::class);
        $value = $container->get(IImplementation::class);

        $this->assertTrue($value instanceof IImplementation);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_interface_with_wildcard_with_invalid_value() {
        $container = new Container();
        $container->set('/Implementation$/', function() {
            return new SimpleClass();
        });

        $this->setExpectedException(TypeMismatchException::class);
        $container->get(IImplementation::class);
    }

    public function test_get_class_with_wildcard() {
        $container = new Container();
        $container->set('/Implementation$/', function() {
            return new SimpleImplementation();
        });

        $value = $container->get(BaseImplementation::class);

        $this->assertTrue($value instanceof BaseImplementation);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_class_singleton_with_wildcard() {
        $container = new Container();
        $container->set('/Implementation$/', function($abstract) {
            return new $abstract(1);
        })->singleton();

        $value1 = $container->get(BaseImplementation::class);
        $value2 = $container->get(BaseImplementation::class);

        $this->assertTrue($value1 instanceof BaseImplementation);
        $this->assertTrue($value1 === $value2);

        $value3 = $container->get(SpecialImplementation::class);
        $this->assertTrue($value3 instanceof SpecialImplementation);
        $this->assertTrue($value1 !== $value3);
    }

    public function test_get_class_with_wildcard_with_instance() {
        $container = new Container();
        $container->set('/Implementation$/', new SimpleImplementation());

        $value = $container->get(BaseImplementation::class);

        $this->assertTrue($value instanceof BaseImplementation);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_class_with_wildcard_with_class_name() {
        $container = new Container();
        $container->set('/Implementation$/', SimpleImplementation::class);

        $value = $container->get(BaseImplementation::class);

        $this->assertTrue($value instanceof BaseImplementation);
        $this->assertTrue($value instanceof SimpleImplementation);
    }

    public function test_get_class_with_wildcard_with_invalid_value() {
        $container = new Container();
        $container->set('#Implementation$#', function() {
            return new SimpleClass();
        });

        $this->setExpectedException(TypeMismatchException::class);
        $container->get(BaseImplementation::class);
    }

    public function test_get_with_wildcard_properly_passes_the_actual_id() {
        $container = new Container();
        $container->set('#Class#', function($abstract) {
            return new ComplexClass(new SimpleClass(), $abstract);
        });

        $value = $container->get(ComplexClass::class);
        $this->assertTrue($value instanceof ComplexClass);
        $this->assertEquals(ComplexClass::class, $value->foo);
    }
}

<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tests\Weew\Container\Stubs\IImplementation;
use Tests\Weew\Container\Stubs\SimpleImplementation;
use Weew\Container\Container;
use Weew\Container\Exceptions\MissingDefinitionIdentifierException;
use Weew\Container\Exceptions\MissingDefinitionValueException;

class ContainerAliasTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_primitive_value_by_alias() {
        $container = new Container();
        $container->set(['foo', 'bar', 'baz'], new stdClass());

        $foo = $container->get('foo');
        $bar = $container->get('bar');
        $baz = $container->get('baz');

        $this->assertTrue($foo === $bar);
        $this->assertTrue($foo === $baz);
    }

    public function test_provide_only_one_identifier_without_aliases() {
        $container = new Container();
        $container->set(['foo'], new stdClass());

        $foo = $container->get('foo');
        $this->assertTrue($foo instanceof stdClass);
    }

    public function test_get_and_set_class_by_alias() {
        $container = new Container();
        $container->set(
            [SimpleImplementation::class, IImplementation::class], new SimpleImplementation()
        );
        $value1 = $container->get(SimpleImplementation::class);
        $value2 = $container->get(IImplementation::class);

        $this->assertTrue($value1 === $value2);
    }

    public function test_get_and_set_singleton_class_by_alias() {
        $container = new Container();
        $container->set(
            [SimpleImplementation::class, IImplementation::class], SimpleImplementation::class
        )->singleton();
        $value1 = $container->get(SimpleImplementation::class);
        $value2 = $container->get(IImplementation::class);

        $this->assertTrue($value1 === $value2);
    }

    public function test_set_definition_without_identifiers() {
        $container = new Container();
        $this->setExpectedException(MissingDefinitionIdentifierException::class);
        $container->set([], new SimpleImplementation());
    }

    public function test_set_definition_alias_without_a_value() {
        $container = new Container();
        $this->setExpectedException(MissingDefinitionValueException::class);
        $container->set([SimpleImplementation::class]);
    }

    public function test_remove_parent_definition_and_get_alias() {
        $container = new Container();
        $container->set(
            [SimpleImplementation::class, IImplementation::class], SimpleImplementation::class
        );
        $value1 = $container->get(SimpleImplementation::class);
        $value2 = $container->get(IImplementation::class);

        $this->assertTrue($value1 instanceof SimpleImplementation);
        $this->assertTrue($value2 instanceof SimpleImplementation);

        $container->remove(SimpleImplementation::class);
        $this->assertFalse($container->has(SimpleImplementation::class));
        $this->assertTrue($container->has(IImplementation::class));
        $value2 = $container->get(IImplementation::class);
        $this->assertTrue($value2 instanceof SimpleImplementation);
    }
}

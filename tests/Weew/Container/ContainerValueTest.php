<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Weew\Container\Container;
use Weew\Container\Exceptions\ValueNotFoundException;

class ContainerValueTest extends PHPUnit_Framework_TestCase {
    public function test_get_value_without_definition() {
        $container = new Container();
        $this->setExpectedException(ValueNotFoundException::class);
        $container->get('foo');
    }

    public function test_get_value() {
        $container = new Container();
        $container->set('foo', 'bar');
        $value = $container->get('foo');
        $this->assertEquals('bar', $value);
    }
}

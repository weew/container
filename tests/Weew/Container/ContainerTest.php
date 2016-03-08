<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Weew\Container\Container;
use Weew\Container\IContainer;

class ContainerTest extends PHPUnit_Framework_TestCase {
    public function test_container_shares_its_instance() {
        $container = new Container();
        $this->assertTrue($container->get(Container::class) === $container);
        $this->assertTrue($container->get(IContainer::class) === $container);
    }

    public function test_has() {
        $container = new Container();
        $this->assertFalse($container->has('foo'));
        $container->set('foo', 'bar');
        $this->assertTrue($container->has('foo'));
    }

    public function test_remove() {
        $container = new Container();
        $container->set('foo', 'bar');
        $container->set('bar', 'bar');
        $container->set('baz', 'bar');
        $this->assertTrue($container->has('foo'));
        $container->remove('foo');
        $this->assertFalse($container->has('foo'));
        $this->assertTrue($container->has('bar'));
        $this->assertTrue($container->has('baz'));
    }

    public function test_remove_invalid_id() {
        $container = new Container();
        $container->remove('foo');
    }

    public function test_is_in_strict_mode_by_default() {
        $container = new Container();
        $this->assertTrue($container->isInStrictMode());
    }

    public function test_container_can_toggle_strict_mode() {
        $container = new Container(false);
        $this->assertFalse($container->isInStrictMode());
        $container->setStrictMode(true);
        $this->assertTrue($container->isInStrictMode());
    }
}

<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Weew\Container\Definitions\ValueDefinition;

class ValueDefinitionTest extends PHPUnit_Framework_TestCase {
    public function test_getters_and_setters() {
        $definition = new ValueDefinition('foo', 'bar');
        $this->assertEquals('foo', $definition->getId());
        $this->assertEquals('bar', $definition->getValue());
        $this->assertFalse($definition->isSingleton());
        $definition->setId('bar');
        $this->assertEquals('bar', $definition->getId());
        $definition->setValue('foo');
        $this->assertEquals('foo', $definition->getValue());
        $definition->singleton();
        $this->assertTrue($definition->isSingleton());
    }
}

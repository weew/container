<?php

namespace Tests\Weew\Container\Stubs;

class ComplexClass {
    public $foo;

    public function __construct(SimpleClass $instance, $foo) {
        $this->foo = $foo;
    }
}

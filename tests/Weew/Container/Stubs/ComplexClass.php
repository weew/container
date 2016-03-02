<?php

namespace Tests\Weew\Container\Stubs;

class ComplexClass {
    public $simple;
    public $foo;

    public function __construct(SimpleClass $simple, $foo) {
        $this->simple = $simple;
        $this->foo = $foo;
    }
}

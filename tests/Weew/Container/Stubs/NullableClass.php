<?php

namespace Tests\Weew\Container\Stubs;

class NullableClass {
    public $interface;
    public $implementation;
    public $foo;

    public function __construct(InterfaceClass $interface = null, IImplementation $implementation = null, $foo = 1) {
        $this->interface = $interface;
        $this->implementation = $implementation;
        $this->foo = $foo;
    }
}

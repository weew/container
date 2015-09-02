<?php

namespace Tests\Weew\Container\Stubs;

class InterfaceConstructor {
    public $implementation;

    public function __construct(IImplementation $implementation) {
        $this->implementation = $implementation;
    }
}

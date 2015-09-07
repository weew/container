<?php

namespace Tests\Weew\Container\Stubs;

class NullableInterfaceConstructor {
    public $implementation;

    public function __construct(IImplementation $implementation = null) {
        $this->implementation = $implementation;
    }
}

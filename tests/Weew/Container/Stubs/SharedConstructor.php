<?php

namespace Tests\Weew\Container\Stubs;

class SharedConstructor {
    public $x;
    public $shared;

    public function __construct($x, SharedClass $shared) {
        $this->x = $x;
        $this->shared = $shared;
    }
}

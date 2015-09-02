<?php

namespace Tests\Weew\Container\Stubs;

class SimpleConstructor {
    public $y;
    public $x;
    public $z;

    public function __construct($x, $y = 2, $z) {
        $this->y = $y;
        $this->x = $x;
        $this->z = $z;
    }
}

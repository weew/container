<?php

namespace Tests\Weew\Container\Stubs;

class ComplexConstructor {
    public $a;
    public $b;
    public $c;

    public function __construct($a, NoConstructor $b, $c = 1) {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}

<?php

namespace Tests\Weew\Container\Stubs;

class MethodClass {
    private $z = 5;
    private static $w = 5;

    public function foo($y, SharedClass $shared) {
        return $shared->x + $y + $this->z;
    }

    public static function bar($y = 2, SharedClass $shared) {
        return $shared->x + $y + self::$w;
    }
}

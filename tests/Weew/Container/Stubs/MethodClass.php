<?php

namespace Tests\Weew\Container\Stubs;

class MethodClass {
    public function method(array $array) {
        return array_pop($array);
    }

    public function complexMethod(SimpleClass $instance, array $array) {
        return array_pop($array);
    }

    public function complexNullableMethod(InterfaceClass $instance = null, array $array, $foo = 1) {
        return $foo;
    }

    public static function staticMethod(array $array) {
        return array_pop($array);
    }

    public static function complexStaticMethod(SimpleClass $instance, array $array) {
        return array_pop($array);
    }

    public static function complexNullableStaticMethod(InterfaceClass $instance = null, array $array, $foo = 1) {
        return $foo;
    }
}

<?php

namespace Tests\Weew\Container;

use PHPUnit_Framework_TestCase;
use Weew\Container\Exceptions\MissingArgumentException;

class MissingArgumentExceptionTest extends PHPUnit_Framework_TestCase {
    public function test_getters_and_setters() {
        $ex = new MissingArgumentException();
        $ex->setArgumentIndex(1);
        $this->assertEquals(1, $ex->getArgumentIndex());
        $ex->setArgumentName('foo');
        $this->assertEquals('foo', $ex->getArgumentName());
        $ex->setArgumentType('bar');
        $this->assertEquals('bar', $ex->getArgumentType());
        $ex->setReceivedArgumentType('yolo');
        $this->assertEquals('yolo', $ex->getReceivedArgumentType());
        $ex->setClassName('cls');
        $this->assertEquals('cls', $ex->getClassName());
        $ex->setFunctionName('fnc');
        $this->assertEquals('fnc', $ex->getFunctionName());
        $ex->setMethodName('mth');
        $this->assertEquals('mth', $ex->getMethodName());
    }
}

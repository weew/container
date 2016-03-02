<?php

namespace Tests\Weew\Container\Exceptions;

use PHPUnit_Framework_TestCase;
use Weew\Container\Exceptions\UnresolveableArgumentException;

class UnresolveableArgumentExceptionTest extends PHPUnit_Framework_TestCase {
    public function test_getters_and_setters() {
        $ex = new UnresolveableArgumentException();
        $ex->setArgumentIndex(1);
        $this->assertEquals(1, $ex->getArgumentIndex());
        $ex->setArgumentName('foo');
        $this->assertEquals('foo', $ex->getArgumentName());
        $ex->setClassName('cls');
        $this->assertEquals('cls', $ex->getClassName());
        $ex->setFunctionName('fnc');
        $this->assertEquals('fnc', $ex->getFunctionName());
        $ex->setMethodName('mth');
        $this->assertEquals('mth', $ex->getMethodName());
    }

    public function test_message_gets_built() {
        $ex = new UnresolveableArgumentException();
        $ex->setClassName('class');
        $ex->setMethodName('method');
        $ex->setArgumentName('arg');
        $ex->setArgumentIndex(3);
        $this->assertEquals(
            'Container could not resolve argument 3 for class::method.',
            $ex->getMessage()
        );
    }
}

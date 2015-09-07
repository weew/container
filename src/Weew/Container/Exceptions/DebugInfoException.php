<?php

namespace Weew\Container\Exceptions;

use Exception;

abstract class DebugInfoException extends Exception {
    /**
     * @var int
     */
    protected $argumentIndex;

    /**
     * @var string
     */
    protected $argumentName;

    /**
     * @var string
     */
    protected $argumentType;

    /**
     * @var string
     */
    protected $receivedArgumentType;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var string
     */
    protected $functionName;

    /**
     * @return int
     */
    public function getArgumentIndex() {
        return $this->argumentIndex;
    }

    /**
     * @param int $argumentIndex
     */
    public function setArgumentIndex($argumentIndex) {
        $this->argumentIndex = $argumentIndex;
        $this->buildMessage();
    }

    /**
     * @return string
     */
    public function getArgumentName() {
        return $this->argumentName;
    }

    /**
     * @param string $argumentName
     */
    public function setArgumentName($argumentName) {
        $this->argumentName = $argumentName;
        $this->buildMessage();
    }

    /**
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className) {
        $this->className = $className;
        $this->buildMessage();
    }

    /**
     * @return string
     */
    public function getMethodName() {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName($methodName) {
        $this->methodName = $methodName;
        $this->buildMessage();
    }

    /**
     * @return string
     */
    public function getFunctionName() {
        return $this->functionName;
    }

    /**
     * @param string $functionName
     */
    public function setFunctionName($functionName) {
        $this->functionName = $functionName;
        $this->buildMessage();
    }

    /**
     * Build exception message.
     */
    abstract protected function buildMessage();
}

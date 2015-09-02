<?php

namespace Weew\Container\Exceptions;

use Exception;

class ClassNotFoundException extends Exception {
    protected $className;

    /**
     * @param string $className
     */
    public function __construct($className) {
        parent::__construct();

        $this->setClassName($className);
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
     * Build exception message.
     */
    public function buildMessage() {
        $this->message = sprintf('Class not found %s', $this->getClassName());
    }
}

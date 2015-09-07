<?php

namespace Weew\Container\Exceptions;

use Exception;

class InterfaceIsNotInstantiableException extends Exception {
    /**
     * @var string
     */
    protected $interface;

    /**
     * @param string $interface
     */
    public function __construct($interface) {
        parent::__construct();

        $this->setInterface($interface);
    }

    /**
     * @return string
     */
    public function getInterface() {
        return $this->interface;
    }

    /**
     * @param string $interface
     */
    public function setInterface($interface) {
        $this->interface = $interface;
        $this->buildMessage();
    }

    /**
     * Build exception message.
     */
    protected function buildMessage() {
        $this->message = s(
            'Can not instantiate interface %s.', $this->getInterface()
        );
    }
}

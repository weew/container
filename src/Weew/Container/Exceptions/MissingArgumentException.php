<?php

namespace Weew\Container\Exceptions;

class MissingArgumentException extends DebugInfoException {
    /**
     * Build exception message.
     */
    protected function buildMessage() {
        $className = $this->getClassName();
        $methodName = $this->getMethodName();
        $functionName = $this->getFunctionName();

        $callable = $callable = $className . '::' . $methodName;;

        if ($functionName !== null) {
            $callable = $functionName;
        }

        $this->message = sprintf(
            'Missing argument %s for %s',
            $this->getArgumentIndex(),
            $callable
        );
    }
}

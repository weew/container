<?php

namespace Weew\Container\Exceptions;

class UnresolveableArgumentException extends DebugInfoException {
    /**
     * Build exception message.
     */
    protected function buildMessage() {
        $className = $this->getClassName();
        $methodName = $this->getMethodName();
        $functionName = $this->getFunctionName();

        if ($className !== null && $methodName !== null) {
            $callable = $className . '::' . $methodName;;
        } else {
            $callable = $functionName;
        }

        $this->message = s(
            'Container could not resolve argument %s for %s.',
            $this->getArgumentIndex(),
            $callable
        );
    }
}

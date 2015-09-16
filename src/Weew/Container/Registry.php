<?php

namespace Weew\Container;

use Weew\Container\Definitions\ClassDefinition;
use Weew\Container\Definitions\InterfaceDefinition;
use Weew\Container\Definitions\ValueDefinition;
use Weew\Container\Definitions\WildcardDefinition;

class Registry {
    /**
     * @var IDefinition[]
     */
    protected $definitions = [];

    /**
     * @param $id
     * @param null $value
     *
     * @return IDefinition
     */
    public function createDefinition($id, $value = null) {
        $args = func_get_args();

        if (count($args) > 2) {
            array_shift($args);
            $value = $args;
        }

        if ($value === null) {
            $value = $id;

            if (is_object($id)) {
                $id = get_class($id);
            }
        }

        if ($this->isRegexPattern($id)) {
            $definition = new WildcardDefinition($id, $value);
        } else if (class_exists($id)) {
            $definition = new ClassDefinition($id, $value);
        } else if (interface_exists($id)) {
            $definition = new InterfaceDefinition($id, $value);
        } else {
            $definition = new ValueDefinition($id, $value);
        }

        $this->addDefinition($definition);

        return $definition;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function has($id) {
        return $this->getDefinition($id) !== null;
    }

    /**
     * @param $id
     */
    public function remove($id) {
        $index = $this->getDefinitionIndex($id);

        if ($index !== null) {
            $definition = $this->definitions[$index];

            if ( ! $definition instanceof WildcardDefinition) {
                array_remove($this->definitions, $index);
            }
        }
    }


    /**
     * @param $id
     *
     * @return IDefinition
     */
    public function getDefinition($id) {
        foreach ($this->definitions as $definition) {
            if ($definition instanceof WildcardDefinition) {
                if ($this->matchRegexPattern($id, $definition->getId())) {
                    return $definition;
                }
            } else if ($definition->getId() == $id) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @param $id
     *
     * @return int|null|string
     */
    public function getDefinitionIndex($id) {
        $definition = $this->getDefinition($id);

        foreach ($this->definitions as $index => $item) {
            if ($item === $definition) {
                return $index;
            }
        }
    }

    /**
     * @param IDefinition $definition
     */
    public function addDefinition(IDefinition $definition) {
        $this->remove($definition->getId());
        array_unshift($this->definitions, $definition);
    }

    /**
     * @param $string
     *
     * @return bool
     */
    protected function isRegexPattern($string) {
        return str_starts_with($string, '/') &&
            str_ends_with($string, '/') ||
            str_starts_with($string, '#') &&
            str_ends_with($string, '#');
    }

    /**
     * @param $string
     * @param $pattern
     *
     * @return bool
     */
    protected function matchRegexPattern($string, $pattern) {
        return preg_match($pattern, $string) == 1;
    }
}

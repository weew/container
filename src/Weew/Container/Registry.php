<?php

namespace Weew\Container;

use Weew\Container\Definitions\AliasDefinition;
use Weew\Container\Definitions\ClassDefinition;
use Weew\Container\Definitions\InterfaceDefinition;
use Weew\Container\Definitions\ValueDefinition;
use Weew\Container\Definitions\WildcardDefinition;
use Weew\Container\Exceptions\MissingDefinitionIdentifierException;
use Weew\Container\Exceptions\MissingDefinitionValueException;

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
        list($id, $value) = $this->getIdAndValueFromCreateDefinitionArgs(
            func_get_args(), $id, $value
        );

        $definition = $this->delegateDefinitionCreation($id, $value);
        $this->addDefinition($definition);

        return $definition;
    }

    /**
     * @param array $args
     * @param $id
     * @param $value
     *
     * @return array
     */
    protected function getIdAndValueFromCreateDefinitionArgs(array $args, $id, $value) {
        if (count($args) > 2) {
            array_shift($args);
            $value = $args;
        }

        if ( ! is_array($id) && $value === null) {
            $value = $id;

            if (is_object($id)) {
                $id = get_class($id);
            }
        }

        return [$id, $value];
    }

    /**
     * @param $id
     * @param $value
     *
     * @return IDefinition
     * @throws MissingDefinitionIdentifierException
     * @throws MissingDefinitionValueException
     */
    protected function delegateDefinitionCreation($id, $value) {
        if (is_array($id)) {
            $definition = $this->createDefinitionWithAliases($id, $value);
        } else if ($this->isRegexPattern($id)) {
            $definition = new WildcardDefinition($id, $value);
        } else if (class_exists($id)) {
            $definition = new ClassDefinition($id, $value);
        } else if (interface_exists($id)) {
            $definition = new InterfaceDefinition($id, $value);
        } else {
            $definition = new ValueDefinition($id, $value);
        }

        return $definition;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function hasDefinition($id) {
        return $this->getDefinition($id) !== null;
    }

    /**
     * @param $id
     */
    public function removeDefinition($id) {
        $index = $this->getDefinitionIndex($id);

        if ($index !== null) {
            $definition = $this->definitions[$index];

            if ($definition instanceof IDefinition &&
                ! $definition instanceof WildcardDefinition) {
                array_remove($this->definitions, $index);
            }
        }
    }

    /**
     * @param array $ids
     * @param $value
     *
     * @return null|IDefinition
     * @throws MissingDefinitionIdentifierException
     * @throws MissingDefinitionValueException
     */
    protected function createDefinitionWithAliases(array $ids, $value) {
        if ($value == null) {
            throw new MissingDefinitionValueException(
                s('Trying to register a class with alias without a value. Received %s.', json_encode($ids))
            );
        } else if (count($ids) == 0) {
            throw new MissingDefinitionIdentifierException(
                'Trying to create a definition without an identifier.'
            );
        }

        $definition = null;

        foreach ($ids as $id) {
            if ( ! $definition instanceof IDefinition) {
                $definition = $this->createDefinition($id, $value);
            } else {
                $alias = $this->createAliasDefinition($definition, $id);
                $definition->addAlias($alias);
                $this->addDefinition($alias);
            }
        }

        return $definition;
    }

    /**
     * @param IDefinition $definition
     * @param $id
     *
     * @return AliasDefinition
     */
    protected function createAliasDefinition(IDefinition $definition, $id) {
        return new AliasDefinition($id, $definition);
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
                if ($definition instanceof AliasDefinition) {
                    return $definition->getValue();
                }

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
        $this->removeDefinition($definition->getId());
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

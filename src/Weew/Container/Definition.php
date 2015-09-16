<?php

namespace Weew\Container;

abstract class Definition implements IDefinition {
    /**
     * @var string
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var bool
     */
    protected $isSingleton = false;

    /**
     * @var IDefinition[]
     */
    protected $aliases = [];

    /**
     * @param string $id
     * @param $value
     */
    public function __construct($id, $value) {
        $this->id = $id;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return IDefinition[]
     */
    public function getAliases() {
        return $this->aliases;
    }

    /**
     * @param IDefinition $alias
     */
    public function addAlias(IDefinition $alias) {
        $this->aliases[] = $alias;
    }

    /**
     * @return void
     */
    public function singleton() {
        $this->isSingleton = true;
    }

    /**
     * @return bool
     */
    public function isSingleton() {
        return $this->isSingleton;
    }
}

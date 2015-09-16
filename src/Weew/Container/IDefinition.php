<?php

namespace Weew\Container;

interface IDefinition {
    /**
     * @return string
     */
    function getId();

    /**
     * @param string $id
     */
    function setId($id);

    /**
     * @return mixed
     */
    function getValue();

    /**
     * @param $value
     */
    function setValue($value);

    /**
     * @return IDefinition[]
     */
    function getAliases();

    /**
     * @param IDefinition $alias
     */
    function addAlias(IDefinition $alias);

    /**
     * @return void
     */
    function singleton();

    /**
     * @return bool
     */
    function isSingleton();
}

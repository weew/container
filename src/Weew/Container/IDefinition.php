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
     * @return void
     */
    function singleton();

    /**
     * @return bool
     */
    function isSingleton();
}

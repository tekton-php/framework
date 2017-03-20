<?php namespace Tekton;

class AliasLoader extends \Illuminate\Foundation\AliasLoader {

    function isAlias($alias) {
        return (array_search($alias, $this->aliases) !== false) ? true : false;
    }

    function hasAlias($class) {
        return (isset($this->aliases[$class])) ? true : false;
    }

    static function instance(array $aliases = []) {
        return parent::getInstance();
    }
}

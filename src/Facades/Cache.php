<?php namespace Tekton\Facades;

class Cache extends \Dynamis\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}

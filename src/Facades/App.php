<?php namespace Tekton\Facades;

class App extends \Tekton\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'container';
    }
}

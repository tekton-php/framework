<?php namespace Tekton\Facades;

class Request extends \Dynamis\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}

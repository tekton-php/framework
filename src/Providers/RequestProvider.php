<?php namespace Tekton\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class RequestProvider extends ServiceProvider
{
    function provides()
    {
        return ['request'];
    }

    function register()
    {
        $this->app->instance('request', Request::capture());
    }
}

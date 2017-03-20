<?php namespace Tekton\Providers;

use Tekton\Support\ServiceProvider;
use Illuminate\Http\Request;

class RequestProvider extends ServiceProvider {

    function register() {
        $this->app->instance('request', Request::capture());
    }

    function boot() {

    }
}

<?php namespace Tekton\Bootstrap;

use Illuminate\Contracts\Container\Container;

class RegisterAppProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Container $app)
    {
        $providers = $app->make('config')->get('app.providers', []);

        foreach ($providers as $provider) {
            $app->register($provider);
        }
    }
}

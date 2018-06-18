<?php namespace Tekton\Bootstrap;

use Dynamis\Facade;
use Illuminate\Contracts\Foundation\Application;
use Tekton\AliasLoader;

class RegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        AliasLoader::getInstance($app['config']->get('app.aliases', []))->register();
    }
}

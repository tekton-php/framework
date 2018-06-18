<?php namespace Tekton\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\ProviderRepository;

class RegisterServices
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        // TODO remove dependency on illuminate filesystems
        (new ProviderRepository($app, new Filesystem, $app->path('cache.tekton').DS.'services.php'))
                        ->load($app->config['app.providers']);
    }

    // /**
    //  * Get the path to the cached services.php file.
    //  *
    //  * @return string
    //  */
    // public function getCachedServicesPath()
    // {
    //     return $app->path('cache').DS.'tekton'.DS.'services.php';
    // }
}

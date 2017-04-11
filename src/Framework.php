<?php namespace Tekton;

use \Tekton\Application;

class Framework {

    protected $container;

    use \Tekton\Support\Traits\LibraryWrapper;
    use \Tekton\Support\Traits\Singleton;

    function __construct() {
        if ( ! defined('TEKTON_VERSION'))
            define('TEKTON_VERSION', '1.0.0');
        if ( ! defined('TEKTON_DIR'))
            define('TEKTON_DIR', __DIR__);
        if ( ! defined('DS'))
            define('DS', DIRECTORY_SEPARATOR);

        if ( ! defined('DATE_ISO'))
            define('DATE_ISO', 'Y-m-d');
        if ( ! defined('DATETIME_ISO'))
            define('DATETIME_ISO', 'Y-m-d H:i:s');

        $this->container = $this->library = new Application();
        $this->container->instance('framework', $this);
    }

    function init($basePath = '') {
        if ( ! empty($basePath)) {
            $this->container->setBasePath($basePath);
        }

        $this->registerPaths();
        $this->registerUris();
        $this->bootstrap();
        $this->registerCore();
        $this->boot();
    }

    function registerPaths($paths = []) {
        $this->container->registerPath('config', $this->container->path().DS.'config');
        $this->container->registerPath('cache', $this->container->path().DS.'cache');
        $this->container->registerPath('storage', $this->container->path().DS.'storage');
        $this->container->registerPath($paths);
    }

    function registerUris($paths = []) {
        $this->container->registerUri('config', $this->container->uri().DS.'config');
        $this->container->registerUri('cache', $this->container->uri().DS.'cache');
        $this->container->registerUri('storage', $this->container->uri().DS.'storage');
        $this->container->registerUri($uris);
    }

    function registerCore($providers = []) {
        $core = [
            \Tekton\Providers\RequestProvider::class,
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Cache\CacheServiceProvider::class,
            \Illuminate\Events\EventServiceProvider::class,
        ];

        foreach ($core as $provider) {
            $this->container->register($provider);
        }

        if (is_array($providers)) {
            foreach ($providers as $provider) {
                $this->container->register($provider);
            }
        }
    }

    function bootstrap($bootstrappers = []) {
        $this->container->bootstrap([
            new \Tekton\Bootstrap\LoadConfiguration(),
            new \Tekton\Bootstrap\RegisterFacades(),
            new \Tekton\Bootstrap\RegisterAppProviders(),
        ]);

        if (is_array($bootstrappers)) {
            $this->container->bootstrap($bootstrappers);
        }
    }
}

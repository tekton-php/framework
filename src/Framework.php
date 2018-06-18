<?php namespace Tekton;

use Tekton\Application;
use Tekton\Support\Contracts\Singleton;
use Dotenv\Dotenv;

class Framework implements Singleton
{
    protected $container;
    protected $configs = [];
    protected $overrides = [
        'path' => [],
        'uri' => [],
    ];

    use \Tekton\Support\Traits\LibraryWrapper;
    use \Tekton\Support\Traits\Singleton;

    public function __construct()
    {
        // Tekton globals
        if ( ! defined('TEKTON_VERSION'))
            define('TEKTON_VERSION', '1.0.0');
        if ( ! defined('TEKTON_DIR'))
            define('TEKTON_DIR', __DIR__);

        // Register Application and Framework in Container
        $this->container = $this->library = new Application();
        $this->container->instance('framework', $this);

        // register event listener for clearing framework cache
        $this->container['events']->listen('framework: cache.clear', [$this, 'clearCache']);
    }

    public function loadEnv($dir)
    {
        if (file_exists("$dir/.env")) {
            $dotenv = new Dotenv($dir);
            $dotenv->load();
        }
    }

    public function init($basePath, $baseUri)
    {
        // Either set base path and uri
        $this->container->setBasePath($basePath);
        $this->container->setBaseUri($baseUri);
        $this->loadEnv($basePath);

        // Start initalization of base system
        $this->container['events']->fire('framework: init', [$this->container]);

        // Start up core components
        $this->registerPaths();
        $this->registerUris();
        $this->registerConfig();
        $this->bootstrap();
        $this->registerCore();

        // Framework ready (running event is to allow additional hooks)
        $this->container['events']->fire('framework: running', [$this->container]);
        $this->container['events']->fire('framework: ready', [$this->container]);

        // Boot Application
        $this->container['events']->fire('app: boot', [$this->container]);
        $this->container->boot();

        // All services are loaded
        $this->container['events']->fire('app: running', [$this->container]);
        $this->container['events']->fire('app: ready', [$this->container]);

        return $this;
    }

    public function registerConfig($paths = [])
    {
        // Fire event for this step of the framework init process
        $this->container['events']->fire('framework: config', [$this->container]);

        // Only add Tekton's default config path if no other has been provided
        if (empty($paths)) {
            $paths = [$this->container->path().DS.'config'];
        }

        if (! empty($this->config)) {
            $paths = array_merge($paths, $this->config);
        }

        // Register in Application
        $this->container->registerConfig($paths);

        return $this;
    }

    public function addConfig($file)
    {
        if (is_array($file)) {
            $this->config = array_merge($this->config, $file);
        }
        else {
            $this->config[] = $file;
        }
    }

    public function overrideConfig($files)
    {
        $this->config = $files;
    }

    public function registerPaths($paths = [])
    {
        // Fire event for this step of the framework init process
        $this->container['events']->fire('framework: paths', [$this->container]);

        // Required paths - merge with sub-frameworks' paths
        $paths = array_merge([
            'config'       => $this->container->path().DS.'config',
            'storage'      => $this->container->path().DS.'storage',
            'cache'        => $this->container->path().DS.'cache',
        ], $paths);

        // Process overrides and register in Application
        $this->container->registerPath(array_merge($paths, $this->overrides['path']));

        // Register tekton cache path (this needs to be done after sub-framework
        // conf has been merged in order to allow the cache path to be overridable)
        $this->container->registerPath('cache.tekton', ensure_dir_exists(get_path('cache').DS.'tekton'));

        return $this;
    }

    public function registerUris($uris = [])
    {
        // Fire event for this step of the framework init process
        $this->container['events']->fire('framework: uris', [$this->container]);

        // Required URIs - merge with sub-frameworks' URIs
        $uris = array_merge([
            'cache'        => $this->container->uri().'/cache',
            'storage'      => $this->container->uri().'/storage',
        ], $uris);

        // Process overrides and register in Application
        $this->container->registerUri(array_merge($uris, $this->overrides['uri']));

        // Register tekton cache uri (this needs to be done after sub-framework
        // conf has been merged in order to allow the cache uri to be overridable)
        $this->container->registerUri('cache.tekton', get_uri('cache').DS.'tekton');

        return $this;
    }

    public function registerCore($providers = [])
    {
        // Fire event for this step of the framework init process
        $this->container['events']->fire('framework: core', [$this->container]);

        // Register all core providers defined by sub-framework
        foreach ($providers as $provider) {
            $this->container->register($provider);
        }

        return $this;
    }

    public function bootstrap($bootstrappers = [])
    {
        // Fire event for this step of the framework init process
        $this->container['events']->fire('framework: bootstrap', [$this->container]);

        // Bootstrap the framework structure
        $this->container->bootstrapWith(array_merge([
            \Tekton\Bootstrap\LoadConfiguration::class,
            \Tekton\Bootstrap\RegisterFacades::class,
            \Tekton\Bootstrap\RegisterServices::class,
        ], $bootstrappers));

        return $this;
    }

    public function setEnvironment($env)
    {
        // Set a string as an environment name
        if (! defined('TEKTON_ENV')) {
            define('TEKTON_ENV', strtolower($env));
        }

        return $this;
    }

    public function getEnvironment()
    {
        // Check what environment the framework is running under
        return (! defined('TEKTON_ENV')) ? TEKTON_ENV : null;
    }

    public function overridePath($key, $path)
    {
        // Allow user overrides of framework paths
        if (is_array($key)) {
            $this->overrides['path'] = array_merge($this->overrides['path'], $key);
        }
        else {
            $this->overrides['path'][$key] = $path;
        }

        return $this;
    }

    public function overrideUri($key, $uri)
    {
        // Allow user overrides of framework uris
        if (is_array($key)) {
            $this->overrides['uri'] = array_merge($this->overrides['uri'], $key);
        }
        else {
            $this->overrides['uri'][$key] = $uri;
        }

        return $this;
    }

    public function clearCache()
    {
        // Clear the framework's file cache (Database/Memcache cache are not
        // included in Tekton out of the box and is therefore not handled here)
        delete_dir_contents($this->container->path('cache'));

        return $this;
    }
}

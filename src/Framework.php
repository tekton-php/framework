<?php namespace Tekton;

use Exception;
use Symfony\Component\Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

use Tekton\Facade;
use Tekton\Config;
use Tekton\ResourceManager;
use Tekton\Support\Contracts\Store;
use Tekton\Support\Contracts\Singleton;
use Tekton\Contracts\ServiceProviderInterface;
use Tekton\Contracts\ResourceManagerInterface;

class Framework implements Singleton
{
    protected $container;
    protected $resources;
    protected $config;
    protected $dotenv;

    protected $cacheDir;
    protected $init = false;
    protected $cache = false;
    protected $env = null;
    protected $configs = [];
    protected $urls = [];
    protected $paths = [];
    protected $providers = [];
    protected $loadedProviders = [];
    protected $aliases = [];
    protected $facadeNamespace = '';
    protected $facadeDir = '';

    use \Tekton\Support\Traits\Singleton;

    public function __construct()
    {
        // Tekton globals
        if (! defined('TEKTON_VERSION'))
            define('TEKTON_VERSION', '3.0.0');
        if (! defined('TEKTON_DIR'))
            define('TEKTON_DIR', __DIR__);
    }

    public function init($basePath, $baseUrl = '')
    {
        if ($this->init) {
            throw new Exception("You cannot re-init the framework");
        }
        if ($this->cache && ! $this->cacheDir) {
            throw new Exception("You must configure a cache directory");
        }
        if (! $this->container) {
            throw new Exception("A PSR-11 container must be set before initializing");
        }

        // Load environment
        if (! $this->dotenv) {
            $this->loadEnv($basePath);
        }

        // Load config
        if (! $this->config) {
            $this->loadConfig();
        }

        // Create resource URIs manager
        if (! $this->resources) {
            $this->setResourceManager(new ResourceManager($basePath, $baseUrl));

            // Register resource URIs
            $this->resources->setPath($this->paths);
            $this->resources->setUrl($this->urls);
        }

        // Register alias autoload
        spl_autoload_register([$this, 'aliasLoader'], true, true);

        // Register providers
        $this->loadProviders();
        $this->init = true;

        return $this;
    }

    public function loadProviders()
    {
        if (! $this->container) {
            throw new Exception("A PSR-11 container must be set before loading providers");
        }

        $unloaded = array_diff_key($this->providers, $this->loadedProviders);
        $unloaded = array_intersect_key($this->providers, $unloaded);
        $loading = [];

        // Run register method
        foreach ($unloaded as $class => $provider) {
            if (! $provider instanceof ServiceProviderInterface) {
                $provider = new $provider;
            }

            if ($provider instanceof ServiceProviderInterface) {
                $provider->register($this->container);
                $loading[$class] = $provider;
            }
        }

        // Run boot method
        foreach ($loading as $class => $provider) {
            $provider->boot($this->container);
            $this->loadedProviders[$class] = $provider;
        }
    }

    public function aliasLoader($alias)
    {
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }

        if ($this->facadeNamespace) {
            $class = $this->facadeNamespace.'\\'.$alias;
            $path = $this->facadeDir.DS.str_replace('\\', DS, $alias).'.php';

            if (file_exists($path)) {
                return class_alias($class, $alias);
            }
        }

        return false;
    }

    public function setFacadeNamespace($namespace, $dir)
    {
        $this->facadeNamespace = $namespace;
        $this->facadeDir = $dir;
        return $this;
    }

    public function setResourceCaching($state, $dir = '')
    {
        $this->cache = (bool) $state;

        if ($dir) {
            $this->setCacheDir($dir);
        }

        return $this;
    }

    public function getResourceCaching()
    {
        return $this->cache;
    }

    public function setCacheDir($dir)
    {
        $this->cacheDir = ensure_dir_exists($dir);
        return $this;
    }

    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    public function loadEnv($path)
    {
        if (is_dir($path)) {
            $path = $path.DS.'.env';
        }

        if (file_exists($path)) {
            if (! $this->dotenv) {
                $this->dotenv = new Dotenv;
            }

            $this->dotenv->load($path);
        }

        return $this;
    }

    public function setContainer(ContainerInterface $container)
    {
        $container->set(ContainerInterface::class, $container);
        $container->set('app', $container);
        $this->container = $container;

        Facade::clearResolvedInstances();
        Facade::setFacadeContainer($container);

        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setResourceManager(ResourceManagerInterface $resources)
    {
        $this->container->set(ResourceManagerInterface::class, $resources);
        $this->container->set('resources', $resources);
        $this->resources = $resources;

        return $this;
    }

    public function getResourceManager()
    {
        return $this->resources;
    }

    public function setConfig(Store $config)
    {
        $this->container->set(Config::class, $config);
        $this->container->set('config', $config);
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function registerPath($name, $path = '')
    {
        if (is_array($name)) {
            $this->paths = array_merge($this->paths, $name);
        }
        else {
            $this->paths[$name] = $path;
        }

        if ($this->init) {
            $this->resources->setPath($name, $path);
        }

        return $this;
    }

    public function registerUrl($name, $url = '')
    {
        if (is_array($name)) {
            $this->urls = array_merge($this->urls, $name);
        }
        else {
            $this->urls[$name] = $url;
        }

        if ($this->init) {
            $this->resources->setUrl($name, $url);
        }

        return $this;
    }

    public function registerProvider($class)
    {
        if (is_array($class)) {
            foreach ($class as $key => $provider) {
                $this->registerProvider($provider);
            }
        }
        else {
            $key = (! is_string($class)) ? get_class($class) : $class;
            $this->providers[$key] = $class;
        }

        if ($this->init) {
            $this->loadProviders();
        }

        return $this;
    }

    public function registerAlias($alias, $class)
    {
        if (is_array($alias)) {
            $this->aliases = array_merge($this->aliases, $alias);
        }
        else {
            $this->aliases[$alias] = $class;
        }

        return $this;
    }

    public function registerConfig($file)
    {
        if (is_array($file)) {
            $this->configs = array_merge($this->configs, $file);
        }
        else {
            $this->configs[] = $file;
        }

        if ($this->init) {
            $this->loadConfig(true);
        }

        return $this;
    }

    public function setEnvironment($env)
    {
        $this->env = $env;
        return $this;
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    public function clearCache()
    {
        // Clear the framework's file cache (Database/Memcache cache are not
        // included in Tekton out of the box and is therefore not handled here)
        if (is_dir($this->cacheDir)) {
            delete_dir_contents($this->cacheDir);
        }

        return $this;
    }

    public function loadConfig($force = false)
    {
        if (! $this->container) {
            throw new Exception("A PSR-11 container must be set before loading the config");
        }
        if (! $this->config) {
            $this->setConfig(new Config);
        }

        $cachePath = $this->cacheDir.DS.'config.php';

        // If a caching and cache file exist, load it instead
        if (! $force && $this->cache && file_exists($cachePath)) {
            $this->config->replace(require $cachePath);
        }
        else {
            foreach ($this->configs as $path) {
                $path = realpath($path);

                // Make sure that the config file/dir exists
                if (! file_exists($path)) {
                    throw new ErrorException("Config path doesn't exist: ".$path);
                }

                // Process config directory
                if (is_dir($path)) {
                    // Load all config files
                    foreach (file_search($path, '/^.*\.php$/i') as $file) {
                        // Create a config key for the file
                        $relPath = rel_path($file, $path);
                        $configKey = str_replace(DS, '.', basename($relPath, '.php'));

                        // Load file
                        $this->config->set($configKey, require $file);
                    }
                }
                // Process single file
                else {
                    $this->config->set(basename($path, '.php'), require $path);
                }
            }

            // Cache the config file
            if ($this->cache) {
                write_object_to_file($cachePath, $this->config->all());
            }
        }

        return $this;
    }
}

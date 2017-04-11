<?php namespace Tekton;

use \Illuminate\Container\Container;

use \Illuminate\Support\Arr;
use \Illuminate\Support\Str;

use \Tekton\Bootstrapper;
use \Tekton\AliasLoader;
use \Illuminate\Support\ServiceProvider;

// Exceptions
use UnexpectedValueException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use InvalidArgumentException;

class Application extends Container
{
    protected $paths = [];
    protected $uris = [];
    protected $loadedProviders = [];
    protected $serviceProviders = [];
    protected $terminatingCallbacks = [];
    protected $hasBeenBootstrapped = false;
    protected $booted = false;

    public function __construct($basePath = null, $baseUri = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }
        if ($baseUri) {
            $this->setBaseUri($baseUri);
        }

        if ( ! defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->registerApplication();
    }

    public function registerApplication()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
    }

    public function register($provider, $options = [], $force = false)
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return \Illuminate\Support\ServiceProvider|null
     */
    public function getProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::first($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param  string  $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered.
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;

        $this->loadedProviders[get_class($provider)] = true;
    }

    public function bootstrap(array $bootstrappers) {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $bootstrapper->bootstrap($this);
            // $this->make($bootstrapper)->bootstrap($this);
        }
    }

    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    public function make($abstract)
    {
        $abstract = $this->getAlias($abstract);

        return parent::make($abstract);
    }

    // public function bound($abstract)
    // {
    //     return isset($this->deferredServices[$abstract]) || parent::bound($abstract);
    // }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return mixed
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    public function setBasePath($basePath)
    {
        $basePath = rtrim($basePath, '\/');
        $this->instance('path', $basePath);
        $this->registerPath('base', $basePath);
    }

    public function path($key = null)
    {
        $key = $key ?? 'base';

        if ( ! isset($this->paths[$key])) {
            throw new InvalidArgumentException('"'.$key.'" is not a registered application path');
        }

        return $this->paths[$key];
    }

    public function registerPath($key, $value = null) {
        $pairs = ( ! is_array($key)) ? [$key => $value] : $key;

        foreach ($pairs as $key => $val) {
            $this->paths[$key] = $path = rtrim($val, '\/');
            $this->instance('path.'.$key, $path);
        }
    }

    public function setBaseUri($baseUri)
    {
        $baseUri = rtrim($baseUri, '\/');
        $this->instance('uri', $baseUri);
        $this->registerUri('base', $baseUri);
    }

    public function uri($key = null)
    {
        $key = $key ?? 'base';

        if ( ! isset($this->uris[$key])) {
            throw new InvalidArgumentException('"'.$key.'" is not a registered application uri');
        }

        return $this->uris[$key];
    }

    public function registerUri($key, $value = null) {
        $pairs = ( ! is_array($key)) ? [$key => $value] : $key;

        foreach ($pairs as $key => $val) {
            $this->uris[$key] = $uri = rtrim($val, '\/');
            $this->instance('uri.'.$key, $uri);
        }
    }

    public function runningInConsole()
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

    public function flush()
    {
        parent::flush();

        $this->loadedProviders = [];
    }

    public function abort($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Register a terminating callback with the application.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function terminating(Closure $callback)
    {
        $this->terminatingCallbacks[] = $callback;

        return $this;
    }

    /**
     * Terminate the application.
     *
     * @return void
     */
    public function terminate()
    {
        foreach ($this->terminatingCallbacks as $terminating) {
            $this->call($terminating);
        }
    }

    /**
     * Get the service providers that have been loaded.
     *
     * @return array
     */
    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    /**
     * Configure the real-time facade namespace.
     *
     * @param  string  $namespace
     * @return void
     */
    public function provideFacades($namespace)
    {
        AliasLoader::setFacadeNamespace($namespace);
    }

    public function configurationIsCached()
    {
        return file_exists($this->getCachedConfigPath());
    }

    /**
     * Get the path to the configuration cache file.
     *
     * @return string
     */
    public function getCachedConfigPath()
    {
        return $this->path('cache').DS.'config.php';
    }

}

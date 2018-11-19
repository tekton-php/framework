Tekton Framework
================

Tekton is a lightweight PHP micro-framework, designed to integrate as a shim in older codebases to enable modern design patterns. `tekton/framework` is the core of sub-frameworks that provides config loading, DI container, environment loading, service providers, facades and class aliases.

The reason it was created was to allow integrating the power and ease of use of modern design patterns when working in more limited environments. A good example is the [Dynamis](https://github.com/dynamis-wp/framework) project that does exactly that by enabling this within a WordPress environment.

## Installation

```sh
composer require tekton/framework
```

## Usage

To get started, just require the project in your composer configuration, provide a PSR-11 container and initialize the framework.

```php
require_once 'vendor/autoload.php';

use Tekton\Framework;
use DI\Container;

$framework = Framework::getInstance();
$framework->setContainer(new Container)
          ->setEnvironment('development')
          ->setResourceCaching(true)
          ->setCacheDir(__DIR__.'/cache')
          ->registerConfig(__DIR__.'/config');
          ->setFacadeNamespace('Project\\Facades', __DIR__.'/Facades')
          ->registerAlias('ProjectClass', 'Project\\Class');

$framework->registerProvider([
    \Project\Providers\ServiceOne::class,
    \Project\Providers\ServiceTwo::class,
    \Project\Providers\ServiceThree::class,
]);

$framework->init(__DIR__, 'http://localhost:8000/');
```

There are several helper functions to quickly retrieve config values, services or resource URIs. You can also initialize the framework in steps if you need to retrieve config values before registering providers or aliases.

```php
$framework = Framework::getInstance();
$framework->setContainer(new Container)
          ->registerConfig(__DIR__.'/config')
          ->loadEnv()
          ->loadConfig();

$framework->registerProvider(app('config')->get('app.providers'));
$framework->registerAlias(app('config')->get('app.aliases'));

$framework->init(__DIR__, 'http://localhost:8000/');
```

## License

MIT

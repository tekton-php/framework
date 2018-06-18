Tekton
======

Tekton is a lightweight PHP framework, designed to integrate well with existing Laravel Illuminate components. `tekton/framework` is the core that sets up the Service Container, the request and input handling, and the configuration loading.

The reason it was created was to allow integrating the power and ease of use of Laravel when working in more limited environments. A good example is the [Dynamis](https://github.com/dynamis-wp/framework) project that does exactly that.

To get started, just require the project in your composer configuration and initialize the framework.

**Sample Code**
```php
// Autoload classes
require_once __DIR__ . '/vendor/autoload.php';

$framework = \Tekton\Framework::instance();
$framework->init(__DIR__, 'http://localhost/');
```

By default all configuration files will be loaded from `[project]/config` but the framework is meant to be easily extended and you can either completely override the config or simply appending additional files or directories.

```php
$framework->overrideConfig('path/to/theme/config');
// OR
$framework->addConfig('path/to/theme/config');
```

Config is treated separately but all other framework paths and URIs can be overridden with `overridePath` and `overrideUri`.

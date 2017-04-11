Tekton
======

Tekton is a lightweight PHP framework, designed to integrate well with existing Laravel Illuminate components. Tekton Foundation is the core that sets up the Service Container, the request and input handling, and the configuration loading. It also registers [Illuminate/Events](https://packagist.org/packages/illuminate/events), [Illuminate/Filesystem](https://packagist.org/packages/illuminate/filesystem) and [Illuminate/Cache](https://packagist.org/packages/illuminate/cache).

The reason it was created was to allow integrating the power and ease of use of Laravel when working in more limited environments. A good example is the [Tekton/Wordpress](https://gitlab.com/tekton/wordpress) project that does exactly that.

To get started, just require the project in your composer configuration and initialize the framework.

**Sample Code**
```php
// Autoload classes
require_once __DIR__ . '/vendor/autoload.php';

\Tekton\Framework::instance()->init();
```

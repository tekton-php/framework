<?php namespace Tekton\Bootstrap;

use Tekton\Support\Repository;
use Illuminate\Contracts\Foundation\Application;
use Exception;

class LoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $configPath = get_path('configs');
        $cachePath = get_path('cache.tekton').DS.'config.php';
        $config = new Repository();
        $createCache = false;

        // If a cache file exist and we're in production, set it as configPath instead
        if (app_env('production') && file_exists($cachePath)) {
            $config->replace(require $cachePath);
        }
        else {
            // Support having multiple config locations
            if (! is_array($configPath)) {
                $configPath = [$configPath];
            }

            foreach ($configPath as $path) {
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
                        $config->set($configKey, require $file);
                    }
                }
                // Process single file
                else {
                    $config->set(basename($path, '.php'), require $path);
                }
            }

            // If we're in prod we need to cache the config file
            if (app_env('production')) {
                write_object_to_file($cachePath, $config->all());
            }
        }

        // Make sure the app config file exists
        if (! $config->has('app')) {
            throw new Exception('Unable to load the "app.php" configuration file.');
        }

        // Register config repository
        $app->instance('config', $config);
    }

}

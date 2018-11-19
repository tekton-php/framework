<?php

if (! function_exists('framework')) {
    function framework()
    {
        return \Tekton\Framework::getInstance();
    }
}

if (! function_exists('app')) {
    function app($abstract = null)
    {
        if (is_null($abstract)) {
            return framework()->getContainer();
        }

        return framework()->getContainer()->get($abstract);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (! function_exists('app_env')) {
    function app_env($test = '')
    {
        if (! empty($test)) {
            return (strtolower(app_env()) == $test) ? true : false;
        }
        else {
            return \Tekton\Framework::getInstance()->getEnvironment();
        }
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('resources')->getRootPath().($path ? DS.$path : $path);
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return app('resources')->get('config').($path ? DS.$path : $path);
    }
}

if (! function_exists('app_url')) {
    /**
     * Get the uri to the application folder.
     *
     * @param  string  $uri
     * @return string
     */
    function app_url($url = '')
    {
        return app('resources')->getRootUrl().($url ? DS.$url : $url);
    }
}

if (! function_exists('get_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function get_path($path)
    {
        return app('resources')->getPath($path);
    }
}

if (! function_exists('get_url')) {
    /**
     * Get the uri to the application folder.
     *
     * @param  string  $uri
     * @return string
     */
    function get_url($url = '')
    {
        return app('resources')->getUrl($url);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key, $default = null)
    {
        return app('config')->get($key, $default);
    }
}

<?php

if (! function_exists('framework')) {
    function framework()
    {
        return app('framework');
    }
}

if (! function_exists('app')) {

    /**
     * Get the available container instance.
     *
     * @param  string  $abstract
     * @return mixed|\Tekton\Application
     */
    function app($abstract = null)
    {
        if (is_null($abstract)) {
            return \Tekton\Application::getInstance();
        }

        return \Tekton\Application::getInstance()->make($abstract);
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
            return (defined('TEKTON_ENV')) ? TEKTON_ENV : null;
        }
    }
}

if (! function_exists('encrypt')) {
    function encrypt($value, $serialize = true)
    {
        return app('encrypter')->encrypt($value, $serialize);
    }
}

if (! function_exists('decrypt')) {
    function decrypt($payload, $unserialize = true)
    {
        return app('encrypter')->decrypt($payload, $unserialize);
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
        return app('path').($path ? DS.$path : $path);
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
        return app('path.config').($path ? DS.$path : $path);
    }
}

if (! function_exists('cwd_rel_path')) {
    // Convert an absolute path to a relative
    function cwd_rel_path($uri)
    {
        return rel_path($uri, get_path('cwd'));
    }
}

if (! function_exists('app_uri')) {
    /**
     * Get the uri to the application folder.
     *
     * @param  string  $uri
     * @return string
     */
    function app_uri($uri = '')
    {
        return app('uri').($uri ? DS.$uri : $uri);
    }
}

if (! function_exists('get_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function get_path($path = '')
    {
        return app('path'.(empty($path) ? '' : '.'.$path));
    }
}

if (! function_exists('cwd')) {
    /**
     * Get the uri to the application folder.
     *
     * @param  string  $uri
     * @return string
     */
    function cwd()
    {
        return (app()->hasPath('cwd')) ? app_path('cwd') : getcwd();
    }
}

if (! function_exists('get_uri')) {
    /**
     * Get the uri to the application folder.
     *
     * @param  string  $uri
     * @return string
     */
    function get_uri($uri = '')
    {
        return app('uri'.(empty($uri) ? '' : '.'.$uri));
    }
}




if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return app('path.storage').($path ? DS.$path : $path);
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
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @return mixed
     *
     * @throws \Exception
     */
    function cache()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            return app('cache');
        }

        if (is_string($arguments[0])) {
            return app('cache')->get($arguments[0], isset($arguments[1]) ? $arguments[1] : null);
        }

        if (is_array($arguments[0])) {
            if (! isset($arguments[1])) {
                throw new Exception(
                    'You must set an expiration time when putting to the cache.'
                );
            }

            return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
        }
    }
}

if (! function_exists('event')) {
    /**
     * Fire an event and call the listeners.
     *
     * @param  object|string  $event
     * @param  mixed   $payload
     * @param  bool    $halt
     * @return array|null
     */
    function event($event, $payload = [], $halt = false)
    {
        return app('events')->fire($event, $payload, $halt);
    }
}

if (! function_exists('user_ip')) {
    function user_ip()
    {
        return app('request')->ip();
    }
}

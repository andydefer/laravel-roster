<?php

if (! function_exists('config_path')) {
    /**
     * Get the path to the config directory.
     *
     * @param  string|null  $path  Optional subpath
     * @return string Full path to config directory or file
     */
    function config_path(?string $path = ''): string
    {
        return $path ? 'config/'.$path : 'config';
    }
}

if (! function_exists('database_path')) {
    /**
     * Get the path to the database directory.
     *
     * @param  string|null  $path  Optional subpath
     * @return string Full path to database directory or file
     */
    function database_path(?string $path = ''): string
    {
        return $path ? 'database/'.$path : 'database';
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the base path of the package.
     *
     * @param  string|null  $path  Optional subpath
     * @return string Full base path of the package
     */
    function base_path(?string $path = ''): string
    {
        $base = __DIR__.'/../';

        return $path ? $base.$path : $base;
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources directory.
     *
     * @param  string|null  $path  Optional subpath
     * @return string Full path to resources directory or file
     */
    function resource_path(?string $path = ''): string
    {
        $base = __DIR__.'/../resources';

        return $path ? $base.'/'.$path : $base;
    }
}

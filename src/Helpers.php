<?php

// Package helpers.php (optional, mainly for IDE support)

if (! function_exists('config_path')) {
    /**
     * Get the path to the config directory.
     */
    function config_path(?string $path = ''): string
    {
        return $path ? 'config/' . $path : 'config';
    }
}

if (! function_exists('database_path')) {
    /**
     * Get the path to the database directory.
     */
    function database_path(?string $path = ''): string
    {
        return $path ? 'database/' . $path : 'database';
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the base path of the package.
     */
    function base_path(?string $path = ''): string
    {
        return $path ? __DIR__ . '/../' . $path : __DIR__ . '/../';
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources directory.
     */
    function resource_path(?string $path = ''): string
    {
        // __DIR__ is the current directory; we move up to the package root using '/../'
        $base = __DIR__ . '/../resources';

        return $path ? $base . '/' . $path : $base;
    }
}

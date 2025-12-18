<?php

// helpers.php du package (optionnel, juste pour l'éditeur)

if (! function_exists('config_path')) {
    function config_path(?string $path = ''): string
    {
        return $path ? 'config/'.$path : 'config';
    }
}

if (! function_exists('database_path')) {
    function database_path(?string $path = ''): string
    {
        return $path ? 'database/'.$path : 'database';
    }
}

if (! function_exists('base_path')) {
    function base_path(?string $path = ''): string
    {
        return $path ? __DIR__.'/../'.$path : __DIR__.'/../';
    }
}

if (! function_exists('resource_path')) {
    /**
     * Obtenir le chemin vers le dossier resources
     */
    function resource_path(?string $path = ''): string
    {
        // __DIR__ est le dossier actuel, on remonte à la racine du package avec '/../'
        $base = __DIR__.'/../resources';

        return $path ? $base.'/'.$path : $base;
    }
}

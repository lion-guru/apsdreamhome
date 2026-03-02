<?php

/**
 * APS Dream Home - Fix database_path() Function
 * Defines missing helper function
 */

if (!function_exists('database_path')) {
    /**
     * Get the database path
     * @param string $path
     * @return string
     */
    function database_path($path = '')
    {
        $basePath = defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/..';
        $databasePath = $basePath . '/database';
        
        return $path ? $databasePath . '/' . ltrim($path, '/') : $databasePath;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the base path
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        $basePath = defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/..';
        
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the config path
     * @param string $path
     * @return string
     */
    function config_path($path = '')
    {
        $basePath = defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/..';
        $configPath = $basePath . '/config';
        
        return $path ? $configPath . '/' . ltrim($path, '/') : $configPath;
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the app path
     * @param string $path
     * @return string
     */
    function app_path($path = '')
    {
        $basePath = defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/..';
        $appPath = $basePath . '/app';
        
        return $path ? $appPath . '/' . ltrim($path, '/') : $appPath;
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the public path
     * @param string $path
     * @return string
     */
    function public_path($path = '')
    {
        $basePath = defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/..';
        $publicPath = $basePath . '/public';
        
        return $path ? $publicPath . '/' . ltrim($path, '/') : $publicPath;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the storage path
     * @param string $path
     * @return string
     */
    function storage_path($path = '')
    {
        $basePath = defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/..';
        $storagePath = $basePath . '/storage';
        
        return $path ? $storagePath . '/' . ltrim($path, '/') : $storagePath;
    }
}

?>

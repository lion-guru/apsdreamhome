<?php

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config($key = null)
    {
        if (is_null($key)) {
            return \App\Core\App::getInstance();
        }
        return \App\Core\App::getInstance()->config($key);
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return dirname(__DIR__, 2) . ($path ? '/' . $path : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return dirname(__DIR__, 2) . '/storage/' . $path;
    }
}

if (!function_exists('database_path')) {
    function database_path($path = '')
    {
        return dirname(__DIR__, 2) . '/database/' . $path;
    }
}

if (!function_exists('str_slug')) {
    function str_slug($str, $separator = '-')
    {
        // Simple slug implementation if App\Helpers\Helpers doesn't exist or isn't loaded
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('/[^\p{L}\p{N}]+/u', $separator, $str);
        $str = trim($str, $separator);
        return $str;
    }
}

if (!function_exists('get_asset_url')) {
    function get_asset_url($path)
    {
        return BASE_URL . 'public/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('h')) {
    function h($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('view')) {
    function view($name, $data = [])
    {
        $app = \App\Core\App::getInstance();
        // Assuming App has a view method or we need to instantiate View
        // For now, let's use the View class directly if available or through App
        // But App doesn't expose view() directly usually.
        // Let's assume usage of View class
        return (new \App\Core\View\View())->render($name, $data);
    }
}

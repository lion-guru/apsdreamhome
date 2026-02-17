<?php

if (!function_exists('h')) {
    /**
     * Escape HTML entities
     *
     * @param string $string
     * @return string
     */
    function h($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     *
     * @param string $path
     * @return string
     */
    function url($path = '')
    {
        $base = defined('BASE_URL') ? BASE_URL : '/';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @return string
     */
    function asset($path)
    {
        return url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('get_asset_url')) {
    /**
     * Get asset URL (Legacy support)
     * Points to public/assets/ directory with type support
     * 
     * @param string $path
     * @param string $type Optional type (css, js, images, vendor)
     * @return string
     */
    function get_asset_url($path, $type = '')
    {
        $path = ltrim($path, '/');

        if (!empty($type)) {
            // Check if path already starts with type to avoid duplication
            // Example: get_asset_url('css/style.css', 'css') -> should be assets/css/style.css
            if (strpos($path, $type . '/') === 0) {
                return asset('assets/' . $path);
            }

            return asset('assets/' . $type . '/' . $path);
        }

        return asset('assets/' . $path);
    }
}

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
        return dirname(__DIR__) . ($path ? '/' . $path : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return dirname(__DIR__) . '/storage/' . $path;
    }
}

if (!function_exists('database_path')) {
    function database_path($path = '')
    {
        return dirname(__DIR__) . '/database/' . $path;
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

if (!function_exists('view')) {
    function view($name, $data = [])
    {
        // Try to resolve View class from Core or appropriate namespace
        if (class_exists('\App\Core\View\View')) {
            return (new \App\Core\View\View())->render($name, $data);
        } elseif (class_exists('\App\Core\View')) {
            return (new \App\Core\View())->render($name, $data);
        }
        // Fallback or throw error if needed, but for now safe fail
        return '';
    }
}

if (!function_exists('getCsrfField')) {
    /**
     * Generate CSRF field
     * 
     * @return string
     */
    function getCsrfField()
    {
        $token = $_SESSION['csrf_token'] ?? '';
        return '<input type="hidden" name="csrf_token" value="' . h($token) . '">';
    }
}

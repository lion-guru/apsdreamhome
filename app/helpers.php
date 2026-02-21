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
            return \App\Core\App::getInstance()->config();
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

if (!function_exists('logger')) {
    /**
     * Get the logger instance or log a message
     *
     * @param string|null $message
     * @param array $context
     * @return \App\Services\SystemLogger|void
     */
    function logger($message = null, array $context = [])
    {
        $logger = \App\Core\App::getInstance()->logger();

        if (is_null($message)) {
            return $logger;
        }

        $logger->info($message, $context);
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $title
     * @param  string  $separator
     * @return string
     */
    function str_slug($title, $separator = '-')
    {
        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
        // Replace @ with the separator
        $title = str_replace('@', $separator . 'at' . $separator, $title);
        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
        return trim($title, $separator);
    }
}

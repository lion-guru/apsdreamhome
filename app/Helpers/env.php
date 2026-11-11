<?php
/**
 * Environment Helper Functions
 * Provides env() and other helper functions for environment variable access
 */

if (!function_exists('env')) {
    /**
     * Get an environment variable value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value
     *
     * NOTE (S451): this used to walk $_ENV only, so a dotted key such as
     * 'app.name' / 'database.default' / 'seo.default_title' (which is how
     * every caller spells it - Core\SEOOptimizer, Models\Pivot) never
     * resolved and always fell through to the default. It now resolves
     * against App\Core\Config\Config, which loads config/*.php and supports
     * dotted keys, and keeps the $_ENV walk as a fallback.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        static $config = [];

        if (array_key_exists($key, $config)) {
            return $config[$key];
        }

        // 1. the real config files (config/app.php, config/database.php, ...)
        if (class_exists('\App\Core\Config\Config')) {
            try {
                $sentinel = "\0__missing__\0";
                $value = \App\Core\Config\Config::get($key, $sentinel);
                if ($value !== $sentinel) {
                    $config[$key] = $value;
                    return $value;
                }
            } catch (\Throwable $e) {
                // fall through to the $_ENV walk
            }
        }

        // 2. environment
        $keys = explode('.', $key);
        $value = $_ENV;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        $config[$key] = $value;
        return $value;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL
     * 
     * @param string $path
     * @return string
     */
    function asset(string $path): string
    {
        $baseUrl = env('APP_URL', 'http://localhost/apsdreamhome');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    /**
     * Generate a route URL
     * 
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function route(string $name, array $parameters = [], $absolute = true): string
    {
        $baseUrl = env('APP_URL', 'http://localhost/apsdreamhome');
        return rtrim($baseUrl, '/') . '/' . ltrim($name, '/');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token
     * 
     * @return string
     */
    function csrf_token(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Get the CSRF token field
     * 
     * @return string
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old(string $key, $default = null)
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    /**
     * Create a redirect response
     * 
     * @param string $url
     * @param int $status
     * @param array $headers
     * @return void
     */
    function redirect(string $url, int $status = 302, array $headers = []): void
    {
        header('Location: ' . $url, true, $status);
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to previous page
     * 
     * @return void
     */
    function back(): void
    {
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP status code
     * 
     * @param int $code
     * @param string $message
     * @return void
     */
    function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }
}

if (!function_exists('view')) {
    /**
     * Load a view file
     * 
     * @param string $view
     * @param array $data
     * @return void
     */
    function view(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../../views/' . $view . '.php';
    }
}

if (!function_exists('json_response')) {
    /**
     * Send JSON response
     * 
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return void
     */
    function json_response($data, int $status = 200, array $headers = []): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('request')) {
    /**
     * Get request input
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function request(string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);
        if ($key === null) {
            return $input;
        }
        return $input[$key] ?? $default;
    }
}

if (!function_exists('validate_csrf')) {
    /**
     * Validate CSRF token
     * 
     * @param string $token
     * @return bool
     */
    function validate_csrf(string $token = null): bool
    {
        $token = $token ?? ($_POST['csrf_token'] ?? '');
        return hash_equals(csrf_token(), $token);
    }
}

if (!function_exists('is_ajax')) {
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

if (!function_exists('is_json')) {
    /**
     * Check if request is JSON
     * 
     * @return bool
     */
    function is_json(): bool
    {
        return stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    }
}
<?php

use App\Core\App;
use App\Core\View;
use App\Core\Auth;

if (!function_exists('app')) {
    /**
     * Get the application instance
     */
    function app() {
        return App::getInstance();
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value
     */
    function config($key, $default = null) {
        return app()->config($key, $default);
    }
}

if (!function_exists('view')) {
    /**
     * Get a view instance or render a view
     */
    function view($view = null, $data = []) {
        $view = new View();
        
        if (func_num_args() === 0) {
            return $view;
        }
        
        return $view->render($view, $data);
    }
}

if (!function_exists('auth')) {
    /**
     * Get the auth instance
     */
    function auth() {
        return new Auth();
    }
}

if (!function_exists('session')) {
    /**
     * Get or set a session value
     */
    function session($key = null, $value = null) {
        if (is_null($key)) {
            return $_SESSION;
        }
        
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
            
            return;
        }
        
        if (is_null($value)) {
            return $_SESSION[$key] ?? null;
        }
        
        $_SESSION[$key] = $value;
    }
}

if (!function_exists('session_flash')) {
    /**
     * Set a flash message in the session
     */
    function session_flash($key, $value) {
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        
        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old($key, $default = null) {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL
     */
    function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to the previous page
     */
    function back() {
        return redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL
     */
    function asset($path) {
        $baseUrl = rtrim(config('app.url', ''), '/');
        $path = ltrim($path, '/');
        return "{$baseUrl}/public/{$path}";
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL
     */
    function url($path = '') {
        $baseUrl = rtrim(config('app.url', ''), '/');
        $path = ltrim($path, '/');
        return $path ? "{$baseUrl}/{$path}" : $baseUrl;
    }
}

if (!function_exists('route')) {
    /**
     * Generate a URL to a named route
     */
    function route($name, $parameters = []) {
        // This would be implemented with a router
        return url($name);
    }
}

if (!function_exists('abort')) {
    /**
     * Throw an HTTP exception
     */
    function abort($code, $message = '', array $headers = []) {
        http_response_code($code);
        
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                header("$key: $value");
            }
        }
        
        if ($message) {
            echo $message;
        }
        
        exit(1);
    }
}

if (!function_exists('abort_if')) {
    /**
     * Throw an HTTP exception if the given condition is true
     */
    function abort_if($boolean, $code, $message = '', array $headers = []) {
        if ($boolean) {
            abort($code, $message, $headers);
        }
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token
     */
    function csrf_token() {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field
     */
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate a method spoofing form field
     */
    function method_field($method) {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script
     */
    function dd(...$args) {
        foreach ($args as $arg) {
            echo '<pre>';
            var_dump($arg);
            echo '</pre>';
        }
        
        die(1);
    }
}

// Include the autoloader
require_once __DIR__ . '/core/autoload.php';

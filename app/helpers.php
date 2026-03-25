<?php

/**
 * Global Helper Functions
 */

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
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
                return null;
        }

        if (strlen($value) > 1 && strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('h')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param  string  $value
     * @return string
     */
    function h($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    function url($path = null)
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $base_url .= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        return rtrim($base_url, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a given path.
     *
     * @param  string  $path
     * @return void
     */
    function redirect($path)
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('session')) {
    /**
     * Get or set session values.
     *
     * @param  string|null  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return $_SESSION;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
            return true;
        }

        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('old')) {
    /**
     * Get the old input value from the session.
     *
     * @param  string  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    function old($key, $default = null)
    {
        return session('_old_input')[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    /**
     * Die and Dump.
     *
     * @param  mixed  $value
     * @return void
     */
    function dd($value)
    {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
        die;
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

if (!function_exists('auth')) {
    /**
     * Get the current authenticated user.
     *
     * @return mixed
     */
    function auth()
    {
        return session('user');
    }
}

if (!function_exists('check_auth')) {
    /**
     * Check if the user is authenticated.
     *
     * @return bool
     */
    function check_auth()
    {
        return !is_null(auth());
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate a CSRF token.
     *
     * @return string
     */
    function csrf_token()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF hidden input field.
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('getCsrfField')) {
    /**
     * Alias for csrf_field()
     */
    function getCsrfField() {
        return csrf_field();
    }
}

if (!function_exists('logger')) {
    /**
     * Log a message.
     *
     * @param  string  $message
     * @param  array   $context
     * @return void
     */
    function logger($message, $context = [])
    {
        $logger = \App\Core\Log\Logger::getInstance();
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

if (!function_exists('get_flash')) {
    /**
     * Get a flash message from the session.
     *
     * @param  string  $key
     * @return mixed|null
     */
    function get_flash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
}

if (!function_exists('get_page_title')) {
    /**
     * Get the current page title.
     *
     * @param  string  $default
     * @return string
     */
    function get_page_title($default = 'APS Dream Home') {
        global $page_title;
        return $page_title ?? $default;
    }
}

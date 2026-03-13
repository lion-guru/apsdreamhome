<?php

namespace App\Http\Controllers;

/**
 * Base Controller
 * 
 * All controllers should extend this base controller
 */
class BaseController
{
    protected $models = [];
    protected $layout = 'layouts/base';
    protected $db;
    protected $data = [];
    protected $session;

    public function __construct()
    {
        // Initialize data array
        $this->data = [];

        // Initialize session
        $this->session = $this;
    }

    /**
     * Render a view with data
     */
    protected function render($view, $data = [])
    {
        // Start output buffering to prevent header issues
        ob_start();

        // Merge with class data
        $data = array_merge($this->data, $data);

        // Extract data to variables
        extract($data);

        // Include layout
        include __DIR__ . '/../../views/' . $view . '.php';

        // Get content and clean buffer
        $content = ob_get_clean();

        // Output content
        echo $content;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: " . $url);
        }
        exit;
    }

    /**
     * Set flash message
     */
    protected function setFlash($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Sanitize input
     */
    protected function sanitize($input)
    {
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * Get flash message
     */
    protected function getFlash($key, $default = null)
    {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return $default;
    }

    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get model instance
     */
    protected function model($name)
    {
        if (!isset($this->models[$name])) {
            $class = "App\\Models\\{$name}";
            $this->models[$name] = new $class();
        }
        return $this->models[$name];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return $this->get('user_id') !== null;
    }

    /**
     * Sanitize input
     */
    protected function sanitizeInput($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    protected function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone
     */
    protected function validatePhone($phone)
    {
        return preg_match('/^[0-9]{10}$/', $phone);
    }

    /**
     * Get CSRF token
     */
    protected function getCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

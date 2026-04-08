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
    protected $request;
    protected $middlewares = [];
    protected $mlSupport;
    protected $start_time;

    /**
     * Register a middleware
     */
    protected function middleware($name, $options = [])
    {
        $this->middlewares[] = [
            'name' => $name,
            'options' => $options
        ];
    }

    /**
     * Alias for render()
     */
    public function view($view, $data = [])
    {
        return $this->render($view, $data);
    }

    public function __construct()
    {
        // Initialize data array
        $this->data = [];

        // Initialize session
        $this->session = $this;

        // Initialize request
        $this->request = \App\Core\Http\Request::createFromGlobals();

        // Initialize database
        $this->db = \App\Core\Database\Database::getInstance();

        // Initialize Localization Service (mlSupport) if available
        if (class_exists('\App\Services\Localization\LocalizationService')) {
            if (method_exists('\App\Services\Localization\LocalizationService', 'getInstance')) {
                try {
                    $this->mlSupport = \App\Services\Localization\LocalizationService::getInstance();
                } catch (\Throwable $e) {
                    // LocalizationService requires deps not available - skip silently
                }
            }
        }

        // Automated CSRF protection for POST requests (skip for public forms)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$this->skipCsrfProtection()) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                \App\Core\ErrorHandler::render(403, "Invalid or missing CSRF token.");
                exit;
            }
        }
    }

    protected function skipCsrfProtection(): bool
    {
        return false;
    }

    /**
     * Public method to get request object
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Public method to get database object
     */
    public function getDatabase()
    {
        return $this->db;
    }

    /**
     * Public method to get session object
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Get the request object (alias for Consistency)
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Send JSON response (alias for consistency)
     */
    public function json($data, int $status = 200)
    {
        return $this->jsonResponse($data, $status);
    }

    /**
     * Public method to get request header
     */
    public function getHeader($name)
    {
        return $this->request->getHeader($name);
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

        // Include view content
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "View not found: " . $view;
        }

        // Get content and clean buffer
        $content = ob_get_clean();

        // If layout exists, render layout with content
        if ($this->layout) {
            $layoutPath = __DIR__ . '/../../views/' . $this->layout . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
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

    /**
     * Require user to be logged in
     */
    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            if (!headers_sent()) {
                header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/login");
            }
            exit;
        }
    }

    /**
     * Get configuration value
     */
    protected function getConfig($key, $default = null)
    {
        $config = [
            'app_name' => 'APS Dream Home',
            'app_url' => defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome',
            'timezone' => 'Asia/Kolkata'
        ];

        return $config[$key] ?? $default;
    }

    /**
     * Check if user has a specific role
     */
    protected function hasRole($role)
    {
        return ($_SESSION['user_role'] ?? $_SESSION['role'] ?? $_SESSION['admin_role'] ?? '') === $role;
    }

    /**
     * Return a JSON response
     */
    public function response($data, int $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Return a JSON response (alias for consistency)
     */
    public function jsonResponse($data, int $status = 200)
    {
        return $this->response($data, $status);
    }

    /**
     * Return a JSON error response
     */
    protected function jsonError($message, $status = 400)
    {
        return $this->jsonResponse(['success' => false, 'error' => $message], $status);
    }

    /**
     * Return a 404 Not Found response
     */
    protected function notFound($message = "Resource not found")
    {
        if ($this->request->isAjax() || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            return $this->jsonError($message, 404);
        }

        http_response_code(404);
        return $this->render('errors/404', ['message' => $message, 'page_title' => '404 Not Found']);
    }

    /**
     * Return a 403 Forbidden response
     */
    protected function forbidden($message = "Access denied")
    {
        if ($this->request->isAjax() || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            return $this->jsonError($message, 403);
        }

        http_response_code(403);
        return $this->render('errors/403', ['message' => $message, 'page_title' => '403 Forbidden']);
    }

    /**
     * Alias for validateCsrfToken
     */
    protected function verifyCsrfToken($token)
    {
        return $this->validateCsrfToken($token);
    }

    /**
     * Get the current logged in user
     */
    protected function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $_SESSION['user_id'];
        $userModel = $this->model('User');
        return $userModel->getUserById($userId);
    }

    /**
     * Log a lead activity
     */
    protected function logLeadActivity($leadId, $type, $description, $metadata = [])
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $sql = "INSERT INTO lead_activities (lead_id, activity_type, description, metadata, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $db->execute($sql, [
                $leadId,
                $type,
                $description,
                json_encode($metadata),
                $_SESSION['user_id'] ?? null
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to log lead activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load model (alias for model)
     */
    protected function modelLocal($modelName)
    {
        return $this->model($modelName);
    }

    /**
     * Get request method
     */
    protected function method()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get POST data
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function getLocal($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Get request files
     */
    protected function files($key = null)
    {
        if ($key === null) {
            return $_FILES;
        }
        return $_FILES[$key] ?? null;
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrfTokenLocal()
    {
        $token = $_POST['csrf_token'] ?? '';
        return $token === $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Log activity
     */
    protected function logActivity($action, $details = '')
    {
        try {
            $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
            if (!$userId) return;

            $db = \App\Core\Database\Database::getInstance();
            $sql = "INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())";
            $db->execute($sql, [$userId, $action, $details]);
        } catch (\Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }

    /**
     * Go back to previous page
     */
    protected function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $referer");
        exit();
    }

    /**
     * Sanitize input
     */
    protected function sanitizeLocal($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin()
    {
        return isset($_SESSION['admin_id']);
    }

    /**
     * Require admin access
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            $_SESSION['error_message'] = 'Admin access required';
            header('Location: /admin/login');
            exit();
        }
    }

    /**
     * Check if current user is logged in
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
    }

    /**
     * Check if current user is associate
     */
    protected function isAssociate()
    {
        return isset($_SESSION['associate_id']);
    }

    /**
     * Get views base path
     */
    protected function getViewsBasePath()
    {
        return realpath(__DIR__ . '/../../views');
    }

    /**
     * Start performance monitoring
     */
    protected function startPerformanceMonitoring()
    {
        $this->start_time = microtime(true);
    }

    /**
     * End performance monitoring
     */
    protected function endPerformanceMonitoring()
    {
        if (isset($this->start_time)) {
            $end_time = microtime(true);
            $execution_time = $end_time - $this->start_time;
            error_log("Page execution time: " . number_format($execution_time, 4) . " seconds");
        }
    }
}

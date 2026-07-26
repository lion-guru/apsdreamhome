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
     * Run the ExperimentMiddleware (A/B testing variant assignment).
     *
     * Idempotent: ExperimentMiddleware::handle() is a static no-op after the
     * first call in a request, so it's safe to invoke from every controller.
     */
    protected function runExperimentMiddleware(): void
    {
        if (class_exists('\\App\\Http\\Middleware\\ExperimentMiddleware')
            && method_exists('\\App\\Http\\Middleware\\ExperimentMiddleware', 'handle')) {
            \App\Http\Middleware\ExperimentMiddleware::handle();
        }
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

        // Monitoring: register global exception handler (once per process).
        // Captures uncaught Throwables into monitoring_errors without breaking
        // any existing handler that PHP installed earlier.
        if (!defined('APS_MONITORING_HANDLER_REGISTERED')) {
            define('APS_MONITORING_HANDLER_REGISTERED', true);
            if (class_exists('\App\Services\Monitoring\ErrorTrackerService')) {
                $previous = set_exception_handler(null);
                set_exception_handler(function ($exception) use ($previous) {
                    try {
                        \App\Services\Monitoring\ErrorTrackerService::captureException($exception, [
                            'url'    => $_SERVER['REQUEST_URI'] ?? null,
                            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                        ]);
                    } catch (\Throwable $e) {
                        // never let monitoring break the app
                    }
                    if (is_callable($previous)) {
                        call_user_func($previous, $exception);
                    } else {
                        // Default rethrow so PHP prints/logs the error
                        throw $exception;
                    }
                });
            }
        }

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

        // A/B testing: assign user to running experiments and auto-track view event
        $this->runExperimentMiddleware();

        // Per-request correlation id for log entries (X-Request-Id if upstream provided)
        if (class_exists('\App\Services\Log')) {
            \App\Services\Log::setRequestId(
                $_SERVER['HTTP_X_REQUEST_ID'] ?? null
            );
        }

        // Security: regenerate session ID periodically (every 5 minutes) to prevent fixation
        $this->initSessionSecurity();

        // Security: set defense-in-depth HTTP headers on every response
        $this->setSecurityHeaders();

        // Automated CSRF protection for POST requests (skip for public forms)
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$this->skipCsrfProtection()) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                \App\Core\ErrorHandler::render(403, "Invalid or missing CSRF token.");
                exit;
            }
        }
    }

    /**
     * Regenerate session ID periodically to prevent session fixation attacks.
     * Runs every 5 minutes (300s). On login, controllers should call
     * session_regenerate_id(true) immediately (see auth controllers).
     */
    protected function initSessionSecurity(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $regenerateInterval = 300; // 5 minutes
        $now = time();
        if (!isset($_SESSION['last_regenerate'])) {
            $_SESSION['last_regenerate'] = $now;
            return;
        }
        if ($now - (int)$_SESSION['last_regenerate'] > $regenerateInterval) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = $now;
        }
    }

    /**
     * Send defense-in-depth security HTTP headers on every response.
     * Mirrors the .htaccess directives so headers are present even when
     * the request bypasses Apache (CLI, internal includes, etc.).
     */
    protected function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        
        // Generate CSP nonce for this request
        $cspNonce = bin2hex(random_bytes(16));
        $_SESSION['csp_nonce'] = $cspNonce;
        
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-XSS-Protection: 1; mode=block');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
        if (class_exists('\App\Services\Log')) {
            header('X-Request-Id: ' . \App\Services\Log::getRequestId());
        }
        // HSTS only when over HTTPS
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)) {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        }
        // Content Security Policy with nonce support
        $base = defined('BASE_URL') ? BASE_URL : '';
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; "
            . "img-src 'self' data: blob: https:; "
            . "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; "
            . "frame-src 'self' https://www.google.com; "
            . "connect-src 'self' https: ws: wss:; "
            . "report-uri {$base}/csp-report; "
            . "report-to csp-endpoint";
        header("Content-Security-Policy: " . $csp);
        header("Reporting-Endpoints: csp-endpoint=\"{$base}/csp-report\"");
        
        // Expose nonce to views
        $GLOBALS['csp_nonce'] = $cspNonce;
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
     *
     * Accepts both slash notation (`admin/users/index`) and dot notation
     * (`admin.users.index`) — dots are converted to slashes automatically so
     * controllers using either style work without code changes.
     *
     * Includes a self-rendering safeguard: if the view file already produced
     * a full HTML document (starts with <!DOCTYPE>), we skip the layout to
     * avoid double-render. This protects against legacy self-contained views
     * that do their own ob_start + include layouts/base.php.
     *
     * Auto-injects SEO meta tags via generateSEO() if not already provided
     * in $data['seo'] — gives every page proper OG / Twitter / JSON-LD.
     */
    protected function render($view, $data = [])
    {
        // Normalize dot-notation (admin.users.index) to slash-notation (admin/users/index)
        $view = str_replace('.', '/', $view);

        // Ensure translation helper is loaded before any view content
        if (!function_exists('__')) {
            require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
        }

        // Start output buffering to prevent header issues
        ob_start();

        // Merge with class data
        $data = array_merge($this->data, $data);

        // Auto-inject SEO meta tags (only if not explicitly provided)
        if (!isset($data['seo']) || !is_array($data['seo'])) {
            $data['seo'] = $this->generateSEO($data);
        }

        // Auto-inject CSRF token into every rendered view (so forms always have a valid token)
        if (!isset($data['csrf_token'])) {
            $data['csrf_token'] = $this->getCsrfToken();
        }

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

        // Self-rendering safeguard: if the view already produced a full HTML
        // document (starts with <!DOCTYPE>), it owns the layout — emit as-is
        // to prevent double-render.
        if (preg_match('/^\s*<!DOCTYPE/i', $content)) {
            echo $content;
            return;
        }

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
     * Generate SEO meta tag payload for auto-injection.
     *
     * Reads from common $data keys (page_title, meta_description, etc.) and
     * falls back to sensible defaults. The returned array is consumed by
     * header.php and base.php to render <title>, <meta>, Open Graph,
     * Twitter Card, and optional JSON-LD structured data.
     *
     * Per-page overrides: pass $data['seo'] from the controller to fully
     * customize, or pass individual keys (page_title, meta_description,
     * meta_keywords, og_image, canonical_url, og_type, json_ld).
     *
     * @param array $data The view data being rendered
     * @return array SEO payload
     */
    protected function generateSEO($data = [])
    {
        $title = $data['page_title'] ?? $data['title'] ?? 'APS Dream Home - Premium Real Estate';
        $description = $data['meta_description'] ?? $data['description'] ?? $data['page_description']
            ?? 'Find your dream home with APS Dream Home. Premium plots, flats, villas, and farmhouses across India.';
        $keywords = $data['meta_keywords'] ?? $data['keywords']
            ?? 'real estate, plots, flats, villas, property, APS Dream Home, India';

        $image = $data['og_image'] ?? (defined('BASE_URL') ? BASE_URL : '') . '/assets/images/og-default.jpg';

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $url = $data['canonical_url']
            ?? ($protocol . '://' . $host . $requestUri);

        return [
            'title'              => $title,
            'description'        => $description,
            'keywords'           => $keywords,
            'og_title'           => $title,
            'og_description'     => $description,
            'og_image'           => $image,
            'og_url'             => $url,
            'og_type'            => $data['og_type'] ?? 'website',
            'twitter_card'       => 'summary_large_image',
            'twitter_title'      => $title,
            'twitter_description'=> $description,
            'twitter_image'      => $image,
            'canonical'          => $url,
            'json_ld'            => $data['json_ld'] ?? null
        ];
    }

    /**
     * Redirect to another URL
     */
    protected function redirect($url)
    {
        // Add BASE_URL if not already present
        if (!empty($url) && strpos($url, 'http') !== 0 && defined('BASE_URL')) {
            $url = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
        }

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
            'app_url' => rtrim(BASE_URL, '/'),
            'timezone' => 'Asia/Kolkata'
        ];

        return $config[$key] ?? $default;
    }

    /**
     * Check if user has a specific role
     */
    protected function hasRole($role)
    {
        return ($_SESSION['role'] ?? $_SESSION['admin_role'] ?? '') === $role;
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
            $sql = "INSERT INTO activity_logs_unified (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())";
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
        if (!isset($_SESSION['admin_id'])) return false;
        // Admin, super_admin, manager, employee, telecaller — all can access admin panel
        // Sidebar filtering via AdminMenuService shows only their department's menus
        $role = $_SESSION['role'] ?? $_SESSION['admin_role'] ?? '';
        return in_array($role, ['admin', 'super_admin', 'manager', 'employee', 'telecaller']);
    }

    /**
     * Require admin access
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            $_SESSION['error_message'] = 'Admin access required';
            $this->redirect('/admin/login');
        }
    }

    /**
     * Check if current user is logged in
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['associate_id']) || isset($_SESSION['agent_id']) || isset($_SESSION['employee_id']);
    }

    /**
     * Check if current user is associate
     */
    protected function isAssociate()
    {
        return isset($_SESSION['associate_id']);
    }

    /**
     * Render error page
     */
    protected function renderError($title, $message, $code = 500)
    {
        http_response_code($code);
        $this->render('errors/generic', [
            'page_title' => $title,
            'error_title' => $title,
            'error_message' => $message,
            'error_code' => $code
        ]);
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
            if (defined('DEBUG_MODE') && constant('DEBUG_MODE')) {
                error_log("Page execution time: " . number_format($execution_time, 4) . " seconds");
            }
        }
    }
}

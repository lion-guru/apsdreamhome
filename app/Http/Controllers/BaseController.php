<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;
use App\Core\Http\Request;
use App\Http\Middleware\ExperimentMiddleware;
use App\Services\Monitoring\ErrorTrackerService;
use App\Services\Localization\LocalizationService;
use App\Core\Middleware\TenantContext;
use App\Http\Middleware\RBACManager;
use App\Services\TenantEnforcement;
use App\Services\Log;
use App\Core\ErrorHandler;

use App\Http\Controllers\Traits\HasCsrfProtection;
use App\Http\Controllers\Traits\HasRateLimiting;
use App\Http\Controllers\Traits\HasTenantScope;
use App\Http\Controllers\Traits\HasJsonResponses;
use App\Http\Controllers\Traits\HasViewRendering;
use App\Http\Controllers\Traits\HasFlashMessages;
use App\Http\Controllers\Traits\HasSecurityHeaders;
use App\Http\Controllers\Traits\HasInputHandling;
use App\Http\Controllers\Traits\HasModelLoading;

/**
 * Base Controller
 *
 * All controllers should extend this base controller.
 * Composed of focused traits for separation of concerns:
 * - HasCsrfProtection: CSRF token generation and validation
 * - HasRateLimiting: Rate limiting enforcement
 * - HasTenantScope: Multi-tenant enforcement
 * - HasJsonResponses: JSON response helpers
 * - HasViewRendering: View rendering with layout support
 * - HasFlashMessages: Flash messages and session management
 * - HasSecurityHeaders: Security headers, CSP, CORS
 * - HasInputHandling: Input validation and sanitization
 * - HasModelLoading: Lazy model loading and utilities
 */
class BaseController
{
    use HasCsrfProtection;
    use HasRateLimiting;
    use HasTenantScope;
    use HasJsonResponses;
    use HasViewRendering;
    use HasFlashMessages;
    use HasSecurityHeaders;
    use HasInputHandling;
    use HasModelLoading;

    /** Canonical list of roles with admin panel access */
    const ADMIN_ROLES = [
        'super_admin', 'admin', 'manager', 'telecaller', 'employee',
        'ceo', 'cfo', 'cto', 'coo', 'cmo', 'chro',
        'director', 'sales_director', 'marketing_director', 'construction_director', 'finance_director', 'hr_director', 'operations_director',
        'legal_head', 'finance_head', 'hr_head', 'operations_head',
        'department_manager', 'project_manager', 'sales_manager', 'hr_manager', 'marketing_manager',
        'finance_manager', 'property_manager', 'it_manager', 'operations_manager',
        'legal_advisor', 'chartered_accountant', 'senior_developer',
    ];

    protected array $models = [];
    protected string $layout = 'layouts/base';
    protected array $data = [];
    protected $session;
    protected $request;
    protected array $middlewares = [];
    protected $mlSupport;
    protected $db;
    protected $start_time;

    /**
     * Register a middleware
     */
    protected function middleware(string $name, array $options = []): void
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
        if (class_exists(ExperimentMiddleware::class)
            && method_exists(ExperimentMiddleware::class, 'handle')) {
            ExperimentMiddleware::handle();
        }
    }

    public function __construct()
    {
        // Initialize data array
        $this->data = [];

        // Initialize session
        $this->session = $this;

        // Initialize request
        $this->request = Request::createFromGlobals();

        // Initialize database
        $this->db = Database::getInstance();

        // Monitoring: register global exception handler (once per process).
        // Captures uncaught Throwables into monitoring_errors without breaking
        // any existing handler that PHP installed earlier.
        if (!defined('APS_MONITORING_HANDLER_REGISTERED')) {
            define('APS_MONITORING_HANDLER_REGISTERED', true);
            if (class_exists(ErrorTrackerService::class)) {
                $previous = set_exception_handler(null);
                set_exception_handler(function ($exception) use ($previous) {
                    try {
                        ErrorTrackerService::captureException($exception, [
                            'url'    => $_SERVER['REQUEST_URI'] ?? null,
                            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                        ]);
                    } catch (\Throwable $e) {
                        // never let monitoring break the app
                        error_log($e->getMessage());
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
        if (class_exists(LocalizationService::class)) {
            if (method_exists(LocalizationService::class, 'getInstance')) {
                try {
                    // Try to get the instance - it will handle null deps gracefully
                    $this->mlSupport = LocalizationService::getInstance();
                } catch (\Throwable $e) {
                    // LocalizationService requires deps not available - skip silently
                    error_log($e->getMessage());
                }
            }
        }

        // A/B testing: assign user to running experiments and auto-track view event
        $this->runExperimentMiddleware();

        // Multi-tenant: resolve tenant context from request (header/subdomain/query/session/default)
        if (class_exists(TenantContext::class)) {
            try {
                TenantContext::resolve();
            } catch (\Throwable $e) {
                // TenantContext failure should not break the app
                error_log($e->getMessage());
            }
        }

        // Tenant enforcement: block suspended/cancelled tenants from write operations
        $this->enforceTenantStatus();

        // Per-request correlation id for log entries (X-Request-Id if upstream provided)
        if (class_exists(Log::class)) {
            Log::setRequestId(
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
                ErrorHandler::render(403, "Invalid or missing CSRF token.");
                exit;
            }
        }

        // Rate limiting: prevent brute force attacks
        // Only apply to POST requests and API/auth endpoints (not GET page loads)
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' || strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
            $this->enforceRateLimit();
        }

        // CORS: set headers for API routes so browser-based clients (SPA, mobile web) work
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
            $this->setCorsHeaders();
        }
    }

    /**
     * Get JSON input from request body (central helper for API controllers).
     * BaseApiController also defines this; controllers extending BaseController
     * previously called $this->getJsonInput() without it → fatal → 500.
     * Returns decoded array, or empty array on invalid/empty JSON.
     */
    protected function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
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
     * Is the current request an AJAX / XHR request?
     *
     * NOTE (S453): only App\Core\Controller had isAjax() - the front
     * BaseController did not - so a front controller calling $this->isAjax()
     * died with "Call to undefined method ...::isAjax()".  Mirrors
     * App\Core\Controller::isAjax().
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Public method to get request header
     */
    public function getHeader(string $name)
    {
        return $this->request->getHeader($name);
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
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
     * Check if current user is logged in
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id'])
            || isset($_SESSION['admin_id'])
            || isset($_SESSION['associate_id'])
            || isset($_SESSION['agent_id'])
            || isset($_SESSION['employee_id']);
    }

    /**
     * Render a view with data (alias for render())
     */
    public function view(string $view, array $data = []): void
    {
        $this->render($view, $data);
    }
}
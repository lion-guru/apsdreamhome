<?php

namespace App\Core;

use Exception;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Routing\Router;
use App\Core\Database;
use App\Core\Session\SessionManager;
use App\Core\Auth;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\ThrottleLoginMiddleware;

class App
{
    /**
     * The application instance
     */
    protected static $instance;

    /**
     * The base path of the application
     */
    protected $basePath;

    /**
     * The application configuration
     */
    protected $config = [];

    /**
     * The service container
     */
    protected $container = [];

    /**
     * The router instance
     */
    public $router;

    /**
     * The request instance
     */
    protected $request;

    /**
     * The response instance
     */
    protected $response;

    /**
     * The database connection
     */
    protected $db;

    /**
     * The session manager
     */
    protected $session;

    /**
     * The auth instance
     */
    public $auth;

    /**
     * The logger instance
     */
    public $logger;

    /**
     * Create a new application instance
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        } else {
            // Set default base path to project root
            $this->setBasePath(dirname(__DIR__, 2));
        }

        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->router = new Router($this);

        $this->bootstrap();

        self::$instance = $this;
    }

    /**
     * Get the singleton application instance
     */
    public static function getInstance($basePath = null)
    {
        if (!self::$instance) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }

    /**
     * Get the logger instance
     *
     * @return \App\Services\SystemLogger
     */
    public function logger()
    {
        if (!$this->logger) {
            $this->logger = new \App\Services\SystemLogger($this->config['logging'] ?? []);
        }
        return $this->logger;
    }

    /**
     * Bootstrap the application
     */
    protected function bootstrap()
    {
        // Load environment variables
        $this->loadEnvironment();

        // Load helpers first (before config)
        $this->loadHelpers();

        // Load configuration
        $this->loadConfig();

        // Set error reporting
        $this->setErrorReporting();

        // Initialize services
        $this->initializeServices();

        // Routes will be loaded by the router
    }

    /**
     * Load application routes
     */
    protected function loadRoutes()
    {
        $routesDir = $this->basePath('routes');
        $app = $this;
        if (file_exists($routesDir . '/modern.php')) {
            require $routesDir . '/modern.php';
        }
        if (file_exists($routesDir . '/api.php')) {
            require $routesDir . '/api.php';
        }
    }

    /**
     * Run the application (dispatch request and send response)
     */
    public function run()
    {
        try {
            $response = $this->router->dispatch($this->request);
            $response->send();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * Set the base path for the application
     */
    public function setBasePath($path)
    {
        $this->basePath = rtrim($path, '\\/');
        return $this;
    }

    /**
     * Get the base path of the application
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : '');
    }

    /**
     * Load application configuration
     */
    protected function loadConfig()
    {
        $configDir = $this->basePath('config');

        if (!is_dir($configDir)) {
            throw new Exception('Config directory not found: ' . $configDir);
        }

        // Load bootstrap.php which sets up the global $config array and environment
        $bootstrapFile = $configDir . '/bootstrap.php';
        if (file_exists($bootstrapFile)) {
            require $bootstrapFile; // Force require to ensure execution
        } else {
            // Bootstrap file not found, continue without it
        }

        // Import global config if available (bridging legacy and new systems)
        global $config;
        if (is_array($config) && !empty($config)) {
            $this->config = $config;
            // return; // Don't return, merge with other files?
            // Actually, if bootstrap loads everything, we might not need the loop.
            // But let's stick to existing logic for now.
        }

        // Fallback: Load each PHP file in the config directory if global config is empty
        foreach (glob($configDir . '/*.php') as $configFile) {
            $basename = basename($configFile);
            if ($basename === 'bootstrap.php') { continue; }
            $fileConfig = require $configFile;
            if (is_array($fileConfig)) {
                $key = pathinfo($basename, PATHINFO_FILENAME);
                if (isset($this->config[$key]) && is_array($this->config[$key])) {
                    $this->config[$key] = array_merge($this->config[$key], $fileConfig);
                } else {
                    $this->config[$key] = $fileConfig;
                }
            }
        }
    }

    /**
     * Load environment variables
     */
    protected function loadEnvironment()
    {
        if (class_exists('Dotenv\Dotenv') && file_exists($this->basePath('.env'))) {
            $dotenv = \Dotenv\Dotenv::createImmutable($this->basePath());
            $dotenv->safeLoad();
        }
    }

    /**
     * Load helper functions
     */
    protected function loadHelpers()
    {
        $helpersFile = $this->basePath('app/Helpers/env.php');
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }

    /**
     * Set error reporting based on environment
     */
    protected function setErrorReporting()
    {
        $environment = $this->config('app.environment', $this->config('app.env', getenv('APP_ENV') ?: 'development'));

        if ($environment === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // Set timezone
        date_default_timezone_set($this->config('app.timezone', 'UTC'));
    }

    /**
     * Initialize application services
     */
    protected function initializeServices()
    {
        // Initialize session
        $sessionOptions = [];
        if (isset($_ENV['SESSION_TIMEOUT'])) {
            $sessionOptions['cookie_lifetime'] = (int) $_ENV['SESSION_TIMEOUT'];
        }
        $sessionOptions['cookie_httponly'] = true;
        $sessionOptions['cookie_samesite'] = 'Lax';
        $sessionOptions['cookie_secure'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        $this->session = new SessionManager();
        $this->session->start($sessionOptions);

        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            // CSP handled by SecurityHeaders middleware
            // header("Content-Security-Policy-Report-Only: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;");
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }
        }

        // Enforce CSRF validation on state-changing requests if enabled
        try {
            $csrfEnabled = $this->config('security.csrf.enabled', true);
            if ($csrfEnabled && class_exists('\App\Services\Security\Legacy\CSRFProtection')) {
                // CSRF protection logic here
            }
        } catch (\Exception $e) {
            // Continue without CSRF protection if there's an error
        }

        $this->register('web', new class {
            public function handle($request, $next)
            {
                return $next($request);
            }
        });

        $this->register('auth', new class {
            public function handle($request, $next)
            {
                // Check if user is logged in
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Check for any authentication session
                if (
                    !isset($_SESSION['user_id']) &&
                    !isset($_SESSION['admin_logged_in']) &&
                    !isset($_SESSION['employee_id']) &&
                    !isset($_SESSION['customer_id']) &&
                    !isset($_SESSION['associate_id'])
                ) {

                    header('Location: /login');
                    exit;
                }
                return $next($request);
            }
        });

        $this->register('admin', new class {
            public function handle($request, $next)
            {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                    header('Location: /admin/login');
                    exit;
                }
                return $next($request);
            }
        });

        $this->register('throttle', new RateLimitMiddleware());

        // Initialize router
        $this->router = new Router($this);
    }

    /**
     * Getters for core services
     */
    public function request()
    {
        return $this->request;
    }

    public function response()
    {
        return $this->response;
    }

    public function session()
    {
        return $this->session;
    }

    public function db()
    {
        return $this->db;
    }

    public function router()
    {
        return $this->router;
    }

    /**
     * Initialize database connection
     */
    protected function initializeDatabase()
    {
        $config = $this->config('database');

        if (!$config) {
            return;
        }

        // Support Laravel-style database config
        if (isset($config['default']) && isset($config['connections'][$config['default']])) {
            $connection = $config['connections'][$config['default']];
            $this->db = Database::getInstance($connection);
            return;
        }

        // Support simple database config (directly nested under 'database')
        if (isset($config['database']) && is_array($config['database'])) {
            $this->db = Database::getInstance($config['database']);
            return;
        }
    }

    /**
     * Handle exceptions
     */
    protected function handleException($e)
    {
        error_log("App::handleException called - " . $e->getMessage());
        $environment = $this->config('app.environment', $this->config('app.env', getenv('APP_ENV') ?: 'development'));

        if ($environment === 'development' || (getenv('APP_ENV') === 'development')) {
            // In development, show detailed error
            $this->renderException($e);
        } else {
            // In production, show a generic error page
            $this->response->setStatusCode(500);
            echo 'An error occurred. Please try again later.';
        }

        // Log the error
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    }

    /**
     * Render exception details (for development)
     */
    protected function renderException($e)
    {
        $this->response->setStatusCode(500);

        echo '<h1>Error: ' . $e->getMessage() . '</h1>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }

    /**
     * Get a configuration value
     */
    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config;
        }
        $segments = explode('.', (string) $key);
        $value = $this->config;
        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * Static helpers used by legacy services
     */
    public static function database()
    {
        return static::getInstance()->db();
    }

    /**
     * Magic method for checking if a service exists
     */
    public function __isset($name)
    {
        return isset($this->container[$name]) || method_exists($this, 'get' . ucfirst($name));
    }

    /**
     * Register a service in the container
     */
    public function register($name, $value)
    {
        $this->container[$name] = $value;
        return $this;
    }

    /**
     * Get a service from the container
     */
    public function make($name, array $parameters = [])
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }

        // Auto-resolve class if it exists
        if (class_exists($name)) {
            return new $name(...$parameters);
        }

        throw new Exception("Service {$name} not found");
    }

    /**
     * Handle dynamic static method calls
     */
    public static function __callStatic($method, $parameters)
    {
        return static::getInstance()->$method(...$parameters);
    }
}

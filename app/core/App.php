<?php

namespace App\Core;

use Exception;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Routing\Router;
use App\Core\Database;
use App\Core\Session\SessionManager;
use App\Core\Auth;

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
    protected $router;

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

        $this->bootstrap();

        self::$instance = $this;
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

        // Load routes
        $this->loadRoutes();
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
            throw new Exception('Config directory not found');
        }

        // Load bootstrap.php which sets up the global $config array and environment
        $bootstrapFile = $configDir . '/bootstrap.php';
        if (file_exists($bootstrapFile)) {
            require_once $bootstrapFile;
        }

        // Import global config if available (bridging legacy and new systems)
        global $config;
        if (is_array($config) && !empty($config)) {
            $this->config = $config;
            return;
        }

        // Fallback: Load each PHP file in the config directory if global config is empty
        foreach (glob($configDir . '/*.php') as $configFile) {
            $key = basename($configFile, '.php');
            if ($key !== 'bootstrap') { // Skip bootstrap as it's already loaded
                $this->config[$key] = require $configFile;
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
        $environment = $this->config('app.env', 'production');

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

        // Initialize request and response
        $this->request = Request::createFromGlobals();
        $this->response = new Response();

        // Initialize database connection
        $this->initializeDatabase();

        // Initialize auth
        $this->auth = new Auth();

        // Register middleware
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

        // Initialize router
        $this->router = new Router($this);
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
     * Load application routes
     */
    protected function loadRoutes()
    {
        // Make $app available to all route files
        $app = $this;

        // Load legacy routes first (so modern routes can override them)
        $legacyRoutesFile = $this->basePath('routes/web.php');
        if (file_exists($legacyRoutesFile)) {
            // Make sure $app is available in the web.php scope
            $app = $this;
            require $legacyRoutesFile;
        }

        // Load modern routes (overrides legacy routes)
        $modernRoutesFile = $this->basePath('routes/modern.php');
        if (file_exists($modernRoutesFile)) {
            $app = $this;
            require $modernRoutesFile;
        }

        // Load API routes
        $apiRoutesFile = $this->basePath('routes/api.php');
        if (file_exists($apiRoutesFile)) {
            require $apiRoutesFile;
        }
    }

    /**
     * Run the application
     */
    public function run()
    {
        try {
            // Handle the request through the router
            $response = $this->router->dispatch($this->request);

            // Send the response
            $response->send();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle exceptions
     */
    protected function handleException($e)
    {
        $environment = $this->config('app.env', 'production');

        if ($environment === 'development') {
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

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Get the router instance
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Get the request instance
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get the response instance
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Get the database connection
     */
    public function db()
    {
        return $this->db;
    }

    /**
     * Get the session manager
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * Get the application instance
     */
    public static function getInstance($basePath = null)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($basePath);
        }

        return static::$instance;
    }

    /**
     * Get the database connection (static accessor)
     */
    public static function database()
    {
        return static::getInstance()->db();
    }

    /**
     * Magic method for getting services from the container
     */
    public function __get($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }

        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new Exception("Property {$name} not found");
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

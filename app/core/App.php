<?php

namespace App\Core;

use Exception;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Routing\Router;
use App\Core\Database\Database;
use App\Core\Session\SessionManager;

class App {
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
     * Create a new application instance
     */
    public function __construct($basePath = null) {
        if ($basePath) {
            $this->setBasePath($basePath);
        }
        
        $this->bootstrap();
        
        self::$instance = $this;
    }
    
    /**
     * Bootstrap the application
     */
    protected function bootstrap() {
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
    public function setBasePath($path) {
        $this->basePath = rtrim($path, '\\/');
        return $this;
    }
    
    /**
     * Get the base path of the application
     */
    public function basePath($path = '') {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : '');
    }
    
    /**
     * Load application configuration
     */
    protected function loadConfig() {
        $configDir = $this->basePath('config');
        
        if (!is_dir($configDir)) {
            throw new Exception('Config directory not found');
        }
        
        // Load each PHP file in the config directory
        foreach (glob($configDir . '/*.php') as $configFile) {
            $key = basename($configFile, '.php');
            $this->config[$key] = require $configFile;
        }
    }
    
    /**
     * Set error reporting based on environment
     */
    protected function setErrorReporting() {
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
    protected function initializeServices() {
        // Initialize session
        $this->session = new SessionManager();
        $this->session->start();
        
        // Initialize request and response
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        
        // Initialize database connection
        $this->initializeDatabase();
        
        // Initialize router
        $this->router = new Router($this);
    }
    
    /**
     * Initialize database connection
     */
    protected function initializeDatabase() {
        $config = $this->config('database');
        
        if ($config && isset($config['connections'][$config['default']])) {
            $connection = $config['connections'][$config['default']];
            $this->db = new Database($connection);
        }
    }
    
    /**
     * Load application routes
     */
    protected function loadRoutes() {
        $routesFile = $this->basePath('routes/web.php');
        
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }
    
    /**
     * Run the application
     */
    public function run() {
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
    protected function handleException($e) {
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
    protected function renderException($e) {
        $this->response->setStatusCode(500);
        
        echo '<h1>Error: ' . $e->getMessage() . '</h1>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
    
    /**
     * Get a configuration value
     */
    public function config($key = null, $default = null) {
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
    public function router() {
        return $this->router;
    }
    
    /**
     * Get the request instance
     */
    public function request() {
        return $this->request;
    }
    
    /**
     * Get the response instance
     */
    public function response() {
        return $this->response;
    }
    
    /**
     * Get the database connection
     */
    public function db() {
        return $this->db;
    }
    
    /**
     * Get the session manager
     */
    public function session() {
        return $this->session;
    }
    
    /**
     * Get the application instance
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        
        return static::$instance;
    }
    
    /**
     * Get the database connection (static accessor)
     */
    public static function database() {
        return static::getInstance()->db();
    }
    
    /**
     * Magic method for getting services from the container
     */
    public function __get($name) {
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
    public function __isset($name) {
        return isset($this->container[$name]) || method_exists($this, 'get' . ucfirst($name));
    }
    
    /**
     * Register a service in the container
     */
    public function register($name, $value) {
        $this->container[$name] = $value;
        return $this;
    }
    
    /**
     * Get a service from the container
     */
    public function make($name, array $parameters = []) {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }
        
        throw new Exception("Service {$name} not found");
    }
    
    /**
     * Handle dynamic static method calls
     */
    public static function __callStatic($method, $parameters) {
        return static::getInstance()->$method(...$parameters);
    }
}

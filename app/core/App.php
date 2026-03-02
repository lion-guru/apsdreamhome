<?php

namespace App\Core;

/**
 * APS Dream Home Application Class
 * Main application bootstrap and routing
 */
class App
{
    private static $instance = null;
    private $basePath;
    private $config = [];
    private $request;
    private $response;
    private $session;
    private $db;
    private $router;
    
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath ?: dirname(__DIR__, 2);
        $this->loadConfig();
        
        // Initialize session
        $this->session = new \stdClass();
        $this->session->isStarted = function() {
            return session_status() !== PHP_SESSION_NONE;
        };
    }
    
    public static function getInstance($basePath = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }
    
    private function loadConfig()
    {
        // Load configuration
        $configFile = $this->basePath . "/config/database.php";
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }
    
    public function run()
    {
        try {
            // Handle request
            return $this->handleRequest();
            
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    public function handle()
    {
        return $this->run();
    }
    
    private function handleRequest()
    {
        // Simple routing
        $uri = $_SERVER["REQUEST_URI"] ?? "/";
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        
        // Check if this is an API request
        if (strpos($uri, '/api') === 0) {
            return $this->handleApiRequest($uri, $method);
        }
        
        // Route to appropriate controller
        return $this->route($uri, $method);
    }
    
    private function handleApiRequest($uri, $method)
    {
        // Load API routes
        require_once $this->basePath . '/routes/api.php';
        
        // Simple API routing - parse URI to get endpoint
        $path = parse_url($uri, PHP_URL_PATH);
        $endpoint = $path['path'] ?? '';
        
        // Remove /api prefix
        $endpoint = str_replace('/api', '', $endpoint);
        
        // Basic API routing
        switch ($endpoint) {
            case '/health':
                header('Content-Type: application/json');
                echo json_encode(['status' => 'ok', 'message' => 'API is running']);
                exit;
                
            case '/properties':
                return $this->loadController("Api\\PropertyController", "index");
                
            case '/leads':
                return $this->loadController("Api\\LeadController", "index");
                
            default:
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Endpoint not found']);
                exit;
        }
    }
    
    private function route($uri, $method)
    {
        // Parse URI to get clean path
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        
        // Basic routing logic
        if ($uri === "" || $uri === "/") {
            return $this->loadController("HomeController", "index");
        } elseif ($uri === "/home") {
            return $this->loadController("HomeController", "index");
        } elseif ($uri === "/about") {
            return $this->loadController("PageController", "about");
        }
        
        // Authentication routes
        elseif ($uri === "/login") {
            return $this->loadController("Public\\AuthController", "login");
        } elseif ($uri === "/login/process") {
            return $this->loadController("Public\\AuthController", "processLogin");
        } elseif ($uri === "/register") {
            return $this->loadController("Public\\AuthController", "register");
        } elseif ($uri === "/register/process") {
            return $this->loadController("Public\\AuthController", "processRegister");
        } elseif ($uri === "/logout") {
            return $this->loadController("Public\\AuthController", "logout");
        }
        
        // User routes
        elseif ($uri === "/dashboard") {
            return $this->loadController("User\\DashboardController", "index");
        }
        
        // Admin routes
        elseif ($uri === "/admin") {
            return $this->loadController("Admin\\AdminController", "index");
        } elseif ($uri === "/admin/dashboard") {
            return $this->loadController("Admin\\AdminDashboardController", "index");
        } elseif ($uri === "/admin/projects") {
            return $this->loadController("Admin\\ProjectController", "index");
        } elseif ($uri === "/admin/properties") {
            return $this->loadController("Admin\\PropertyController", "index");
        } elseif ($uri === "/admin/users") {
            return $this->loadController("Admin\\UserController", "index");
        } elseif ($uri === "/admin/leads") {
            return $this->loadController("Admin\\LeadController", "index");
        } elseif ($uri === "/admin/customers") {
            return $this->loadController("Admin\\CustomerController", "index");
        }
        
        // Property routes
        elseif ($uri === "/properties") {
            return $this->loadController("HomeController", "properties");
        } elseif ($uri === "/projects") {
            return $this->loadController("HomeController", "projects");
        } elseif ($uri === "/contact") {
            return $this->loadController("HomeController", "contact");
        }
        
        // Agent routes
        elseif ($uri === "/agent") {
            return $this->loadController("AgentController", "index");
        } elseif ($uri === "/agent/dashboard") {
            return $this->loadController("Agent\\AgentDashboardController", "index");
        }
        
        // API routes handled separately in handleApiRequest
        else {
            // Default to home
            return $this->loadController("HomeController", "index");
        }
    }
    
    private function loadController($controller, $method)
    {
        $controllerClass = "App\\Http\\Controllers\\" . $controller;
        
        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $method)) {
                ob_start();
                $controllerInstance->$method();
                return ob_get_clean();
            } else {
                return "Method " . $method . " not found in " . $controllerClass;
            }
        } else {
            return "Controller " . $controllerClass . " not found";
        }
    }
    
    private function handleError($exception)
    {
        error_log("Application Error: " . $exception->getMessage());
        return "<h1>Application Error</h1><p>An error occurred. Please try again later.</p>";
    }
    
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }
    
    public function request()
    {
        static $request = null;
        if ($request === null) {
            $request = new \stdClass();
            $request->uri = $_SERVER["REQUEST_URI"] ?? "/";
            $request->method = $_SERVER["REQUEST_METHOD"] ?? "GET";
            $request->get = $_GET;
            $request->post = $_POST;
        }
        return $request;
    }
    
    public function response()
    {
        static $response = null;
        if ($response === null) {
            $response = new \stdClass();
            $response->status = 200;
            $response->headers = [];
            $response->content = "";
        }
        return $response;
    }
    
    public function session()
    {
        static $session = null;
        if ($session === null) {
            $session = new class {
                public $started;
                
                public function __construct() {
                    $this->started = session_status() === PHP_SESSION_ACTIVE;
                }
                
                public function isStarted() {
                    return $this->started;
                }
                
                public function start() {
                    if (session_status() === PHP_SESSION_NONE) {
                        @session_start();
                        $this->started = true;
                    }
                    return $this;
                }
                
                public function get($key, $default = null) {
                    return $_SESSION[$key] ?? $default;
                }
                
                public function set($key, $value) {
                    $_SESSION[$key] = $value;
                    return $this;
                }
                
                public function has($key) {
                    return isset($_SESSION[$key]);
                }
                
                public function flash($key, $value) {
                    $_SESSION['_flash'][$key] = $value;
                    return $this;
                }
                
                public function remove($key) {
                    unset($_SESSION[$key]);
                    return $this;
                }
            };
        }
        return $session;
    }
    
    public function db()
    {
        static $db = null;
        if ($db === null) {
            try {
                $db = new \stdClass();
                $db->connected = true;
                $db->connection = "database_connection";
            } catch (\Exception $e) {
                $db = new \stdClass();
                $db->connected = false;
                $db->error = $e->getMessage();
            }
        }
        return $db;
    }
    
    public function auth()
    {
        static $auth = null;
        if ($auth === null) {
            $auth = new \stdClass();
            $auth->user = null;
            $auth->authenticated = false;
        }
        return $auth;
    }
    
    public function router()
    {
        return $this->router;
    }
    
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : '');
    }
}

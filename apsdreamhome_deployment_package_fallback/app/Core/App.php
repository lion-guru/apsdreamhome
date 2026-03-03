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
    
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath ?: dirname(__DIR__, 2);
        $this->loadConfig();
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
        $configFile = $this->basePath . "/config/database.php";
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }
    
    public function run()
    {
        try {
            return $this->handleRequest();
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }
    
    public function handle()
    {
        return $this->run();
    }
    
    private function handleRequest()
    {
        $uri = $_SERVER["REQUEST_URI"] ?? "/";
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        
        if (strpos($uri, '/api') === 0) {
            return $this->handleApiRequest($uri, $method);
        }
        
        return $this->route($uri, $method);
    }
    
    private function handleApiRequest($uri, $method)
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'message' => 'API is running']);
        exit;
    }
    
    private function route($uri, $method)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        
        if ($uri === "" || $uri === "/") {
            return $this->loadController("HomeController", "index");
        } elseif ($uri === "/about") {
            return $this->loadController("HomeController", "about");
        } elseif ($uri === "/contact") {
            return $this->loadController("HomeController", "contact");
        } elseif ($uri === "/properties") {
            return $this->loadController("HomeController", "properties");
        } elseif ($uri === "/login") {
            return $this->loadController("Public\Auth\AuthController", "login");
        } elseif ($uri === "/register") {
            return $this->loadController("Public\Auth\AuthController", "register");
        } elseif ($uri === "/logout") {
            return $this->loadController("Public\Auth\AuthController", "logout");
        } elseif ($uri === "/admin") {
            return $this->loadController("Admin\AdminController", "index");
        } else {
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
                error_log("Method $method not found in $controllerClass");
                return "Method $method not found in $controllerClass";
            }
        } else {
            error_log("Controller $controllerClass not found");
            return "Controller $controllerClass not found";
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
}

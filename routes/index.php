<?php
/**
 * APS Dream Home - Router
 * Handle all routing logic
 */

class Router {
    private $routes = [];
    
    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function put($path, $handler) {
        $this->routes['PUT'][$path] = $handler;
    }
    
    public function delete($path, $handler) {
        $this->routes['DELETE'][$path] = $handler;
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path
        $basePath = '/apsdreamhome';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        if (!isset($this->routes[$method][$uri])) {
            // Handle 404
            http_response_code(404);
            echo "Page not found";
            return;
        }
        
        $handler = $this->routes[$method][$uri];
        
        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_string($handler)) {
            // Parse "Controller@method" format
            list($controller, $method) = explode('@', $handler);
            $controllerFile = __DIR__ . '/app/Controllers/' . $controller . '.php';
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerInstance = new $controller();
                $controllerInstance->$method();
            } else {
                echo "Controller not found: $controller";
            }
        }
    }
}

// Load routes
require_once __DIR__ . '/routes/web.php';
require_once __DIR__ . '/routes/api.php';

$router = new Router();
$router->dispatch();
?>
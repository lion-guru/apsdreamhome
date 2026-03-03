<?php
/**
 * Router Class
 * 
 * Handles URL routing and controller dispatching
 */

namespace App\Core;

class Router {
    private $routes = [];
    private $params = [];
    
    public function __construct() {
        // Add default routes
        $this->add('', 'HomeController', 'index');
        $this->add('home', 'HomeController', 'index');
        $this->add('admin', 'AdminController', 'dashboard');
        $this->add('admin/dashboard', 'AdminController', 'dashboard');
        $this->add('admin/users', 'AdminController', 'users');
        $this->add('admin/properties', 'AdminController', 'properties');
        $this->add('admin/keys', 'AdminController', 'keys');
    }
    
    /**
     * Add a route
     */
    public function add($route, $controller, $action, $method = 'GET') {
        $this->routes[$route] = [
            'controller' => $controller,
            'action' => $action,
            'method' => $method
        ];
    }
    
    /**
     * Dispatch the route
     */
    public function dispatch($url) {
        $url = $this->removeQueryStringVariables($url);
        
        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $action = $this->params['action'];
            
            $controllerClass = 'App\\Controllers\\' . $controller;
            
            if (class_exists($controllerClass)) {
                $controllerObject = new $controllerClass();
                
                if (method_exists($controllerObject, $action)) {
                    $controllerObject->$action();
                    return;
                }
            }
        }
        
        // If no route found, show 404
        $this->show404();
    }
    
    /**
     * Match the URL to a route
     */
    private function match($url) {
        foreach ($this->routes as $route => $params) {
            if ($route === $url) {
                $this->params = $params;
                return true;
            }
        }
        return false;
    }
    
    /**
     * Remove query string variables from URL
     */
    private function removeQueryStringVariables($url) {
        if (strpos($url, '&') !== false) {
            $parts = explode('&', $url, 2);
            $url = $parts[0];
        }
        return $url;
    }
    
    /**
     * Show 404 page
     */
    private function show404() {
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h1 class="display-1 text-danger">404</h1>
                        <h2 class="mb-4">Page Not Found</h2>
                        <p class="lead">The page you are looking for does not exist.</p>
                        <a href="' . BASE_URL . '" class="btn btn-primary">Go to Homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    }
}

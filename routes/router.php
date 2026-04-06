<?php
/**
 * APS Dream Home - Router Class
 * Clean routing with consistent URI handling
 */
class Router
{
    public $routes = [];
    private $lastMethod;
    private $lastPath;

    public function __construct()
    {
        // Router initialized
    }

    public function get($path, $handler)
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute($method, $path, $handler)
    {
        // Store routes WITH leading slash for consistency
        if ($path !== '/' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middleware' => []
        ];

        $this->lastMethod = $method;
        $this->lastPath = $path;

        return $this;
    }

    public function middleware($middleware)
    {
        if ($this->lastMethod && $this->lastPath) {
            $this->routes[$this->lastMethod][$this->lastPath]['middleware'][] = $middleware;
        }
        return $this;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function dispatch($uri = null)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Step 1: Get URI
        if ($uri === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        }
        $uri = (string) $uri;

        // Step 2: Strip /apsdreamhome prefix if present
        if (strpos($uri, '/apsdreamhome') === 0) {
            $uri = substr($uri, strlen('/apsdreamhome'));
            if (empty($uri)) $uri = '/';
        }

        // Step 3: Normalize double slashes to single
        $uri = preg_replace('#/+#', '/', $uri);

        // Step 4: Remove .php extension
        $uri = preg_replace('/\.php$/', '', $uri);

        // Step 5: Ensure leading slash for non-root URIs
        if ($uri !== '/' && !str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        // Step 6: Route lookup
        $routeData = null;
        $params = [];

        if (isset($this->routes[$method][$uri])) {
            // Exact match found
            $routeData = $this->routes[$method][$uri];
        } else {
            // Try dynamic route matching
            $matchedRoute = $this->matchDynamicRoute($method, $uri);
            if ($matchedRoute) {
                $routeData = $matchedRoute['route_data'];
                $params = $matchedRoute['params'];
            }
        }

        // Step 7: Handle no match
        if ($routeData === null) {
            http_response_code(404);
            echo '<!DOCTYPE html><html><head><title>404 - Not Found</title>';
            echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
            echo '</head><body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">';
            echo '<div class="text-center"><h1 class="display-1 text-muted">404</h1>';
            echo '<p class="lead">Page not found</p>';
            echo '<p class="text-muted">' . htmlspecialchars($method . ' ' . $uri) . '</p>';
            echo '<a href="/" class="btn btn-primary mt-3">Go Home</a></div></body></html>';
            return;
        }

        // Step 8: Get handler
        $handler = is_array($routeData) ? $routeData['handler'] : $routeData;
        $middlewareList = is_array($routeData) ? ($routeData['middleware'] ?? []) : [];

        // Step 9: Execute middleware
        foreach ($middlewareList as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $middleware = new $middlewareClass();
                if (method_exists($middleware, 'handle')) {
                    $request = new \App\Core\Http\Request();
                    $middleware->handle($request, function ($req) {
                        return $req;
                    });
                }
            }
        }

        // Step 10: Execute handler
        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                list($controller, $handlerMethod) = explode('@', $handler);

                // Resolve controller class and file path
                if (strpos($controller, 'App\\') === 0) {
                    $controllerClass = $controller;
                    $relativePath = substr($controller, 4);
                    $controllerFile = __DIR__ . '/../app/' . str_replace('\\', '/', $relativePath) . '.php';
                } elseif (strpos($controller, 'Admin\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\Admin\\" . substr($controller, 6);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Admin/' . substr(str_replace('\\', '/', $controller), 6) . '.php';
                } elseif (strpos($controller, 'Front\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\Front\\" . substr($controller, 6);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Front/' . substr(str_replace('\\', '/', $controller), 6) . '.php';
                } elseif (strpos($controller, 'Auth\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\Auth\\" . substr($controller, 5);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Auth/' . substr(str_replace('\\', '/', $controller), 5) . '.php';
                } elseif (strpos($controller, 'MLM\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\MLM\\" . substr($controller, 4);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/MLM/' . substr(str_replace('\\', '/', $controller), 4) . '.php';
                } elseif (strpos($controller, 'AI\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\AI\\" . substr($controller, 3);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/AI/' . substr(str_replace('\\', '/', $controller), 3) . '.php';
                } elseif (strpos($controller, 'Employee\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\Employee\\" . substr($controller, 9);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Employee/' . substr(str_replace('\\', '/', $controller), 9) . '.php';
                } elseif (strpos($controller, 'Api\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\Api\\" . substr($controller, 4);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Api/' . substr(str_replace('\\', '/', $controller), 4) . '.php';
                } elseif (strpos($controller, 'Property\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\Property\\" . substr($controller, 9);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Property/' . substr(str_replace('\\', '/', $controller), 9) . '.php';
                } elseif (strpos($controller, 'Tech\\') === 0) {
                    $controllerClass = "App\\Http\\Controllers\\Tech\\" . substr($controller, 5);
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Tech/' . substr(str_replace('\\', '/', $controller), 5) . '.php';
                } else {
                    $controllerClass = "App\\Http\\Controllers\\" . $controller;
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/' . str_replace('\\', '/', $controller) . '.php';
                }

                // Debug logging
                error_log("Router: Looking for controller at: $controllerFile");
                error_log("Router: Controller class: $controllerClass");
                
                if (file_exists($controllerFile)) {
                    try {
                        // Load base controllers first
                        $baseController = __DIR__ . '/../app/Http/Controllers/BaseController.php';
                        $adminBaseController = __DIR__ . '/../app/Http/Controllers/AdminBaseController.php';
                        if (file_exists($baseController)) require_once $baseController;
                        if (file_exists($adminBaseController)) require_once $adminBaseController;

                        require_once $controllerFile;
                        $controllerInstance = new $controllerClass();

                        if (!empty($params)) {
                            call_user_func_array([$controllerInstance, $handlerMethod], $params);
                        } else {
                            $controllerInstance->$handlerMethod();
                        }
                    } catch (\Exception $e) {
                        error_log("Controller error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                        http_response_code(500);
                        echo '<h1>500 - Server Error</h1>';
                        if (ini_get('display_errors')) {
                            echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                        }
                    } catch (\Error $e) {
                        error_log("Controller fatal: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
                        http_response_code(500);
                        echo '<h1>500 - Fatal Error</h1>';
                        if (ini_get('display_errors')) {
                            echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                        }
                    }
                } else {
                    error_log("Controller file not found: $controllerFile");
                    http_response_code(500);
                    echo '<h1>500 - Controller Not Found</h1>';
                    echo '<p>' . htmlspecialchars($controllerClass) . '</p>';
                }
            } else {
                call_user_func($handler);
            }
        }
    }

    /**
     * Match dynamic routes with parameters
     */
    private function matchDynamicRoute($method, $uri)
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return [
                    'route_data' => $handler,
                    'params' => $matches
                ];
            }
        }

        return null;
    }
}

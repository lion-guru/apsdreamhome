<?php
/**
 * APS Dream Home - Project Optimizer
 * Deep scan and optimize the entire project structure
 */

echo "🔍 APS DREAM HOME - PROJECT DEEP SCAN\n";
echo "=====================================\n";

// 1. Scan all controllers
echo "📁 Scanning Controllers...\n";
$controllerDir = __DIR__ . '/app/Http/Controllers';
$controllers = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerDir));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = str_replace(__DIR__ . '/', '', $file->getPath());
        $controllers[] = $path;
    }
}

echo "✅ Found " . count($controllers) . " controllers\n";
echo "📋 Controller List:\n";
foreach ($controllers as $controller) {
    echo "  - $controller\n";
}

// 2. Scan all views
echo "\n🎨 Scanning Views...\n";
$viewDir = __DIR__ . '/app/views';
$views = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir));

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $path = str_replace(__DIR__ . '/', '', $file->getPath());
        $views[] = $path;
    }
}

echo "✅ Found " . count($views) . " view files\n";

// 3. Scan models
echo "\n🗄️ Scanning Models...\n";
$modelDir = __DIR__ . '/app/Models';
$models = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelDir));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = str_replace(__DIR__ . '/', '', $file->getPath());
        $models[] = $path;
    }
}

echo "✅ Found " . count($models) . " models\n";

// 4. Analyze current routing
echo "\n🛣️ Analyzing Current Routing...\n";
$appFile = __DIR__ . '/app/core/App.php';
$appContent = file_get_contents($appFile);

// Extract routes from App.php
preg_match_all('/elseif \(\$uri === "([^"]+)"/', $appContent, $matches);
$currentRoutes = $matches[1] ?? [];

echo "✅ Current routes: " . count($currentRoutes) . "\n";
foreach ($currentRoutes as $route) {
    echo "  - $route\n";
}

// 5. Generate optimized routing suggestions
echo "\n💡 OPTIMIZATION SUGGESTIONS:\n";
echo "=====================================\n";

// Suggest dynamic routing based on controllers
$suggestedRoutes = [];
foreach ($controllers as $controller) {
    $controllerName = basename($controller, '.php');
    
    // Skip API controllers for web routing
    if (strpos($controllerName, 'Api') === false) {
        // Extract controller name for routing
        if (strpos($controllerName, 'Admin') !== false) {
            $baseName = str_replace(['Admin', 'Controller'], '', $controllerName);
            $suggestedRoutes[] = "/admin/" . strtolower($baseName);
        } elseif (strpos($controllerName, 'Agent') !== false) {
            $baseName = str_replace(['Agent', 'Controller'], '', $controllerName);
            $suggestedRoutes[] = "/agent/" . strtolower($baseName);
        } elseif (strpos($controllerName, 'User') !== false) {
            $baseName = str_replace(['User', 'Controller'], '', $controllerName);
            $suggestedRoutes[] = "/" . strtolower($baseName);
        } elseif (strpos($controllerName, 'Public') !== false) {
            $baseName = str_replace(['Public', 'Controller'], '', $controllerName);
            $suggestedRoutes[] = "/" . strtolower($baseName);
        }
    }
}

// Add essential routes
$essentialRoutes = ['/', '/home', '/about', '/login', '/register', '/logout', '/dashboard', '/admin'];
$allSuggestedRoutes = array_merge($essentialRoutes, $suggestedRoutes);

echo "🎯 Suggested Routes (" . count($allSuggestedRoutes) . " total):\n";
foreach ($allSuggestedRoutes as $route) {
    echo "  - $route\n";
}

// 6. Generate optimized App.php structure
echo "\n🔧 GENERATING OPTIMIZED ROUTING...\n";

$optimizedAppContent = '<?php
namespace App\Core;

/**
 * APS Dream Home - Optimized Application Router
 * Dynamic routing system for better scalability
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
        $uri = $_SERVER["REQUEST_URI"] ?? "/";
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        
        // Check if this is an API request
        if (strpos($uri, \'/api\') === 0) {
            return $this->handleApiRequest($uri, $method);
        }
        
        // Clean URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, \'/\');
        
        // Dynamic routing
        return $this->routeDynamically($uri, $method);
    }
    
    private function routeDynamically($uri, $method)
    {
        // Route mappings
        $routes = [
            // Public routes
            \'\' => [\'HomeController\', \'index\'],
            \'/home\' => [\'HomeController\', \'index\'],
            \'/about\' => [\'PageController\', \'about\'],
            \'/login\' => [\'Public\\\\AuthController\', \'login\'],
            \'/login/process\' => [\'Public\\\\AuthController\', \'processLogin\'],
            \'/register\' => [\'Public\\\\AuthController\', \'register\'],
            \'/register/process\' => [\'Public\\\\AuthController\', \'processRegister\'],
            \'/logout\' => [\'Public\\\\AuthController\', \'logout\'],
            \'/dashboard\' => [\'User\\\\DashboardController\', \'index\'],
            \'/properties\' => [\'HomeController\', \'properties\'],
            \'/projects\' => [\'HomeController\', \'projects\'],
            \'/contact\' => [\'HomeController\', \'contact\'],
            
            // Admin routes
            \'/admin\' => [\'Admin\\\\AdminController\', \'index\'],
            \'/admin/dashboard\' => [\'Admin\\\\AdminDashboardController\', \'index\'],
            \'/admin/projects\' => [\'Admin\\\\ProjectController\', \'index\'],
            \'/admin/properties\' => [\'Admin\\\\PropertyController\', \'index\'],
            \'/admin/users\' => [\'Admin\\\\UserController\', \'index\'],
            \'/admin/leads\' => [\'Admin\\\\LeadController\', \'index\'],
            \'/admin/customers\' => [\'Admin\\\\CustomerController\', \'index\'],
            
            // Agent routes
            \'/agent\' => [\'AgentController\', \'index\'],
            \'/agent/dashboard\' => [\'Agent\\\\AgentDashboardController\', \'index\'],
        ];
        
        // Check for exact match
        if (isset($routes[$uri])) {
            return $this->loadController($routes[$uri][0], $routes[$uri][1]);
        }
        
        // Check for dynamic routes (ID-based)
        if (preg_match(\'/^\\/admin\\/projects\\/([^\\/]+)\//\', $uri, $matches)) {
            return $this->loadController(\'Admin\\\\ProjectController\', $matches[1]);
        }
        
        if (preg_match(\'/^\\/admin\\/properties\\/([^\\/]+)\//\', $uri, $matches)) {
            return $this->loadController(\'Admin\\\\PropertyController\', $matches[1]);
        }
        
        if (preg_match(\'/^\\/admin\\/users\\/([^\\/]+)\//\', $uri, $matches)) {
            return $this->loadController(\'Admin\\\\UserController\', $matches[1]);
        }
        
        if (preg_match(\'/^\\/properties\\/([^\\/]+)\//\', $uri, $matches)) {
            return $this->loadController(\'HomeController\', \'propertyDetail\');
        }
        
        // Default to home
        return $this->loadController(\'HomeController\', \'index\');
    }
    
    private function handleApiRequest($uri, $method)
    {
        // Load API routes
        require_once $this->basePath . \'/routes/api.php\';
        
        // Simple API routing
        $path = parse_url($uri, PHP_URL_PATH);
        $endpoint = str_replace(\'/api\', \'\', $path);
        
        switch ($endpoint) {
            case \'/health\':
                header(\'Content-Type: application/json\');
                echo json_encode([\'status\' => \'ok\', \'message\' => \'API is running\']);
                exit;
                
            case \'/properties\':
                return $this->loadController(\'Api\\\\PropertyController\', \'index\');
                
            case \'/leads\':
                return $this->loadController(\'Api\\\\LeadController\', \'index\');
                
            default:
                header(\'Content-Type: application/json\');
                echo json_encode([\'error\' => \'Endpoint not found\']);
                exit;
        }
    }
    
    private function loadController($controller, $method)
    {
        $controllerClass = "App\\\\Http\\\\Controllers\\\\" . $controller;
        
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
    
    // Additional methods for compatibility
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
                    $_SESSION[\'_flash\'][$key] = $value;
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
    
    public function basePath()
    {
        return $this->basePath;
    }
}
';

file_put_contents(__DIR__ . '/app/core/App_Optimized.php', $optimizedAppContent);

echo "✅ Generated optimized App.php\n";
echo "📁 Saved as: app/core/App_Optimized.php\n";

// 7. Generate project summary
echo "\n📊 PROJECT SUMMARY:\n";
echo "==================\n";
echo "Controllers: " . count($controllers) . "\n";
echo "Views: " . count($views) . "\n";
echo "Models: " . count($models) . "\n";
echo "Current Routes: " . count($currentRoutes) . "\n";
echo "Suggested Routes: " . count($allSuggestedRoutes) . "\n";

echo "\n🎯 NEXT STEPS:\n";
echo "1. Review app/core/App_Optimized.php\n";
echo "2. Test suggested routes\n";
echo "3. Implement missing controller methods\n";
echo "4. Optimize database queries\n";

echo "\n✅ PROJECT SCAN COMPLETE!\n";
?>

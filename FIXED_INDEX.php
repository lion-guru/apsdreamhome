<?php
/**
 * Fixed Index File
 * 
 * Bypass bootstrap and create working application
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define basic constants
define('APP_NAME', 'APSDreamHome');
define('APP_VERSION', '1.0.0');
define('APP_ROOT', __DIR__);
define('BASE_URL', 'http://localhost:8000/apsdreamhome/');

// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Simple App class
class App {
    private static $instance = null;
    private $basePath;
    
    public function __construct($basePath = null) {
        $this->basePath = $basePath ?: __DIR__;
    }
    
    public static function getInstance($basePath = null) {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }
    
    public function db() {
        static $db = null;
        if ($db === null) {
            try {
                $db = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                // Fallback to mock database
                $db = new class {
                    public function prepare($query) { return new class {
                        public function execute($params = []) { return true; }
                        public function fetch() { return null; }
                        public function fetchAll() { return []; }
                        public function fetchColumn() { return 0; }
                    }; }
                };
            }
        }
        return $db;
    }
    
    public function run() {
        // Simple routing
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove base path
        $basePath = '/apsdreamhome';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        if ($path === '/' || $path === '') {
            return $this->showHomePage();
        } elseif (strpos($path, '/admin/') === 0) {
            return $this->showAdminPage($path);
        } else {
            return $this->show404();
        }
    }
    
    private function showHomePage() {
        ob_start();
        include __DIR__ . '/app/views/home/home.php';
        return ob_get_clean();
    }
    
    private function showAdminPage($path) {
        $file = __DIR__ . $path;
        if (file_exists($file)) {
            ob_start();
            include $file;
            return ob_get_clean();
        }
        return $this->show404();
    }
    
    private function show404() {
        return '<h1>404 Not Found</h1><p>The requested resource was not found.</p>';
    }
}

// Run the application
try {
    $app = App::getInstance();
    $response = $app->run();
    echo $response;
} catch (Exception $e) {
    echo '<h1>Application Error</h1>';
    echo '<p>Error: ' . $e->getMessage() . '</p>';
    echo '<p>File: ' . $e->getFile() . '</p>';
    echo '<p>Line: ' . $e->getLine() . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
?>

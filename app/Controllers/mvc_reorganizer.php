<?php
/**
 * APS Dream Home - MVC Structure Reorganizer
 * Reorganize project into proper MVC structure
 */

class MVCOrganizer {
    private $projectRoot;
    private $publicDir;
    private $appDir;
    
    public function __construct() {
        $this->projectRoot = __DIR__;
        $this->publicDir = $this->projectRoot . '/public';
        $this->appDir = $this->projectRoot . '/app';
    }
    
    /**
     * Create proper MVC directory structure
     */
    public function createMVCStructure() {
        $directories = [
            'public' => [
                'css',
                'js',
                'images',
                'assets',
                'uploads'
            ],
            'app' => [
                'Controllers' => [
                    'Admin',
                    'User',
                    'Api'
                ],
                'Models' => [
                    'User',
                    'Property',
                    'Testimonial',
                    'Career',
                    'FAQ',
                    'Contact',
                    'Newsletter',
                    'Payment',
                    'Blog'
                ],
                'Views' => [
                    'admin' => [
                        'dashboard',
                        'properties',
                        'users',
                        'testimonials',
                        'careers',
                        'faqs',
                        'contacts',
                        'settings'
                    ],
                    'user' => [
                        'dashboard',
                        'profile',
                        'properties',
                        'payments'
                    ],
                    'auth' => [
                        'login',
                        'register',
                        'forgot'
                    ],
                    'layouts' => [
                        'app',
                        'admin',
                        'auth'
                    ],
                    'components' => [
                        'header',
                        'footer',
                        'sidebar',
                        'navbar'
                    ]
                ],
                'Middleware' => [
                    'Auth',
                    'Admin',
                    'Cors',
                    'RateLimit'
                ],
                'Services' => [
                    'Auth',
                    'Email',
                    'Payment',
                    'File',
                    'Database'
                ],
                'Helpers' => [
                    'URL',
                    'Form',
                    'Validation',
                    'Security'
                ],
                'Config' => [
                    'database',
                    'app',
                    'mail',
                    'cache'
                ]
            ],
            'storage' => [
                'logs',
                'cache',
                'sessions',
                'uploads',
                'backups'
            ],
            'vendor' => [],
            'config' => [],
            'routes' => [],
            'database' => [
                'migrations',
                'seeds',
                'factories'
            ],
            'tests' => [
                'Unit',
                'Feature',
                'Integration'
            ],
            'docs' => []
        ];
        
        $this->createDirectories($directories);
        echo "✅ MVC directory structure created\n";
    }
    
    /**
     * Create directories recursively
     */
    private function createDirectories($dirs, $basePath = '') {
        foreach ($dirs as $dir => $subdirs) {
            if (is_numeric($dir)) {
                $dir = $subdirs;
                $subdirs = [];
            }
            
            $fullPath = $basePath ? $basePath . '/' . $dir : $dir;
            
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                echo "Created directory: $fullPath\n";
            }
            
            if (!empty($subdirs) && is_array($subdirs)) {
                $this->createDirectories($subdirs, $fullPath);
            }
        }
    }
    
    /**
     * Move root PHP files to appropriate locations
     */
    public function organizeRootFiles() {
        $rootFiles = glob($this->projectRoot . '/*.php');
        
        foreach ($rootFiles as $file) {
            $filename = basename($file, '.php');
            
            // Skip important files
            if (in_array($filename, ['index', 'fix_syntax_errors'])) {
                continue;
            }
            
            // Categorize files
            if (strpos($filename, 'admin') !== false || strpos($filename, 'dashboard') !== false) {
                $targetDir = $this->appDir . '/Controllers/Admin';
            } elseif (strpos($filename, 'api') !== false) {
                $targetDir = $this->appDir . '/Controllers/Api';
            } elseif (strpos($filename, 'test') !== false || strpos($filename, 'debug') !== false) {
                $targetDir = $this->projectRoot . '/tests';
            } elseif (strpos($filename, 'setup') !== false || strpos($filename, 'create') !== false) {
                $targetDir = $this->projectRoot . '/database/migrations';
            } elseif (strpos($filename, 'config') !== false || strpos($filename, 'performance') !== false) {
                $targetDir = $this->projectRoot . '/config';
            } else {
                $targetDir = $this->appDir . '/Controllers';
            }
            
            $targetFile = $targetDir . '/' . basename($file);
            
            if (!file_exists($targetFile)) {
                rename($file, $targetFile);
                echo "Moved: $filename -> Controllers/" . basename($file) . "\n";
            }
        }
    }
    
    /**
     * Create proper router
     */
    public function createRouter() {
        $routerContent = '<?php
/**
 * APS Dream Home - Router
 * Handle all routing logic
 */

class Router {
    private $routes = [];
    
    public function get($path, $handler) {
        $this->routes[\'GET\'][$path] = $handler;
    }
    
    public function post($path, $handler) {
        $this->routes[\'POST\'][$path] = $handler;
    }
    
    public function put($path, $handler) {
        $this->routes[\'PUT\'][$path] = $handler;
    }
    
    public function delete($path, $handler) {
        $this->routes[\'DELETE\'][$path] = $handler;
    }
    
    public function dispatch() {
        $method = $_SERVER[\'REQUEST_METHOD\'];
        $uri = parse_url($_SERVER[\'REQUEST_URI\'], PHP_URL_PATH);
        
        // Remove base path
        $basePath = \'/apsdreamhome\';
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
            list($controller, $method) = explode(\'@\', $handler);
            $controllerFile = __DIR__ . \'/app/Controllers/\' . $controller . \'.php\';
            
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
require_once __DIR__ . \'/routes/web.php\';
require_once __DIR__ . \'/routes/api.php\';

$router = new Router();
$router->dispatch();
?>';
        
        file_put_contents($this->projectRoot . '/routes/index.php', $routerContent);
        echo "✅ Router created\n";
    }
    
    /**
     * Create route files
     */
    public function createRouteFiles() {
        // Web routes
        $webRoutes = '<?php
// Web Routes
$router->get(\'/\', \'HomeController@index\');
$router->get(\'/properties\', \'PropertyController@index\');
$router->get(\'/properties/{id}\', \'PropertyController@show\');
$router->get(\'/about\', \'PageController@about\');
$router->get(\'/contact\', \'PageController@contact\');
$router->get(\'/careers\', \'CareerController@index\');
$router->get(\'/testimonials\', \'TestimonialController@index\');
$router->get(\'/faq\', \'FAQController@index\');
$router->get(\'/team\', \'PageController@team\');

// Auth routes
$router->get(\'/login\', \'AuthController@login\');
$router->post(\'/login\', \'AuthController@authenticate\');
$router->get(\'/register\', \'AuthController@register\');
$router->post(\'/register\', \'AuthController@store\');
$router->get(\'/logout\', \'AuthController@logout\');

// Admin routes
$router->get(\'/admin\', \'AdminController@dashboard\');
$router->get(\'/admin/properties\', \'Admin\\PropertyController@index\');
$router->get(\'/admin/users\', \'Admin\\UserController@index\');
$router->get(\'/admin/dashboard\', \'AdminController@dashboard\');

// MCP routes
$router->get(\'/mcp_dashboard\', \'MCPController@dashboard\');
$router->get(\'/mcp_configuration_gui\', \'MCPController@configuration\');
$router->get(\'/import_mcp_config\', \'MCPController@import\');
?>';
        
        // API routes  
        $apiRoutes = '<?php
// API Routes
$router->get(\'/api/health\', \'ApiController@health\');
$router->get(\'/api/properties\', \'ApiController@properties\');
$router->post(\'/api/contact\', \'ApiController@contact\');
$router->post(\'/api/newsletter\', \'ApiController@newsletter\');
$router->post(\'/api/property-inquiry\', \'ApiController@propertyInquiry\');
?>';
        
        file_put_contents($this->projectRoot . '/routes/web.php', $webRoutes);
        file_put_contents($this->projectRoot . '/routes/api.php', $apiRoutes);
        echo "✅ Route files created\n";
    }
    
    /**
     * Create new index.php
     */
    public function createIndex() {
        $indexContent = '<?php
/**
 * APS Dream Home - Entry Point
 */

// Define constants
define(\'ROOT_PATH\', dirname(__DIR__));
define(\'APP_PATH\', ROOT_PATH . \'/app\');
define(\'CONFIG_PATH\', ROOT_PATH . \'/config\');
define(\'STORAGE_PATH\', ROOT_PATH . \'/storage\');

// Autoload
require_once ROOT_PATH . \'/vendor/autoload.php\';

// Load configuration
require_once CONFIG_PATH . \'/app.php\';

// Start session
session_start();

// Load router
require_once ROOT_PATH . \'/routes/index.php\';
?>';
        
        file_put_contents($this->projectRoot . '/public/index.php', $indexContent);
        echo "✅ New index.php created\n";
    }
    
    /**
     * Create .htaccess for public folder
     */
    public function createHtaccess() {
        $htaccessContent = '<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect to public folder
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Hide .htaccess
<Files .htaccess>
    Order allow,deny
    Deny from all
</Files>';
        
        file_put_contents($this->projectRoot . '/.htaccess', $htaccessContent);
        
        $publicHtaccess = '<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L]
</IfModule>';
        
        file_put_contents($this->projectRoot . '/public/.htaccess', $publicHtaccess);
        echo "✅ .htaccess files created\n";
    }
    
    /**
     * Run full reorganization
     */
    public function reorganize() {
        echo "🚀 Starting MVC reorganization...\n\n";
        
        $this->createMVCStructure();
        $this->organizeRootFiles();
        $this->createRouter();
        $this->createRouteFiles();
        $this->createIndex();
        $this->createHtaccess();
        
        echo "\n✅ MVC reorganization complete!\n";
        echo "📁 New structure:\n";
        echo "├── public/ (web root)\n";
        echo "├── app/ (application code)\n";
        echo "│   ├── Controllers/\n";
        echo "│   ├── Models/\n";
        echo "│   ├── Views/\n";
        echo "│   ├── Middleware/\n";
        echo "│   └── Services/\n";
        echo "├── config/ (configuration)\n";
        echo "├── routes/ (routing)\n";
        echo "├── storage/ (logs, cache, uploads)\n";
        echo "├── database/ (migrations, seeds)\n";
        echo "└── tests/ (test files)\n";
    }
}

// Run reorganization
$organizer = new MVCOrganizer();
$organizer->reorganize();
?>

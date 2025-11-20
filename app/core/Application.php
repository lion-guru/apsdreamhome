<?php
/**
 * APS Dream Home - Application Core Class
 * Main application instance and initialization
 */

namespace App\Core;

use App\Core\Router;
use App\Core\Database;
use App\Core\SessionManager;
use App\Core\ErrorHandler;
use Exception;

class Application
{
    private static $instance = null;
    private $config = [];
    private $router;
    private $database;
    private $session;
    private $errorHandler;
    private $initialized = false;

    /**
     * Get application instance (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Initialize the application
     */
    public function initialize()
    {
        if ($this->initialized) {
            return $this;
        }

        try {
            // Initialize core components
            $this->initializeErrorHandler();
            $this->initializeDatabase();
            $this->initializeSession();
            $this->initializeRouter();

            $this->initialized = true;
            return $this;

        } catch (Exception $e) {
            $this->handleInitializationError($e);
        }
    }

    /**
     * Load application configuration
     */
    private function loadConfiguration()
    {
        global $config;

        // Load default configuration
        $this->config = [
            'app' => [
                'name' => APP_NAME ?? 'APS Dream Home',
                'version' => APP_VERSION ?? '2.1',
                'environment' => ENVIRONMENT ?? 'development',
                'debug' => (ENVIRONMENT ?? 'development') === 'development',
                'url' => getenv('APP_URL') ?: 'http://localhost/apsdreamhome',
                'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kolkata',
            ],
            'database' => $config['database'] ?? [],
            'security' => $config['security'] ?? [],
            'mail' => $config['mail'] ?? [],
            'cache' => $config['cache'] ?? [],
        ];
    }

    /**
     * Initialize error handler
     */
    private function initializeErrorHandler()
    {
        $this->errorHandler = new ErrorHandler();
        $this->errorHandler->register();
    }

    /**
     * Initialize database connection
     */
    private function initializeDatabase()
    {
        $this->database = new Database($this->config['database']);
    }

    /**
     * Initialize session manager
     */
    private function initializeSession()
    {
        $this->session = new SessionManager($this->config['security']['session'] ?? []);
        $this->session->start();
    }

    /**
     * Initialize router
     */
    private function initializeRouter()
    {
        $this->router = new Router();
    }

    /**
     * Handle initialization errors
     */
    private function handleInitializationError(Exception $e)
    {
        if ($this->isDebug()) {
            die('Application initialization failed: ' . $e->getMessage());
        } else {
            error_log('Application initialization failed: ' . $e->getMessage());
            die('Service temporarily unavailable.');
        }
    }

    /**
     * Check if debug mode is enabled
     */
    public function isDebug()
    {
        return $this->config['app']['debug'] ?? false;
    }

    /**
     * Get application configuration
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Get router instance
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Get database instance
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get session manager instance
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Run the application
     */
    public function run()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $route = $_GET['route'] ?? 'home';

        try {
            $this->router->dispatch($route);
        } catch (Exception $e) {
            $this->handleRoutingError($e);
        }
    }

    /**
     * Handle routing errors
     */
    private function handleRoutingError(Exception $e)
    {
        error_log('Routing error: ' . $e->getMessage());

        if ($this->isDebug()) {
            echo 'Routing Error: ' . $e->getMessage();
        } else {
            http_response_code(404);
            $this->load404Page();
        }
    }

    /**
     * Load 404 page
     */
    private function load404Page()
    {
        $layoutPath = __DIR__ . '/../views/layouts/404.php';
        if (file_exists($layoutPath)) {
            require_once $layoutPath;
        } else {
            echo 'Page not found';
        }
    }

    /**
     * Get application version
     */
    public function getVersion()
    {
        return $this->config['app']['version'];
    }

    /**
     * Get application name
     */
    public function getName()
    {
        return $this->config['app']['name'];
    }

    /**
     * Check if application is in production
     */
    public function isProduction()
    {
        return $this->config['app']['environment'] === 'production';
    }

    /**
     * Check if application is in development
     */
    public function isDevelopment()
    {
        return $this->config['app']['environment'] === 'development';
    }
}

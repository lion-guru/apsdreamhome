<?php
namespace App\Core;

use PDO;
use Exception;
/**
 * APS Dream Home - Complete System Integration
 * Ensures all components work together properly
 */

class SystemIntegration {
    private static $suppressOutput = false;

    /**
     * Initialize complete system integration
     */
    public static function initialize() {
        self::$suppressOutput = true;

        // Load all models
        self::loadModels();

        // Initialize database connections
        self::initializeDatabase();

        // Setup session management
        self::setupSession();

        // Configure error handling
        self::configureErrorHandling();

        // Setup security
        self::setupSecurity();

        // Initialize caching
        self::initializeCache();

        // Setup logging
        self::setupLogging();

        // Initialize features
        self::initializeFeatures();

        self::$suppressOutput = false;

        return true;
    }

    /**
     * Load all model classes
     */
    private static function loadModels() {
        $modelFiles = [
            'User' => 'models/User.php',
            'Property' => 'models/Property.php',
            'Associate' => 'models/Associate.php',
            'Customer' => 'models/Customer.php',
            'Payment' => 'models/Payment.php',
            'Project' => 'models/Project.php',
            'Farmer' => 'models/Farmer.php',
            'CRMLead' => 'models/CRMLead.php',
            'AssociateMLM' => 'models/AssociateMLM.php',
            'PropertyFavorite' => 'models/PropertyFavorite.php',
            'PropertyInquiry' => 'models/PropertyInquiry.php',
            'Admin' => 'models/Admin.php',
            'Employee' => 'models/Employee.php',
            'AIChatbot' => 'models/AIChatbot.php',
            'ModelIntegration' => 'models/ModelIntegration.php'
        ];

        foreach ($modelFiles as $modelName => $filePath) {
            $fullPath = __DIR__ . '/../' . $filePath;
            if (file_exists($fullPath)) {
                require_once $fullPath;
            }
        }
    }

    /**
     * Initialize database connections and integrity
     */
    private static function initializeDatabase() {
        // Ensure database connection is available globally
        global $pdo;
        if (!$pdo) {
            try {
                $pdo = new \PDO(
                    'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';' .
                    'dbname=' . (getenv('DB_NAME') ?: 'apsdreamhome') . ';' .
                    'charset=utf8mb4',
                    getenv('DB_USER') ?: 'root',
                    getenv('DB_PASS') ?: ''
                );
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log('Database connection failed: ' . $e->getMessage());
            }
        }

        // Load ModelIntegration class
        require_once __DIR__ . '/../models/ModelIntegration.php';

        // Check database integrity
        if ($pdo) {
            \App\Models\ModelIntegration::ensureDatabaseIntegrity();
        }
    }

    /**
     * Setup session management
     */
    private static function setupSession() {
        // Set session security parameters before starting session
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set session timeout
        $timeout = 3600; // 1 hour
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $timeout) {
                session_unset();
                session_destroy();
                session_start();
            }
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Configure error handling
     */
    private static function configureErrorHandling() {
        // Set error reporting
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

        // Custom error handler
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if (!(error_reporting() & $errno)) {
                return;
            }

            $errorMessage = "Error [$errno] $errstr in $errfile:$errline";

            // Log error
            error_log($errorMessage);

            // Don't display errors in production or during initialization
            if (getenv('APP_ENV') === 'production' || self::$suppressOutput) {
                return;
            }

            // Show error in development
            echo "<div style='background: #f8d7da; color: #721c24; padding: 1rem; margin: 1rem; border-radius: 5px;'>";
            echo "<strong>Error:</strong> $errorMessage";
            echo "</div>";
        });

        // Custom exception handler
        set_exception_handler(function($exception) {
            error_log('Uncaught exception: ' . $exception->getMessage());

            if (getenv('APP_ENV') === 'production' || self::$suppressOutput) {
                if (!self::$suppressOutput) {
                    header('HTTP/1.0 500 Internal Server Error');
                    echo 'An error occurred. Please try again later.';
                }
                return;
            }

            echo "<div style='background: #f8d7da; color: #721c24; padding: 1rem; margin: 1rem; border-radius: 5px;'>";
            echo "<strong>Exception:</strong> " . $exception->getMessage();
            echo "<br><strong>File:</strong> " . $exception->getFile() . ":" . $exception->getLine();
            echo "</div>";
        });
    }

    /**
     * Setup security measures
     */
    private static function setupSecurity() {
        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Rate limiting setup
        self::setupRateLimiting();
    }

    /**
     * Setup rate limiting
     */
    private static function setupRateLimiting() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitFile = __DIR__ . '/../cache/rate_limit_' . md5($ip) . '.json';

        // Check rate limit
        if (file_exists($rateLimitFile)) {
            $rateData = json_decode(file_get_contents($rateLimitFile), true);

            if (isset($rateData['requests']) && isset($rateData['window_start'])) {
                $currentTime = time();
                $windowSize = 3600; // 1 hour
                $maxRequests = 1000; // Max requests per hour

                if (($currentTime - $rateData['window_start']) < $windowSize) {
                    if ($rateData['requests'] >= $maxRequests) {
                        http_response_code(429);
                        echo json_encode(['error' => 'Rate limit exceeded']);
                        exit;
                    }
                }
            }
        }

        // Update rate limit counter
        $rateData = [
            'requests' => ($rateData['requests'] ?? 0) + 1,
            'window_start' => time()
        ];
        file_put_contents($rateLimitFile, json_encode($rateData));
    }

    /**
     * Initialize caching system
     */
    private static function initializeCache() {
        $cacheDir = __DIR__ . '/../cache';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Clean old cache files (older than 24 hours)
        $files = glob($cacheDir . '/*.json');
        $expireTime = time() - (24 * 3600);

        foreach ($files as $file) {
            if (filemtime($file) < $expireTime) {
                unlink($file);
            }
        }
    }

    /**
     * Setup logging system
     */
    private static function setupLogging() {
        $logDir = __DIR__ . '/../logs';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Ensure log files exist
        $logFiles = ['error.log', 'access.log', 'activity.log'];
        foreach ($logFiles as $logFile) {
            $logPath = $logDir . '/' . $logFile;
            if (!file_exists($logPath)) {
                touch($logPath);
            }
        }
    }

    /**
     * Initialize all features
     */
    private static function initializeFeatures() {
        // Initialize AI features if enabled
        if (self::isFeatureEnabled('ai_chatbot')) {
            // AI chatbot will be initialized when needed
        }

        // Initialize payment gateway if enabled
        if (self::isFeatureEnabled('payment_gateway')) {
            // Payment gateway will be initialized when needed
        }

        // Initialize analytics if enabled
        if (self::isFeatureEnabled('analytics')) {
            // Analytics will be initialized when needed
        }
    }

    /**
     * Check if feature is enabled
     */
    private static function isFeatureEnabled($feature) {
        $enabledFeatures = [
            'mlm' => true,
            'ai_chatbot' => true,
            'payment_gateway' => true,
            'analytics' => true,
            'metaverse' => true,
            'quantum_computing' => true,
            'sustainability' => true,
            'blockchain' => true,
            'edge_computing' => true
        ];

        return isset($enabledFeatures[$feature]) && $enabledFeatures[$feature];
    }

    /**
     * Get system status
     */
    public static function getSystemStatus() {
        $status = [
            'database' => false,
            'models' => false,
            'controllers' => false,
            'views' => false,
            'routes' => false,
            'security' => false,
            'cache' => false,
            'features' => []
        ];

        // Check database
        global $pdo;
        $status['database'] = $pdo !== null;

        // Check models
        $status['models'] = class_exists('App\Models\User') &&
                           class_exists('App\Models\Property') &&
                           class_exists('App\Models\Associate');

        // Check controllers
        $status['controllers'] = file_exists(__DIR__ . '/../controllers/HomeControllerSimple.php') &&
                                file_exists(__DIR__ . '/../controllers/PropertyController.php');

        // Check views
        $status['views'] = is_dir(__DIR__ . '/../views/pages') &&
                          is_dir(__DIR__ . '/../views/layouts');

        // Check routes
        $status['routes'] = file_exists(__DIR__ . '/Router.php');

        // Check security
        $status['security'] = isset($_SESSION['csrf_token']);

        // Check cache
        $status['cache'] = is_dir(__DIR__ . '/../cache');

        // Check features
        $status['features'] = [
            'mlm' => self::isFeatureEnabled('mlm'),
            'ai_chatbot' => self::isFeatureEnabled('ai_chatbot'),
            'payment_gateway' => self::isFeatureEnabled('payment_gateway'),
            'analytics' => self::isFeatureEnabled('analytics'),
            'metaverse' => self::isFeatureEnabled('metaverse')
        ];

        return $status;
    }

    /**
     * Get system health check
     */
    public static function getHealthCheck() {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '2.0.0',
            'uptime' => shell_exec('uptime -p') ?: 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'database_connected' => false,
            'critical_issues' => []
        ];

        // Check database
        try {
            global $pdo;
            if ($pdo) {
                $pdo->query('SELECT 1');
                $health['database_connected'] = true;
            } else {
                $health['critical_issues'][] = 'Database connection failed';
                $health['status'] = 'unhealthy';
            }
        } catch (\Exception $e) {
            $health['critical_issues'][] = 'Database query failed: ' . $e->getMessage();
            $health['status'] = 'unhealthy';
        }

        // Check file permissions
        $criticalFiles = [
            __DIR__ . '/../config/bootstrap.php',
            __DIR__ . '/../controllers/BaseController.php',
            __DIR__ . '/Router.php'
        ];

        foreach ($criticalFiles as $file) {
            if (!file_exists($file)) {
                $health['critical_issues'][] = "Critical file missing: " . basename($file);
                $health['status'] = 'unhealthy';
            }
        }

        return $health;
    }
} 
 

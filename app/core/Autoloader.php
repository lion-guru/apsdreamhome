<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Core;

/**
 * Autoloader class
 * Handles dynamic loading of classes
 */
class Autoloader
{
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Registered namespaces
     */
    private $namespaces = [];

    /**
     * Class map for legacy classes
     */
    private $classMap = [];

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {}

    /**
     * Register autoloader
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Add a namespace mapping
     */
    public function addNamespace($prefix, $baseDir)
    {
        // Normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // Normalize base directory with trailing separator
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Initialize namespace array
        if (!isset($this->namespaces[$prefix])) {
            $this->namespaces[$prefix] = [];
        }

        // Add base directory
        $this->namespaces[$prefix][] = $baseDir;
    }

    /**
     * Add a class map
     */
    public function addClassMap($className, $path)
    {
        $this->classMap[$className] = $path;
    }

    /**
     * Load class
     */
    public function loadClass($className)
    {
        // error_log("Autoloader: Request to load class: " . $className);

        // Check class map first
        if (isset($this->classMap[$className])) {
            // error_log("Autoloader: Found in class map: " . $this->classMap[$className]);
            require_once $this->classMap[$className];
            return;
        }

        // Check legacy classes
        if (strpos($className, '\\') === false) {
            $this->loadLegacyClass($className);
            return;
        }

        // Load namespaced class
        $this->loadNamespacedClass($className);
    }

    /**
     * Load namespaced class
     */
    private function loadNamespacedClass($className)
    {
        // error_log("Autoloader: Attempting to load class: " . $className);
        foreach ($this->namespaces as $prefix => $baseDirs) {
            $len = strlen($prefix);
            if (strncmp($prefix, $className, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($className, $len);
            foreach ($baseDirs as $baseDir) {
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                // error_log("Autoloader: Checking file: " . $file);
                if (file_exists($file)) {
                    // error_log("Autoloader: Found file: " . $file);
                    require_once $file;
                    return;
                } else {
                    // error_log("Autoloader: File not found: " . $file);
                }
            }
        }

        // Fallback for non-namespaced or legacy structure
        $filePath = \APP_ROOT . '/app/' . strtolower(str_replace('\\', '/', $className)) . '.php';
        // error_log("Autoloader: Checking fallback: " . $filePath);
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }

    /**
     * Load legacy (non-namespaced) class
     */
    private function loadLegacyClass($className)
    {
        // Try common locations for legacy classes
        $possiblePaths = [
            \APP_ROOT . '/app/Http/Controllers/' . $className . '.php',
            \APP_ROOT . '/app/Models/' . $className . '.php',
            \APP_ROOT . '/app/core/' . $className . '.php',
            \APP_ROOT . '/includes/' . $className . '.php',
            \APP_ROOT . '/' . strtolower($className) . '.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    }
}

// Initialize autoloader
$autoloader = Autoloader::getInstance();
$autoloader->register();

// Register default namespaces - guard against missing APP_ROOT
if (defined('APP_ROOT')) {
    $autoloader->addNamespace('App', \APP_ROOT . '/app');

    // Register common class mappings for legacy compatibility
    $autoloader->addClassMap('Database', \APP_ROOT . '/app/Core/Database/Database.php');
    $autoloader->addClassMap('App\Core\Database\Database', \APP_ROOT . '/app/Core/Database/Database.php');
    $autoloader->addClassMap('SessionManager', \APP_ROOT . '/app/Core/Session/SessionManager.php');
    $autoloader->addClassMap('ErrorHandler', \APP_ROOT . '/app/Core/ErrorHandler.php');
    $autoloader->addClassMap('Security', \APP_ROOT . '/app/Core/Security.php');

    // Register consolidated models for seamless migration
    $autoloader->addClassMap('ConsolidatedUser', \APP_ROOT . '/app/Models/ConsolidatedUser.php');
    $autoloader->addClassMap('ConsolidatedProperty', \APP_ROOT . '/app/Models/ConsolidatedProperty.php');
    $autoloader->addClassMap('UnifiedModel', \APP_ROOT . '/app/Core/UnifiedModel.php');

    // Register root-namespace controllers
    $autoloader->addClassMap('AIAssistantController', \APP_ROOT . '/app/Http/Controllers/AI/AssistantController.php');
    $autoloader->addClassMap('App\Http\Controllers\AIAssistantController', \APP_ROOT . '/app/Http/Controllers/AI/AssistantController.php');

    // Register misnamed-file controllers
    $autoloader->addClassMap('App\Http\Controllers\Api\MonitorController', \APP_ROOT . '/app/Http/Controllers/Api/MonitorApiController.php');

    // Fix broken Api\* route references — routes/api.php references controllers
    // in the Api\ namespace that live at different file locations.
    $autoloader->addClassMap('App\Http\Controllers\Api\PropertyController', \APP_ROOT . '/app/Http/Controllers/PropertyController.php');
    $autoloader->addClassMap('App\Http\Controllers\Api\NotificationController', \APP_ROOT . '/app/Http/Controllers/NotificationController.php');
    $autoloader->addClassMap('App\Http\Controllers\Api\PaymentGatewayController', \APP_ROOT . '/app/Http/Controllers/Payment/PaymentGatewayController.php');
    $autoloader->addClassMap('App\Http\Controllers\Api\AnalyticsController', \APP_ROOT . '/app/Http/Controllers/Admin/AnalyticsController.php');
    $autoloader->addClassMap('App\Http\Controllers\Api\ReferralController', \APP_ROOT . '/app/Http/Controllers/Admin/ReferralController.php');
    $autoloader->addClassMap('App\Http\Controllers\Api\AuthController', \APP_ROOT . '/app/Http/Controllers/AuthController.php');

    // Register BaseAgent (class is in Agents/ directory but namespace says 'users')
    $autoloader->addClassMap('App\Services\AI\users\BaseAgent', \APP_ROOT . '/app/Services/AI/Agents/BaseAgent.php');
    $autoloader->addClassMap('App\Services\AI\users\AgentInterface', \APP_ROOT . '/app/Services/AI/Agents/AgentInterface.php');

    // Register legacy managers for backward compatibility
    $autoloader->addClassMap('Cache', \APP_ROOT . '/app/Core/Cache.php');
    $autoloader->addClassMap('RedisCache', \APP_ROOT . '/app/Core/RedisCache.php');
    $autoloader->addClassMap('UserManager', \APP_ROOT . '/includes/managers.php');
    $autoloader->addClassMap('PropertyManager', \APP_ROOT . '/includes/managers.php');
    $autoloader->addClassMap('ContactManager', \APP_ROOT . '/includes/managers.php');
    $autoloader->addClassMap('UploadValidator', \APP_ROOT . '/app/helpers/UploadValidator.php');

    // Alias the namespaced CacheService to the legacy global name
    // so any pre-namespace references (e.g. `CacheService::getProjects()`)
    // continue to work.
    if (PHP_VERSION_ID >= 80000 && !class_exists('CacheService', false)) {
        class_alias('App\Services\CacheService', 'CacheService');
    }
}

// Register legacy managers for backward compatibility
$autoloader->addClassMap('Cache', \APP_ROOT . '/app/Core/Cache.php');
$autoloader->addClassMap('RedisCache', \APP_ROOT . '/app/Core/RedisCache.php');
$autoloader->addClassMap('UserManager', \APP_ROOT . '/includes/managers.php');
$autoloader->addClassMap('PropertyManager', \APP_ROOT . '/includes/managers.php');
$autoloader->addClassMap('ContactManager', \APP_ROOT . '/includes/managers.php');
$autoloader->addClassMap('UploadValidator', \APP_ROOT . '/app/helpers/UploadValidator.php');

// Alias the namespaced CacheService to the legacy global name
// so any pre-namespace references (e.g. `CacheService::getProjects()`)
// continue to work.
if (PHP_VERSION_ID >= 80000 && !class_exists('CacheService', false)) {
    class_alias('App\\Services\\CacheService', 'CacheService');
}

// Ensure AppConfig is autoloaded from legacy config
// $autoloader->addClassMap('AppConfig', \APP_ROOT . '/includes/config.php');

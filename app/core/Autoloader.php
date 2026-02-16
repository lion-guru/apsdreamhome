<?php

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
        // Check class map first
        if (isset($this->classMap[$className])) {
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
        // Iterate through namespaces
        foreach ($this->namespaces as $prefix => $baseDirs) {
            // Check if class uses this namespace prefix
            $len = strlen($prefix);
            if (strncmp($prefix, $className, $len) !== 0) {
                continue;
            }

            // Get relative class name
            $relativeClass = substr($className, $len);

            // Try to load from base directories
            foreach ($baseDirs as $baseDir) {
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }

        // Default to App namespace structure
        $filePath = APP_ROOT . '/app/' . strtolower(str_replace('\\', '/', $className)) . '.php';
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
            APP_ROOT . '/app/controllers/' . $className . '.php',
            APP_ROOT . '/app/models/' . $className . '.php',
            APP_ROOT . '/app/core/' . $className . '.php',
            APP_ROOT . '/includes/' . $className . '.php',
            APP_ROOT . '/' . strtolower($className) . '.php',
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

// Register default namespaces
$autoloader->addNamespace('App', APP_ROOT . '/app');

// Register common class mappings for legacy compatibility
$autoloader->addClassMap('Database', APP_ROOT . '/app/core/Database.php');
$autoloader->addClassMap('SessionManager', APP_ROOT . '/app/core/SessionManager.php');
$autoloader->addClassMap('ErrorHandler', APP_ROOT . '/app/core/ErrorHandler.php');

// Register consolidated models for seamless migration
$autoloader->addClassMap('ConsolidatedUser', APP_ROOT . '/app/Models/ConsolidatedUser.php');
$autoloader->addClassMap('ConsolidatedProperty', APP_ROOT . '/app/Models/ConsolidatedProperty.php');
$autoloader->addClassMap('UnifiedModel', APP_ROOT . '/app/Core/UnifiedModel.php');

// Register legacy managers for backward compatibility
$autoloader->addClassMap('UserManager', APP_ROOT . '/includes/managers.php');
$autoloader->addClassMap('PropertyManager', APP_ROOT . '/includes/managers.php');
$autoloader->addClassMap('ContactManager', APP_ROOT . '/includes/managers.php');

// Ensure AppConfig is autoloaded from legacy config
$autoloader->addClassMap('AppConfig', APP_ROOT . '/includes/config.php');

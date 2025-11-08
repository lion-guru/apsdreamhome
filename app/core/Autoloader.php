<?php
/**
 * Autoloader Class
 * Handles automatic loading of PHP classes
 */

namespace App\Core;

class Autoloader {
    private static $instance = null;
    private $namespaces = [];
    private $classMap = [];

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register the autoloader
     */
    public function register() {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Add namespace mapping
     */
    public function addNamespace($namespace, $path) {
        $this->namespaces[$namespace] = rtrim($path, '/') . '/';
    }

    /**
     * Add class map entry
     */
    public function addClassMap($class, $file) {
        $this->classMap[$class] = $file;
    }

    /**
     * Load a class file
     */
    public function loadClass($className) {
        // Check class map first
        if (isset($this->classMap[$className])) {
            $file = $this->classMap[$className];
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }

        // Handle namespaced classes
        if (strpos($className, '\\') !== false) {
            $this->loadNamespacedClass($className);
            return;
        }

        // Handle legacy classes (non-namespaced)
        $this->loadLegacyClass($className);
    }

    /**
     * Load namespaced class
     */
    private function loadNamespacedClass($className) {
        $parts = explode('\\', $className);
        $namespace = implode('\\', array_slice($parts, 0, -1));
        $class = end($parts);

        // Remove 'App' from namespace for file path
        $namespaceParts = explode('\\', $namespace);
        if (isset($namespaceParts[0]) && $namespaceParts[0] === 'App') {
            array_shift($namespaceParts);
        }
        $relativeNamespace = implode('/', $namespaceParts);

        // Look for class in registered namespaces
        foreach ($this->namespaces as $registeredNamespace => $path) {
            if (strpos($namespace, $registeredNamespace) === 0) {
                $relativePath = str_replace($registeredNamespace, '', $namespace);
                $relativePath = str_replace('\\', '/', $relativePath);
                $filePath = $path . trim($relativePath, '/') . '/' . $class . '.php';

                if (file_exists($filePath)) {
                    require_once $filePath;
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
    private function loadLegacyClass($className) {
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

?>

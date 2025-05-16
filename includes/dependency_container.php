<?php
// Dependency Injection Container

class DependencyContainer {
    private static $instance = null;
    private $services = [];
    private $shared = [];

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Register a service
    public function register($name, $service, $shared = false) {
        $this->services[$name] = $service;
        $this->shared[$name] = $shared;
    }

    // Resolve a service
    public function resolve($name, $args = []) {
        if (!isset($this->services[$name])) {
            throw new Exception("Service {$name} is not registered");
        }

        // If it's a shared service and already instantiated, return the existing instance
        if (isset($this->shared[$name]) && $this->shared[$name] && isset($this->services[$name]['instance'])) {
            return $this->services[$name]['instance'];
        }

        $service = $this->services[$name];

        // If it's a callable, invoke it
        if (is_callable($service)) {
            $instance = call_user_func_array($service, $args);
        } 
        // If it's a class name, instantiate it
        elseif (is_string($service) && class_exists($service)) {
            $reflection = new ReflectionClass($service);
            $instance = $reflection->newInstanceArgs($args);
        } 
        else {
            $instance = $service;
        }

        // If it's a shared service, store the instance
        if (isset($this->shared[$name]) && $this->shared[$name]) {
            $this->services[$name]['instance'] = $instance;
        }

        return $instance;
    }

    // Check if a service is registered
    public function has($name) {
        return isset($this->services[$name]);
    }

    // Remove a service
    public function remove($name) {
        unset($this->services[$name]);
        unset($this->shared[$name]);
    }
}

// Global helper function
function container() {
    return DependencyContainer::getInstance();
}

// Pre-register some common services
$container = DependencyContainer::getInstance();

// Database Connection
$container->register('db_connection', function() {
    require_once __DIR__ . '/db_settings.php';
    return get_db_connection();
}, true);

// Performance Cache
$container->register('performance_cache', function() {
    require_once __DIR__ . '/performance_config.php';
    return PerformanceCache::getInstance();
}, true);

// Security Helper
$container->register('security_helper', function() {
    require_once __DIR__ . '/security/xss_protection.php';
    return new class {
        public function sanitizeInput($input, $type = 'string') {
            return sanitize_input($input, $type);
        }

        public function csrfToken() {
            return csrf_token();
        }

        public function csrfValidate() {
            return csrf_validate();
        }
    };
}, true);

// Logging Service
$container->register('logger', function() {
    return new class {
        public function log($message, $level = 'info') {
            $log_dir = __DIR__ . '/../logs';
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            $log_file = $log_dir . '/app_' . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $log_message = "[{$timestamp}] [{$level}] {$message}\n";
            
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }
    };
}, true);

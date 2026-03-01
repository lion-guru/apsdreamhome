<?php

namespace App\Services\Legacy;
// Dependency Injection Container

class DependencyContainer {
    private static $instance = null;
    private $services = [];
    private $shared = [];
    private $instances = [];

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
        // If it's a shared service and already instantiated, return the existing instance
        if (isset($this->shared[$name]) && $this->shared[$name] && isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->services[$name])) {
            throw new Exception("Service {$name} is not registered");
        }

        $service = $this->services[$name];

        // If it's a callable, invoke it
        if ($service instanceof Closure || is_callable($service)) {
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
            $this->instances[$name] = $instance;
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
    require_once __DIR__ . '/db_connection.php';
    return getDbConnection();
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

// Email Service
$container->register('email_service', function($container) {
    require_once __DIR__ . '/email_service.php';
    $logger = $container->resolve('logger');
    $db = $container->resolve('db_connection');
    return new EmailService($logger, $db);
}, true);

// SMS Service
$container->register('sms_service', function($container) {
    require_once __DIR__ . '/sms_service.php';
    $logger = $container->resolve('logger');
    $db = $container->resolve('db_connection');
    return new SMSService($logger, $db);
}, true);

// Notification Manager
$container->register('notification_manager', function($container) {
    require_once __DIR__ . '/notification_manager.php';
    $db = $container->resolve('db_connection');
    $email = $container->resolve('email_service');
    $sms = $container->resolve('sms_service');
    return new NotificationManager($db, $email, $sms);
}, true);

// Logging Service
$container->register('logger', function() {
    require_once __DIR__ . '/logger.php';
    return Logger::getInstance();
}, true);

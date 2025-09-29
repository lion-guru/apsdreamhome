<?php
/**
 * Service Container and Dependency Injection
 * Provides a robust mechanism for managing application services and dependencies
 */

// require_once __DIR__ . '/logger.php'; // already commented
require_once __DIR__ . '/config_manager.php';
require_once __DIR__ . '/performance_cache.php';
require_once __DIR__ . '/security_middleware.php';

class ServiceContainer implements ContainerInterface {
    // Advanced service container with dependency injection
    private const LIFECYCLE_TRANSIENT = 'transient';
    private const LIFECYCLE_SINGLETON = 'singleton';
    private const LIFECYCLE_SCOPED = 'scoped';

    // PSR-11 Container Interface compatibility
    private const AUTOWIRE_MARKER = '__autowire__';
    private const SINGLETON_MARKER = '__singleton__';
    private const FACTORY_MARKER = '__factory__';

    // Service storage and management
    /**
     * Dependency Injection Container with Advanced Features
     * - Autowiring
     * - Singleton Management
     * - Lazy Loading
     * - Circular Dependency Detection
     * - Service Decoration
     */
    private static $instance = null;
    private $services = [];
    private $resolvedServices = [];
    private $circularDependencyStack = [];
    private $serviceDefinitions = [];
    private $autowiredServices = [];
    private $serviceAliases = [];
    private $aliases = [];
    private $logger; // legacy, unused

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->logger = null;
        $this->registerCoreServices();
    }

    /**
     * Get singleton instance of ServiceContainer
     * 
     * @return ServiceContainer
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register core application services
     */
    private function registerCoreServices() {
        // Configuration Management
        $this->singleton('config', function() {
            return ConfigManager::getInstance();
        });

        // Logging
        // $this->singleton('logger', function() {
//     return new Logger();
// });

        // Performance Caching
        $this->singleton('cache', function() {
            return new PerformanceCache();
        });

        // Security Middleware
        $this->singleton('security', function() {
            return new SecurityMiddleware();
        });

        // Database Connection
        $this->singleton('database', function() {
            return new DatabaseConnection(
                $this->get('config')->get('DB_HOST'),
                $this->get('config')->get('DB_USER'),
                $this->get('config')->get('DB_PASS'),
                $this->get('config')->get('DB_NAME')
            );
        });
    }

    /**
     * Register a service
     * 
     * @param string $name Service name
     * @param callable $resolver Service resolver function
     * @param bool $singleton Whether to use singleton pattern
     */
    public function register($name, callable $resolver, $singleton = false) {
        $this->services[$name] = [
            'resolver' => $resolver,
            'singleton' => $singleton,
            'instance' => null
        ];
    }

    /**
     * Register a singleton service
     * 
     * @param string $name Service name
     * @param callable $resolver Service resolver function
     */
    public function singleton($name, callable $resolver) {
        $this->register($name, $resolver, true);
    }

    /**
     * Create an alias for a service
     * 
     * @param string $alias Alias name
     * @param string $original Original service name
     */
    public function alias($alias, $original) {
        $this->aliases[$alias] = $original;
    }

    /**
     * Resolve a service
     * 
     * @param string $name Service name
     * @return mixed Resolved service
     * @throws Exception If service not found
     */
    public function get($name) {
        // Check for alias
        $name = $this->aliases[$name] ?? $name;

        if (!isset($this->services[$name])) {
            // $this->logger->warning("Service not found", [...]);
            throw new Exception("Service '$name' is not registered");
        }

        $service = &$this->services[$name];

        // Return singleton instance if exists
        if ($service['singleton'] && $service['instance'] !== null) {
            return $service['instance'];
        }

        // Resolve service
        $instance = $service['resolver']($this);

        // Cache singleton instance
        if ($service['singleton']) {
            $service['instance'] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service is registered
     * 
     * @param string $name Service name
     * @return bool
     */
    public function has($name) {
        $name = $this->aliases[$name] ?? $name;
        return isset($this->services[$name]);
    }

    /**
     * Dependency Injection Container
     * 
     * @param string $className Class to instantiate
     * @param array $arguments Constructor arguments
     * @return object Instantiated object
     */
    public function make($className, $arguments = []) {
        try {
            $reflectionClass = new ReflectionClass($className);
            $constructor = $reflectionClass->getConstructor();

            if ($constructor === null) {
                return $reflectionClass->newInstance();
            }

            $constructorParams = $constructor->getParameters();
            $resolvedArguments = [];

            foreach ($constructorParams as $param) {
                $paramName = $param->getName();
                
                // Use provided arguments first
                if (isset($arguments[$paramName])) {
                    $resolvedArguments[] = $arguments[$paramName];
                    continue;
                }

                // Try to resolve from container
                $paramType = $param->getType();
                if ($paramType && !$paramType->isBuiltin()) {
                    try {
                        $resolvedArguments[] = $this->get($paramType->getName());
                        continue;
                    } catch (Exception $e) {
                        // Fallback to default value or throw
                        if ($param->isOptional()) {
                            $resolvedArguments[] = $param->getDefaultValue();
                        } else {
                            throw $e;
                        }
                    }
                }

                // Use default value if available
                if ($param->isOptional()) {
                    $resolvedArguments[] = $param->getDefaultValue();
                }
            }

            return $reflectionClass->newInstanceArgs($resolvedArguments);
        } catch (Exception $e) {
            // $this->logger->error('Dependency Injection Failed', [...]);
            throw $e;
        }
    }

    /**
     * Reset all services (useful for testing)
     */
    public function reset() {
        foreach ($this->services as &$service) {
            $service['instance'] = null;
        }
    }
}

// Global service container function
function services() {
    return ServiceContainer::getInstance();
}

// Convenience functions for core services
function config() {
    return services()->get('config');
}

// function logger() {
//     return services()->get('logger');
// }

function cache() {
    return services()->get('cache');
}

function security() {
    return services()->get('security');
}

// Example custom service registration
services()->register('mailer', function($container) {
    return new EmailService(
        $container->get('config')->get('SMTP_HOST'),
        $container->get('config')->get('SMTP_USER')
    );
});

// Alias example
services()->alias('db', 'database');

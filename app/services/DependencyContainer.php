<?php

namespace App\Services;

use App\Contracts\ContainerInterface;
use App\Contracts\ServiceNotFoundException;
use App\Contracts\ContainerException;
use Closure;
use ReflectionClass;
use ReflectionException;

/**
 * Modern Dependency Injection Container
 * PSR-11 compliant with enhanced features
 */
class DependencyContainer implements ContainerInterface
{
    private static ?self $instance = null;
    private array $services = [];
    private array $shared = [];
    private array $instances = [];
    private array $aliases = [];

    private function __construct()
    {
        $this->registerCoreServices();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     */
    public function get(string $id)
    {
        // Check aliases first
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        // If it's a shared service and already instantiated, return the existing instance
        if (isset($this->shared[$id]) && $this->shared[$id] && isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!$this->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        return $this->resolve($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->aliases[$id]);
    }

    /**
     * Register a service in the container.
     */
    public function register(string $id, $definition, bool $shared = false): self
    {
        $this->services[$id] = $definition;
        $this->shared[$id] = $shared;

        // Remove existing instance if re-registering
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }

        return $this;
    }

    /**
     * Register a singleton service.
     */
    public function singleton(string $id, $definition): self
    {
        return $this->register($id, $definition, true);
    }

    /**
     * Register an alias for a service.
     */
    public function alias(string $alias, string $id): self
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        $this->aliases[$alias] = $id;
        return $this;
    }

    /**
     * Resolve a service from the container.
     */
    public function resolve(string $id, array $args = [])
    {
        // Check aliases first
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        // If it's a shared service and already instantiated, return the existing instance
        if (isset($this->shared[$id]) && $this->shared[$id] && isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->services[$id])) {
            throw new ServiceNotFoundException($id);
        }

        $service = $this->services[$id];

        try {
            // If it's a closure, invoke it
            if ($service instanceof Closure) {
                $instance = $service($this, ...$args);
            }
            // If it's a class name, instantiate it with dependency injection
            elseif (is_string($service) && class_exists($service)) {
                $instance = $this->instantiateClass($service, $args);
            }
            // If it's already an instance, return it
            elseif (is_object($service)) {
                $instance = $service;
            }
            else {
                throw new ContainerException("Invalid service definition for '{$id}'");
            }

            // If it's a shared service, store the instance
            if (isset($this->shared[$id]) && $this->shared[$id]) {
                $this->instances[$id] = $instance;
            }

            return $instance;
        } catch (\Throwable $e) {
            throw new ContainerException("Error resolving service '{$id}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Instantiate a class with automatic dependency injection.
     */
    private function instantiateClass(string $className, array $args = []): object
    {
        try {
            $reflection = new ReflectionClass($className);

            if (!$reflection->isInstantiable()) {
                throw new ContainerException("Class '{$className}' is not instantiable");
            }

            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                // No constructor, just instantiate
                return new $className();
            }

            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $dependency = null;

                // Check if parameter is provided in args
                if (isset($args[$parameter->getName()])) {
                    $dependencies[] = $args[$parameter->getName()];
                    continue;
                }

                // Try to resolve from type hint
                $type = $parameter->getType();
                if ($type && !$type->isBuiltin()) {
                    $typeName = $type->getName();
                    if ($this->has($typeName)) {
                        $dependencies[] = $this->get($typeName);
                        continue;
                    }
                }

                // Check if parameter has a default value
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                // If parameter is optional, skip it
                if ($parameter->isOptional()) {
                    continue;
                }

                throw new ContainerException("Cannot resolve dependency '{$parameter->getName()}' for class '{$className}'");
            }

            return $reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException $e) {
            throw new ContainerException("Error instantiating class '{$className}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remove a service from the container.
     */
    public function remove(string $id): self
    {
        unset($this->services[$id]);
        unset($this->shared[$id]);
        unset($this->instances[$id]);

        // Remove aliases pointing to this service
        $this->aliases = array_filter($this->aliases, fn($alias) => $alias !== $id);

        return $this;
    }

    /**
     * Get all registered service identifiers.
     */
    public function getRegisteredServices(): array
    {
        return array_keys($this->services);
    }

    /**
     * Get all registered aliases.
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Clear all services from the container.
     */
    public function clear(): self
    {
        $this->services = [];
        $this->shared = [];
        $this->instances = [];
        $this->aliases = [];

        return $this;
    }

    /**
     * Register core services.
     */
    private function registerCoreServices(): void
    {
        // Register the container itself
        $this->singleton(self::class, fn() => $this);
        $this->alias(ContainerInterface::class, self::class);

        // Database connection
        $this->singleton('db', function() {
            return new \PDO(
                'mysql:host=localhost;dbname=apsdreamhome',
                'root',
                '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        });

        // Logger
        $this->singleton('logger', function() {
            return new class {
                public function info(string $message, array $context = []): void
                {
                    $logEntry = date('[Y-m-d H:i:s]') . ' INFO: ' . $message;
                    if (!empty($context)) {
                        $logEntry .= ' ' . json_encode($context);
                    }
                    error_log($logEntry);
                }

                public function error(string $message, array $context = []): void
                {
                    $logEntry = date('[Y-m-d H:i:s]') . ' ERROR: ' . $message;
                    if (!empty($context)) {
                        $logEntry .= ' ' . json_encode($context);
                    }
                    error_log($logEntry);
                }

                public function warning(string $message, array $context = []): void
                {
                    $logEntry = date('[Y-m-d H:i:s]') . ' WARNING: ' . $message;
                    if (!empty($context)) {
                        $logEntry .= ' ' . json_encode($context);
                    }
                    error_log($logEntry);
                }
            };
        });

        // Cache
        $this->singleton('cache', function() {
            return new class {
                private array $cache = [];

                public function get(string $key, $default = null)
                {
                    return $this->cache[$key] ?? $default;
                }

                public function set(string $key, $value, int $ttl = 3600): void
                {
                    $this->cache[$key] = $value;
                }

                public function has(string $key): bool
                {
                    return isset($this->cache[$key]);
                }

                public function forget(string $key): void
                {
                    unset($this->cache[$key]);
                }

                public function clear(): void
                {
                    $this->cache = [];
                }
            };
        });
    }

    /**
     * Call a method with automatic dependency injection.
     */
    public function call(callable $callback, array $parameters = [])
    {
        if (is_array($callback) && count($callback) === 2) {
            [$object, $method] = $callback;
            $reflection = new \ReflectionMethod($object, $method);
        } else {
            $reflection = new \ReflectionFunction($callback);
        }

        $args = [];
        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            
            if (isset($parameters[$name])) {
                $args[] = $parameters[$name];
            } elseif ($param->getType() && !$param->getType()->isBuiltin()) {
                $typeName = $param->getType()->getName();
                $args[] = $this->has($typeName) ? $this->get($typeName) : null;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        return $callback(...$args);
    }
}

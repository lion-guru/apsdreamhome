<?php

namespace App\Http\Controllers;

use App\Services\DependencyContainer;
use App\Contracts\ContainerInterface;
use App\Contracts\ServiceNotFoundException;
use App\Contracts\ContainerException;

/**
 * Controller for managing Dependency Injection Container
 */
class ContainerController extends BaseController
{
    private DependencyContainer $container;

    public function __construct(DependencyContainer $container = null)
    {
        parent::__construct();
        $this->container = $container ?: new DependencyContainer();
    }

    /**
     * Get all registered services
     */
    public function index()
    {
        try {
            $services = $this->container->getRegisteredServices();
            $aliases = $this->container->getAliases();

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'services' => $services,
                    'aliases' => $aliases,
                    'total_services' => count($services),
                    'total_aliases' => count($aliases)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve container information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new service
     */
    public function register()
    {
        try {
            $validated = $this->request->all();

            // Basic validation
            if (empty($validated['id']) || empty($validated['definition'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'ID and definition are required'
                ], 400);
            }

            $id = $validated['id'];
            $definition = $validated['definition'];
            $shared = $validated['shared'] ?? false;

            // Handle different definition types
            if (is_string($definition) && class_exists($definition)) {
                // Class name
                $this->container->register($id, $definition, $shared);
            } elseif (is_callable($definition)) {
                // Closure/callable
                $this->container->register($id, $definition, $shared);
            } elseif (is_object($definition)) {
                // Instance
                $this->container->register($id, $definition, $shared);
            } else {
                throw new \InvalidArgumentException('Invalid service definition');
            }

            // Register alias if provided
            if (!empty($validated['alias'])) {
                $this->container->alias($validated['alias'], $id);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => "Service '{$id}' registered successfully",
                'data' => [
                    'id' => $id,
                    'shared' => $shared,
                    'alias' => $validated['alias'] ?? null
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to register service',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get a specific service
     */
    public function bind(string $id)
    {
        try {
            $hasService = $this->container->has($id);

            if (!$hasService) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Service '{$id}' not found"
                ], 404);
            }

            // Note: We don't actually resolve the service here as it might be expensive
            // We just confirm it exists
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'id' => $id,
                    'registered' => true,
                    'resolvable' => true
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to check service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve and test a service
     */
    public function resolve($id)
    {
        try {
            $service = $this->container->get($id);

            return $this->jsonResponse([
                'success' => true,
                'message' => "Service '{$id}' resolved successfully",
                'data' => [
                    'id' => $id,
                    'class' => get_class($service),
                    'type' => gettype($service)
                ]
            ]);
        } catch (ServiceNotFoundException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (ContainerException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Container error',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to resolve service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a service
     */
    public function destroy(string $id)
    {
        try {
            $hasService = $this->container->has($id);

            if (!$hasService) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Service '{$id}' not found"
                ], 404);
            }

            $this->container->remove($id);

            return $this->jsonResponse([
                'success' => true,
                'message' => "Service '{$id}' removed successfully"
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to remove service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all services
     */
    public function clear()
    {
        try {
            $this->container->clear();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Container cleared successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to clear container',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test container functionality
     */
    public function check()
    {
        try {
            $results = [];

            // Test 1: Register and resolve a simple service
            $this->container->register('test_service', fn() => 'Hello World');
            $resolved = $this->container->get('test_service');
            $results['simple_resolution'] = $resolved === 'Hello World';

            // Test 2: Singleton service
            $this->container->singleton('test_singleton', fn() => new \stdClass());
            $instance1 = $this->container->get('test_singleton');
            $instance2 = $this->container->get('test_singleton');
            $results['singleton'] = $instance1 === $instance2;

            // Test 3: Alias functionality
            $this->container->register('test_aliased', fn() => 'Aliased Service');
            $this->container->alias('alias', 'test_aliased');
            $results['alias'] = $this->container->get('alias') === 'Aliased Service';

            // Test 4: Class resolution with dependency injection
            $this->container->register('test_dependency', fn() => 'Dependency');
            $this->container->register('test_class', function ($container) {
                return new class($container->get('test_dependency')) {
                    public function __construct(private $dependency) {}
                    public function getDependency()
                    {
                        return $this->dependency;
                    }
                };
            });
            $classInstance = $this->container->get('test_class');
            $results['dependency_injection'] = $classInstance->getDependency() === 'Dependency';

            // Cleanup test services
            $this->container->remove('test_service');
            $this->container->remove('test_singleton');
            $this->container->remove('test_aliased');
            $this->container->remove('test_class');

            $allPassed = array_reduce($results, fn($carry, $result) => $carry && $result, true);

            return $this->jsonResponse([
                'success' => true,
                'message' => $allPassed ? 'All tests passed' : 'Some tests failed',
                'data' => [
                    'tests' => $results,
                    'passed' => $allPassed
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Test execution failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get container statistics
     */
    public function stats()
    {
        try {
            $services = $this->container->getRegisteredServices();
            $aliases = $this->container->getAliases();

            $stats = [
                'total_services' => count($services),
                'total_aliases' => count($aliases),
                'services' => $services,
                'aliases' => $aliases,
                'container_class' => get_class($this->container),
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ];

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get container statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

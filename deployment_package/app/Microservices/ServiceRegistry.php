<?php
namespace App\Microservices;

use Exception;

class ServiceRegistry
{
    private $services = [];
    private $healthChecks = [];
    private $loadBalancers = [];
    
    /**
     * Register a service
     */
    public function register($serviceName, $serviceConfig)
    {
        $this->services[$serviceName] = [
            'name' => $serviceName,
            'host' => $serviceConfig['host'] ?? 'localhost',
            'port' => $serviceConfig['port'] ?? 8080,
            'protocol' => $serviceConfig['protocol'] ?? 'http',
            'health_check' => $serviceConfig['health_check'] ?? '/health',
            'instances' => $serviceConfig['instances'] ?? 1,
            'load_balancer' => $serviceConfig['load_balancer'] ?? 'round_robin',
            'timeout' => $serviceConfig['timeout'] ?? 30,
            'retry_attempts' => $serviceConfig['retry_attempts'] ?? 3,
            'circuit_breaker' => $serviceConfig['circuit_breaker'] ?? true,
            'registered_at' => time(),
            'status' => 'healthy'
        ];
        
        $this->initializeHealthCheck($serviceName);
        $this->initializeLoadBalancer($serviceName);
        
        return true;
    }
    
    /**
     * Get service URL
     */
    public function getServiceUrl($serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            throw new Exception("Service {$serviceName} not found");
        }
        
        $service = $this->services[$serviceName];
        
        if ($service['status'] !== 'healthy') {
            throw new Exception("Service {$serviceName} is not healthy");
        }
        
        $instance = $this->loadBalancers[$serviceName]->getInstance();
        
        return "{$service['protocol']}://{$instance['host']}:{$instance['port']}";
    }
    
    /**
     * Make service call
     */
    public function call($serviceName, $endpoint, $method = 'GET', $data = null, $headers = [])
    {
        $url = $this->getServiceUrl($serviceName) . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->services[$serviceName]['timeout']);
        
        // Set method
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        // Set headers
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Service call failed: {$error}");
        }
        
        if ($httpCode >= 500) {
            $this->handleServiceError($serviceName);
            throw new Exception("Service {$serviceName} returned HTTP {$httpCode}");
        }
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true) ?: $response
        ];
    }
    
    /**
     * Initialize health check
     */
    private function initializeHealthCheck($serviceName)
    {
        $this->healthChecks[$serviceName] = new HealthChecker(
            $this->services[$serviceName],
            $this
        );
    }
    
    /**
     * Initialize load balancer
     */
    private function initializeLoadBalancer($serviceName)
    {
        $type = $this->services[$serviceName]['load_balancer'];
        
        switch ($type) {
            case 'round_robin':
                $this->loadBalancers[$serviceName] = new RoundRobinLoadBalancer($serviceName);
                break;
            case 'least_connections':
                $this->loadBalancers[$serviceName] = new LeastConnectionsLoadBalancer($serviceName);
                break;
            case 'weighted':
                $this->loadBalancers[$serviceName] = new WeightedLoadBalancer($serviceName);
                break;
            default:
                $this->loadBalancers[$serviceName] = new RoundRobinLoadBalancer($serviceName);
        }
    }
    
    /**
     * Handle service error
     */
    private function handleServiceError($serviceName)
    {
        $this->services[$serviceName]['status'] = 'unhealthy';
        
        // Trigger circuit breaker if enabled
        if ($this->services[$serviceName]['circuit_breaker']) {
            $this->triggerCircuitBreaker($serviceName);
        }
    }
    
    /**
     * Trigger circuit breaker
     */
    private function triggerCircuitBreaker($serviceName)
    {
        // Mark service as unhealthy for a period
        $this->services[$serviceName]['status'] = 'circuit_breaker_open';
        
        // Schedule recovery check
        $this->scheduleRecoveryCheck($serviceName);
    }
    
    /**
     * Schedule recovery check
     */
    private function scheduleRecoveryCheck($serviceName)
    {
        // This would be implemented with a scheduler
        // For now, just mark as healthy after 60 seconds
        $this->services[$serviceName]['status'] = 'healthy';
    }
    
    /**
     * Get all services
     */
    public function getServices()
    {
        return $this->services;
    }
    
    /**
     * Get service health
     */
    public function getServiceHealth($serviceName)
    {
        if (!isset($this->healthChecks[$serviceName])) {
            return null;
        }
        
        return $this->healthChecks[$serviceName]->check();
    }
    
    /**
     * Get service metrics
     */
    public function getServiceMetrics($serviceName)
    {
        if (!isset($this->loadBalancers[$serviceName])) {
            return null;
        }
        
        return $this->loadBalancers[$serviceName]->getMetrics();
    }
}

/**
 * Health Checker Class
 */
class HealthChecker
{
    private $service;
    private $registry;
    
    public function __construct($service, $registry)
    {
        $this->service = $service;
        $this->registry = $registry;
    }
    
    public function check()
    {
        try {
            $url = $this->getServiceUrl() . $this->service['health_check'];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return [
                'status' => $httpCode === 200 ? 'healthy' : 'unhealthy',
                'response_time' => curl_getinfo($ch, CURLINFO_TOTAL_TIME),
                'timestamp' => time()
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'timestamp' => time()
            ];
        }
    }
    
    private function getServiceUrl()
    {
        return "{$this->service['protocol']}://{$this->service['host']}:{$this->service['port']}";
    }
}

/**
 * Round Robin Load Balancer
 */
class RoundRobinLoadBalancer
{
    private $serviceName;
    private $currentInstance = 0;
    private $instances = [];
    private $metrics = [
        'requests' => 0,
        'failures' => 0,
        'response_times' => []
    ];
    
    public function __construct($serviceName)
    {
        $this->serviceName = $serviceName;
        $this->initializeInstances();
    }
    
    public function getInstance()
    {
        $instance = $this->instances[$this->currentInstance];
        $this->currentInstance = ($this->currentInstance + 1) % count($this->instances);
        
        $this->metrics['requests']++;
        
        return $instance;
    }
    
    public function recordFailure($instance)
    {
        $this->metrics['failures']++;
    }
    
    public function recordResponseTime($time)
    {
        $this->metrics['response_times'][] = $time;
        
        // Keep only last 100 response times
        if (count($this->metrics['response_times']) > 100) {
            array_shift($this->metrics['response_times']);
        }
    }
    
    public function getMetrics()
    {
        $avgResponseTime = count($this->metrics['response_times']) > 0
            ? array_sum($this->metrics['response_times']) / count($this->metrics['response_times'])
            : 0;
        
        return [
            'requests' => $this->metrics['requests'],
            'failures' => $this->metrics['failures'],
            'success_rate' => $this->metrics['requests'] > 0
                ? (($this->metrics['requests'] - $this->metrics['failures']) / $this->metrics['requests']) * 100
                : 0,
            'avg_response_time' => $avgResponseTime
        ];
    }
    
    private function initializeInstances()
    {
        // This would be implemented based on service configuration
        // For now, create dummy instances
        for ($i = 0; $i < 3; $i++) {
            $this->instances[] = [
                'host' => 'localhost',
                'port' => 8080 + $i,
                'healthy' => true
            ];
        }
    }
}

/**
 * Least Connections Load Balancer
 */
class LeastConnectionsLoadBalancer
{
    private $instances = [];
    
    public function __construct($serviceName)
    {
        $this->initializeInstances();
    }
    
    public function getInstance()
    {
        $leastConnections = null;
        $minConnections = PHP_INT_MAX;
        
        foreach ($this->instances as $instance) {
            if ($instance['connections'] < $minConnections && $instance['healthy']) {
                $minConnections = $instance['connections'];
                $leastConnections = $instance;
            }
        }
        
        if ($leastConnections) {
            $leastConnections['connections']++;
        }
        
        return $leastConnections;
    }
    
    private function initializeInstances()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->instances[] = [
                'host' => 'localhost',
                'port' => 8080 + $i,
                'connections' => 0,
                'healthy' => true
            ];
        }
    }
}

/**
 * Weighted Load Balancer
 */
class WeightedLoadBalancer
{
    private $instances = [];
    
    public function __construct($serviceName)
    {
        $this->initializeInstances();
    }
    
    public function getInstance()
    {
        $totalWeight = 0;
        foreach ($this->instances as $instance) {
            if ($instance['healthy']) {
                $totalWeight += $instance['weight'];
            }
        }
        
        if ($totalWeight === 0) {
            return null;
        }
        
        $random = mt_rand(1, $totalWeight);
        $currentWeight = 0;
        
        foreach ($this->instances as $instance) {
            if (!$instance['healthy']) {
                continue;
            }
            
            $currentWeight += $instance['weight'];
            
            if ($random <= $currentWeight) {
                return $instance;
            }
        }
        
        return null;
    }
    
    private function initializeInstances()
    {
        $weights = [3, 2, 1]; // Different weights for instances
        
        for ($i = 0; $i < 3; $i++) {
            $this->instances[] = [
                'host' => 'localhost',
                'port' => 8080 + $i,
                'weight' => $weights[$i],
                'healthy' => true
            ];
        }
    }
}

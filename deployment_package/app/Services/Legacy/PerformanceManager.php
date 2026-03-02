<?php

namespace App\Services\Legacy;
// Advanced Performance Optimization Manager

require_once __DIR__ . '/dependency_container.php';
require_once __DIR__ . '/env_loader.php';

class PerformanceManager {
    // Cache Types
    const CACHE_TYPE_MEMORY = 'memory';
    const CACHE_TYPE_FILE = 'file';
    const CACHE_TYPE_REDIS = 'redis';

    // Cache Drivers
    private $cache_driver;

    // Performance Configuration
    private $config = [
        'cache_enabled' => true,
        'cache_driver' => self::CACHE_TYPE_REDIS,
        'cache_lifetime' => 3600, // 1 hour default
        'cache_path' => '',
        'compression_level' => 6,
        'profiling_enabled' => false,
        'max_memory_limit' => '512M', // Maximum allowed memory
        'slow_query_threshold' => 0.5, // Seconds
        'query_log_enabled' => true,
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'timeout' => 0.0,
            'persistent' => true
        ],
        'security_checks' => [
            'sql_injection_prevention' => true,
            'sensitive_data_masking' => true
        ]
    ];

    // Performance Metrics
    private $metrics = [
        'start_time' => 0,
        'end_time' => 0,
        'memory_usage' => 0,
        'query_count' => 0,
        'cached_queries' => 0
    ];

    // Dependencies
    private $logger;
    private $db;
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            $logger = null;
            $db = null;
            
            if (function_exists('container')) {
                try {
                    $container = container();
                    $logger = $container->resolve('logger');
                    $db = $container->resolve('db_connection');
                } catch (\Exception $e) {
                    // Fallback if container fails
                }
            }
            
            if (!$db) {
                try {
                    $db = \App\Core\App::database()->getConnection();
                } catch (\Exception $e) {
                    // Final fallback
                }
            }
            
            self::$instance = new self($logger, $db);
        }
        return self::$instance;
    }

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;

        // Set cache path
        $this->config['cache_path'] = __DIR__ . '/../cache/';

        // Auto-detect best available cache driver
        $this->autoDetectCacheDriver();

        // Ensure cache directory exists
        $this->ensureCacheDirectory();

        // Initialize cache driver
        $this->initializeCacheDriver();

        // Set memory limit
        ini_set('memory_limit', $this->config['max_memory_limit']);

        // Register shutdown function for memory monitoring
        register_shutdown_function([$this, 'checkMemoryUsage']);
    }

    /**
     * Safe logging helper
     */
    private function log($message, $level = 'info', $context = 'performance') {
        if ($this->logger) {
            $this->logger->log($message, $level, $context);
        } else {
            error_log("[PerformanceManager][$level][$context] $message");
        }
    }

    /**
     * Auto-detect best available cache driver
     */
    private function autoDetectCacheDriver() {
        if (class_exists('\\Redis')) {
            $this->config['cache_driver'] = self::CACHE_TYPE_REDIS;
        } else {
            $this->config['cache_driver'] = self::CACHE_TYPE_FILE;
        }
    }

    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectory() {
        if (!is_dir($this->config['cache_path'])) {
            mkdir($this->config['cache_path'], 0755, true);
        }
    }

    /**
     * Initialize cache driver
     */
    private function initializeCacheDriver() {
        switch ($this->config['cache_driver']) {
            case self::CACHE_TYPE_MEMORY:
                $this->cache_driver = new MemoryCacheDriver();
                break;
            case self::CACHE_TYPE_REDIS:
                $this->cache_driver = new RedisCacheDriver($this->config['redis']);
                // Fallback to file cache if Redis is not connected
                if (!$this->cache_driver->isConnected()) {
                    $this->log("Redis connection failed, falling back to file cache", 'warning', 'performance');
                    $this->cache_driver = new FileCacheDriver($this->config['cache_path']);
                }
                break;
            default:
                $this->cache_driver = new FileCacheDriver($this->config['cache_path']);
        }
    }

    /**
     * Start performance profiling
     */
    public function startProfiling() {
        if (!$this->config['profiling_enabled']) return;

        $this->metrics['start_time'] = microtime(true);
        $this->metrics['memory_usage'] = memory_get_usage();
    }

    /**
     * End performance profiling
     */
    public function endProfiling() {
        if (!$this->config['profiling_enabled']) return;

        $this->metrics['end_time'] = microtime(true);
        $this->metrics['memory_peak'] = memory_get_peak_usage();
        
        $this->logPerformanceMetrics();
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics() {
        $execution_time = $this->metrics['end_time'] - $this->metrics['start_time'];
        $memory_usage = $this->metrics['memory_peak'] - $this->metrics['memory_usage'];

        $this->log(
            sprintf(
                "Performance Metrics: Execution Time: %.4f sec, Memory Usage: %s, Queries: %d (Cached: %d)", 
                $execution_time, 
                $this->formatMemory($memory_usage),
                $this->metrics['query_count'],
                $this->metrics['cached_queries']
            ), 
            'info', 
            'performance'
        );
    }

    /**
     * Format memory usage
     * @param int $bytes Memory in bytes
     * @return string Formatted memory string
     */
    private function formatMemory($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * Convert human-readable memory size to bytes
     * @param string $size Memory size (e.g., '128M')
     * @return int Bytes
     */
    public function convertToBytes($size) {
        // Remove whitespace
        $size = trim($size);

        // Parse last character to determine unit
        $unit = strtolower(substr($size, -1));
        $value = (int)substr($size, 0, -1);

        switch ($unit) {
            case 'g': // Gigabytes
                return $value * 1024 * 1024 * 1024;
            case 'm': // Megabytes
                return $value * 1024 * 1024;
            case 'k': // Kilobytes
                return $value * 1024;
            default:  // Bytes or no unit
                return is_numeric($size) ? (int)$size : 0;
        }
    }

    /**
     * Cache query result
     * @param string $query SQL query
     * @param array $result Query result
     * @param int $lifetime Cache lifetime
     */
    public function cacheQueryResult($query, $result, $lifetime = null, $params = []) {
        if (!$this->config['cache_enabled']) return;

        $lifetime = (int)($lifetime ?? $this->config['cache_lifetime']);
        $cache_key = $this->generateCacheKey($query, $params);

        try {
            $serialized_data = serialize($result);
            $compressed_data = gzcompress($serialized_data, $this->config['compression_level']);
            
            $this->cache_driver->set($cache_key, $compressed_data, $lifetime);
        } catch (Exception $e) {
            $this->log(
                "Cache write error: " . $e->getMessage(), 
                'warning', 
                'performance'
            );
        }
    }

    /**
     * Retrieve cached query result
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array|null Cached result or null
     */
    public function getCachedQueryResult($query, $params = []) {
        if (!$this->config['cache_enabled']) return null;

        $cache_key = $this->generateCacheKey($query, $params);

        try {
            $cached_data = $this->cache_driver->get($cache_key);
            
            if ($cached_data === null) return null;

            $decompressed_data = gzuncompress($cached_data);
            $result = unserialize($decompressed_data);

            return $result;
        } catch (Exception $e) {
            $this->log(
                "Cache read error: " . $e->getMessage(), 
                'warning', 
                'performance'
            );
            return null;
        }
    }

    /**
     * Generate unique cache key for a query
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return string Cache key
     */
    private function generateCacheKey($query, $params = []) {
        $key_data = $query . serialize($params);
        return 'query_' . hash('sha256', $key_data);
    }

    /**
     * Prevent SQL Injection
     * @param string $query SQL query to validate
     * @throws Exception If potential SQL injection is detected
     */
    private function preventSqlInjection($query) {
        // Basic SQL injection detection
        $dangerous_patterns = [
            '/\b(DELETE|DROP|TRUNCATE|ALTER)\b/i',  // Destructive commands
            '/\b(UNION|SELECT)\s*\(/i',  // Potential subquery injection
            '/--/',  // Comment-based injection
            '/;/',   // Multiple statement injection
        ];

        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                $this->log(
                    "Potential SQL Injection Attempt: " . $this->maskSensitiveData($query), 
                    'security', 
                    'alert'
                );
                throw new Exception("Potential SQL Injection Detected");
            }
        }
    }

    /**
     * Mask sensitive data in query
     * @param string $query SQL query
     * @return string Masked query
     */
    private function maskSensitiveData($query) {
        // Mask email addresses
        $query = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', '***@***.***', $query);

        // Mask phone numbers
        $query = preg_replace('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', '***-***-****', $query);

        // Mask credit card numbers
        $query = preg_replace('/\b(?:\d{4}[-\s]?){3}\d{4}\b/', '**** **** **** ****', $query);

        return $query;
    }

    /**
     * Check memory usage on shutdown
     */
    public function checkMemoryUsage() {
        $memory_usage = memory_get_usage(true);
        $memory_limit = $this->convertToBytes(ini_get('memory_limit'));

        if ($memory_usage > $memory_limit * 0.9) { // 90% threshold
            $this->log(
                sprintf(
                    "High Memory Usage: %s / %s", 
                    $this->formatMemory($memory_usage), 
                    $this->formatMemory($memory_limit)
                ), 
                'warning', 
                'performance'
            );
        }
    }

    /**
     * Execute cached query
     * @param string $query SQL query
     * @param int $lifetime Cache lifetime
     * @return array Query results
     */
    /**
     * Compatibility wrapper for getCachedQuery (used in some dashboards)
     * @param string $query SQL query
     * @param array $params Query parameters (currently unused in this implementation)
     * @param int|null $lifetime Cache lifetime in seconds
     * @return array Query results
     */
    public function getCachedQuery($query, $params = [], $lifetime = null) {
        return $this->executeCachedQuery($query, $params, $lifetime);
    }

    public function executeCachedQuery($query, $params = [], $lifetime = null) {
        // Support legacy calls where the second parameter is lifetime
        if (!is_array($params) && is_numeric($params) && $lifetime === null) {
            $lifetime = $params;
            $params = [];
        }
        
        $this->metrics['query_count']++;

        // Security: Prevent SQL Injection (only for raw queries if no params)
        if (empty($params) && $this->config['security_checks']['sql_injection_prevention']) {
            $this->preventSqlInjection($query);
        }

        // Start query performance tracking
        $query_start_time = microtime(true);

        // Check cached result first
        $cached_result = $this->getCachedQueryResult($query, $params);
        if ($cached_result !== null) {
            $this->metrics['cached_queries']++;
            $this->log("Cache hit for query: $query", 'debug', 'performance');
            return $cached_result;
        }

        $this->log("Cache miss for query: $query", 'debug', 'performance');

        // Execute actual query using unified Database layer
        try {
            $db = \App\Core\App::database();
            $result = $db->fetchAll($query, $params);
        } catch (\Exception $e) {
            $this->log("Query Error: " . $e->getMessage(), 'error', 'performance');
            // If it's a stats query on a missing table, don't throw, just return empty
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return [];
            }
            throw $e;
        }

        // Calculate query execution time
        $query_execution_time = microtime(true) - $query_start_time;

        // Log slow queries
        if ($query_execution_time > $this->config['slow_query_threshold']) {
            $this->log(
                sprintf(
                    "Slow Query Detected: %.4f seconds\nQuery: %s", 
                    $query_execution_time, 
                    $this->maskSensitiveData($query)
                ), 
                'warning', 
                'performance'
            );
        }

        // Cache the result
        $this->cacheQueryResult($query, $result, $lifetime, $params);
        return $result;
    }

    /**
     * Clear cache
     * @param string|null $prefix Optional cache key prefix
     */
    public function clearCache($prefix = null) {
        $this->cache_driver->clear($prefix);
        
        $this->log(
            "Cache cleared" . ($prefix ? " (prefix: {$prefix})" : ""), 
            'info', 
            'performance'
        );
    }
}

// Abstract Cache Driver
abstract class CacheDriver {
    abstract public function set($key, $value, $lifetime);
    abstract public function get($key);
    abstract public function clear($prefix = null);
    public function isConnected() { return true; } // Default for file/memory
}

// File-based Cache Driver
class FileCacheDriver extends CacheDriver {
    private $cache_path;

    public function __construct($cache_path) {
        $this->cache_path = $cache_path;
    }

    public function isConnected() {
        return is_dir($this->cache_path) && is_writable($this->cache_path);
    }

    public function set($key, $value, $lifetime) {
        $file_path = $this->cache_path . $key;
        $cache_data = [
            'value' => $value,
            'expiry' => time() + $lifetime
        ];
        file_put_contents($file_path, serialize($cache_data));
    }

    public function get($key) {
        $file_path = $this->cache_path . $key;
        
        if (!file_exists($file_path)) return null;

        $cache_data = unserialize(file_get_contents($file_path));
        
        if (time() > $cache_data['expiry']) {
            unlink($file_path);
            return null;
        }

        return $cache_data['value'];
    }

    public function clear($prefix = null) {
        $files = glob($this->cache_path . ($prefix ? $prefix . '*' : '*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// Memory Cache Driver (Simple Implementation)
class MemoryCacheDriver extends CacheDriver {
    private $cache = [];

    public function set($key, $value, $lifetime) {
        $this->cache[$key] = [
            'value' => $value,
            'expiry' => time() + $lifetime
        ];
    }

    public function get($key) {
        if (!isset($this->cache[$key])) return null;

        $cache_item = $this->cache[$key];
        
        if (time() > $cache_item['expiry']) {
            unset($this->cache[$key]);
            return null;
        }

        return $cache_item['value'];
    }

    public function clear($prefix = null) {
        if ($prefix === null) {
            $this->cache = [];
        } else {
            foreach (array_keys($this->cache) as $key) {
                if (strpos($key, $prefix) === 0) {
                    unset($this->cache[$key]);
                }
            }
        }
    }
}

// Redis Cache Driver Implementation
class RedisCacheDriver extends CacheDriver {
    private $redis;
    private $connected = false;

    public function __construct($config = []) {
        if (class_exists('\\Redis')) {
            try {
                $redisClass = '\\Redis';
                $this->redis = new $redisClass();
                $host = $config['host'] ?? '127.0.0.1';
                $port = $config['port'] ?? 6379;
                $timeout = $config['timeout'] ?? 0.0;
                $persistent = $config['persistent'] ?? false;
                
                $connect_method = $persistent ? 'pconnect' : 'connect';
                
                if ($this->redis->$connect_method($host, $port, $timeout)) {
                    if (!empty($config['password'])) {
                        $this->redis->auth($config['password']);
                    }
                    $this->connected = true;
                }
            } catch (Exception $e) {
                // Redis connection failed, will fallback to null/default
                error_log("Redis Connection Error: " . $e->getMessage());
            }
        }
    }

    public function isConnected() {
        return $this->connected;
    }

    public function set($key, $value, $lifetime) {
        if (!$this->connected) return false;
        // The value is already serialized and compressed by PerformanceManager
        return $this->redis->setex($key, (int)$lifetime, $value);
    }

    public function get($key) {
        if (!$this->connected) return null;
        $value = $this->redis->get($key);
        // Return as is, PerformanceManager will decompress and unserialize
        return $value !== false ? $value : null;
    }

    public function clear($prefix = null) {
        if (!$this->connected) return;
        
        if ($prefix === null) {
            $this->redis->flushDB();
        } else {
            // More efficient way to delete by prefix using SCAN if available, 
            // but for simplicity using keys() here as it's a small app
            $keys = $this->redis->keys($prefix . '*');
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
        }
    }
}

// Helper function for dependency injection
function getPerformanceManager() {
    return PerformanceManager::getInstance();
}

return getPerformanceManager();


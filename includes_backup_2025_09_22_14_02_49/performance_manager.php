<?php
// Advanced Performance Optimization Manager

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
        'cache_driver' => self::CACHE_TYPE_FILE,
        'cache_lifetime' => 3600, // 1 hour default
        'cache_path' => '',
        'compression_level' => 6,
        'profiling_enabled' => false,
        'max_memory_limit' => '512M', // Maximum allowed memory
        'slow_query_threshold' => 0.5, // Seconds
        'query_log_enabled' => true,
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

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;

        // Set cache path
        $this->config['cache_path'] = __DIR__ . '/../cache/';

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
                $this->cache_driver = new RedisCacheDriver();
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

        $this->logger->log(
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
    public function cacheQueryResult($query, $result, $lifetime = null) {
        if (!$this->config['cache_enabled']) return;

        $lifetime = $lifetime ?? $this->config['cache_lifetime'];
        $cache_key = $this->generateCacheKey($query);

        try {
            $serialized_result = serialize($result);
            $compressed_result = gzcompress($serialized_result, $this->config['compression_level']);

            $this->cache_driver->set($cache_key, $compressed_result, $lifetime);
            $this->metrics['cached_queries']++;
        } catch (Exception $e) {
            $this->logger->log(
                "Cache write error: " . $e->getMessage(), 
                'warning', 
                'performance'
            );
        }
    }

    /**
     * Retrieve cached query result
     * @param string $query SQL query
     * @return array|null Cached result or null
     */
    public function getCachedQueryResult($query) {
        if (!$this->config['cache_enabled']) return null;

        $cache_key = $this->generateCacheKey($query);

        try {
            $cached_data = $this->cache_driver->get($cache_key);
            
            if ($cached_data === null) return null;

            $decompressed_data = gzuncompress($cached_data);
            $result = unserialize($decompressed_data);

            return $result;
        } catch (Exception $e) {
            $this->logger->log(
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
     * @return string Cache key
     */
    private function generateCacheKey($query) {
        return 'query_' . hash('sha256', $query);
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
                $this->logger->log(
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
            $this->logger->log(
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
    public function executeCachedQuery($query, $lifetime = null) {
        $this->metrics['query_count']++;

        // Security: Prevent SQL Injection
        if ($this->config['security_checks']['sql_injection_prevention']) {
            $this->preventSqlInjection($query);
        }

        // Start query performance tracking
        $query_start_time = microtime(true);

        // Check cached result first
        $cached_result = $this->getCachedQueryResult($query);
        if ($cached_result !== null) {
            return $cached_result;
        }

        // Execute actual query
        $result = $this->db->query($query);

        // Calculate query execution time
        $query_execution_time = microtime(true) - $query_start_time;

        // Log slow queries
        if ($query_execution_time > $this->config['slow_query_threshold']) {
            $this->logger->log(
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
        $this->cacheQueryResult($query, $result, $lifetime);

        return $result;

        // Try to get cached result
        $cached_result = $this->getCachedQueryResult($query);
        if ($cached_result !== null) {
            return $cached_result;
        }

        // Execute query
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Cache the result
        $this->cacheQueryResult($query, $result, $lifetime);

        return $result;
    }

    /**
     * Clear cache
     * @param string|null $prefix Optional cache key prefix
     */
    public function clearCache($prefix = null) {
        $this->cache_driver->clear($prefix);
        
        $this->logger->log(
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
}

// File-based Cache Driver
class FileCacheDriver extends CacheDriver {
    private $cache_path;

    public function __construct($cache_path) {
        $this->cache_path = $cache_path;
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

// Placeholder for Redis Cache Driver (to be implemented)
class RedisCacheDriver extends CacheDriver {
    public function set($key, $value, $lifetime) {
        // TODO: Implement Redis caching
        throw new Exception("Redis caching not implemented");
    }

    public function get($key) {
        // TODO: Implement Redis caching
        return null;
    }

    public function clear($prefix = null) {
        // TODO: Implement Redis caching
    }
}

// Helper function for dependency injection
function getPerformanceManager() {
    $container = container(); // Assuming dependency container is loaded
    
    // Lazy load dependencies
    $logger = $container->resolve('logger');
    $db = $container->resolve('db_connection');
    
    return new PerformanceManager($logger, $db);
}

return getPerformanceManager();

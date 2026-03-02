<?php

namespace App\Services\Legacy;
/**
 * Advanced Caching and Memoization System
 * Provides multi-driver caching with intelligent storage and retrieval strategies
 */

// require_once __DIR__ . '/logger.php';

class AdvancedCache {
    // Cache Drivers
    public const DRIVER_MEMORY = 'memory';
    public const DRIVER_FILE = 'file';
    public const DRIVER_REDIS = 'redis';
    public const DRIVER_APCU = 'apcu';

    // Cache Strategies
    public const STRATEGY_LRU = 'least_recently_used';
    public const STRATEGY_LFU = 'least_frequently_used';
    public const STRATEGY_FIFO = 'first_in_first_out';

    // Cache Configuration
    private $driver;
    private $strategy;
    private $logger;
    private $config;

    // Cache Storage
    private $memoryCache = [];
    private $fileCache = [];
    private $redisConnection = null;

    // Performance and Limit Tracking
    private $cacheHits = 0;
    private $cacheMisses = 0;
    private $maxCacheSize = 1000;
    private $defaultTTL = 3600; // 1 hour

    public function __construct(
        $driver = self::DRIVER_MEMORY,
        $strategy = self::STRATEGY_LRU
    ) {
        $this->driver = $driver;
        $this->strategy = $strategy;
        $this->logger = new \App\Services\Legacy\Logger();
        $this->config = \App\Services\Legacy\ConfigManager::getInstance();

        // Load configuration
        $this->loadConfiguration();
        $this->initializeDriver();
    }

    /**
     * Load cache configuration from environment
     */
    private function loadConfiguration() {
        $this->maxCacheSize = $this->config->get(
            'CACHE_MAX_SIZE',
            1000
        );
        $this->defaultTTL = $this->config->get(
            'CACHE_DEFAULT_TTL',
            3600
        );
    }

    /**
     * Initialize cache driver
     */
    private function initializeDriver() {
        switch ($this->driver) {
            case self::DRIVER_REDIS:
                $this->initRedis();
                break;
            case self::DRIVER_APCU:
                $this->initAPCu();
                break;
        }
    }

    /**
     * Initialize Redis connection
     */
    private function initRedis() {
        if (!extension_loaded('redis')) {
            $this->logger->warning('Redis extension not loaded');
            return;
        }

        try {
            $this->redisConnection = new \Redis();
            $this->redisConnection->connect(
                $this->config->get('REDIS_HOST', '127.0.0.1'),
                $this->config->get('REDIS_PORT', 6379)
            );
        } catch (\Exception $e) {
            $this->logger->error('Redis Connection Failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Initialize APCu cache
     */
    private function initAPCu() {
        if (!extension_loaded('apcu')) {
            $this->logger->warning('APCu extension not loaded');
        }
    }

    /**
     * Store value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTTL;

        // Implement caching strategy
        $this->manageCacheSize();

        switch ($this->driver) {
            case self::DRIVER_MEMORY:
                $this->memoryCache[$key] = [
                    'value' => $value,
                    'expires' => time() + $ttl,
                    'hits' => 0
                ];
                break;

            case self::DRIVER_REDIS:
                if ($this->redisConnection) {
                    $this->redisConnection->set($key, serialize($value), $ttl);
                }
                break;

            case self::DRIVER_APCU:
                if (\function_exists('apcu_store')) {
                    \apcu_store($key, $value, $ttl);
                }
                break;

            case self::DRIVER_FILE:
                $this->fileCache[$key] = [
                    'value' => $value,
                    'expires' => time() + $ttl
                ];
                $this->persistFileCache();
                break;
        }
    }

    /**
     * Retrieve value from cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public function get($key, $default = null) {
        $value = null;

        switch ($this->driver) {
            case self::DRIVER_MEMORY:
                $value = $this->getMemoryCache($key);
                break;

            case self::DRIVER_REDIS:
                $value = $this->redisConnection
                    ? unserialize($this->redisConnection->get($key))
                    : null;
                break;

            case self::DRIVER_APCU:
                $value = \function_exists('apcu_fetch') ? \apcu_fetch($key) : null;
                break;

            case self::DRIVER_FILE:
                $value = $this->getFileCache($key);
                break;
        }

        // Track cache performance
        $value !== null
            ? $this->cacheHits++
            : $this->cacheMisses++;

        return $value ?? $default;
    }

    /**
     * Retrieve from memory cache with expiration check
     */
    private function getMemoryCache($key) {
        if (!isset($this->memoryCache[$key])) {
            return null;
        }

        $cacheEntry = &$this->memoryCache[$key];

        // Check expiration
        if ($cacheEntry['expires'] < time()) {
            unset($this->memoryCache[$key]);
            return null;
        }

        // Track hits for LFU strategy
        $cacheEntry['hits']++;

        return $cacheEntry['value'];
    }

    /**
     * Retrieve from file cache
     */
    private function getFileCache($key) {
        if (!isset($this->fileCache[$key])) {
            return null;
        }

        $cacheEntry = $this->fileCache[$key];

        // Check expiration
        if ($cacheEntry['expires'] < time()) {
            unset($this->fileCache[$key]);
            $this->persistFileCache();
            return null;
        }

        return $cacheEntry['value'];
    }

    /**
     * Manage cache size based on strategy
     */
    private function manageCacheSize() {
        if (count($this->memoryCache) >= $this->maxCacheSize) {
            switch ($this->strategy) {
                case self::STRATEGY_LRU:
                    $this->evictLRU();
                    break;
                case self::STRATEGY_LFU:
                    $this->evictLFU();
                    break;
                case self::STRATEGY_FIFO:
                    $this->evictFIFO();
                    break;
            }
        }
    }

    /**
     * Evict Least Recently Used items
     */
    private function evictLRU() {
        uasort($this->memoryCache, function($a, $b) {
            return $a['expires'] <=> $b['expires'];
        });
        array_shift($this->memoryCache);
    }

    /**
     * Evict Least Frequently Used items
     */
    private function evictLFU() {
        uasort($this->memoryCache, function($a, $b) {
            return $a['hits'] <=> $b['hits'];
        });
        array_shift($this->memoryCache);
    }

    /**
     * Evict First In First Out items
     */
    private function evictFIFO() {
        array_shift($this->memoryCache);
    }

    /**
     * Persist file cache to disk
     */
    private function persistFileCache() {
        $cachePath = $this->config->get(
            'FILE_CACHE_PATH',
            __DIR__ . '/../cache/file_cache.json'
        );

        // Clean expired entries
        $this->fileCache = array_filter($this->fileCache, function($entry) {
            return $entry['expires'] > time();
        });

        file_put_contents($cachePath, json_encode($this->fileCache));
    }

    /**
     * Memoize function results
     *
     * @param callable $function Function to memoize
     * @param array $args Function arguments
     * @return mixed Cached or computed result
     */
    public function memoize(callable $function, array $args = []) {
        $cacheKey = md5(serialize($function) . serialize($args));

        // Check cache first
        $cachedResult = $this->get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }

        // Compute and cache result
        $result = call_user_func_array($function, $args);
        $this->set($cacheKey, $result);

        return $result;
    }

    /**
     * Generate cache performance report
     *
     * @return array Cache performance metrics
     */
    public function getPerformanceReport() {
        $totalRequests = $this->cacheHits + $this->cacheMisses;
        $hitRatio = $totalRequests > 0
            ? ($this->cacheHits / $totalRequests) * 100
            : 0;

        return [
            'total_requests' => $totalRequests,
            'cache_hits' => $this->cacheHits,
            'cache_misses' => $this->cacheMisses,
            'hit_ratio' => $hitRatio,
            'current_size' => count($this->memoryCache),
            'max_size' => $this->maxCacheSize
        ];
    }

    /**
     * Demonstrate advanced caching capabilities
     */
    public function demonstrateCaching() {
        // Simulate expensive computation
        $expensiveComputation = function($x, $y) {
            usleep(100000);  // Simulate 100ms delay
            return $x * $y;
        };

        // Memoize the function
        $result1 = $this->memoize($expensiveComputation, [5, 7]);
        $result2 = $this->memoize($expensiveComputation, [5, 7]);

        echo "First computation: $result1\n";
        echo "Cached computation: $result2\n";

        // Generate performance report
        $report = $this->getPerformanceReport();
        print_r($report);
    }
}

// Global helper function for easy caching
function cache($driver = AdvancedCache::DRIVER_MEMORY) {
    return new AdvancedCache($driver);
}

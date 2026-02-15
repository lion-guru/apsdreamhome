<?php

namespace App\Services\Legacy;
/**
 * Performance and Caching Layer
 * Provides advanced caching mechanisms and performance optimization
 */

class PerformanceCache {
    private $logger;
    private $config;
    private $cacheDriver;
    private $cachePrefix;

    /**
     * Constructor to initialize cache system
     */
    public function __construct() {
        $this->logger = new \App\Services\Legacy\Logger();
        $this->config = \App\Services\Legacy\ConfigManager::getInstance();
        $this->cachePrefix = $this->config->get('CACHE_PREFIX', 'aps_');
        $this->initializeCacheDriver();
    }

    /**
     * Initialize appropriate cache driver
     */
    private function initializeCacheDriver() {
        // Prefer Redis if available, fallback to APCu, then file-based
        if (extension_loaded('redis')) {
            $this->initRedisCache();
        } elseif (extension_loaded('apcu')) {
            $this->initApcuCache();
        } else {
            $this->initFileCache();
        }
    }

    /**
     * Initialize Redis cache driver
     */
    private function initRedisCache() {
        try {
            $redis = new \Redis();
            $redis->connect(
                $this->config->get('REDIS_HOST', '127.0.0.1'),
                $this->config->get('REDIS_PORT', 6379)
            );

            // Optional authentication
            $redisPass = $this->config->get('REDIS_PASS');
            if ($redisPass) {
                $redis->auth($redisPass);
            }

            $this->cacheDriver = $redis;
            $this->logger->info('Redis cache driver initialized');
        } catch (\Exception $e) {
            $this->logger->warning('Failed to initialize Redis cache', [
                'error' => $e->getMessage()
            ]);
            $this->initFileCache(); // Fallback
        }
    }

    /**
     * Initialize APCu cache driver
     */
    private function initApcuCache() {
        if (!function_exists('apcu_store')) {
            $this->initFileCache();
            return;
        }

        $this->cacheDriver = 'apcu';
        $this->logger->info('APCu cache driver initialized');
    }

    /**
     * Initialize file-based cache driver
     */
    private function initFileCache() {
        $cachePath = $this->config->get('FILE_CACHE_PATH', sys_get_temp_dir() . '/aps_cache');

        // Ensure cache directory exists
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $this->cacheDriver = [
            'type' => 'file',
            'path' => $cachePath
        ];
        $this->logger->info('File-based cache driver initialized');
    }

    /**
     * Set cache item
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool Success status
     */
    public function set($key, $value, $ttl = 3600) {
        $fullKey = $this->cachePrefix . $key;

        try {
            if ($this->cacheDriver instanceof \Redis) {
                return $this->cacheDriver->set($fullKey, serialize($value), $ttl);
            } elseif ($this->cacheDriver === 'apcu') {
                return \function_exists('apcu_store') ? \apcu_store($fullKey, $value, $ttl) : false;
            } elseif (is_array($this->cacheDriver) && $this->cacheDriver['type'] === 'file') {
                $cacheFile = $this->cacheDriver['path'] . '/' . md5($fullKey);
                $cacheData = [
                    'value' => $value,
                    'expires' => time() + $ttl
                ];
                return file_put_contents($cacheFile, serialize($cacheData)) !== false;
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache set failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get cache item
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public function get($key, $default = null) {
        $fullKey = $this->cachePrefix . $key;

        try {
            if ($this->cacheDriver instanceof \Redis) {
                $value = $this->cacheDriver->get($fullKey);
                return $value !== false ? unserialize($value) : $default;
            } elseif ($this->cacheDriver === 'apcu') {
                $value = \function_exists('apcu_fetch') ? \apcu_fetch($fullKey) : false;
                return $value !== false ? $value : $default;
            } elseif (is_array($this->cacheDriver) && $this->cacheDriver['type'] === 'file') {
                $cacheFile = $this->cacheDriver['path'] . '/' . md5($fullKey);

                if (!file_exists($cacheFile)) {
                    return $default;
                }

                $cacheData = unserialize(file_get_contents($cacheFile));

                // Check expiration
                if (time() > $cacheData['expires']) {
                    unlink($cacheFile);
                    return $default;
                }

                return $cacheData['value'];
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Delete cache item
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete($key) {
        $fullKey = $this->cachePrefix . $key;

        try {
            if ($this->cacheDriver instanceof \Redis) {
                return $this->cacheDriver->del($fullKey) > 0;
            } elseif ($this->cacheDriver === 'apcu') {
                return \function_exists('apcu_delete') ? \apcu_delete($fullKey) : false;
            } elseif (is_array($this->cacheDriver) && $this->cacheDriver['type'] === 'file') {
                $cacheFile = $this->cacheDriver['path'] . '/' . md5($fullKey);
                return @unlink($cacheFile);
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear all cache
     *
     * @return bool Success status
     */
    public function clear() {
        try {
            if ($this->cacheDriver instanceof \Redis) {
                $this->cacheDriver->flushDB();
            } elseif ($this->cacheDriver === 'apcu') {
                if (\function_exists('apcu_clear_cache')) {
                    \apcu_clear_cache();
                }
            } elseif (is_array($this->cacheDriver) && $this->cacheDriver['type'] === 'file') {
                $files = glob($this->cacheDriver['path'] . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            $this->logger->info('Cache cleared successfully');
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Cache clear failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Memoize function results
     *
     * @param callable $callback Function to memoize
     * @param array $args Function arguments
     * @param int $ttl Time to live
     * @return mixed Function result
     */
    public function memoize(callable $callback, array $args = [], $ttl = 3600) {
        // Generate unique cache key based on function and arguments
        $cacheKey = md5(serialize([
            'function' => $callback,
            'args' => $args
        ]));

        // Try to get cached result
        $cachedResult = $this->get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }

        // Execute function and cache result
        $result = call_user_func_array($callback, $args);
        $this->set($cacheKey, $result, $ttl);

        return $result;
    }
}

// Global performance cache instance
function cache() {
    static $performanceCache = null;
    if ($performanceCache === null) {
        $performanceCache = new PerformanceCache();
    }
    return $performanceCache;
}

// Performance optimization settings
ini_set('opcache.enable', 1);
ini_set('opcache.memory_consumption', 128);
ini_set('opcache.interned_strings_buffer', 8);
ini_set('opcache.max_accelerated_files', 4000);
ini_set('opcache.revalidate_freq', 60);
ini_set('opcache.fast_shutdown', 1);

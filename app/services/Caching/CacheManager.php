<?php

namespace App\Services\Caching;

use App\Core\Database\Database;

/**
 * Cache Manager - Multi-layer caching system
 */
class CacheManager
{
    private $layers = [];
    private $config = [];
    private $redis = null;
    private $db;

    const LAYER_MEMORY = 'memory';
    const LAYER_FILE = 'file';
    const LAYER_REDIS = 'redis';

    public function __construct($config = [])
    {
        $this->db = Database::getInstance();
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initializeLayers();
    }

    /**
     * Get cache value
     */
    public function get($key, $default = null)
    {
        foreach ($this->layers as $layer) {
            $value = $layer->get($key);
            if ($value !== null) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Set cache value
     */
    public function set($key, $value, $ttl = 3600)
    {
        $success = true;
        foreach ($this->layers as $layer) {
            $success = $layer->set($key, $value, $ttl) && $success;
        }
        return $success;
    }

    /**
     * Delete cache key
     */
    public function delete($key)
    {
        $success = true;
        foreach ($this->layers as $layer) {
            $success = $layer->delete($key) && $success;
        }
        return $success;
    }

    /**
     * Clear all cache
     */
    public function clear()
    {
        $success = true;
        foreach ($this->layers as $layer) {
            $success = $layer->clear() && $success;
        }
        return $success;
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        $stats = [];
        foreach ($this->layers as $name => $layer) {
            $stats[$name] = $layer->getStats();
        }
        return $stats;
    }

    /**
     * Initialize cache layers
     */
    private function initializeLayers()
    {
        // Memory cache (APCu)
        if (function_exists('apcu_enabled') && \apcu_enabled()) {
            $this->layers[self::LAYER_MEMORY] = new ApcuCacheLayer();
        }

        // File cache
        $this->layers[self::LAYER_FILE] = new FileCacheLayer($this->config['file_path']);

        // Redis cache
        if ($this->config['redis_enabled']) {
            $this->initializeRedis();
        }
    }

    /**
     * Initialize Redis connection
     */
    private function initializeRedis()
    {
        if (class_exists('Redis') && $this->config['redis_enabled']) {
            try {
                $this->redis = new \Redis();
                $this->redis->connect(
                    $this->config['redis_host'],
                    $this->config['redis_port']
                );

                if ($this->config['redis_password']) {
                    $this->redis->auth($this->config['redis_password']);
                }

                $this->redis->select($this->config['redis_db']);
                $this->layers[self::LAYER_REDIS] = new RedisCacheLayer($this->redis);
            } catch (\Exception $e) {
                $this->log("Redis connection failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Get default configuration
     */
    private function getDefaultConfig()
    {
        return [
            'redis_enabled' => false,
            'redis_host' => '127.0.0.1',
            'redis_port' => 6379,
            'redis_password' => null,
            'redis_db' => 0,
            'file_path' => getenv('CACHE_FILE_PATH') ?: __DIR__ . '/../../../storage/cache',
            'file_ttl' => getenv('CACHE_FILE_TTL') ?: 3600
        ];
    }

    /**
     * Log cache operations
     */
    private function log($message)
    {
        if (class_exists('App\Services\LoggingService')) {
            $logger = new \App\Services\LoggingService($this->db);
            $logger->info("CacheManager: {$message}");
        } else {
            error_log("CacheManager: {$message}");
        }
    }
}

/**
 * Redis Cache Layer
 */
class RedisCacheLayer
{
    private $redis;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function get($key)
    {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : null;
    }

    public function set($key, $value, $ttl)
    {
        return $this->redis->setex($key, $ttl, serialize($value));
    }

    public function delete($key)
    {
        return $this->redis->del($key) > 0;
    }

    public function clear()
    {
        return $this->redis->flushDB();
    }

    public function getStats()
    {
        return $this->redis->info();
    }
}

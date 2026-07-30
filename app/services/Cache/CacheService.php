<?php

namespace App\Services\Cache;

/**
 * Cache Service
 * Redis/Memcached-based caching with file fallback
 */
class CacheService
{
    private string $driver;
    private string $prefix;
    private int $defaultTTL;
    private string $fileCachePath;
    
    /** @var \Redis|null */
    private $redis = null;
    
    /** @var \Memcached|null */
    private $memcached = null;
    
    public function __construct(string $driver = 'file', int $defaultTTL = 3600)
    {
        $this->driver = $driver;
        $this->prefix = $_ENV['CACHE_PREFIX'] ?? 'aps_';
        $this->defaultTTL = $defaultTTL;
        $this->fileCachePath = STORAGE_PATH . '/cache/';
        
        if (!is_dir($this->fileCachePath)) {
            mkdir($this->fileCachePath, 0755, true);
        }
        
        $this->initDriver();
    }
    
    /**
     * Initialize cache driver
     */
    private function initDriver(): void
    {
        switch ($this->driver) {
            case 'redis':
                $this->initRedis();
                break;
            case 'memcached':
                $this->initMemcached();
                break;
            default:
                $this->driver = 'file';
        }
    }
    
    /**
     * Initialize Redis
     */
    private function initRedis(): void
    {
        try {
            $redis = new \Redis();
            $redis->connect(
                $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                $_ENV['REDIS_PORT'] ?? 6379
            );
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $redis->auth($_ENV['REDIS_PASSWORD']);
            }
            $redis->select($_ENV['REDIS_DATABASE'] ?? 0);
            $this->redis = $redis;
        } catch (\Exception $e) {
            $this->driver = 'file';
        }
    }
    
    /**
     * Initialize Memcached
     */
    private function initMemcached(): void
    {
        try {
            $memcached = new \Memcached();
            $memcached->addServer(
                $_ENV['MEMCACHED_HOST'] ?? '127.0.0.1',
                $_ENV['MEMCACHED_PORT'] ?? 11211
            );
            $this->memcached = $memcached;
        } catch (\Exception $e) {
            $this->driver = 'file';
        }
    }
    
    /**
     * Get cache key with tenant prefix
     */
    private function getKey(string $key): string
    {
        return $this->prefix . self::tenantPrefix() . $key;
    }

    /**
     * Tenant-aware cache key prefix.
     */
    private static function tenantPrefix(): string
    {
        if (!class_exists('\App\Core\Middleware\TenantContext')) {
            return '';
        }
        try {
            $tid = \App\Core\Middleware\TenantContext::getId();
            if ($tid > 1) {
                return 't' . $tid . '_';
            }
        } catch (\Throwable $e) {
        // fail open
        error_log($e->getMessage());
        }
        return '';
    }
    
    /**
     * Get value from cache
     */
    public function get(string $key, $default = null)
    {
        $fullKey = $this->getKey($key);
        
        try {
            switch ($this->driver) {
                case 'redis':
                    $value = $this->redis->get($fullKey);
                    return $value !== false ? unserialize($value) : $default;
                    
                case 'memcached':
                    $value = $this->memcached->get($fullKey);
                    return $value !== false ? $value : $default;
                    
                case 'file':
                default:
                    return $this->fileGet($fullKey, $default);
            }
        } catch (\Exception $e) {
            return $default;
        }
    }
    
    /**
     * Set value in cache
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $fullKey = $this->getKey($key);
        $ttl = $ttl ?? $this->defaultTTL;
        
        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->redis->setex($fullKey, $ttl, serialize($value));
                    
                case 'memcached':
                    return $this->memcached->set($fullKey, $value, $ttl);
                    
                case 'file':
                default:
                    return $this->fileSet($fullKey, $value, $ttl);
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
    
    /**
     * Delete from cache
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->getKey($key);
        
        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->redis->del($fullKey) > 0;
                    
                case 'memcached':
                    return $this->memcached->delete($fullKey);
                    
                case 'file':
                default:
                    $file = $this->fileCachePath . md5($fullKey) . '.cache';
                    return !file_exists($file) || unlink($file);
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->redis->flushDB();
                    
                case 'memcached':
                    return $this->memcached->flush();
                    
                case 'file':
                default:
                    $files = glob($this->fileCachePath . '*.cache');
                    foreach ($files as $file) {
                        unlink($file);
                    }
                    return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Remember - Get or store
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Increment value
     */
    public function increment(string $key, int $value = 1): int
    {
        $fullKey = $this->getKey($key);
        
        try {
            switch ($this->driver) {
                case 'redis':
                    return $this->redis->incrBy($fullKey, $value);
                    
                case 'memcached':
                    $current = (int) $this->memcached->get($fullKey);
                    $new = $current + $value;
                    $this->memcached->set($fullKey, $new);
                    return $new;
                    
                default:
                    $current = (int) $this->get($key);
                    $new = $current + $value;
                    $this->set($key, $new);
                    return $new;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Decrement value
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }
    
    /**
     * Cache tags (for cache invalidation)
     */
    public function flushTag(string $tag): bool
    {
        $pattern = $this->prefix . $tag . '*';
        
        try {
            if ($this->driver === 'redis' && isset($this->redis)) {
                $keys = $this->redis->keys($pattern);
                if (!empty($keys)) {
                    return $this->redis->del($keys) > 0;
                }
                return true;
            }
            
            // For file cache, scan and delete
            if ($this->driver === 'file') {
                $files = glob($this->fileCachePath . '*.cache');
                foreach ($files as $file) {
                    $data = unserialize(file_get_contents($file));
                    if (isset($data['tags']) && in_array($tag, $data['tags'])) {
                        unlink($file);
                    }
                }
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get multiple keys
     */
    public function many(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }
    
    /**
     * Set multiple values
     */
    public function setMany(array $values, ?int $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * File cache - Get
     */
    private function fileGet(string $key, $default = null)
    {
        $file = $this->fileCachePath . md5($key) . '.cache';
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * File cache - Set
     */
    private function fileSet(string $key, $value, int $ttl): bool
    {
        $file = $this->fileCachePath . md5($key) . '.cache';
        
        $data = [
            'expires' => time() + $ttl,
            'value' => $value
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            switch ($this->driver) {
                case 'redis':
                    $info = $this->redis->info();
                    return [
                        'driver' => 'redis',
                        'connected' => $this->redis->ping() === '+PONG',
                        'used_memory' => $info['used_memory'] ?? 0,
                        'keys_count' => $this->redis->dbSize()
                    ];
                    
                case 'memcached':
                    $stats = $this->memcached->getStats();
                    return [
                        'driver' => 'memcached',
                        'connected' => !empty($stats),
                        'stats' => $stats
                    ];
                    
                case 'file':
                default:
                    $files = glob($this->fileCachePath . '*.cache');
                    $totalSize = 0;
                    foreach ($files as $file) {
                        $totalSize += filesize($file);
                    }
                    return [
                        'driver' => 'file',
                        'files_count' => count($files),
                        'total_size_bytes' => $totalSize
                    ];
            }
        } catch (\Exception $e) {
            return [
                'driver' => $this->driver,
                'error' => $e->getMessage()
            ];
        }
    }
}

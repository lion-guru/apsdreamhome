<?php

namespace App\Core;

/**
 * Comprehensive Caching System
 * Supports multiple caching backends (File, Redis, Memcached)
 */
class Cache
{
    private static $instance = null;
    private $driver = null;
    private $config = [];
    private $defaultTtl = 3600; // 1 hour

    private function __construct()
    {
        $this->config = [
            'driver' => config('cache.driver', 'file'),
            'ttl' => config('cache.ttl', 3600),
            'path' => config('cache.path', __DIR__ . '/../cache/'),
            'redis' => [
                'host' => config('cache.redis.host', '127.0.0.1'),
                'port' => config('cache.redis.port', 6379),
                'password' => config('cache.redis.password', null),
                'database' => config('cache.redis.database', 0)
            ],
            'memcached' => [
                'host' => config('cache.memcached.host', '127.0.0.1'),
                'port' => config('cache.memcached.port', 11211)
            ]
        ];

        $this->initializeDriver();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the cache driver
     */
    private function initializeDriver(): void
    {
        switch ($this->config['driver']) {
            case 'redis':
                $this->initRedis();
                break;
            case 'memcached':
                $this->initMemcached();
                break;
            case 'file':
            default:
                $this->initFile();
                break;
        }
    }

    /**
     * Initialize file-based caching
     */
    private function initFile(): void
    {
        if (!is_dir($this->config['path'])) {
            mkdir($this->config['path'], 0755, true);
        }
        $this->driver = 'file';
    }

    /**
     * Initialize Redis caching
     */
    private function initRedis(): void
    {
        if (extension_loaded('redis')) {
            try {
                $redis = new \Redis();
                $redis->connect(
                    $this->config['redis']['host'],
                    $this->config['redis']['port']
                );

                if ($this->config['redis']['password']) {
                    $redis->auth($this->config['redis']['password']);
                }

                $redis->select($this->config['redis']['database']);
                $this->driver = $redis;
            } catch (\Exception $e) {
                // Fallback to file caching
                $this->initFile();
            }
        } else {
            $this->initFile();
        }
    }

    /**
     * Initialize Memcached caching
     */
    private function initMemcached(): void
    {
        if (extension_loaded('memcached')) {
            try {
                $memcached = new \Memcached();
                $memcached->addServer(
                    $this->config['memcached']['host'],
                    $this->config['memcached']['port']
                );
                $this->driver = $memcached;
            } catch (\Exception $e) {
                $this->initFile();
            }
        } else {
            $this->initFile();
        }
    }

    /**
     * Store data in cache
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?: $this->config['ttl'];
        $expiresAt = time() + $ttl;

        $data = [
            'value' => $value,
            'expires_at' => $expiresAt,
            'created_at' => time()
        ];

        switch ($this->driver) {
            case 'file':
                return $this->setFile($key, $data, $ttl);
            default:
                return $this->setMemory($key, $data, $ttl);
        }
    }

    /**
     * Retrieve data from cache
     */
    public function get(string $key)
    {
        switch ($this->driver) {
            case 'file':
                return $this->getFile($key);
            default:
                return $this->getMemory($key);
        }
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        switch ($this->driver) {
            case 'file':
                return $this->hasFile($key);
            default:
                return $this->hasMemory($key);
        }
    }

    /**
     * Delete key from cache
     */
    public function delete(string $key): bool
    {
        switch ($this->driver) {
            case 'file':
                return $this->deleteFile($key);
            default:
                return $this->deleteMemory($key);
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        switch ($this->driver) {
            case 'file':
                return $this->clearFile();
            default:
                return $this->clearMemory();
        }
    }

    // File-based cache methods
    private function setFile(string $key, array $data, int $ttl): bool
    {
        $filePath = $this->config['path'] . md5($key) . '.cache';
        $data['ttl'] = $ttl;

        return file_put_contents($filePath, serialize($data)) !== false;
    }

    private function getFile(string $key)
    {
        $filePath = $this->config['path'] . md5($key) . '.cache';

        if (!file_exists($filePath)) {
            return null;
        }

        $data = unserialize(file_get_contents($filePath));

        if ($data['expires_at'] < time()) {
            unlink($filePath);
            return null;
        }

        return $data['value'];
    }

    private function hasFile(string $key): bool
    {
        $filePath = $this->config['path'] . md5($key) . '.cache';

        if (!file_exists($filePath)) {
            return false;
        }

        $data = unserialize(file_get_contents($filePath));

        if ($data['expires_at'] < time()) {
            unlink($filePath);
            return false;
        }

        return true;
    }

    private function deleteFile(string $key): bool
    {
        $filePath = $this->config['path'] . md5($key) . '.cache';
        return file_exists($filePath) && unlink($filePath);
    }

    private function clearFile(): bool
    {
        $files = glob($this->config['path'] . '*.cache');
        $deleted = 0;

        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }

        return $deleted > 0;
    }

    // Memory-based cache methods (Redis/Memcached)
    private function setMemory(string $key, array $data, int $ttl)
    {
        if ($this->driver instanceof \Redis) {
            return $this->driver->setEx($key, $ttl, serialize($data));
        } elseif ($this->driver instanceof \Memcached) {
            return $this->driver->set($key, serialize($data), $ttl);
        }
        return false;
    }

    private function getMemory(string $key)
    {
        $data = null;

        if ($this->driver instanceof \Redis) {
            $data = $this->driver->get($key);
        } elseif ($this->driver instanceof \Memcached) {
            $data = $this->driver->get($key);
        }

        if ($data === false || $data === null) {
            return null;
        }

        $data = unserialize($data);

        if ($data['expires_at'] < time()) {
            $this->deleteMemory($key);
            return null;
        }

        return $data['value'];
    }

    private function hasMemory(string $key): bool
    {
        if ($this->driver instanceof \Redis) {
            return $this->driver->exists($key);
        } elseif ($this->driver instanceof \Memcached) {
            return $this->driver->get($key) !== false;
        }
        return false;
    }

    private function deleteMemory(string $key): bool
    {
        if ($this->driver instanceof \Redis) {
            return $this->driver->del($key) > 0;
        } elseif ($this->driver instanceof \Memcached) {
            return $this->driver->delete($key);
        }
        return false;
    }

    private function clearMemory(): bool
    {
        if ($this->driver instanceof \Redis) {
            return $this->driver->flushDB();
        } elseif ($this->driver instanceof \Memcached) {
            return $this->driver->flush();
        }
        return false;
    }

    /**
     * Cache with tags for better organization
     */
    public function tag(string $tag): CacheTag
    {
        return new CacheTag($this, $tag);
    }

    /**
     * Remember - cache result of callback
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'driver' => $this->config['driver'],
            'enabled' => config('cache.enabled', false),
            'ttl' => $this->config['ttl']
        ];
    }
}

/**
 * Cache Tag Helper Class
 */
class CacheTag
{
    private $cache;
    private $tag;

    public function __construct(Cache $cache, string $tag)
    {
        $this->cache = $cache;
        $this->tag = $tag;
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        $taggedKey = $this->tag . ':' . $key;
        return $this->cache->set($taggedKey, $value, $ttl);
    }

    public function get(string $key)
    {
        $taggedKey = $this->tag . ':' . $key;
        return $this->cache->get($taggedKey);
    }

    public function has(string $key): bool
    {
        $taggedKey = $this->tag . ':' . $key;
        return $this->cache->has($taggedKey);
    }

    public function delete(string $key): bool
    {
        $taggedKey = $this->tag . ':' . $key;
        return $this->cache->delete($taggedKey);
    }

    public function flush(): bool
    {
        // For file-based cache, we'd need to implement tag-based flushing
        // For memory-based cache, we can use keys with tag prefix
        return $this->cache->clear();
    }
}

/**
 * Global cache helper functions
 */
function cache(string $key = null)
{
    $cache = Cache::getInstance();

    if ($key === null) {
        return $cache;
    }

    return $cache;
}

function cache_remember(string $key, int $ttl, callable $callback)
{
    return Cache::getInstance()->remember($key, $ttl, $callback);
}

function cache_forget(string $key): bool
{
    return Cache::getInstance()->delete($key);
}

?>

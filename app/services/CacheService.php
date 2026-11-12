<?php

namespace App\Services;

/**
 * CacheService - Redis Caching for APS Dream Home
 *
 * Caches database query results to speed up the website.
 * Use this service to cache frequently accessed data like plots, colonies, etc.
 */
class CacheService
{
    private static ?self $instance = null;
    private $redis;
    private $prefix = 'apsdreamhome:';
    private $defaultTTL = 3600; // 1 hour cache
    private ?string $fileCacheDir = null;

    public function __construct()
    {
        try {
            if (class_exists('\Redis')) {
                $this->redis = new \Redis();
                $host = getenv('REDIS_HOST') ?: '127.0.0.1';
                $port = (int)(getenv('REDIS_PORT') ?: 6379);
                @$this->redis->connect($host, $port, 1.0);
            }
        } catch (\Throwable $e) {
            $this->redis = null;
        }
    }

    private function getFileCacheDir(): string
    {
        if ($this->fileCacheDir === null) {
            $base = defined('STORAGE_PATH') ? STORAGE_PATH : (defined('APP_ROOT') ? APP_ROOT . '/storage' : dirname(__DIR__, 2) . '/storage');
            $dir = $base . '/cache';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $this->fileCacheDir = $dir;
        }
        return $this->fileCacheDir;
    }

    /**
     * Singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if Redis is available
     */
    public function isAvailable(): bool
    {
        return $this->redis !== null;
    }

    /**
     * Get cached data
     */
    public function get(string $key): ?string
    {
        if ($this->redis) {
            try {
                $val = $this->redis->get($this->prefix . $key);
                if ($val !== false && $val !== null) return $val;
            } catch (\Throwable $e) {}
        }

        // File fallback
        $file = $this->getFileCacheDir() . '/' . md5($this->prefix . $key) . '.cache';
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $data = @json_decode($content, true);
                if (is_array($data) && isset($data['exp']) && $data['exp'] > time()) {
                    return $data['val'];
                }
                @unlink($file);
            }
        }
        return null;
    }

    /**
     * Set cached data
     */
    public function set(string $key, string $value, int $ttl = 3600): bool
    {
        $ok = false;
        if ($this->redis) {
            try {
                $ok = (bool)$this->redis->set($this->prefix . $key, $value, $ttl);
            } catch (\Throwable $e) {}
        }

        // Store file copy as well (ensures persistence across restarts)
        $file = $this->getFileCacheDir() . '/' . md5($this->prefix . $key) . '.cache';
        $payload = json_encode(['exp' => time() + $ttl, 'val' => $value]);
        $fileOk = @file_put_contents($file, $payload) !== false;

        return $ok || $fileOk;
    }

    /**
     * Delete cached data
     */
    public function delete(string $key): bool
    {
        if ($this->redis) {
            try { @$this->redis->del($this->prefix . $key); } catch (\Throwable $e) {}
        }
        $file = $this->getFileCacheDir() . '/' . md5($this->prefix . $key) . '.cache';
        if (file_exists($file)) @unlink($file);
        return true;
    }

    /**
     * Clear all cache
     */
    public function clearAll(): bool
    {
        if ($this->redis) {
            try {
                $keys = $this->redis->keys($this->prefix . '*');
                if (!empty($keys)) $this->redis->del($keys);
            } catch (\Throwable $e) {}
        }
        $files = glob($this->getFileCacheDir() . '/*.cache') ?: [];
        foreach ($files as $f) @unlink($f);
        return true;
    }

    /**
     * Cache or compute value (used by HotPathCacheService and others)
     */
    public static function cache(string $key, int $ttl, callable $callback)
    {
        try {
            $inst = self::getInstance();
            $cached = $inst->get($key);
            if ($cached !== null && $cached !== '') {
                $unserialized = @unserialize($cached);
                if ($unserialized !== false || $cached === 'b:0;') {
                    return $unserialized;
                }
                $decoded = json_decode($cached, true);
                return $decoded !== null ? $decoded : $cached;
            }
            $value = $callback();
            $stored = is_string($value) ? $value : serialize($value);
            $inst->set($key, $stored, $ttl);
            return $value;
        } catch (\Throwable $e) {
            error_log('CacheService::cache error: ' . $e->getMessage());
            return $callback();
        }
    }

    /**
     * Remember helper
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        return self::cache($key, $ttl, $callback);
    }

    /**
     * Invalidate cached key
     */
    public static function invalidate(string $key): int
    {
        try {
            return self::getInstance()->delete($key) ? 1 : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Invalidate keys matching a pattern
     */
    public static function invalidatePattern(string $pattern): int
    {
        try {
            $inst = self::getInstance();
            if ($inst->redis) {
                $keys = $inst->redis->keys($inst->prefix . $pattern);
                if (!empty($keys)) {
                    return (int)$inst->redis->del($keys);
                }
            }
            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Flush all cache keys
     */
    public static function flushAll(): bool
    {
        try {
            return self::getInstance()->clearAll();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Handle any dynamic static calls (e.g. invalidatePropertyList, tenantKey, etc.)
     */
    public static function __callStatic(string $name, array $arguments)
    {
        try {
            $inst = self::getInstance();
            if (method_exists($inst, $name)) {
                return $inst->{$name}(...$arguments);
            }
            if (str_starts_with($name, 'invalidate')) {
                if (isset($arguments[0]) && is_string($arguments[0])) {
                    return $inst->delete($arguments[0]);
                }
                return true;
            }
            if (in_array($name, ['remember', 'cache']) && isset($arguments[2]) && is_callable($arguments[2])) {
                return self::cache((string)$arguments[0], (int)$arguments[1], $arguments[2]);
            }
            return null;
        } catch (\Throwable $e) {
            error_log("CacheService::{$name} fallback error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get or set data (get from cache if exists, otherwise run callback)
     */
    public function getOrSet(string $key, callable $callback, int $ttl = 3600): string
    {
        $cached = $this->get($key);
        if ($cached !== false && $cached !== '') {
            return $cached;
        }

        $data = $callback();
        $this->set($key, $data, $ttl);
        return $data;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        if (!$this->redis) return ['error' => 'Redis not connected'];

        $info = $this->redis->info('memory');
        return [
            'used_memory' => $info['used_memory_human'] ?? 'N/A',
            'used_memory_human' => $info['used_memory_human'] ?? 'N/A',
            'connected_clients' => $this->redis->info('clients')['connected_clients'] ?? 0,
        ];
    }

    /**
     * Get or compute cached gamification data
     */
    public static function getGamification(string $role, int $k1, int $k2, callable $callback): array
    {
        try {
            $key = "gamification_{$role}_{$k1}_{$k2}";
            $inst = new self();
            $cached = $inst->get($key);
            if ($cached !== null && $cached !== '') {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
            $data = is_callable($callback) ? $callback() : [];
            if (is_array($data) && !empty($data)) {
                $inst->set($key, json_encode($data), 300);
            }
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return is_callable($callback) ? (array)$callback() : [];
        }
    }
}
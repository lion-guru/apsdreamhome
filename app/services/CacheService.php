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
    private $redis;
    private $prefix = 'apsdreamhome:';
    private $defaultTTL = 3600; // 1 hour cache

    public function __construct()
    {
        try {
            $this->redis = new \Redis();
            $this->redis->connect('127.0.0.1', 6379);
        } catch (\Exception $e) {
            error_log('Redis connection failed: ' . $e->getMessage());
            $this->redis = null;
        }
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
        if (!$this->redis) return null;
        return $this->redis->get($this->prefix . $key);
    }

    /**
     * Set cached data
     */
    public function set(string $key, string $value, int $ttl = 3600): bool
    {
        if (!$this->redis) return false;
        return $this->redis->set($this->prefix . $key, $value, $ttl);
    }

    /**
     * Delete cached data
     */
    public function delete(string $key): bool
    {
        if (!$this->redis) return false;
        return $this->redis->del($this->prefix . $key);
    }

    /**
     * Clear all cache
     */
    public function clearAll(): bool
    {
        if (!$this->redis) return false;
        $keys = $this->redis->keys($this->prefix . '*');
        if (!empty($keys)) {
            return $this->redis->del($keys);
        }
        return true;
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
            if ($cached !== null && $cached !== false && $cached !== '') {
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
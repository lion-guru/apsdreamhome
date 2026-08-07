<?php
/**
 * Response Cache Service
 * 
 * Fast in-memory caching for API responses
 * Dramatically improves response times
 */

namespace App\Services;

class ResponseCache
{
    private static $cache = [];
    private static $hits = 0;
    private static $misses = 0;

    /**
     * Get cached response
     */
    public static function get(string $key): ?array
    {
        if (isset(self::$cache[$key])) {
            $entry = self::$cache[$key];
            if ($entry['expires'] > time()) {
                self::$hits++;
                return $entry['data'];
            }
            unset(self::$cache[$key]);
        }
        self::$misses++;
        return null;
    }

    /**
     * Set cached response
     */
    public static function set(string $key, $data, int $ttl = 60): void
    {
        self::$cache[$key] = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time(),
        ];
    }

    /**
     * Delete cached response
     */
    public static function delete(string $key): void
    {
        unset(self::$cache[$key]);
    }

    /**
     * Clear all cache
     */
    public static function clear(): void
    {
        self::$cache = [];
        self::$hits = 0;
        self::$misses = 0;
    }

    /**
     * Get cache stats
     */
    public static function stats(): array
    {
        return [
            'hits' => self::$hits,
            'misses' => self::$misses,
            'hit_rate' => (self::$hits + self::$misses) > 0 
                ? round((self::$hits / (self::$hits + self::$misses)) * 100, 1) 
                : 0,
            'entries' => count(self::$cache),
        ];
    }

    /**
     * Clear expired entries
     */
    public static function gc(): int
    {
        $cleaned = 0;
        foreach (self::$cache as $key => $entry) {
            if ($entry['expires'] <= time()) {
                unset(self::$cache[$key]);
                $cleaned++;
            }
        }
        return $cleaned;
    }

    /**
     * Invalidate by pattern
     */
    public static function invalidate(string $pattern): int
    {
        $count = 0;
        foreach (array_keys(self::$cache) as $key) {
            if (strpos($key, $pattern) !== false) {
                unset(self::$cache[$key]);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Remember pattern - get or compute
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $data = $callback();
        self::set($key, $data, $ttl);
        return $data;
    }
}

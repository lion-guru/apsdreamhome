<?php
/**
 * Simple in-memory cache helper for repeated queries
 * Use for dashboard statistics, configuration lookups, etc.
 */

class QueryCache
{
    private static array $cache = [];
    private static array $ttl = [];

    public static function remember(string $key, callable $callback, int $ttlSeconds = 300)
    {
        $now = time();
        
        if (isset(self::$cache[$key]) && isset(self::$ttl[$key]) && $self::$ttl[$key] > $now) {
            return self::$cache[$key];
        }

        $result = $callback();
        self::$cache[$key] = $result;
        self::$ttl[$key] = $now + $ttlSeconds;
        
        return $result;
    }

    public static function forget(string $key): void
    {
        unset(self::$cache[$key], self::$ttl[$key]);
    }

    public static function flush(): void
    {
        self::$cache = [];
        self::$ttl = [];
    }

    public static function has(string $key): bool
    {
        $now = time();
        return isset(self::$cache[$key]) && isset(self::$ttl[$key]) && self::$ttl[$key] > $now;
    }
}
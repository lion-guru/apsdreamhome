<?php
/**
 * LookupCacheService — File-based caching for high-traffic lookup APIs.
 * 
 * Caches pincode, IFSC, circle rate, and stamp duty lookups to reduce DB load.
 * TTL: 1 hour (3600s) by default. Falls back gracefully if cache dir is not writable.
 * 
 * Usage:  $result = LookupCacheService::remember('pincode:273001', 3600, function() { ... });
 */

namespace App\Services;

class LookupCacheService
{
    private static string $cacheDir = '';

    private static function dir(): string
    {
        if (self::$cacheDir === '') {
            self::$cacheDir = (defined('APS_STORAGE') ? APS_STORAGE : __DIR__ . '/../../storage') . '/cache/lookup';
            if (!is_dir(self::$cacheDir)) {
                @mkdir(self::$cacheDir, 0755, true);
            }
        }
        return self::$cacheDir;
    }

    /**
     * Get-or-set with file cache.
     *
     * @param string   $key      Unique cache key
     * @param int      $ttl      Time-to-live in seconds
     * @param callable $callback Returns value on cache miss
     * @return mixed
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $file = self::dir() . '/' . md5($key) . '.cache';

        // Try read from cache
        if (is_file($file)) {
            $data = @unserialize(file_get_contents($file));
            if (is_array($data) && ($data['expires'] ?? 0) > time()) {
                return $data['value'];
            }
            // Expired — delete
            @unlink($file);
        }

        // Cache miss — compute
        $value = $callback();

        // Store
        $payload = ['expires' => time() + $ttl, 'value' => $value];
        @file_put_contents($file, serialize($payload));

        return $value;
    }

    /**
     * Invalidate a specific cache key.
     */
    public static function forget(string $key): void
    {
        $file = self::dir() . '/' . md5($key) . '.cache';
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Flush all lookup caches.
     */
    public static function flush(): int
    {
        $count = 0;
        $dir = self::dir();
        if (is_dir($dir)) {
            foreach (glob($dir . '/*.cache') as $file) {
                @unlink($file);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get cache stats.
     */
    public static function stats(): array
    {
        $dir = self::dir();
        $files = is_dir($dir) ? glob($dir . '/*.cache') : [];
        $totalSize = 0;
        $expired = 0;
        $active = 0;
        foreach ($files as $f) {
            $totalSize += filesize($f);
            $data = @unserialize(file_get_contents($f));
            if (is_array($data) && ($data['expires'] ?? 0) > time()) {
                $active++;
            } else {
                $expired++;
            }
        }
        return [
            'total_files' => count($files),
            'active' => $active,
            'expired' => $expired,
            'total_size_bytes' => $totalSize,
            'total_size_human' => self::formatBytes($totalSize),
            'cache_dir' => $dir,
        ];
    }

    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }
}

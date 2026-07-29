<?php

namespace App\Core;

/**
 * File-Based Caching System for APS Dream Home.
 *
 * Backward-compatible facade over the new App\Services\CacheService.
 * All legacy static calls (Cache::get, Cache::set, etc.) still work —
 * they are routed through the unified CacheService so that
 * Redis (when available) and file cache are kept in sync.
 *
 * If you are writing new code, prefer the new CacheService::cache()
 * helper which returns-or-sets in a single call.
 */

class Cache
{
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour default

    /**
     * Initialize cache directory.
     */
    public static function init($cacheDir = null)
    {
        if ($cacheDir === null) {
            $cacheDir = defined('APP_ROOT')
                ? APP_ROOT . '/storage/cache'
                : __DIR__ . '/../../storage/cache';
        }
        self::$cacheDir = $cacheDir;
        if (!is_dir(self::$cacheDir)) {
            @mkdir(self::$cacheDir, 0755, true);
        }
    }

    /**
     * Get cached data. Tries Redis first, then file cache.
     */
    public static function get($key, $default = null)
    {
        $redis = RedisCache::getInstance();
        if ($redis->isAvailable()) {
            $value = $redis->get($key);
            if ($value !== null) {
                return $value;
            }
        }
        return self::fileGet($key, $default);
    }

    /**
     * Set cached data in both layers.
     */
    public static function set($key, $value, $ttl = null)
    {
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        $redis = RedisCache::getInstance();
        if ($redis->isAvailable()) {
            $redis->set($key, $value, $ttl);
        }
        return self::fileSet($key, $value, $ttl);
    }

    /**
     * Delete from both layers.
     */
    public static function delete($key)
    {
        $redis = RedisCache::getInstance();
        $r1 = false;
        if ($redis->isAvailable()) {
            $r1 = $redis->delete($key);
        }
        $r2 = self::fileDelete($key);
        return $r1 || $r2;
    }

    /**
     * Clear the file cache. Use App\Services\CacheService::flushAll()
     * if you want to clear Redis too.
     */
    public static function clear()
    {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
        return true;
    }

    /**
     * Clear expired cache entries.
     */
    public static function clearExpired()
    {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        $cleared = 0;
        foreach ($files as $file) {
            $content = @file_get_contents($file);
            $data = json_decode($content, true);
            if (is_array($data) && isset($data['expires']) && $data['expires'] < time()) {
                @unlink($file);
                $cleared++;
            }
        }
        return $cleared;
    }

    /**
     * Get-or-set pattern. Backward-compatible: returns cached value
     * or executes callback and stores the result.
     */
    public static function remember($key, $callback, $ttl = null)
    {
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Get cache file path for a key (private helper).
     */
    private static function getFilename($key)
    {
        self::init();
        $safeKey = md5($key);
        return self::$cacheDir . '/' . $safeKey . '.cache';
    }

    /**
     * File-layer get (only the file cache, not Redis).
     */
    private static function fileGet($key, $default = null)
    {
        self::init();
        $filename = self::getFilename($key);
        if (!file_exists($filename)) {
            return $default;
        }
        $content = @file_get_contents($filename);
        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expires'])) {
            return $default;
        }
        if ($data['expires'] < time()) {
            @unlink($filename);
            return $default;
        }
        return $data['value'] ?? $default;
    }

    /**
     * File-layer set (only the file cache, not Redis).
     */
    private static function fileSet($key, $value, $ttl)
    {
        self::init();
        $filename = self::getFilename($key);
        $data = [
            'key'     => $key,
            'value'   => $value,
            'expires' => time() + $ttl,
            'created' => time(),
        ];
        return @file_put_contents($filename, json_encode($data)) !== false;
    }

    /**
     * File-layer delete (only the file cache, not Redis).
     */
    private static function fileDelete($key)
    {
        self::init();
        $filename = self::getFilename($key);
        if (file_exists($filename)) {
            return @unlink($filename);
        }
        return true;
    }

    /**
     * Get file-cache statistics.
     */
    public static function getStats()
    {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;

        foreach ($files as $file) {
            $totalSize += @filesize($file);
            $content = @file_get_contents($file);
            $data = json_decode($content, true);
            if (is_array($data) && isset($data['expires']) && $data['expires'] < time()) {
                $expiredCount++;
            }
        }

        return [
            'total_files'   => count($files),
            'total_size'    => self::formatBytes($totalSize),
            'expired_files' => $expiredCount,
            'active_files'  => count($files) - $expiredCount,
        ];
    }

    /**
     * Format bytes for display.
     */
    private static function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Cache database query results.
     */
    public static function rememberQuery($key, $query, $params = [], $ttl = null)
    {
        // Tenant-prefix the key for multi-tenant isolation
        if (class_exists('\App\Services\CacheService')) {
            $key = \App\Services\CacheService::tenantKey($key);
        }
        return self::remember($key, function () use ($query, $params) {
            try {
                $db = \App\Core\Database\Database::getInstance();
                if (!empty($params)) {
                    $stmt = $db->prepare($query);
                    $stmt->execute($params);
                    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    return $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
                }
            } catch (\Exception $e) {
                error_log("Cache query error: " . $e->getMessage());
                return [];
            }
        }, $ttl);
    }
}

// Auto-initialize cache on include
\App\Core\Cache::init();

<?php

namespace App\Core;

/**
 * Simple File-Based Caching System for APS Dream Home
 * Provides caching for frequently accessed data without external dependencies
 */

class Cache
{
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour default

    /**
     * Initialize cache system
     */
    public static function init($cacheDir = null)
    {
        if ($cacheDir === null) {
            $cacheDir = __DIR__ . '/../../storage/cache';
        }

        self::$cacheDir = $cacheDir;

        // Create cache directory if it doesn't exist
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    /**
     * Get cached data
     */
    public static function get($key, $default = null)
    {
        self::init();

        $filename = self::getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $content = file_get_contents($filename);
        $data = json_decode($content, true);

        // Check if cache has expired
        if ($data['expires'] < time()) {
            self::delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set cached data
     */
    public static function set($key, $value, $ttl = null)
    {
        self::init();

        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }

        $filename = self::getFilename($key);

        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        return file_put_contents($filename, json_encode($data)) !== false;
    }

    /**
     * Delete cached data
     */
    public static function delete($key)
    {
        self::init();

        $filename = self::getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    /**
     * Clear all cached data
     */
    public static function clear()
    {
        self::init();

        $files = glob(self::$cacheDir . '/*.cache');

        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    /**
     * Clear expired cache entries
     */
    public static function clearExpired()
    {
        self::init();

        $files = glob(self::$cacheDir . '/*.cache');
        $cleared = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if ($data['expires'] < time()) {
                unlink($file);
                $cleared++;
            }
        }

        return $cleared;
    }

    /**
     * Remember pattern - get from cache or execute callback
     */
    public static function remember($key, $callback, $ttl = null)
    {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Get cache filename for key
     */
    private static function getFilename($key)
    {
        $safeKey = md5($key);
        return self::$cacheDir . '/' . $safeKey . '.cache';
    }

    /**
     * Get cache statistics
     */
    public static function getStats()
    {
        self::init();

        $files = glob(self::$cacheDir . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;

        foreach ($files as $file) {
            $totalSize += filesize($file);

            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if ($data['expires'] < time()) {
                $expiredCount++;
            }
        }

        return [
            'total_files' => count($files),
            'total_size' => self::formatBytes($totalSize),
            'expired_files' => $expiredCount,
            'active_files' => count($files) - $expiredCount
        ];
    }

    /**
     * Format bytes for display
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
     * Cache database query results
     */
    public static function rememberQuery($key, $query, $params = [], $ttl = null)
    {
        return self::remember($key, function () use ($query, $params) {
            try {
                // Use the database connection to execute query
                $db = \App\Core\Database\Database::getInstance();

                if (!empty($params)) {
                    $stmt = $db->prepare($query);
                    $stmt->execute($params);
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
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

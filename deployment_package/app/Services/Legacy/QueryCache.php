<?php

namespace App\Services\Legacy;
/**
 * Query Cache System - APS Dream Homes
 * Improves database performance by caching frequent queries
 */

class QueryCache {
    private static $cache = [];
    private static $cacheDir = __DIR__ . "/cache/db_cache";

    public static function init() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    /**
     * Get cached query result
     */
    public static function get($key, $ttl = 300) {
        $cacheFile = self::$cacheDir . "/" . md5($key) . ".cache";

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            return unserialize(file_get_contents($cacheFile));
        }

        return false;
    }

    /**
     * Set query cache
     */
    public static function set($key, $data) {
        $cacheFile = self::$cacheDir . "/" . md5($key) . ".cache";
        file_put_contents($cacheFile, serialize($data));
    }

    /**
     * Clear cache
     */
    public static function clear($pattern = "*") {
        $files = glob(self::$cacheDir . "/" . $pattern . ".cache");
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Execute cached query
     */
    public static function query($sql, $ttl = 300) {
        $key = $sql;

        // Try to get from cache
        $result = self::get($key, $ttl);
        if ($result !== false) {
            return $result;
        }

        // Execute query
        $db = \App\Core\App::database();
        $data = $db->fetchAll($sql);

        // Cache the result
        if (!empty($data)) {
            self::set($key, $data);
            return $data;
        }

        return false;
    }
}

// Initialize cache system
QueryCache::init();
?>

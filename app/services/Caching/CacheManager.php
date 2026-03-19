<?php

namespace App\Services\Caching;

use PDO;

/**
 * Advanced Cache Manager for APS Dream Home
 * Implements multi-layer caching, Redis support, and cache warming
 */
class CacheManager
{
    private $layers = [];
    private $config;
    private $db;
    private $redis = null;

    // Cache layers (ordered by speed: fastest first)
    const LAYER_MEMORY = 'memory';      // APCu/In-Memory (fastest)
    const LAYER_REDIS = 'redis';        // Redis (very fast)
    const LAYER_FILE = 'file';          // File system (slower)

    // Cache strategies
    const STRATEGY_WRITE_THROUGH = 'write_through';    // Write to all layers
    const STRATEGY_WRITE_BEHIND = 'write_behind';      // Write to fast layer, defer slow
    const STRATEGY_CACHE_ASIDE = 'cache_aside';       // Lazy loading

    public function __construct($db = null)
    {
        $this->db = $db;
        $this->config = $this->loadCacheConfig();
        $this->initializeLayers();
        $this->initializeRedis();
    }

    /**
     * Get cached value with multi-layer lookup
     */
    public function get($key, $default = null)
    {
        // Check memory cache first (fastest)
        if ($this->hasLayer(self::LAYER_MEMORY)) {
            $value = $this->layers[self::LAYER_MEMORY]->get($key);
            if ($value !== null) {
                return $this->unserializeValue($value);
            }
        }

        // Check Redis cache
        if ($this->hasLayer(self::LAYER_REDIS) && $this->redis) {
            $value = $this->redis->get($key);
            if ($value !== false) {
                // Store in memory for future requests
                if ($this->hasLayer(self::LAYER_MEMORY)) {
                    $this->layers[self::LAYER_MEMORY]->set($key, $value, $this->config['memory_ttl']);
                }
                return $this->unserializeValue($value);
            }
        }

        // Check file cache (slowest)
        if ($this->hasLayer(self::LAYER_FILE)) {
            $value = $this->layers[self::LAYER_FILE]->get($key);
            if ($value !== null) {
                // Promote to faster layers
                $this->set($key, $this->unserializeValue($value), $this->config['file_ttl']);
                return $this->unserializeValue($value);
            }
        }

        return $default;
    }

    /**
     * Set cached value with multi-layer storage
     */
    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?? $this->config['default_ttl'];
        $serialized = $this->serializeValue($value);

        $strategy = $this->config['strategy'];

        if ($strategy === self::STRATEGY_WRITE_THROUGH) {
            $this->writeThrough($key, $serialized, $ttl);
        } elseif ($strategy === self::STRATEGY_WRITE_BEHIND) {
            $this->writeBehind($key, $serialized, $ttl);
        } else {
            $this->cacheAside($key, $serialized, $ttl);
        }

        return true;
    }

    /**
     * Check if key exists in any cache layer
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete from all cache layers
     */
    public function delete($key)
    {
        $deleted = false;

        if ($this->hasLayer(self::LAYER_MEMORY)) {
            $this->layers[self::LAYER_MEMORY]->delete($key);
            $deleted = true;
        }

        if ($this->hasLayer(self::LAYER_REDIS) && $this->redis) {
            $this->redis->del($key);
            $deleted = true;
        }

        if ($this->hasLayer(self::LAYER_FILE)) {
            $this->layers[self::LAYER_FILE]->delete($key);
            $deleted = true;
        }

        return $deleted;
    }

    /**
     * Clear all cache layers
     */
    public function clear()
    {
        if ($this->hasLayer(self::LAYER_MEMORY)) {
            $this->layers[self::LAYER_MEMORY]->clear();
        }

        if ($this->hasLayer(self::LAYER_REDIS) && $this->redis) {
            $this->redis->flushdb();
        }

        if ($this->hasLayer(self::LAYER_FILE)) {
            $this->layers[self::LAYER_FILE]->clear();
        }

        return true;
    }

    /**
     * Get or set cache value (cache miss callback)
     */
    public function remember($key, $ttl, callable $callback)
    {
        $value = $this->get($key);

        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * Cache warming for frequently accessed data
     */
    public function warmCache()
    {
        $this->log("Starting cache warming process");

        // Warm frequently accessed data
        $this->warmUserData();
        $this->warmPropertyData();
        $this->warmSystemConfig();
        $this->warmLookupTables();

        $this->log("Cache warming completed");
    }

    /**
     * Warm user-related cache
     */
    private function warmUserData()
    {
        if (!$this->db) return;

        try {
            // Cache active users count
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $result = $stmt->fetch();
            $this->set('users.active_count', $result['count'], 3600); // 1 hour

            // Cache recent users (for admin dashboard)
            $stmt = $this->db->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 50");
            $recentUsers = $stmt->fetchAll();
            $this->set('users.recent', $recentUsers, 1800); // 30 minutes

            $this->log("User data cache warmed");
        } catch (\Exception $e) {
            $this->log("Failed to warm user data cache: " . $e->getMessage());
        }
    }

    /**
     * Warm property-related cache
     */
    private function warmPropertyData()
    {
        if (!$this->db) return;

        try {
            // Cache property counts by status
            $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM properties GROUP BY status");
            $stmt->execute();
            $propertyStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $this->set('properties.stats', $propertyStats, 3600);

            // Cache featured properties
            $stmt = $this->db->query("SELECT * FROM properties WHERE featured = 1 AND status = 'available' LIMIT 20");
            $featuredProperties = $stmt->fetchAll();
            $this->set('properties.featured', $featuredProperties, 1800);

            // Cache property types
            $stmt = $this->db->query("SELECT DISTINCT type FROM properties ORDER BY type");
            $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->set('properties.types', $types, 86400); // 24 hours

            $this->log("Property data cache warmed");
        } catch (\Exception $e) {
            $this->log("Failed to warm property data cache: " . $e->getMessage());
        }
    }

    /**
     * Warm system configuration cache
     */
    private function warmSystemConfig()
    {
        // Cache application settings
        $appConfig = [
            'name' => 'APS Dream Home',
            'version' => '2.0.0',
            'environment' => getenv('APP_ENV') ?: 'production',
            'debug' => getenv('APP_DEBUG') === 'true',
            'timezone' => date_default_timezone_get()
        ];
        $this->set('system.config', $appConfig, 3600);

        // Cache navigation menu
        $navigation = [
            ['name' => 'Home', 'url' => '/', 'active' => true],
            ['name' => 'Properties', 'url' => '/properties', 'active' => false],
            ['name' => 'About', 'url' => '/about', 'active' => false],
            ['name' => 'Contact', 'url' => '/contact', 'active' => false]
        ];
        $this->set('system.navigation', $navigation, 3600);
    }

    /**
     * Warm lookup tables cache
     */
    private function warmLookupTables()
    {
        if (!$this->db) return;

        try {
            // Cache cities/states
            $stmt = $this->db->query("SELECT DISTINCT city, state FROM properties ORDER BY state, city");
            $locations = $stmt->fetchAll();
            $this->set('lookup.locations', $locations, 86400);

            // Cache amenities
            $stmt = $this->db->query("SELECT DISTINCT amenity FROM property_amenities ORDER BY amenity");
            $amenities = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->set('lookup.amenities', $amenities, 86400);

            $this->log("Lookup tables cache warmed");
        } catch (\Exception $e) {
            $this->log("Failed to warm lookup tables cache: " . $e->getMessage());
        }
    }

    /**
     * Advanced cache warming with predictive loading
     */
    public function predictiveWarm($userId = null, $context = [])
    {
        // Warm user-specific data
        if ($userId) {
            $this->set("user.{$userId}.favorites", $this->getUserFavorites($userId), 1800);
            $this->set("user.{$userId}.searches", $this->getUserSearches($userId), 3600);
        }

        // Context-based warming
        if (isset($context['page'])) {
            switch ($context['page']) {
                case 'dashboard':
                    $this->warmDashboardData();
                    break;
                case 'properties':
                    $this->warmPropertyListingData($context);
                    break;
                case 'profile':
                    $this->warmUserProfileData($userId);
                    break;
            }
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        $stats = [
            'layers' => [],
            'hits' => 0,
            'misses' => 0,
            'hit_ratio' => 0,
            'memory_usage' => 0
        ];

        foreach ($this->layers as $layerName => $layer) {
            if (method_exists($layer, 'getStats')) {
                $stats['layers'][$layerName] = $layer->getStats();
            }
        }

        // Redis stats
        if ($this->redis) {
            try {
                $info = $this->redis->info();
                $stats['redis'] = [
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'used_memory' => $info['used_memory'] ?? 0,
                    'total_keys' => $this->redis->dbSize()
                ];
            } catch (\Exception $e) {
                $stats['redis'] = ['error' => $e->getMessage()];
            }
        }

        return $stats;
    }

    /**
     * Initialize cache layers
     */
    private function initializeLayers()
    {
        // Memory cache (APCu)
        if (function_exists('apcu_enabled') && apcu_enabled()) {
            $this->layers[self::LAYER_MEMORY] = new ApcuCacheLayer();
        }

        // File cache
        $this->layers[self::LAYER_FILE] = new FileCacheLayer($this->config['file_path']);
    }

    /**
     * Initialize Redis connection
     */
    private function initializeRedis()
    {
        if (class_exists('Redis') && $this->config['redis_enabled']) {
            try {
                $this->redis = new \Redis();
                $this->redis->connect(
                    $this->config['redis_host'],
                    $this->config['redis_port']
                );

                if ($this->config['redis_password']) {
                    $this->redis->auth($this->config['redis_password']);
                }

                $this->redis->select($this->config['redis_db']);
                $this->layers[self::LAYER_REDIS] = true; // Mark Redis as available

            } catch (\Exception $e) {
                $this->log("Redis connection failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Write-through caching strategy
     */
    private function writeThrough($key, $value, $ttl)
    {
        // Write to all available layers
        if ($this->hasLayer(self::LAYER_MEMORY)) {
            $this->layers[self::LAYER_MEMORY]->set($key, $value, $ttl);
        }

        if ($this->hasLayer(self::LAYER_REDIS) && $this->redis) {
            $this->redis->setex($key, $ttl, $value);
        }

        if ($this->hasLayer(self::LAYER_FILE)) {
            $this->layers[self::LAYER_FILE]->set($key, $value, $ttl);
        }
    }

    /**
     * Write-behind caching strategy
     */
    private function writeBehind($key, $value, $ttl)
    {
        // Write to fast layers immediately
        if ($this->hasLayer(self::LAYER_MEMORY)) {
            $this->layers[self::LAYER_MEMORY]->set($key, $value, $ttl);
        }

        if ($this->hasLayer(self::LAYER_REDIS) && $this->redis) {
            $this->redis->setex($key, $ttl, $value);
        }

        // Defer file cache write (could be done asynchronously)
        if ($this->hasLayer(self::LAYER_FILE)) {
            // In a real implementation, this would be queued
            $this->layers[self::LAYER_FILE]->set($key, $value, $ttl);
        }
    }

    /**
     * Cache-aside strategy
     */
    private function cacheAside($key, $value, $ttl)
    {
        // Write to fast layers only
        if ($this->hasLayer(self::LAYER_MEMORY)) {
            $this->layers[self::LAYER_MEMORY]->set($key, $value, $ttl);
        }

        if ($this->hasLayer(self::LAYER_REDIS) && $this->redis) {
            $this->redis->setex($key, $ttl, $value);
        }
    }

    /**
     * Check if cache layer is available
     */
    private function hasLayer($layer)
    {
        return isset($this->layers[$layer]);
    }

    /**
     * Serialize value for storage
     */
    private function serializeValue($value)
    {
        return serialize($value);
    }

    /**
     * Unserialize value from storage
     */
    private function unserializeValue($value)
    {
        return unserialize($value);
    }

    /**
     * Load cache configuration
     */
    private function loadCacheConfig()
    {
        return [
            'strategy' => getenv('CACHE_STRATEGY') ?: self::STRATEGY_WRITE_THROUGH,
            'default_ttl' => getenv('CACHE_DEFAULT_TTL') ?: 3600,
            'memory_ttl' => getenv('CACHE_MEMORY_TTL') ?: 300,
            'redis_enabled' => getenv('REDIS_ENABLED') ?: true,
            'redis_host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'redis_port' => getenv('REDIS_PORT') ?: 6379,
            'redis_password' => getenv('REDIS_PASSWORD'),
            'redis_db' => getenv('REDIS_DB') ?: 0,
            'file_path' => getenv('CACHE_FILE_PATH') ?: __DIR__ . '/../../../storage/cache',
            'file_ttl' => getenv('CACHE_FILE_TTL') ?: 3600
        ];
    }

    /**
     * Log cache operations
     */
    private function log($message)
    {
        $logger = new \App\Services\Monitoring\LoggingService($this->db);
        $logger->info("CacheManager: {$message}");
    }

    // Helper methods for predictive warming
    private function getUserFavorites($userId)
    { /* Implementation */
        return [];
    }
    private function getUserSearches($userId)
    { /* Implementation */
        return [];
    }
    private function warmDashboardData()
    { /* Implementation */
    }
    private function warmPropertyListingData($context)
    { /* Implementation */
    }
    private function warmUserProfileData($userId)
    { /* Implementation */
    }
}

/**
 * APCu Cache Layer
 */
class ApcuCacheLayer
{
    public function get($key)
    {
        $success = false;
        $value = apcu_fetch($key, $success);
        return $success ? $value : null;
    }
    public function set($key, $value, $ttl)
    {
        return apcu_store($key, $value, $ttl);
    }
    public function delete($key)
    {
        return apcu_delete($key);
    }
    public function clear()
    {
        return apcu_clear_cache();
    }
    public function getStats()
    {
        return apcu_cache_info(true);
    }
}

/**
 * File Cache Layer
 */
class FileCacheLayer
{
    private $cacheDir;

    public function __construct($cacheDir)
    {
        $this->cacheDir = rtrim($cacheDir, '/');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key)
    {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) return null;

        $data = unserialize(file_get_contents($file));
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl)
    {
        $file = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        return file_put_contents($file, serialize($data)) !== false;
    }

    public function delete($key)
    {
        $file = $this->getFilePath($key);
        return file_exists($file) ? unlink($file) : true;
    }

    public function clear()
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
        return true;
    }

    public function getStats()
    {
        $files = glob($this->cacheDir . '/*');
        return [
            'files' => count($files),
            'size' => array_sum(array_map('filesize', $files))
        ];
    }

    private function getFilePath($key)
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Services\Legacy\CacheManager.php

function setBrowserCache($ttl = 3600, $public = true)
{
    $expires = gmdate('D, d M Y H:i:s T', time() + $ttl);
    $max_age = $ttl;
    $cache_control = $public ? 'public' : 'private';

    header("Cache-Control: {$cache_control}, max-age={$max_age}");
    header("Expires: {$expires}");
    header("Pragma: cache");
}

function getCacheFile($key)
{
    $filename = md5($key) . '.cache';
    return $this->cache_dir . $filename;
}

function cacheQuery($query, $params, $result, $ttl = 1800)
{
    $key = 'query_' . md5($query . serialize($params));
    return $this->set($key, $result, $ttl);
}
function getCachedQuery($query, $params)
{
    $key = 'query_' . md5($query . serialize($params));
    return $this->get($key);
}
function cacheAPIResponse($endpoint, $params, $response, $ttl = 3600)
{
    $key = 'api_' . md5($endpoint . serialize($params));
    return $this->set($key, $response, $ttl);
}
function getCachedAPIResponse($endpoint, $params)
{
    $key = 'api_' . md5($endpoint . serialize($params));
    return $this->get($key);
}
function cacheHTML($key, $html, $ttl = 3600)
{
    return $this->set('html_' . $key, $html, $ttl);
}
function getCachedHTML($key)
{
    return $this->get('html_' . $key);
}
function cacheImageMetadata($image_path, $metadata, $ttl = 86400)
{
    $key = 'img_meta_' . md5($image_path);
    return $this->set($key, $metadata, $ttl);
}
function getCachedImageMetadata($image_path)
{
    $key = 'img_meta_' . md5($image_path);
    return $this->get($key);
}
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 627 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//
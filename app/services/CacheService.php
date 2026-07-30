<?php

namespace App\Services;

use App\Core\Cache;
use App\Core\RedisCache;

/**
 * CacheService - unified cache facade for the whole application.
 *
 * Tries Redis first (when configured + reachable), then transparently
 * falls back to the file-based Cache. Tracking of hits/misses is done
 * locally; RedisCache also tracks its own stats.
 *
 * Hot keys used in the application (see "cache strategy" in AGENTS.md):
 *   - admin_sidebar_*                 (1h)  invalidated on menu change
 *   - header_projects_*               (5m)  invalidated on project change
 *   - unread_count_user_{id}          (30s) invalidated on notification read
 *   - admin_dash_*                    (2m)  invalidated on dashboard events
 *   - property_filters_*              (1h)  invalidated on filter data change
 */
class CacheService
{
    private static ?array $stats = null;
    private static array $localStats = [
        'redis_hits'   => 0,
        'redis_misses' => 0,
        'file_hits'    => 0,
        'file_misses'  => 0,
        'invalidations'=> 0,
    ];

    /**
     * Tenant-aware cache key prefix.
     * Returns 't{N}_' for tenants > 1, empty string for superadmin (tenant 1).
     * Used to isolate cached data across tenants in shared Redis/file cache.
     */
    public static function tenantPrefix(): string
    {
        if (!class_exists('\App\Core\Middleware\TenantContext')) {
            return '';
        }
        try {
            $tid = \App\Core\Middleware\TenantContext::getId();
            if ($tid > 1) {
                return 't' . $tid . '_';
            }
        } catch (\Throwable $e) {
        // fail open — no prefix
        error_log($e->getMessage());
        }
        return '';
    }

    /**
     * Prepend tenant prefix to a cache key for multi-tenant isolation.
     * Logical key 'admin_menu_role_abc' becomes 't2_admin_menu_role_abc' for tenant 2.
     * For tenant 1 (APS Dream Home), key is returned unchanged.
     */
    public static function tenantKey(string $key): string
    {
        return self::tenantPrefix() . $key;
    }

    /**
     * Get-or-set: try Redis first, then file cache, then callback.
     *
     * @param string   $key      Cache key (without prefix)
     * @param int      $ttl      TTL in seconds
     * @param callable $callback Function to compute the value on miss
     */
    public static function cache(string $key, int $ttl, callable $callback)
    {
        $key = self::tenantKey($key);

        $redis = RedisCache::getInstance();
        if ($redis->isAvailable()) {
            $value = $redis->get($key);
            if ($value !== null) {
                self::$localStats['redis_hits']++;
                return $value;
            }
            self::$localStats['redis_misses']++;
        } else {
            $value = Cache::get($key);
            if ($value !== null) {
                self::$localStats['file_hits']++;
                return $value;
            }
            self::$localStats['file_misses']++;
        }

        $value = $callback();

        // Best-effort write to both layers (file is durable, redis is fast).
        if ($redis->isAvailable()) {
            $redis->set($key, $value, $ttl);
        }
        Cache::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Invalidate a single key in both cache layers.
     */
    public static function invalidate(string $key): bool
    {
        $key = self::tenantKey($key);
        self::$localStats['invalidations']++;
        $ok1 = RedisCache::getInstance()->delete($key);
        $ok2 = Cache::delete($key);
        return $ok1 || $ok2;
    }

    /**
     * Invalidate all keys matching a glob pattern across both layers.
     *
     *   CacheService::invalidatePattern('admin_menu_*')
     *
     * Pattern is automatically prefixed with tenant scope so only the
     * current tenant's keys are invalidated.
     */
    public static function invalidatePattern(string $pattern): int
    {
        $pattern = self::tenantPrefix() . $pattern;
        self::$localStats['invalidations']++;
        $count = 0;

        // Redis: pattern-based delete
        $count += RedisCache::getInstance()->deletePattern($pattern);

        // File: iterate cache files and check the embedded key
        $count += self::invalidateFilePattern($pattern);

        return $count;
    }

    /**
     * Get aggregate statistics from both cache layers.
     */
    public static function getStats(): array
    {
        $redis = RedisCache::getInstance();
        $redisStats = $redis->getStats();
        $fileStats  = Cache::getStats();

        return [
            'driver'   => $redisStats['driver'] ?? 'unknown',
            'available'=> $redisStats['available'] ?? false,
            'host'     => $redisStats['host'] ?? null,
            'port'     => $redisStats['port'] ?? null,
            'prefix'   => $redisStats['prefix'] ?? 'apsdream_',
            'redis'    => [
                'hits'      => $redisStats['hits']   ?? 0,
                'misses'    => $redisStats['misses'] ?? 0,
                'sets'      => $redisStats['sets']   ?? 0,
                'deletes'   => $redisStats['deletes']?? 0,
                'evictions' => $redisStats['evictions'] ?? 0,
                'errors'    => $redisStats['errors'] ?? 0,
                'size'      => $redis->size(),
                'info'      => $redis->info(),
            ],
            'file' => [
                'total_files'  => $fileStats['total_files']  ?? 0,
                'total_size'   => $fileStats['total_size']   ?? '0 bytes',
                'expired_files'=> $fileStats['expired_files']?? 0,
                'active_files' => $fileStats['active_files'] ?? 0,
            ],
            'session' => self::$localStats,
            'hit_rate' => self::calcHitRate($redisStats, self::$localStats),
        ];
    }

    /**
     * Flush both cache layers.
     */
    public static function flushAll(): bool
    {
        $r = RedisCache::getInstance()->flush();
        $f = Cache::clear();
        self::$localStats['invalidations']++;
        return $r || $f;
    }

    /**
     * Flush only the Redis cache (file cache untouched).
     */
    public static function flushRedis(): bool
    {
        self::$localStats['invalidations']++;
        return RedisCache::getInstance()->flush();
    }

    /**
     * Test that Redis is reachable. Returns diagnostic info.
     */
    public static function testConnection(): array
    {
        $redis = RedisCache::getInstance();
        $start = microtime(true);
        $available = $redis->isAvailable();
        $latency = round((microtime(true) - $start) * 1000, 2);

        $result = [
            'available' => $available,
            'driver'    => $available ? 'redis' : 'file (fallback)',
            'latency_ms'=> $latency,
            'host'      => $redis->getStats()['host'] ?? null,
            'port'      => $redis->getStats()['port'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        if ($available) {
            try {
                $redis->set('__cache_test__', 'ok', 10);
                $value = $redis->get('__cache_test__');
                $redis->delete('__cache_test__');
                $result['read_write'] = ($value === 'ok');
                $result['info'] = array_slice($redis->info(), 0, 5);
            } catch (\Throwable $e) {
                $result['error'] = $e->getMessage();
            }
        } else {
            $result['error'] = 'Redis not available. Using file cache fallback.';
        }
        return $result;
    }

    /**
     * Calculate overall cache hit-rate percentage.
     */
    private static function calcHitRate(array $redisStats, array $localStats): float
    {
        $hits = ($redisStats['hits'] ?? 0) + $localStats['file_hits'];
        $total = $hits + ($redisStats['misses'] ?? 0) + $localStats['file_misses'];
        if ($total === 0) {
            return 0.0;
        }
        return round(($hits / $total) * 100, 2);
    }

    /**
     * Walk the file cache directory, decoding each entry's original key
     * to determine if it matches the supplied glob pattern.
     */
    private static function invalidateFilePattern(string $pattern): int
    {
        $count = 0;
        $cacheDir = defined('APP_ROOT')
            ? APP_ROOT . '/storage/cache'
            : __DIR__ . '/../../storage/cache';

        if (!is_dir($cacheDir)) {
            return 0;
        }

        $regex = self::globToRegex($pattern);
        $files = glob($cacheDir . '/*.cache');
        if (!$files) {
            return 0;
        }

        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if (!$content) {
                continue;
            }
            $data = json_decode($content, true);
            if (!is_array($data) || !isset($data['key'])) {
                continue;
            }
            if (preg_match($regex, $data['key'])) {
                @unlink($file);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Convert a glob pattern like 'admin_menu_*' to a regex.
     */
    private static function globToRegex(string $pattern): string
    {
        $quoted = preg_quote($pattern, '~');
        $regex = str_replace(['\*', '\?'], ['.*', '.'], $quoted);
        return '~^' . $regex . '$~';
    }

    // -----------------------------------------------------------------
    // Domain-specific helpers — pre-canned cache strategies for hot data
    // -----------------------------------------------------------------

    /**
     * Cache admin menu items (1 hour).
     * @param string $role  e.g. 'admin', 'manager', 'associate'
     */
    public static function getAdminMenu(string $role, callable $callback): array
    {
        $key = 'admin_menu_role_' . md5($role);
        return self::cache($key, 3600, $callback);
    }

    /**
     * Cache header projects / locations (10 minutes).
     */
    public static function getHeaderProjects(callable $callback): array
    {
        return self::cache('header_projects_all', 600, $callback);
    }

    /**
     * Cache unread notification count for a user (30 seconds).
     */
    public static function getUnreadCount(int $userId, callable $callback): int
    {
        return (int) self::cache('unread_count_user_' . $userId, 30, $callback);
    }

    /**
     * Cache admin dashboard stats (2 minutes).
     */
    public static function getAdminDashboardStats(callable $callback): array
    {
        return self::cache('admin_dash_stats', 120, $callback);
    }

    /**
     * Cache property list filters — cities, types, price ranges (1 hour).
     */
    public static function getPropertyFilters(callable $callback): array
    {
        return self::cache('property_filters_all', 3600, $callback);
    }

    public static function getGamification(string $role, int $primaryId, int $secondaryId, callable $callback): array
    {
        $key = 'gamify_' . $role . '_' . $primaryId . '_' . $secondaryId;
        return self::cache($key, 300, $callback);
    }

    /**
     * Invalidate hooks — call these from places that mutate the underlying data.
     */
    public static function invalidateAdminMenu(): int
    {
        return self::invalidatePattern('admin_menu_') + self::invalidatePattern('admin_sidebar_');
    }

    public static function invalidateHeaderProjects(): int
    {
        return self::invalidate('header_projects_all');
    }

    public static function invalidateUnreadCount(int $userId): int
    {
        self::invalidate('unread_count_user_' . $userId);
        return 1;
    }

    public static function invalidateAdminDashboard(): int
    {
        return self::invalidatePattern('admin_dash_');
    }

    public static function invalidatePropertyFilters(): int
    {
        return self::invalidate('property_filters_all');
    }

    public static function invalidateGamification(string $role, int $primaryId, int $secondaryId = 0): int
    {
        $key = 'gamify_' . $role . '_' . $primaryId . '_' . $secondaryId;
        self::invalidate($key);
        return 1;
    }
}

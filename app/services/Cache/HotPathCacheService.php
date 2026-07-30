<?php

namespace App\Services\Cache;

use App\Services\CacheService;

/**
 * HotPathCacheService — domain-specific cache facade for the 5 hottest
 * query paths in the application. Sits on top of {@see CacheService}
 * (Redis with file fallback) and exposes:
 *
 *   1. Property listing queries   (5 min TTL)
 *   2. Header projects dropdown   (10 min TTL — bumped from 5m)
 *   3. Admin dashboard KPIs       (2 min TTL, per role+userId)
 *   4. Home featured properties   (15 min TTL)
 *   5. Saved searches count/user  (30 sec TTL, per userId)
 *
 * Invalidation helpers are exposed for each path so callers can drop
 * stale keys when underlying data changes.
 *
 * Hot-path stats (hits / misses / calls per path) are tracked in-process
 * via a static $localStats array. Use {@see getStats()} to read them.
 *
 * @see App\Services\CacheService — underlying Redis+file cache
 */
class HotPathCacheService
{
    /** @var int property list TTL (seconds) */
    public const TTL_PROPERTY_LIST = 300;       // 5 min

    /** @var int header projects TTL (seconds) — bumped per spec */
    public const TTL_HEADER_PROJECTS = 600;     // 10 min

    /** @var int admin dashboard KPI TTL (seconds) */
    public const TTL_ADMIN_KPIS = 120;          // 2 min

    /** @var int home featured properties TTL (seconds) */
    public const TTL_HOME_FEATURED = 900;       // 15 min

    /** @var int user saved-searches count TTL (seconds) */
    public const TTL_SAVED_SEARCHES_COUNT = 30; // 30 sec

    /** @var array<string, array{hits:int,misses:int,calls:int}> per-path stats */
    private static array $localStats = [
        'property_list'          => ['hits' => 0, 'misses' => 0, 'calls' => 0],
        'header_projects'        => ['hits' => 0, 'misses' => 0, 'calls' => 0],
        'admin_dash_kpis'        => ['hits' => 0, 'misses' => 0, 'calls' => 0],
        'home_featured'          => ['hits' => 0, 'misses' => 0, 'calls' => 0],
        'saved_searches_count'   => ['hits' => 0, 'misses' => 0, 'calls' => 0],
    ];

    // ───────────────────────────────────────────────────────────────────
    //   Hot path 1: Property listing
    // ───────────────────────────────────────────────────────────────────

    /**
     * Cached property listing query (5 min TTL).
     *
     * @param array  $filters  Filter array from $_GET (e.g. type, location, price, …)
     * @param int    $page     Current page number (1-based)
     * @param int    $perPage  Items per page
     * @param string $sort     Sort key (newest, price_low, …)
     * @param callable $callback Returns the full payload from DB
     *
     * @return array  Whatever the callback returned (properties + total + meta)
     */
    public static function getPropertyList(array $filters, int $page, int $perPage, string $sort, callable $callback): array
    {
        self::bumpCall('property_list');
        $key = self::propertyListKey($filters, $page, $perPage, $sort);

        return self::wrap('property_list', $key, self::TTL_PROPERTY_LIST, $callback);
    }

    /**
     * Drop all cached property-list pages.
     */
    public static function invalidatePropertyList(): int
    {
        // Wildcard suffix — every property-list cache key is
        // 'properties_list_<md5(filter+page+pp+sort)>'
        $n = CacheService::invalidatePattern('properties_list_*');
        return $n;
    }

    // ───────────────────────────────────────────────────────────────────
    //   Hot path 2: Header projects dropdown
    // ───────────────────────────────────────────────────────────────────

    /**
     * Cached header projects dropdown (10 min TTL — bumped from 5m).
     */
    public static function getHeaderProjects(callable $callback): array
    {
        self::bumpCall('header_projects');
        return self::wrap('header_projects', 'header_projects_all', self::TTL_HEADER_PROJECTS, $callback);
    }

    /**
     * Invalidate the header-projects cache.
     */
    public static function invalidateHeaderProjects(): int
    {
        return CacheService::invalidate('header_projects_all');
    }

    // ───────────────────────────────────────────────────────────────────
    //   Hot path 3: Admin dashboard KPIs (per role + user)
    // ───────────────────────────────────────────────────────────────────

    /**
     * Cached admin dashboard KPI payload (2 min TTL).
     *
     * @param string   $role     e.g. 'admin', 'super_admin', 'manager'
     * @param int      $userId   Admin user id
     * @param callable $callback Returns the KPI array
     */
    public static function getAdminDashboardKpis(string $role, int $userId, callable $callback): array
    {
        self::bumpCall('admin_dash_kpis');
        $key = 'admin_dash_kpis_' . md5($role . ':' . $userId);
        return self::wrap('admin_dash_kpis', $key, self::TTL_ADMIN_KPIS, $callback);
    }

    /**
     * Invalidate the admin dashboard KPI cache. Pass null to nuke ALL
     * admin KPI buckets (used when a lead/booking/payment changes globally);
     * pass an int to drop just one user's bucket.
     */
    public static function invalidateAdminDashboard(?int $userId = null): int
    {
        // Wildcard suffix required — keys are 'admin_dash_kpis_<md5(role:userId)>'.
        $total = CacheService::invalidatePattern('admin_dash_*')
               + CacheService::invalidatePattern('admin_dash_kpis_*');
        return $total;
    }

    // ───────────────────────────────────────────────────────────────────
    //   Hot path 4: Home featured properties
    // ───────────────────────────────────────────────────────────────────

    /**
     * Cached home-page featured properties (15 min TTL).
     */
    public static function getHomeFeaturedProperties(callable $callback): array
    {
        self::bumpCall('home_featured');
        return self::wrap('home_featured', 'home_featured_properties', self::TTL_HOME_FEATURED, $callback);
    }

    /**
     * Invalidate the home featured-properties cache.
     */
    public static function invalidateHomeFeatured(): int
    {
        return CacheService::invalidate('home_featured_properties');
    }

    // ───────────────────────────────────────────────────────────────────
    //   Hot path 5: Saved searches count (per user)
    // ───────────────────────────────────────────────────────────────────

    /**
     * Cached saved-searches count for a user (30 sec TTL).
     */
    public static function getUserSavedSearchesCount(int $userId, callable $callback): int
    {
        self::bumpCall('saved_searches_count');
        $key = 'saved_searches_count_' . $userId;
        return (int) self::wrap('saved_searches_count', $key, self::TTL_SAVED_SEARCHES_COUNT, $callback);
    }

    /**
     * Invalidate the saved-searches count for a single user.
     */
    public static function invalidateUserSavedSearches(int $userId): int
    {
        return CacheService::invalidate('saved_searches_count_' . $userId);
    }

    // ───────────────────────────────────────────────────────────────────
    //   Aggregate stats
    // ───────────────────────────────────────────────────────────────────

    /**
     * Per-path hit-rate + counts + aggregate.
     *
     * @return array{
     *   paths: array<string, array{hits:int,misses:int,calls:int,hit_rate:float}>,
     *   total: array{hits:int,misses:int,calls:int,hit_rate:float}
     * }
     */
    public static function getStats(): array
    {
        $total = ['hits' => 0, 'misses' => 0, 'calls' => 0];
        $out = [];
        foreach (self::$localStats as $path => $s) {
            $hits   = (int) $s['hits'];
            $misses = (int) $s['misses'];
            $calls  = (int) $s['calls'];
            $rate   = ($hits + $misses) > 0 ? round(($hits / ($hits + $misses)) * 100, 2) : 0.0;
            $out[$path] = [
                'hits'     => $hits,
                'misses'   => $misses,
                'calls'    => $calls,
                'hit_rate' => $rate,
            ];
            $total['hits']   += $hits;
            $total['misses'] += $misses;
            $total['calls']  += $calls;
        }
        $totalRate = ($total['hits'] + $total['misses']) > 0
            ? round(($total['hits'] / ($total['hits'] + $total['misses'])) * 100, 2)
            : 0.0;
        $total['hit_rate'] = $totalRate;
        return ['paths' => $out, 'total' => $total];
    }

    /**
     * Reset all in-process stats. Useful for tests.
     */
    public static function resetStats(): void
    {
        foreach (self::$localStats as $path => $_) {
            self::$localStats[$path] = ['hits' => 0, 'misses' => 0, 'calls' => 0];
        }
    }

    // ───────────────────────────────────────────────────────────────────
    //   Internals
    // ───────────────────────────────────────────────────────────────────

    /**
     * Sentinel marker used to distinguish "I haven't checked cache yet"
     * from "real value is null" in {@see wrap()}. We use an array
     * (not a stdClass) so it survives JSON round-trip through the
     * file cache layer cleanly.
     */
    private const SENTINEL = ['__hotpath_miss__' => true];

    /**
     * Wraps {@see CacheService::cache()} and bumps the per-path hit/miss
     * counter. Falls back to invoking the callback directly if the cache
     * layer itself fails.
     */
    private static function wrap(string $path, string $key, int $ttl, callable $callback)
    {
        $sentinel = self::SENTINEL;

        try {
            $value = CacheService::cache($key, $ttl, function () use ($path, $sentinel) {
                // Real miss — return sentinel so wrap() knows to fire the
                // real callback and re-store the result.
                self::$localStats[$path]['misses']++;
                return $sentinel;
            });
        } catch (\Throwable $e) {
            error_log('HotPathCacheService::wrap [' . $path . ']: ' . $e->getMessage());
            $value = $sentinel;
        }

        // Sentinel == "miss"; fire the real callback and store the result.
        // Use array equality (not ===) because JSON round-trip preserves
        // shape but not object identity.
        $isMiss = (is_array($value) && isset($value['__hotpath_miss__']) && $value['__hotpath_miss__'] === true);
        if ($isMiss) {
            try {
                $value = $callback();
            } catch (\Throwable $e) {
                error_log('HotPathCacheService::wrap callback [' . $path . ']: ' . $e->getMessage());
                $value = [];
            }
            try {
                // Drop any cached sentinel and re-store the real payload.
                CacheService::invalidate($key);
                CacheService::cache($key, $ttl, function () use ($value) {
                    return $value;
                });
            } catch (\Throwable $e) {
            // best-effort write
            error_log($e->getMessage());
            }
        } else {
            self::$localStats[$path]['hits']++;
        }

        return $value;
    }

    private static function bumpCall(string $path): void
    {
        self::$localStats[$path]['calls']++;
    }

    /**
     * Build a stable cache key from filter+page+perPage+sort.
     */
    private static function propertyListKey(array $filters, int $page, int $perPage, string $sort): string
    {
        ksort($filters);
        $payload = [
            'f'  => $filters,
            'p'  => $page,
            'pp' => $perPage,
            's'  => $sort,
        ];
        return 'properties_list_' . md5(json_encode($payload));
    }
}

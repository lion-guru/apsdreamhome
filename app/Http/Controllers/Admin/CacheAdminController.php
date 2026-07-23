<?php

namespace App\Http\Controllers\Admin;

use App\Services\CacheService;
use App\Services\Cache\HotPathCacheService;
use Exception;

/**
 * Admin controller for inspecting, flushing and testing the cache layer.
 *
 * Routes (registered in routes/web.php):
 *   GET  /admin/cache                  -> index()
 *   POST /admin/cache/flush            -> flush()
 *   POST /admin/cache/redis/flush      -> flushRedis()
 *   POST /admin/cache/test             -> test()
 *   GET  /admin/cache/stats            -> stats() (JSON)
 *   POST /admin/cache/hotpath/flush    -> flushHotpath()
 *   GET  /admin/cache/hotpath/stats    -> hotpathStats() (JSON)
 */
class CacheAdminController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->data = [];
    }

    /**
     * Show cache stats + action buttons.
     */
    public function index()
    {
        $this->requireAdmin();

        $stats = CacheService::getStats();
        $test = CacheService::testConnection();
        $hotpath = HotPathCacheService::getStats();

        return $this->render('admin/cache', [
            'page_title'   => 'Cache Management',
            'page_heading' => 'Cache Management',
            'stats'        => $stats,
            'test'         => $test,
            'driver'       => $test['driver'],
            'hotpath'      => $hotpath,
        ]);
    }

    /**
     * Flush both Redis and file cache.
     */
    public function flush()
    {
        $this->requireAdmin();
        try {
            CacheService::flushAll();
            // Also drop hot-path keys (the wildcard pattern catches them).
            HotPathCacheService::invalidatePropertyList();
            HotPathCacheService::invalidateHeaderProjects();
            HotPathCacheService::invalidateHomeFeatured();
            HotPathCacheService::invalidateAdminDashboard();
            $this->setFlash('success', 'All cache layers flushed successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Flush failed: ' . $e->getMessage());
        }
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/cache');
        exit;
    }

    /**
     * Flush Redis only (keep file cache intact).
     */
    public function flushRedis()
    {
        $this->requireAdmin();
        try {
            $ok = CacheService::flushRedis();
            if ($ok) {
                $this->setFlash('success', 'Redis cache flushed.');
            } else {
                $this->setFlash('warning', 'Redis was not available — nothing to flush.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Redis flush failed: ' . $e->getMessage());
        }
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/cache');
        exit;
    }

    /**
     * Run a Redis connection test and show the result.
     */
    public function test()
    {
        $this->requireAdmin();
        try {
            $result = CacheService::testConnection();
            $msg = $result['available']
                ? 'Redis connection OK (' . $result['latency_ms'] . 'ms).'
                : 'Redis unavailable — using file cache fallback.';
            $this->setFlash($result['available'] ? 'success' : 'warning', $msg);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Test failed: ' . $e->getMessage());
        }
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/cache');
        exit;
    }

    /**
     * JSON endpoint used by the auto-refresh widget on the cache page.
     */
    public function stats()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        echo json_encode(CacheService::getStats());
        exit;
    }

    /**
     * Flush ONLY the hot-path cache keys (5 hot paths × any combo of pages/users).
     * Does not touch generic admin_menu / header / unread / dashboard keys.
     */
    public function flushHotpath()
    {
        $this->requireAdmin();
        try {
            $dropped = 0;
            $dropped += HotPathCacheService::invalidatePropertyList();
            $dropped += HotPathCacheService::invalidateHeaderProjects();
            $dropped += HotPathCacheService::invalidateHomeFeatured();
            $dropped += HotPathCacheService::invalidateAdminDashboard();
            // saved-searches counts are per-user, so we pattern-drop them.
            $dropped += \App\Services\CacheService::invalidatePattern('saved_searches_count_');
            $this->setFlash('success', "Hot-path cache flushed ({$dropped} keys dropped).");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Hot-path flush failed: ' . $e->getMessage());
        }
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/cache');
        exit;
    }

    /**
     * JSON endpoint: returns per-path hot-path hit/miss stats.
     */
    public function hotpathStats()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        echo json_encode(HotPathCacheService::getStats());
        exit;
    }
}

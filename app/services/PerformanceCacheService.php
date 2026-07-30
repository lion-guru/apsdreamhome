<?php
namespace App\Services;
use App\Core\Cache;

class PerformanceCacheService
{
    private $cache;
    private $ttl;

    public function __construct(int $ttl = 3600)
    {
        $this->cache = new Cache();
        $this->ttl = $ttl;
    }

    /**
     * Tenant-aware cache key prefix.
     */
    private static function tenantPrefix(): string
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
        // fail open
        error_log($e->getMessage());
        }
        return '';
    }

    private static function tenantKey(string $key): string
    {
        return self::tenantPrefix() . $key;
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $key = self::tenantKey($key);
        $ttl = $ttl ?? $this->ttl;
        $cached = $this->cache->get($key);
        if ($cached !== null) return $cached;

        $value = $callback();
        $this->cache->set($key, $value, $ttl);
        return $value;
    }

    public function get(string $key): mixed
    {
        return $this->cache->get(self::tenantKey($key));
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->cache->set(self::tenantKey($key), $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return $this->cache->delete(self::tenantKey($key));
    }

    public function flush(): bool
    {
        return $this->cache->clear();
    }

    public function getStats(): array
    {
        return $this->cache->getStats();
    }

    // Dashboard-specific caching
    public function cacheDashboardStats(callable $callback): array
    {
        return $this->remember('dashboard_stats', $callback, 300);
    }

    public function cachePropertyStats(callable $callback): array
    {
        return $this->remember('property_stats', $callback, 600);
    }

    public function cacheLeadStats(callable $callback): array
    {
        return $this->remember('lead_stats', $callback, 300);
    }

    public function invalidateDashboard(): void
    {
        $this->forget('dashboard_stats');
        $this->forget('property_stats');
        $this->forget('lead_stats');
    }
}

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

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->ttl;
        $cached = $this->cache->get($key);
        if ($cached !== null) return $cached;

        $value = $callback();
        $this->cache->set($key, $value, $ttl);
        return $value;
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return $this->cache->delete($key);
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

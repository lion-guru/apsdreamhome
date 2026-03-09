<?php

namespace App\Models;

use App\Models\Model;

/**
 * Performance Cache Model
 * Handles performance cache data operations
 */
class PerformanceCache extends Model
{
    protected static $table = 'performance_cache';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'cache_key',
        'cache_value',
        'cache_data',
        'expires_at',
        'cache_type',
        'size_bytes',
        'hit_count',
        'created_at',
        'updated_at'
    ];

    /**
     * Get cache by key
     */
    public static function getByKey(string $key): ?PerformanceCache
    {
        $cache = self::where('cache_key', $key)->first();
        
        if ($cache && $cache->isExpired()) {
            $cache->delete();
            return null;
        }
        
        return $cache;
    }

    /**
     * Get active cache entries
     */
    public static function getActive(): array
    {
        return self::where('expires_at', '>', date('Y-m-d H:i:s'))
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get expired cache entries
     */
    public static function getExpired(): array
    {
        return self::where('expires_at', '<=', date('Y-m-d H:i:s'))
                   ->orderBy('expires_at', 'asc')
                   ->get();
    }

    /**
     * Get cache by type
     */
    public static function getByType(string $type): array
    {
        return self::where('cache_type', $type)
                   ->where('expires_at', '>', date('Y-m-d H:i:s'))
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        $stats = [];

        // Total cache entries
        $stats['total_entries'] = self::count();

        // Active entries
        $stats['active_entries'] = self::where('expires_at', '>', date('Y-m-d H:i:s'))->count();

        // Expired entries
        $stats['expired_entries'] = self::where('expires_at', '<=', date('Y-m-d H:i:s'))->count();

        // Total size
        $sizeResult = self::raw("SELECT SUM(size_bytes) as total_size FROM " . static::$table);
        $stats['total_size'] = $sizeResult[0]['total_size'] ?? 0;

        // Cache by type
        $typeStats = self::raw("
            SELECT cache_type, COUNT(*) as count, SUM(size_bytes) as size 
            FROM " . static::$table . " 
            WHERE expires_at > NOW()
            GROUP BY cache_type
        ");

        $stats['by_type'] = [];
        foreach ($typeStats as $stat) {
            $stats['by_type'][$stat['cache_type']] = [
                'count' => $stat['count'],
                'size' => $stat['size']
            ];
        }

        // Hit statistics
        $hitStats = self::raw("
            SELECT SUM(hit_count) as total_hits, 
                   AVG(hit_count) as avg_hits,
                   MAX(hit_count) as max_hits
            FROM " . static::$table . "
            WHERE expires_at > NOW()
        ");

        $stats['hits'] = $hitStats[0] ?? [
            'total_hits' => 0,
            'avg_hits' => 0,
            'max_hits' => 0
        ];

        return $stats;
    }

    /**
     * Create or update cache
     */
    public static function createOrUpdate(string $key, $value, string $type = 'general', int $ttl = 3600): PerformanceCache
    {
        $existing = self::getByKey($key);
        
        $data = [
            'cache_key' => $key,
            'cache_value' => is_string($value) ? $value : serialize($value),
            'cache_data' => json_encode([
                'type' => gettype($value),
                'size' => strlen(serialize($value)),
                'created' => time()
            ]),
            'expires_at' => date('Y-m-d H:i:s', time() + $ttl),
            'cache_type' => $type,
            'size_bytes' => strlen(serialize($value)),
            'hit_count' => 0
        ];

        if ($existing) {
            $existing->update($data);
            return $existing;
        } else {
            return self::create($data);
        }
    }

    /**
     * Increment hit count
     */
    public function incrementHit(): void
    {
        $this->hit_count++;
        $this->save();
    }

    /**
     * Check if cache is expired
     */
    public function isExpired(): bool
    {
        return strtotime($this->expires_at) <= time();
    }

    /**
     * Get cache value
     */
    public function getValue()
    {
        $value = $this->cache_value;
        
        // Try to unserialize if it's serialized
        $unserialized = @unserialize($value);
        return $unserialized !== false ? $unserialized : $value;
    }

    /**
     * Get formatted size
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get time remaining
     */
    public function getTimeRemaining(): string
    {
        $remaining = strtotime($this->expires_at) - time();
        
        if ($remaining <= 0) {
            return 'Expired';
        }

        if ($remaining < 60) {
            return $remaining . ' seconds';
        } elseif ($remaining < 3600) {
            return floor($remaining / 60) . ' minutes';
        } elseif ($remaining < 86400) {
            return floor($remaining / 3600) . ' hours';
        } else {
            return floor($remaining / 86400) . ' days';
        }
    }

    /**
     * Get cache age
     */
    public function getAge(): string
    {
        $age = time() - strtotime($this->created_at);
        
        if ($age < 60) {
            return $age . ' seconds';
        } elseif ($age < 3600) {
            return floor($age / 60) . ' minutes';
        } elseif ($age < 86400) {
            return floor($age / 3600) . ' hours';
        } else {
            return floor($age / 86400) . ' days';
        }
    }

    /**
     * Clear expired cache
     */
    public static function clearExpired(): int
    {
        $expired = self::getExpired();
        $deleted = 0;
        
        foreach ($expired as $cache) {
            $cache->delete();
            $deleted++;
        }
        
        return $deleted;
    }

    /**
     * Clear cache by type
     */
    public static function clearByType(string $type): int
    {
        $caches = self::where('cache_type', $type)->get();
        $deleted = 0;
        
        foreach ($caches as $cache) {
            $cache->delete();
            $deleted++;
        }
        
        return $deleted;
    }

    /**
     * Clear all cache
     */
    public static function clearAll(): int
    {
        $caches = self::all();
        $deleted = 0;
        
        foreach ($caches as $cache) {
            $cache->delete();
            $deleted++;
        }
        
        return $deleted;
    }

    /**
     * Get most used cache entries
     */
    public static function getMostUsed(int $limit = 10): array
    {
        return self::where('expires_at', '>', date('Y-m-d H:i:s'))
                   ->orderBy('hit_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get largest cache entries
     */
    public static function getLargest(int $limit = 10): array
    {
        return self::where('expires_at', '>', date('Y-m-d H:i:s'))
                   ->orderBy('size_bytes', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Search cache by key
     */
    public static function searchByKey(string $term): array
    {
        return self::where('cache_key', 'LIKE', "%{$term}%")
                   ->orderBy('created_at', 'desc')
                   ->get();
    }
}

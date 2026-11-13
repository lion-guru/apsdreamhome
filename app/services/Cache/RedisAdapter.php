<?php

namespace App\Services\Cache;

/**
 * RedisAdapter — Redis cache with file-based fallback
 * 
 * If Redis is available (extension loaded + server reachable), uses Redis.
 * Otherwise falls back to file-based cache (FileCache).
 * 
 * Usage:
 *   $cache = new RedisAdapter();
 *   $cache->set('key', 'value', 3600);
 *   $value = $cache->get('key');
 *   $cache->delete('key');
 * 
 * Config (in config or .env):
 *   REDIS_HOST=127.0.0.1
 *   REDIS_PORT=6379
 *   REDIS_PASSWORD=
 *   REDIS_DB=0
 */
class RedisAdapter
{
    private $redis = null;
    private $fallback = null;
    private $available = false;
    private $prefix = 'aps:';

    public function __construct(array $config = [])
    {
        $host = $config['host'] ?? (getenv('REDIS_HOST') ?: '127.0.0.1');
        $port = (int)($config['port'] ?? (getenv('REDIS_PORT') ?: 6379));
        $password = $config['password'] ?? (getenv('REDIS_PASSWORD') ?: '');
        $db = (int)($config['db'] ?? (getenv('REDIS_DB') ?: 0));
        $this->prefix = $config['prefix'] ?? 'aps:';

        // Try Redis
        if (extension_loaded('redis')) {
            try {
                $this->redis = new \Redis();
                if ($this->redis->connect($host, $port, 2)) {
                    if (!empty($password)) {
                        $this->redis->auth($password);
                    }
                    $this->redis->select($db);
                    $this->redis->setOption(\Redis::OPT_PREFIX, $this->prefix);
                    $this->available = true;
                }
            } catch (\Throwable $e) {
                error_log("RedisAdapter: Redis unavailable, falling back to file cache. " . $e->getMessage());
                $this->redis = null;
                $this->available = false;
            }
        }

        // Fallback to file cache
        if (!$this->available) {
            $this->fallback = new FileCache($config);
        }
    }

    public function isRedisAvailable(): bool
    {
        return $this->available;
    }

    public function get(string $key)
    {
        if ($this->available) {
            try {
                $val = $this->redis->get($key);
                return $val === false ? null : $val;
            } catch (\Throwable $e) {
                return $this->fallback->get($key);
            }
        }
        return $this->fallback->get($key);
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        if ($this->available) {
            try {
                if ($ttl > 0) {
                    return $this->redis->setex($key, $ttl, $value);
                }
                return $this->redis->set($key, $value);
            } catch (\Throwable $e) {
                return $this->fallback->set($key, $value, $ttl);
            }
        }
        return $this->fallback->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        if ($this->available) {
            try {
                return $this->redis->del($key) > 0;
            } catch (\Throwable $e) {
                return $this->fallback->delete($key);
            }
        }
        return $this->fallback->delete($key);
    }

    public function has(string $key): bool
    {
        if ($this->available) {
            try {
                return $this->redis->exists($key) > 0;
            } catch (\Throwable $e) {
                return $this->fallback->has($key);
            }
        }
        return $this->fallback->has($key);
    }

    public function flush(): bool
    {
        if ($this->available) {
            try {
                return $this->redis->flushDB();
            } catch (\Throwable $e) {
                return $this->fallback->flush();
            }
        }
        return $this->fallback->flush();
    }

    public function remember(string $key, callable $callback, int $ttl = 3600)
    {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    public function getStats(): array
    {
        if ($this->available) {
            try {
                $info = $this->redis->info();
                return [
                    'driver' => 'redis',
                    'available' => true,
                    'version' => $info['redis_version'] ?? 'unknown',
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'used_memory' => $info['used_memory_human'] ?? 'unknown',
                    'keys' => $this->redis->dbSize(),
                ];
            } catch (\Throwable $e) { error_log('[' . __CLASS__ . ']: ' . $e->getMessage()); }
        }
        return array_merge(['driver' => 'file', 'available' => false], $this->fallback->getStats());
    }
}

/**
 * FileCache — file-based cache fallback
 */
class FileCache
{
    private $cacheDir;
    private $prefix = 'aps_';

    public function __construct(array $config = [])
    {
        $this->cacheDir = $config['cache_dir'] ?? (__DIR__ . '/../../storage/cache');
        $this->prefix = $config['prefix'] ?? 'aps_';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key)
    {
        $file = $this->cacheDir . '/' . md5($this->prefix . $key) . '.cache';
        if (!file_exists($file)) return null;
        $data = @file_get_contents($file);
        if ($data === false) return null;
        $decoded = @json_decode($data, true);
        if (!is_array($decoded)) return null;
        if (isset($decoded['expires']) && $decoded['expires'] < time()) {
            @unlink($file);
            return null;
        }
        return $decoded['value'] ?? null;
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $file = $this->cacheDir . '/' . md5($this->prefix . $key) . '.cache';
        $data = json_encode(['value' => $value, 'expires' => time() + $ttl]);
        return @file_put_contents($file, $data) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->cacheDir . '/' . md5($this->prefix . $key) . '.cache';
        if (file_exists($file)) {
            return @unlink($file);
        }
        return false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function flush(): bool
    {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $f) {
            @unlink($f);
        }
        return true;
    }

    public function getStats(): array
    {
        $files = glob($this->cacheDir . '/*.cache');
        $size = 0;
        foreach ($files as $f) {
            $size += filesize($f);
        }
        return [
            'files' => count($files),
            'size_bytes' => $size,
            'cache_dir' => $this->cacheDir,
        ];
    }
}
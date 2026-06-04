<?php

namespace App\Core;

use Exception;
use Throwable;

/**
 * RedisCache - Redis-backed cache with automatic file-based fallback.
 *
 * Connection is lazy: we only attempt to connect when the first
 * get/set/delete call is made. If Redis is unreachable (extension
 * missing, server down, auth failure, network timeout, etc.) we
 * transparently fall back to the file-based Cache class.
 *
 * All operations are wrapped in try/catch — a Redis outage must
 * never break the application.
 */
class RedisCache
{
    private static ?self $instance = null;

    private bool $available = false;
    private $client = null;
    private array $config;
    private string $prefix;

    /** @var array<string,int> In-process hit/miss statistics */
    private array $stats = [
        'hits'   => 0,
        'misses' => 0,
        'sets'   => 0,
        'deletes'=> 0,
        'errors' => 0,
        'evictions' => 0,
    ];

    /**
     * Singleton accessor.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reset singleton — used in tests and after config reload.
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    private function __construct()
    {
        $this->config = $this->loadConfig();
        $this->prefix = $this->config['prefix'] ?? 'apsdream_';
    }

    /**
     * Load cache configuration from config/cache.php + env overrides.
     */
    private function loadConfig(): array
    {
        $configPath = defined('APP_ROOT')
            ? APP_ROOT . '/config/cache.php'
            : __DIR__ . '/../../config/cache.php';

        $config = [];
        if (file_exists($configPath)) {
            $loaded = @include $configPath;
            if (is_array($loaded)) {
                $config = $loaded;
            }
        }

        return [
            'driver'   => getenv('CACHE_DRIVER') ?: ($config['driver'] ?? 'redis'),
            'fallback' => getenv('CACHE_FALLBACK') ?: ($config['fallback'] ?? 'file'),
            'prefix'   => getenv('CACHE_PREFIX') ?: ($config['prefix'] ?? 'apsdream_'),
            'redis'    => [
                'host'     => getenv('REDIS_HOST')     ?: ($config['redis']['host']     ?? '127.0.0.1'),
                'port'     => (int)(getenv('REDIS_PORT') ?: ($config['redis']['port']  ?? 6379)),
                'password' => getenv('REDIS_PASSWORD') ?: ($config['redis']['password'] ?? null),
                'database' => (int)(getenv('REDIS_DB')  ?: ($config['redis']['database'] ?? 0)),
                'timeout'  => (float)(getenv('REDIS_TIMEOUT') ?: ($config['redis']['timeout'] ?? 2.0)),
            ],
        ];
    }

    /**
     * Lazy connect to Redis. Returns true if connection succeeded.
     */
    private function connect(): bool
    {
        if ($this->available) {
            return true;
        }
        if ($this->config['driver'] !== 'redis') {
            return false;
        }
        if (!extension_loaded('redis')) {
            return false;
        }
        try {
            $this->client = new \Redis();
            $host = $this->config['redis']['host'];
            $port = $this->config['redis']['port'];
            $timeout = $this->config['redis']['timeout'];
            $ok = $this->client->connect($host, $port, $timeout);
            if (!$ok) {
                $this->client = null;
                return false;
            }
            if (!empty($this->config['redis']['password'])) {
                $this->client->auth($this->config['redis']['password']);
            }
            $db = $this->config['redis']['database'];
            if ($db > 0) {
                $this->client->select($db);
            }
            $this->client->setOption(\Redis::OPT_PREFIX, $this->prefix);
            $this->client->setOption(\Redis::OPT_SERIALIZER, (string)\Redis::SERIALIZER_NONE);
            $this->available = true;
            return true;
        } catch (Throwable $e) {
            $this->client = null;
            $this->stats['errors']++;
            error_log('[RedisCache] connect failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns true if Redis is configured AND we have a live connection.
     */
    public function isAvailable(): bool
    {
        return $this->available || $this->connect();
    }

    /**
     * Get value from cache. Returns $default on miss or failure.
     */
    public function get(string $key, $default = null)
    {
        try {
            if ($this->connect()) {
                $raw = $this->client->get($key);
                if ($raw === false || $raw === null) {
                    $this->stats['misses']++;
                    return $default;
                }
                $decoded = $this->unserialize($raw);
                $this->stats['hits']++;
                return $decoded;
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
            error_log('[RedisCache] get(' . $key . ') failed: ' . $e->getMessage());
            $this->available = false;
            $this->client = null;
        }
        $this->stats['misses']++;
        return $default;
    }

    /**
     * Set value in cache with TTL (seconds). Returns true on success.
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            if ($this->connect()) {
                $payload = $this->serialize($value);
                $result = $ttl > 0
                    ? $this->client->setex($key, $ttl, $payload)
                    : $this->client->set($key, $payload);
                if ($result) {
                    $this->stats['sets']++;
                    return true;
                }
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
            error_log('[RedisCache] set(' . $key . ') failed: ' . $e->getMessage());
            $this->available = false;
            $this->client = null;
        }
        return false;
    }

    /**
     * Delete one key. Returns true on success or if key didn't exist.
     */
    public function delete(string $key): bool
    {
        try {
            if ($this->connect()) {
                $this->client->del($key);
                $this->stats['deletes']++;
                return true;
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
            error_log('[RedisCache] delete(' . $key . ') failed: ' . $e->getMessage());
            $this->available = false;
            $this->client = null;
        }
        return false;
    }

    /**
     * Check if a key exists.
     */
    public function has(string $key): bool
    {
        try {
            if ($this->connect()) {
                return (bool)$this->client->exists($key);
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
            error_log('[RedisCache] has(' . $key . ') failed: ' . $e->getMessage());
            $this->available = false;
            $this->client = null;
        }
        return false;
    }

    /**
     * Delete all keys matching a glob-style pattern.
     * Uses SCAN (non-blocking) for production safety.
     */
    public function deletePattern(string $pattern): int
    {
        $count = 0;
        try {
            if ($this->connect()) {
                $fullPattern = $this->prefix . $pattern . '*';
                $iter = null;
                $this->client->setOption(\Redis::OPT_SCAN, (string)\Redis::SCAN_RETRY);
                while (($keys = $this->client->scan($iter, $fullPattern, 200)) !== false) {
                    if (!empty($keys)) {
                        $this->client->del($keys);
                        $count += count($keys);
                    }
                }
                $this->stats['deletes'] += $count;
                $this->stats['evictions'] += $count;
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
            error_log('[RedisCache] deletePattern(' . $pattern . ') failed: ' . $e->getMessage());
            $this->available = false;
            $this->client = null;
        }
        return $count;
    }

    /**
     * Flush entire Redis database.
     */
    public function flush(): bool
    {
        try {
            if ($this->connect()) {
                $this->client->flushDB();
                return true;
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
            error_log('[RedisCache] flush failed: ' . $e->getMessage());
            $this->available = false;
            $this->client = null;
        }
        return false;
    }

    /**
     * Get-or-set pattern. Returns cached value or executes callback and caches the result.
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Return Redis server info (or empty array if unavailable).
     */
    public function info(): array
    {
        try {
            if ($this->connect()) {
                $info = $this->client->info();
                return is_array($info) ? $info : [];
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
        }
        return [];
    }

    /**
     * Return the number of keys currently in the Redis db.
     */
    public function size(): int
    {
        try {
            if ($this->connect()) {
                return (int)$this->client->dbSize();
            }
        } catch (Throwable $e) {
            $this->stats['errors']++;
        }
        return 0;
    }

    /**
     * Return current statistics (hits, misses, sets, deletes, errors, evictions).
     */
    public function getStats(): array
    {
        return array_merge($this->stats, [
            'driver'   => $this->config['driver'],
            'available'=> $this->isAvailable(),
            'host'     => $this->config['redis']['host'],
            'port'     => $this->config['redis']['port'],
            'prefix'   => $this->prefix,
        ]);
    }

    /**
     * Reset in-process hit/miss counters. Useful for benchmarking.
     */
    public function resetStats(): void
    {
        $this->stats = [
            'hits'   => 0,
            'misses' => 0,
            'sets'   => 0,
            'deletes'=> 0,
            'errors' => 0,
            'evictions' => 0,
        ];
    }

    /**
     * Encode a value to a string. JSON is used by default for portability.
     */
    private function serialize($value): string
    {
        return json_encode([
            'v' => $value,
            't' => time(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Decode a value produced by serialize().
     */
    private function unserialize(string $raw)
    {
        $data = json_decode($raw, true);
        if (!is_array($data) || !array_key_exists('v', $data)) {
            return null;
        }
        return $data['v'];
    }
}

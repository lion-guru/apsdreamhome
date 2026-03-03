<?php

namespace App\Services\Legacy;
/**
 * Rate Limiter Class
 * Implements rate limiting using Redis or file-based storage
 */

class RateLimiter {
    private $storage;
    private $prefix = 'rate_limit:';
    private $defaultRules = [
        'api' => ['requests' => 100, 'period' => 3600], // 100 requests per hour
        'login' => ['requests' => 5, 'period' => 300],  // 5 attempts per 5 minutes
        'register' => ['requests' => 3, 'period' => 3600], // 3 attempts per hour
        'contact' => ['requests' => 10, 'period' => 3600], // 10 requests per hour
        'search' => ['requests' => 30, 'period' => 60]    // 30 requests per minute
    ];

    public function __construct() {
        // Try Redis first, fallback to file-based storage
        if (extension_loaded('redis')) {
            try {
                $redis = new Redis();
                $redis->connect('127.0.0.1', 6379);
                $this->storage = new RedisStorage($redis);
            } catch (Exception $e) {
                $this->storage = new FileStorage();
            }
        } else {
            $this->storage = new FileStorage();
        }
    }

    /**
     * Check if the request should be rate limited
     */
    public function checkLimit($key, $type = 'api') {
        $identifier = $this->getIdentifier($key);
        $rule = $this->defaultRules[$type] ?? $this->defaultRules['api'];
        
        $current = $this->storage->get($identifier);
        if ($current === false) {
            $current = ['count' => 0, 'reset' => time() + $rule['period']];
        }

        // Reset counter if period has expired
        if (time() >= $current['reset']) {
            $current = ['count' => 0, 'reset' => time() + $rule['period']];
        }

        // Check if limit is exceeded
        if ($current['count'] >= $rule['requests']) {
            return [
                'limited' => true,
                'remaining' => 0,
                'reset' => $current['reset'],
                'retry_after' => $current['reset'] - time()
            ];
        }

        // Increment counter
        $current['count']++;
        $this->storage->set($identifier, $current, $rule['period']);

        return [
            'limited' => false,
            'remaining' => $rule['requests'] - $current['count'],
            'reset' => $current['reset'],
            'retry_after' => null
        ];
    }

    /**
     * Get unique identifier for rate limiting
     */
    private function getIdentifier($key) {
        return $this->prefix . $key . '_' . $this->getClientIp();
    }

    /**
     * Get client IP address
     */
    private function getClientIp() {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy
            'HTTP_X_REAL_IP',        // Nginx proxy
            'REMOTE_ADDR'            // Direct
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

/**
 * Redis Storage Implementation
 */
class RedisStorage {
    private $redis;

    public function __construct($redis) {
        $this->redis = $redis;
    }

    public function get($key) {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : false;
    }

    public function set($key, $value, $ttl) {
        return $this->redis->setex($key, $ttl, json_encode($value));
    }
}

/**
 * File-based Storage Implementation
 */
class FileStorage {
    private $storageDir;

    public function __construct() {
        $this->storageDir = __DIR__ . '/../storage/rate_limits';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function get($key) {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) {
            return false;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['expires']) || time() >= $data['expires']) {
            @unlink($file);
            return false;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl) {
        $file = $this->getFilePath($key);
        $data = [
            'expires' => time() + $ttl,
            'value' => $value
        ];

        return file_put_contents($file, json_encode($data)) !== false;
    }

    private function getFilePath($key) {
        return $this->storageDir . '/' . preg_replace('/[^a-zA-Z0-9]/', '_', $key) . '.json';
    }
}

// Create global rate limiter instance
$rateLimiter = new RateLimiter();


<?php
/**
 * Cache Class
 * 
 * Provides a simple file-based caching system
 */
class Cache {
    /** @var string Cache directory */
    private $cacheDir;
    
    /** @var int Default cache time-to-live in seconds */
    private $defaultTtl = 3600; // 1 hour
    
    /** @var Cache Singleton instance */
    private static $instance = null;
    
    /**
     * Get the singleton instance
     * 
     * @return Cache The cache instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     * 
     * @param string $cacheDir Custom cache directory (optional)
     */
    private function __construct(string $cacheDir = null) {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../cache';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        // Ensure the cache directory is writable
        if (!is_writable($this->cacheDir)) {
            throw new RuntimeException(sprintf('Cache directory is not writable: %s', $this->cacheDir));
        }
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
    
    /**
     * Set a cache item
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache (must be serializable)
     * @param int $ttl Time to live in seconds (0 = unlimited)
     * @return bool True on success, false on failure
     */
    public function set(string $key, $value, int $ttl = null): bool {
        $ttl = $ttl ?? $this->defaultTtl;
        $expires = $ttl > 0 ? time() + $ttl : 0;
        
        $cacheFile = $this->getCacheFilePath($key);
        $cacheData = [
            'expires' => $expires,
            'data' => $value
        ];
        
        $result = file_put_contents(
            $cacheFile, 
            serialize($cacheData), 
            LOCK_EX
        );
        
        return $result !== false;
    }
    
    /**
     * Get a cache item
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found or expired
     * @return mixed Cached data or default value
     */
    public function get(string $key, $default = null) {
        $cacheFile = $this->getCacheFilePath($key);
        
        // Check if cache file exists
        if (!file_exists($cacheFile) || !is_readable($cacheFile)) {
            return $default;
        }
        
        // Read and unserialize cache data
        $cacheData = @unserialize(file_get_contents($cacheFile));
        if ($cacheData === false) {
            // Invalid cache data, remove the file
            @unlink($cacheFile);
            return $default;
        }
        
        // Check if cache has expired
        if ($cacheData['expires'] > 0 && $cacheData['expires'] < time()) {
            // Cache expired, remove the file
            @unlink($cacheFile);
            return $default;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * Delete a cache item
     * 
     * @param string $key Cache key
     * @return bool True if the item was removed, false otherwise
     */
    public function delete(string $key): bool {
        $cacheFile = $this->getCacheFilePath($key);
        
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return false;
    }
    
    /**
     * Check if a cache item exists and is not expired
     * 
     * @param string $key Cache key
     * @return bool True if the item exists and is not expired
     */
    public function has(string $key): bool {
        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile) || !is_readable($cacheFile)) {
            return false;
        }
        
        $cacheData = @unserialize(file_get_contents($cacheFile));
        if ($cacheData === false) {
            return false;
        }
        
        // Check if cache has expired
        if ($cacheData['expires'] > 0 && $cacheData['expires'] < time()) {
            // Cache expired, remove the file
            @unlink($cacheFile);
            return false;
        }
        
        return true;
    }
    
    /**
     * Clear all cache items
     * 
     * @param string $prefix Optional key prefix to clear only matching items
     * @return int Number of cache items cleared
     */
    public function clear(string $prefix = ''): int {
        $count = 0;
        $files = glob($this->cacheDir . '/' . $prefix . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Clean up expired cache items
     * 
     * @return int Number of expired cache items removed
     */
    public function cleanup(): int {
        $count = 0;
        $files = glob($this->cacheDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $cacheData = @unserialize(file_get_contents($file));
                
                if ($cacheData !== false && 
                    $cacheData['expires'] > 0 && 
                    $cacheData['expires'] < time()) {
                    
                    if (@unlink($file)) {
                        $count++;
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Get the full filesystem path for a cache key
     * 
     * @param string $key Cache key
     * @return string Full filesystem path
     */
    private function getCacheFilePath(string $key): string {
        // Sanitize the key to create a valid filename
        $key = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->cacheDir . '/' . $key . '.cache';
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public function getStats(): array {
        $stats = [
            'total_items' => 0,
            'total_size' => 0,
            'expired_items' => 0,
            'expired_size' => 0,
            'valid_items' => 0,
            'valid_size' => 0
        ];
        
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                $stats['total_items']++;
                $stats['total_size'] += $size;
                
                $cacheData = @unserialize(file_get_contents($file));
                
                if ($cacheData === false || 
                    ($cacheData['expires'] > 0 && $cacheData['expires'] < time())) {
                    
                    $stats['expired_items']++;
                    $stats['expired_size'] += $size;
                } else {
                    $stats['valid_items']++;
                    $stats['valid_size'] += $size;
                }
            }
        }
        
        // Format sizes for display
        $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);
        $stats['expired_size_formatted'] = $this->formatBytes($stats['expired_size']);
        $stats['valid_size_formatted'] = $this->formatBytes($stats['valid_size']);
        
        return $stats;
    }
    
    /**
     * Format bytes to a human-readable string
     * 
     * @param int $bytes Number of bytes
     * @param int $precision Number of decimal places
     * @return string Formatted string with units
     */
    private function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

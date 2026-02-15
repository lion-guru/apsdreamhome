<?php

namespace App\Services\Legacy;
/**
 * Caching System for APS Dream Home
 * Provides browser caching and server-side caching mechanisms
 */

class CacheManager {
    private $cache_dir;
    private $default_ttl = 3600; // 1 hour default
    
    public function __construct($cache_dir = null) {
        $this->cache_dir = $cache_dir ?: __DIR__ . '/cache/';
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Set browser cache headers
     */
    public function setBrowserCache($ttl = 3600, $public = true) {
        $expires = gmdate('D, d M Y H:i:s T', time() + $ttl);
        $max_age = $ttl;
        $cache_control = $public ? 'public' : 'private';
        
        header("Cache-Control: {$cache_control}, max-age={$max_age}, must-revalidate");
        header("Expires: {$expires}");
        header("Pragma: cache");
        
        // Add ETag for better caching
        $etag = md5($expires);
        header("ETag: \"{$etag}\"");
        
        // Handle If-None-Match
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === "\"{$etag}\"") {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }
    
    /**
     * Cache dynamic content
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?: $this->default_ttl;
        $cache_file = $this->getCacheFile($key);
        
        $cache_data = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($cache_file, serialize($cache_data));
    }
    
    /**
     * Get cached content
     */
    public function get($key) {
        $cache_file = $this->getCacheFile($key);
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        $cache_data = unserialize(file_get_contents($cache_file));
        
        if ($cache_data['expires'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * Delete cached content
     */
    public function delete($key) {
        $cache_file = $this->getCacheFile($key);
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        $files = glob($this->cache_dir . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
    
    /**
     * Get cache file path
     */
    private function getCacheFile($key) {
        $filename = md5($key) . '.cache';
        return $this->cache_dir . $filename;
    }
    
    /**
     * Cache database query results
     */
    public function cacheQuery($query, $params, $result, $ttl = 1800) {
        $key = 'query_' . md5($query . serialize($params));
        return $this->set($key, $result, $ttl);
    }
    
    /**
     * Get cached query result
     */
    public function getCachedQuery($query, $params) {
        $key = 'query_' . md5($query . serialize($params));
        return $this->get($key);
    }
    
    /**
     * Cache API responses
     */
    public function cacheAPIResponse($endpoint, $params, $response, $ttl = 3600) {
        $key = 'api_' . md5($endpoint . serialize($params));
        return $this->set($key, $response, $ttl);
    }
    
    /**
     * Get cached API response
     */
    public function getCachedAPIResponse($endpoint, $params) {
        $key = 'api_' . md5($endpoint . serialize($params));
        return $this->get($key);
    }
    
    /**
     * Cache rendered HTML
     */
    public function cacheHTML($key, $html, $ttl = 3600) {
        return $this->set('html_' . $key, $html, $ttl);
    }
    
    /**
     * Get cached HTML
     */
    public function getCachedHTML($key) {
        return $this->get('html_' . $key);
    }
    
    /**
     * Cache image metadata
     */
    public function cacheImageMetadata($image_path, $metadata, $ttl = 86400) {
        $key = 'img_meta_' . md5($image_path);
        return $this->set($key, $metadata, $ttl);
    }
    
    /**
     * Get cached image metadata
     */
    public function getCachedImageMetadata($image_path) {
        $key = 'img_meta_' . md5($image_path);
        return $this->get($key);
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        $files = glob($this->cache_dir . '*.cache');
        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'oldest_file' => null,
            'newest_file' => null
        ];
        
        if (empty($files)) {
            return $stats;
        }
        
        $timestamps = [];
        foreach ($files as $file) {
            $stats['total_size'] += filesize($file);
            $timestamps[] = filemtime($file);
        }
        
        $stats['total_size'] = round($stats['total_size'] / 1024 / 1024, 2) . ' MB';
        $stats['oldest_file'] = date('Y-m-d H:i:s', min($timestamps));
        $stats['newest_file'] = date('Y-m-d H:i:s', max($timestamps));
        
        return $stats;
    }
}

// Usage example
if (php_sapi_name() === 'cli') {
    echo "APS Dream Home Cache Manager\n";
    echo "============================\n\n";
    
    $cache = new CacheManager();
    
    echo "Cache Statistics:\n";
    $stats = $cache->getStats();
    echo "Total files: {$stats['total_files']}\n";
    echo "Total size: {$stats['total_size']}\n";
    if ($stats['oldest_file']) {
        echo "Oldest file: {$stats['oldest_file']}\n";
        echo "Newest file: {$stats['newest_file']}\n";
    }
}
?>

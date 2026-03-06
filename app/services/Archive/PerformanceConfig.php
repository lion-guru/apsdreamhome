<?php

namespace App\Services\Legacy;
// Performance Optimization Configuration

// Caching settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_DIR', __DIR__ . '/../cache');

// Performance logging
function log_performance_event($message, $execution_time = null) {
    $log_dir = __DIR__ . '/../logs/performance';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/performance_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_message = "[{$timestamp}] {$message}";
    if ($execution_time !== null) {
        $log_message .= " (Execution Time: {$execution_time} seconds)";
    }
    $log_message .= "\n";
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Simple caching mechanism
class PerformanceCache {
    private static $instance = null;
    private $cache = [];
    
    private function __construct() {
        if (!is_dir(CACHE_DIR)) {
            mkdir(CACHE_DIR, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key) {
        if (!CACHE_ENABLED) return null;
        
        $cache_file = CACHE_DIR . '/' . md5($key) . '.cache';
        
        if (file_exists($cache_file) && (time() - filemtime($cache_file) < CACHE_LIFETIME)) {
            return unserialize(file_get_contents($cache_file));
        }
        
        return null;
    }
    
    public function set($key, $value) {
        if (!CACHE_ENABLED) return false;
        
        $cache_file = CACHE_DIR . '/' . md5($key) . '.cache';
        
        return file_put_contents($cache_file, serialize($value)) !== false;
    }
    
    public function clear($key = null) {
        if ($key === null) {
            // Clear all cache files
            $files = glob(CACHE_DIR . '/*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            // Clear specific cache file
            $cache_file = CACHE_DIR . '/' . md5($key) . '.cache';
            if (file_exists($cache_file)) {
                unlink($cache_file);
            }
        }
    }
}

// Performance measurement wrapper
function measure_performance($callback) {
    $start_time = microtime(true);
    
    try {
        $result = $callback();
        
        $execution_time = round(microtime(true) - $start_time, 4);
        
        // Log performance if execution takes more than 0.5 seconds
        if ($execution_time > 0.5) {
            log_performance_event("Slow execution detected", $execution_time);
        }
        
        return $result;
    } catch (Exception $e) {
        log_performance_event("Performance measurement error: " . $e->getMessage());
        throw $e;
    }
}

// PHP Configuration Optimizations
ini_set('opcache.enable', 1);
ini_set('opcache.memory_consumption', 128);
ini_set('opcache.interned_strings_buffer', 8);
ini_set('opcache.max_accelerated_files', 4000);
ini_set('opcache.revalidate_freq', 60);
ini_set('opcache.fast_shutdown', 1);

// Output compression
if (!ob_get_level()) {
    ob_start('ob_gzhandler');
}

// Error reporting for production
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

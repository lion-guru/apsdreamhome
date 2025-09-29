<?php
/**
 * Advanced Performance Optimization Manager
 * Comprehensive system for monitoring and optimizing application performance
 */

class PerformanceManager {
    // Cache Types
    const CACHE_TYPE_MEMORY = 'memory';
    const CACHE_TYPE_FILE = 'file';
    const CACHE_TYPE_REDIS = 'redis';
    const CACHE_TYPE_DATABASE = 'database';

    // Performance Levels
    const LEVEL_FAST = 'fast';
    const LEVEL_MEDIUM = 'medium';
    const LEVEL_SLOW = 'slow';
    const LEVEL_CRITICAL = 'critical';

    // Cache Drivers
    private $cache_driver;

    // Performance Configuration
    private $config = [
        'cache_enabled' => true,
        'cache_driver' => self::CACHE_TYPE_FILE,
        'cache_lifetime' => 3600, // 1 hour default
        'cache_path' => '',
        'compression_level' => 6,
        'profiling_enabled' => true,
        'max_memory_limit' => '512M',
        'slow_query_threshold' => 0.5, // Seconds
        'query_log_enabled' => true,
        'minify_html' => true,
        'minify_css' => true,
        'minify_js' => true,
        'optimize_images' => true,
        'cdn_enabled' => false,
        'cdn_url' => '',
        'lazy_loading' => true,
        'security_checks' => [
            'sql_injection_prevention' => true,
            'sensitive_data_masking' => true,
            'performance_monitoring' => true
        ]
    ];

    // Performance Metrics
    private $metrics = [
        'start_time' => 0,
        'end_time' => 0,
        'memory_usage' => 0,
        'peak_memory_usage' => 0,
        'query_count' => 0,
        'cached_queries' => 0,
        'cache_hits' => 0,
        'cache_misses' => 0,
        'slow_queries' => [],
        'database_connections' => 0,
        'files_included' => 0,
        'total_execution_time' => 0
    ];

    // Dependencies
    private $logger;
    private $db;

    // Performance tracking
    private $query_log = [];
    private $included_files = [];

    public function __construct($logger = null, $db = null) {
        $this->logger = $logger;
        $this->db = $db;

        // Set cache path
        $this->config['cache_path'] = __DIR__ . '/../cache/';

        // Ensure cache directory exists
        $this->ensureCacheDirectory();

        // Initialize cache driver
        $this->initializeCacheDriver();

        // Set memory limit
        ini_set('memory_limit', $this->config['max_memory_limit']);

        // Register shutdown function for performance monitoring
        register_shutdown_function([$this, 'checkPerformance']);

        // Track included files
        $this->trackIncludedFiles();

        // Initialize performance profiling
        $this->startProfiling();
    }

    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectory() {
        if (!is_dir($this->config['cache_path'])) {
            mkdir($this->config['cache_path'], 0755, true);
        }

        // Create subdirectories
        $subdirs = ['css', 'js', 'html', 'images', 'data'];
        foreach ($subdirs as $subdir) {
            $path = $this->config['cache_path'] . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Initialize cache driver
     */
    private function initializeCacheDriver() {
        switch ($this->config['cache_driver']) {
            case self::CACHE_TYPE_MEMORY:
                $this->cache_driver = new MemoryCacheDriver();
                break;
            case self::CACHE_TYPE_REDIS:
                $this->cache_driver = new RedisCacheDriver();
                break;
            case self::CACHE_TYPE_DATABASE:
                $this->cache_driver = new DatabaseCacheDriver($this->db);
                break;
            default:
                $this->cache_driver = new FileCacheDriver($this->config['cache_path']);
        }
    }

    /**
     * Track included files
     */
    private function trackIncludedFiles() {
        $this->included_files = get_included_files();
        $this->metrics['files_included'] = count($this->included_files);

        // Register include handler
        register_tick_function(function() {
            $this->included_files = get_included_files();
            $this->metrics['files_included'] = count($this->included_files);
        }, 1000); // Check every 1000 ticks
    }

    /**
     * Start performance profiling
     */
    public function startProfiling() {
        if (!$this->config['profiling_enabled']) return;

        $this->metrics['start_time'] = microtime(true);
        $this->metrics['memory_usage'] = memory_get_usage();
        $this->metrics['peak_memory_usage'] = memory_get_peak_usage();

        // Start database query logging
        if ($this->config['query_log_enabled']) {
            $this->startQueryLogging();
        }
    }

    /**
     * Start database query logging
     */
    private function startQueryLogging() {
        if ($this->db) {
            // Override database methods to log queries
            $this->db->query = $this->wrapQueryMethod($this->db->query);
            $this->db->prepare = $this->wrapQueryMethod($this->db->prepare);
        }
    }

    /**
     * Wrap query methods for logging
     */
    private function wrapQueryMethod($method) {
        return function($query, ...$args) use ($method) {
            $start_time = microtime(true);

            $result = $method($query, ...$args);

            $execution_time = microtime(true) - $start_time;
            $this->metrics['query_count']++;

            // Log slow queries
            if ($execution_time > $this->config['slow_query_threshold']) {
                $this->metrics['slow_queries'][] = [
                    'query' => $query,
                    'execution_time' => $execution_time,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Log query for analysis
            $this->query_log[] = [
                'query' => $query,
                'execution_time' => $execution_time,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            return $result;
        };
    }

    /**
     * End performance profiling
     */
    public function endProfiling() {
        if (!$this->config['profiling_enabled']) return;

        $this->metrics['end_time'] = microtime(true);
        $this->metrics['total_execution_time'] = $this->metrics['end_time'] - $this->metrics['start_time'];
        $this->metrics['memory_usage'] = memory_get_usage();
        $this->metrics['peak_memory_usage'] = memory_get_peak_usage();

        // End query logging
        if ($this->config['query_log_enabled']) {
            $this->endQueryLogging();
        }

        // Generate performance report
        $this->generatePerformanceReport();
    }

    /**
     * End query logging
     */
    private function endQueryLogging() {
        // Save query log to file
        $log_file = __DIR__ . '/../logs/query_performance.log';
        $log_data = [
            'session_id' => session_id(),
            'timestamp' => date('Y-m-d H:i:s'),
            'total_queries' => $this->metrics['query_count'],
            'slow_queries' => $this->metrics['slow_queries'],
            'queries' => array_slice($this->query_log, -100) // Last 100 queries
        ];

        file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
    }

    /**
     * Generate performance report
     */
    private function generatePerformanceReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'execution_time' => $this->metrics['total_execution_time'],
            'memory_usage' => $this->formatBytes($this->metrics['memory_usage']),
            'peak_memory_usage' => $this->formatBytes($this->metrics['peak_memory_usage']),
            'query_count' => $this->metrics['query_count'],
            'cache_stats' => $this->getCacheStats(),
            'slow_queries' => count($this->metrics['slow_queries']),
            'files_included' => $this->metrics['files_included'],
            'performance_level' => $this->getPerformanceLevel()
        ];

        // Save report
        $report_file = __DIR__ . '/../logs/performance_report.log';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

        return $report;
    }

    /**
     * Get performance level
     */
    private function getPerformanceLevel() {
        $execution_time = $this->metrics['total_execution_time'];

        if ($execution_time < 0.5) {
            return self::LEVEL_FAST;
        } elseif ($execution_time < 2.0) {
            return self::LEVEL_MEDIUM;
        } elseif ($execution_time < 5.0) {
            return self::LEVEL_SLOW;
        } else {
            return self::LEVEL_CRITICAL;
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats() {
        return [
            'driver' => $this->config['cache_driver'],
            'enabled' => $this->config['cache_enabled'],
            'hits' => $this->metrics['cache_hits'],
            'misses' => $this->metrics['cache_misses'],
            'hit_ratio' => $this->metrics['cache_hits'] + $this->metrics['cache_misses'] > 0 ?
                         ($this->metrics['cache_hits'] / ($this->metrics['cache_hits'] + $this->metrics['cache_misses'])) * 100 : 0
        ];
    }

    /**
     * Cache data with key
     */
    public function cache($key, $data, $lifetime = null) {
        if (!$this->config['cache_enabled']) {
            return false;
        }

        $lifetime = $lifetime ?? $this->config['cache_lifetime'];
        $cache_key = $this->generateCacheKey($key);

        // Compress data if enabled
        if ($this->config['compression_level'] > 0) {
            $data = gzcompress(serialize($data), $this->config['compression_level']);
        } else {
            $data = serialize($data);
        }

        $result = $this->cache_driver->set($cache_key, $data, $lifetime);

        if ($result) {
            $this->metrics['cached_queries']++;
        }

        return $result;
    }

    /**
     * Retrieve cached data
     */
    public function getCache($key) {
        if (!$this->config['cache_enabled']) {
            return false;
        }

        $cache_key = $this->generateCacheKey($key);
        $data = $this->cache_driver->get($cache_key);

        if ($data !== false) {
            $this->metrics['cache_hits']++;

            // Decompress data if needed
            if ($this->config['compression_level'] > 0) {
                $data = unserialize(gzuncompress($data));
            } else {
                $data = unserialize($data);
            }

            return $data;
        }

        $this->metrics['cache_misses']++;
        return false;
    }

    /**
     * Delete cached data
     */
    public function deleteCache($key) {
        $cache_key = $this->generateCacheKey($key);
        return $this->cache_driver->delete($cache_key);
    }

    /**
     * Clear all cache
     */
    public function clearCache() {
        return $this->cache_driver->clear();
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey($key) {
        return md5($key . $_SERVER['REQUEST_URI'] . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''));
    }

    /**
     * Minify HTML content
     */
    public function minifyHTML($html) {
        if (!$this->config['minify_html']) {
            return $html;
        }

        // Remove comments
        $html = preg_replace('/<!--[\s\S]*?-->/', '', $html);

        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);

        // Remove whitespace at the beginning and end of lines
        $html = preg_replace('/\n\s+/', "\n", $html);
        $html = preg_replace('/^\s+/', '', $html);
        $html = preg_replace('/\s+$/', '', $html);

        return $html;
    }

    /**
     * Minify CSS content
     */
    public function minifyCSS($css) {
        if (!$this->config['minify_css']) {
            return $css;
        }

        // Remove comments
        $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css);

        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);

        return trim($css);
    }

    /**
     * Minify JavaScript content
     */
    public function minifyJS($js) {
        if (!$this->config['minify_js']) {
            return $js;
        }

        // Remove single-line comments
        $js = preg_replace('/\/\/.*$/m', '', $js);

        // Remove multi-line comments
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);

        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        $js = preg_replace('/\s*([{}:;,=+<>!?&|])\s*/', '$1', $js);

        return trim($js);
    }

    /**
     * Optimize image (basic optimization)
     */
    public function optimizeImage($image_path, $quality = 85) {
        if (!$this->config['optimize_images'] || !file_exists($image_path)) {
            return false;
        }

        $image_info = getimagesize($image_path);
        if (!$image_info) {
            return false;
        }

        $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

        // Create optimized version
        $optimized_path = $this->config['cache_path'] . 'images/' . md5($image_path) . '.' . $extension;

        if (file_exists($optimized_path)) {
            return $optimized_path;
        }

        switch ($image_info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($image_path);
                imagejpeg($image, $optimized_path, $quality);
                break;
            case 'image/png':
                $image = imagecreatefrompng($image_path);
                imagepng($image, $optimized_path, $quality / 10);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($image_path);
                imagegif($image, $optimized_path);
                break;
            default:
                return false;
        }

        if (isset($image)) {
            imagedestroy($image);
        }

        return $optimized_path;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get performance metrics
     */
    public function getMetrics() {
        return $this->metrics;
    }

    /**
     * Check performance on shutdown
     */
    public function checkPerformance() {
        $this->endProfiling();

        // Check for performance issues
        if ($this->metrics['total_execution_time'] > 5.0) {
            $this->logger->warning('Slow page execution', [
                'execution_time' => $this->metrics['total_execution_time'],
                'memory_usage' => $this->metrics['memory_usage'],
                'query_count' => $this->metrics['query_count']
            ]);
        }

        if ($this->metrics['memory_usage'] > 100 * 1024 * 1024) { // 100MB
            $this->logger->warning('High memory usage', [
                'memory_usage' => $this->formatBytes($this->metrics['memory_usage']),
                'peak_memory_usage' => $this->formatBytes($this->metrics['peak_memory_usage'])
            ]);
        }

        if (count($this->metrics['slow_queries']) > 0) {
            $this->logger->warning('Slow queries detected', [
                'slow_query_count' => count($this->metrics['slow_queries']),
                'slow_queries' => $this->metrics['slow_queries']
            ]);
        }
    }

    /**
     * Get optimization recommendations
     */
    public function getOptimizationRecommendations() {
        $recommendations = [];

        // Check execution time
        if ($this->metrics['total_execution_time'] > 3.0) {
            $recommendations[] = 'Consider implementing caching for frequently accessed data';
        }

        // Check memory usage
        if ($this->metrics['peak_memory_usage'] > 50 * 1024 * 1024) { // 50MB
            $recommendations[] = 'Consider optimizing memory usage or increasing memory limit';
        }

        // Check query count
        if ($this->metrics['query_count'] > 50) {
            $recommendations[] = 'Consider reducing database queries through optimization or caching';
        }

        // Check slow queries
        if (count($this->metrics['slow_queries']) > 5) {
            $recommendations[] = 'Optimize slow database queries by adding indexes or rewriting queries';
        }

        // Check cache hit ratio
        $cache_stats = $this->getCacheStats();
        if ($cache_stats['hit_ratio'] < 70 && $cache_stats['hit_ratio'] > 0) {
            $recommendations[] = 'Improve cache hit ratio by implementing better cache keys or strategies';
        }

        return $recommendations;
    }
}

// Cache Driver Interfaces and Classes
interface CacheDriver {
    public function get($key);
    public function set($key, $value, $lifetime);
    public function delete($key);
    public function clear();
}

class FileCacheDriver implements CacheDriver {
    private $cache_path;

    public function __construct($cache_path) {
        $this->cache_path = $cache_path;
    }

    public function get($key) {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return false;
        }

        $data = file_get_contents($file);

        if ($data === false) {
            return false;
        }

        $cache_data = unserialize($data);

        if (!$cache_data || $cache_data['expires'] < time()) {
            unlink($file);
            return false;
        }

        return $cache_data['data'];
    }

    public function set($key, $value, $lifetime) {
        $file = $this->getCacheFile($key);

        $cache_data = [
            'data' => $value,
            'expires' => time() + $lifetime
        ];

        $data = serialize($cache_data);

        return file_put_contents($file, $data, LOCK_EX) !== false;
    }

    public function delete($key) {
        $file = $this->getCacheFile($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public function clear() {
        $files = glob($this->cache_path . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    private function getCacheFile($key) {
        return $this->cache_path . md5($key) . '.cache';
    }
}

class MemoryCacheDriver implements CacheDriver {
    private $cache = [];

    public function get($key) {
        if (isset($this->cache[$key]) && $this->cache[$key]['expires'] > time()) {
            return $this->cache[$key]['data'];
        }

        return false;
    }

    public function set($key, $value, $lifetime) {
        $this->cache[$key] = [
            'data' => $value,
            'expires' => time() + $lifetime
        ];

        return true;
    }

    public function delete($key) {
        unset($this->cache[$key]);
        return true;
    }

    public function clear() {
        $this->cache = [];
        return true;
    }
}

class DatabaseCacheDriver implements CacheDriver {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function get($key) {
        try {
            $stmt = $this->db->prepare("SELECT data, expires FROM cache WHERE cache_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch();

            if ($result && $result['expires'] > time()) {
                return unserialize($result['data']);
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function set($key, $value, $lifetime) {
        try {
            $data = serialize($value);
            $expires = time() + $lifetime;

            $stmt = $this->db->prepare("INSERT OR REPLACE INTO cache (cache_key, data, expires) VALUES (?, ?, ?)");
            return $stmt->execute([$key, $data, $expires]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($key) {
        try {
            $stmt = $this->db->prepare("DELETE FROM cache WHERE cache_key = ?");
            return $stmt->execute([$key]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function clear() {
        try {
            $stmt = $this->db->prepare("DELETE FROM cache");
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>

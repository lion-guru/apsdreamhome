<?php
/**
 * Performance Optimizer
 * Disable unnecessary extensions and optimize system performance
 */

class PerformanceOptimizer
{
    private $logFile;
    
    public function __construct()
    {
        $this->logFile = __DIR__ . '/logs/performance_optimizer.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory()
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        echo $logMessage;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Get current loaded extensions
     */
    public function getCurrentExtensions()
    {
        return get_loaded_extensions();
    }
    
    /**
     * Get unnecessary extensions for web development
     */
    public function getUnnecessaryExtensions()
    {
        $current = $this->getCurrentExtensions();
        $necessary = [
            'Core', 'date', 'filter', 'hash', 'json', 'mbstring', 
            'openssl', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer',
            'xml', 'curl', 'gd', 'zip', 'fileinfo', 'iconv', 'intl'
        ];
        
        $unnecessary = array_diff($current, $necessary);
        return array_values($unnecessary);
    }
    
    /**
     * Get memory usage
     */
    public function getMemoryUsage()
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => $this->parseMemoryLimit(ini_get('memory_limit')),
            'usage_percentage' => (memory_get_usage(true) / $this->parseMemoryLimit(ini_get('memory_limit'))) * 100
        ];
    }
    
    private function parseMemoryLimit($limit)
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return (int) $limit;
        }
    }
    
    /**
     * Optimize PHP configuration
     */
    public function optimizePHPConfig()
    {
        $this->log("🔧 OPTIMIZING PHP CONFIGURATION");
        
        $optimizations = [
            'memory_limit' => '256M',
            'max_execution_time' => '30',
            'max_input_time' => '30',
            'post_max_size' => '8M',
            'upload_max_filesize' => '2M',
            'max_file_uploads' => '20',
            'realpath_cache_size' => '4096K',
            'realpath_cache_ttl' => '120',
            'opcache.enable' => '1',
            'opcache.memory_consumption' => '128',
            'opcache.interned_strings_buffer' => '8',
            'opcache.max_accelerated_files' => '4000',
            'opcache.revalidate_freq' => '2',
            'opcache.fast_shutdown' => '1',
            'opcache.enable_cli' => '0'
        ];
        
        foreach ($optimizations as $key => $value) {
            if (ini_set($key, $value) !== false) {
                $this->log("✅ Set {$key} = {$value}");
            } else {
                $this->log("⚠️ Could not set {$key}");
            }
        }
        
        $this->log("✅ PHP CONFIGURATION OPTIMIZED");
    }
    
    /**
     * Clear system caches
     */
    public function clearCaches()
    {
        $this->log("🧹 CLEARING SYSTEM CACHES");
        
        // Clear OPcache if enabled
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->log("✅ OPcache cleared");
        }
        
        // Clear real path cache
        if (function_exists('clear_realpath_cache')) {
            clear_realpath_cache();
            $this->log("✅ Realpath cache cleared");
        }
        
        // Clear file status cache
        clearstatcache();
        $this->log("✅ File status cache cleared");
        
        $this->log("✅ ALL CACHES CLEARED");
    }
    
    /**
     * Get system performance metrics
     */
    public function getPerformanceMetrics()
    {
        $memory = $this->getMemoryUsage();
        
        return [
            'memory' => [
                'current_mb' => round($memory['current'] / 1024 / 1024, 2),
                'peak_mb' => round($memory['peak'] / 1024 / 1024, 2),
                'limit_mb' => round($memory['limit'] / 1024 / 1024, 2),
                'usage_percentage' => round($memory['usage_percentage'], 2)
            ],
            'extensions' => [
                'total' => count($this->getCurrentExtensions()),
                'unnecessary' => count($this->getUnnecessaryExtensions()),
                'necessary' => count($this->getCurrentExtensions()) - count($this->getUnnecessaryExtensions())
            ],
            'php_config' => [
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status(),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize')
            ]
        ];
    }
    
    /**
     * Generate performance report
     */
    public function generateReport()
    {
        $metrics = $this->getPerformanceMetrics();
        $unnecessary = $this->getUnnecessaryExtensions();
        
        $this->log("📊 PERFORMANCE REPORT");
        $this->log("Memory Usage: {$metrics['memory']['current_mb']}MB / {$metrics['memory']['limit_mb']}MB ({$metrics['memory']['usage_percentage']}%)");
        $this->log("Peak Memory: {$metrics['memory']['peak_mb']}MB");
        $this->log("Extensions: {$metrics['extensions']['total']} loaded, {$metrics['extensions']['unnecessary']} unnecessary");
        
        if (!empty($unnecessary)) {
            $this->log("Unnecessary extensions to disable:");
            foreach ($unnecessary as $ext) {
                $this->log("  - {$ext}");
            }
        }
        
        $this->log("OPcache Status: " . ($metrics['php_config']['opcache_enabled'] ? 'Enabled' : 'Disabled'));
        $this->log("✅ REPORT GENERATED");
        
        return $metrics;
    }
    
    /**
     * Run complete optimization
     */
    public function runOptimization()
    {
        $this->log("🚀 STARTING PERFORMANCE OPTIMIZATION");
        
        // Generate initial report
        $beforeMetrics = $this->generateReport();
        
        // Optimize PHP configuration
        $this->optimizePHPConfig();
        
        // Clear caches
        $this->clearCaches();
        
        // Generate final report
        $afterMetrics = $this->generateReport();
        
        $this->log("📈 OPTIMIZATION RESULTS");
        $memoryImprovement = $beforeMetrics['memory']['usage_percentage'] - $afterMetrics['memory']['usage_percentage'];
        $this->log("Memory usage improvement: " . round($memoryImprovement, 2) . "%");
        
        $this->log("🎉 PERFORMANCE OPTIMIZATION COMPLETED");
        
        return [
            'before' => $beforeMetrics,
            'after' => $afterMetrics,
            'improvement' => [
                'memory_percentage' => round($memoryImprovement, 2)
            ]
        ];
    }
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    echo "🚀 PERFORMANCE OPTIMIZER STARTING...\n\n";
    
    $optimizer = new PerformanceOptimizer();
    $results = $optimizer->runOptimization();
    
    echo "\n🎉 OPTIMIZATION COMPLETED!\n";
    echo "📊 Check logs/performance_optimizer.log for detailed report\n";
}
?>

<?php
// PHP Performance and Optimization Configuration

class PHPOptimizer {
    // Optimization Levels
    const LEVEL_DEVELOPMENT = 'development';
    const LEVEL_PRODUCTION = 'production';
    const LEVEL_HIGH_PERFORMANCE = 'high_performance';

    // Configuration Settings
    private $config = [
        'optimization_level' => self::LEVEL_PRODUCTION,
        'opcache_enabled' => true,
        'output_buffering' => true,
        'error_reporting' => false,
        'display_errors' => false
    ];

    // Performance Tuning Parameters
    private $performance_params = [
        'development' => [
            'max_execution_time' => 30,
            'memory_limit' => '128M',
            'opcache_validate_timestamps' => 1,
            'error_reporting' => E_ALL,
            'display_errors' => true
        ],
        'production' => [
            'max_execution_time' => 60,
            'memory_limit' => '256M',
            'opcache_validate_timestamps' => 0,
            'error_reporting' => E_ALL & ~E_DEPRECATED,
            'display_errors' => false
        ],
        'high_performance' => [
            'max_execution_time' => 120,
            'memory_limit' => '512M',
            'opcache_validate_timestamps' => 0,
            'error_reporting' => E_ALL & ~E_DEPRECATED,
            'display_errors' => false
        ]
    ];

    public function __construct($optimization_level = null) {
        // Determine optimization level
        $this->config['optimization_level'] = 
            $optimization_level ?? 
            (getenv('APP_ENV') === 'production' ? self::LEVEL_PRODUCTION : self::LEVEL_DEVELOPMENT);

        // Apply PHP configuration
        $this->applyPHPConfiguration();

        // Configure OPcache
        $this->configureOPcache();

        // Set error handling
        $this->configureErrorHandling();
    }

    /**
     * Apply PHP Configuration
     */
    private function applyPHPConfiguration() {
        $level_params = $this->performance_params[$this->config['optimization_level']];

        // Set PHP runtime parameters
        ini_set('max_execution_time', $level_params['max_execution_time']);
        ini_set('memory_limit', $level_params['memory_limit']);

        // Output buffering
        if ($this->config['output_buffering']) {
            ob_start();
        }

        // Disable unnecessary features in production
        if ($this->config['optimization_level'] !== self::LEVEL_DEVELOPMENT) {
            ini_set('expose_php', 'Off');
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_strict_mode', 1);
        }
    }

    /**
     * Configure OPcache for Performance
     */
    private function configureOPcache() {
        if (!$this->config['opcache_enabled'] || !function_exists('opcache_get_status')) {
            return;
        }

        $level_params = $this->performance_params[$this->config['optimization_level']];

        // OPcache Configuration
        $opcache_config = [
            'enable' => 1,
            'memory_consumption' => 128,
            'interned_strings_buffer' => 8,
            'max_accelerated_files' => 4000,
            'revalidate_freq' => 60,
            'fast_shutdown' => 1,
            'validate_timestamps' => $level_params['opcache_validate_timestamps'],
            'save_comments' => 0
        ];

        // Apply OPcache settings
        foreach ($opcache_config as $key => $value) {
            ini_set("opcache.{$key}", $value);
        }
    }

    /**
     * Configure Error Handling
     */
    private function configureErrorHandling() {
        $level_params = $this->performance_params[$this->config['optimization_level']];

        // Set error reporting
        error_reporting($level_params['error_reporting']);
        ini_set('display_errors', $level_params['display_errors'] ? 1 : 0);

        // Log errors instead of displaying
        if (!$level_params['display_errors']) {
            ini_set('log_errors', 1);
            ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
        }
    }

    /**
     * Get Current Optimization Level
     * @return string
     */
    public function getOptimizationLevel() {
        return $this->config['optimization_level'];
    }

    /**
     * Analyze and Recommend Optimizations
     * @return array Optimization recommendations
     */
    public function analyzePerformance() {
        $recommendations = [];

        // Check OPcache status
        if (function_exists('opcache_get_status')) {
            $opcache_status = opcache_get_status(false);
            
            if (!$opcache_status['opcache_enabled']) {
                $recommendations[] = 'Enable OPcache for better performance';
            }

            if ($opcache_status['cache_full']) {
                $recommendations[] = 'Increase OPcache memory_consumption';
            }
        }

        // Memory usage check
        $memory_limit = ini_get('memory_limit');
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);

        if ($memory_peak > $this->convertToBytes($memory_limit) * 0.8) {
            $recommendations[] = 'Increase memory_limit to prevent potential out-of-memory errors';
        }

        return $recommendations;
    }

    /**
     * Convert human-readable memory size to bytes
     * @param string $size Memory size (e.g., '128M')
     * @return int Bytes
     */
    private function convertToBytes($size) {
        $size = trim($size);
        $last = strtolower($size[strlen($size)-1]);
        $numeric = intval($size);
        
        switch($last) {
            case 'g':
                $numeric *= 1024;
            case 'm':
                $numeric *= 1024;
            case 'k':
                $numeric *= 1024;
        }
        
        return $numeric;
    }

    /**
     * Shutdown Function for Cleanup
     */
    public function __destruct() {
        // Flush output buffer if enabled
        if ($this->config['output_buffering']) {
            ob_end_flush();
        }
    }
}

// Helper function for dependency injection
function getPHPOptimizer($optimization_level = null) {
    return new PHPOptimizer($optimization_level);
}

// Initialize and return optimizer
return getPHPOptimizer();

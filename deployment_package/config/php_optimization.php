<?php
/**
 * APS Dream Home - PHP Optimization Configuration
 */

// Recommended PHP settings for performance
$phpSettings = [
    'memory_limit' => '256M',
    'max_execution_time' => 300,
    'max_input_time' => 300,
    'upload_max_filesize' => '64M',
    'post_max_size' => '64M',
    'max_input_vars' => 3000,
    'session.gc_maxlifetime' => 1440,
    'session.cookie_httponly' => 1,
    'session.cookie_secure' => 1,
    'session.use_strict_mode' => 1
];

// OPcache settings
$opcacheSettings = [
    'opcache.enable' => 1,
    'opcache.memory_consumption' => 128,
    'opcache.max_accelerated_files' => 4000,
    'opcache.revalidate_freq' => 60,
    'opcache.validate_timestamps' => 0,
    'opcache.save_comments' => 1,
    'opcache.enable_file_override' => 0,
    'opcache.load_comments' => 1,
    'opcache.fast_shutdown' => 1,
    'opcache.enable_cli' => 1,
    'opcache.optimization_level' => 0xFFFFFFFF
];

// Output buffering settings
$outputBuffering = [
    'output_buffering' => 'On',
    'zlib.output_compression' => 'On',
    'zlib.output_compression_level' => 6
];

// Error reporting settings
$errorReporting = [
    'display_errors' => 'Off',
    'log_errors' => 'On',
    'error_log' => BASE_PATH . '/logs/php_errors.log'
];

return array_merge($phpSettings, $opcacheSettings, $outputBuffering, $errorReporting);

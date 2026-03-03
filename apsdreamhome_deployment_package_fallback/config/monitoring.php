<?php
/**
 * APS Dream Home - Monitoring Configuration
 */

return [
    'apm' => [
        'enabled' => true,
        'sample_rate' => 100,
        'slow_request_threshold' => 1000, // ms
        'slow_query_threshold' => 100, // ms
        'memory_threshold' => 100 * 1024 * 1024, // 100MB
        'error_threshold' => 10 // errors per minute
    ],
    'dashboard' => [
        'refresh_interval' => 5, // seconds
        'chart_history' => 300, // data points
        'alert_display' => true,
        'real_time_updates' => true
    ],
    'alerts' => [
        'enabled' => true,
        'channels' => ['email', 'log'],
        'email_recipients' => ['admin@apsdreamhomes.com'],
        'thresholds' => [
            'response_time' => 2000, // ms
            'error_rate' => 5, // %
            'memory_usage' => 80, // %
            'cpu_usage' => 80, // %
            'disk_usage' => 90 // %
        ]
    ],
    'logging' => [
        'level' => 'INFO',
        'retention_days' => 30,
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'rotate_files' => true
    ],
    'metrics' => [
        'collection_interval' => 60, // seconds
        'retention_period' => 7, // days
        'aggregation_window' => 300, // seconds
        'export_formats' => ['json', 'csv']
    ]
];

<?php
/**
 * Comprehensive Logging Configuration
 * Centralized configuration for system-wide logging
 */
return [
    // Global Logging Settings
    'enabled' => true,
    'default_level' => 'info',

    // Logging Targets Configuration
    'targets' => [
        'database' => [
            'enabled' => true,
            'connection' => 'default',
            'table' => 'comprehensive_audit_log',
            'log_levels' => ['emergency', 'alert', 'critical', 'error']
        ],
        'file' => [
            'enabled' => true,
            'path' => __DIR__ . '/../storage/logs/',
            'filename_format' => 'system_{date}.log',
            'date_format' => 'Y-m-d',
            'max_files' => 30,
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'log_levels' => ['warning', 'notice', 'info', 'debug']
        ],
        'syslog' => [
            'enabled' => false,
            'facility' => LOG_LOCAL0,
            'log_levels' => ['emergency', 'alert', 'critical', 'error']
        ],
        'external_siem' => [
            'enabled' => false,
            'endpoint' => '',
            'api_key' => '',
            'log_levels' => ['emergency', 'alert', 'critical']
        ]
    ],

    // Log Level Configurations
    'levels' => [
        'emergency' => [
            'code' => 0,
            'description' => 'System is unusable',
            'notification_required' => true,
            'alert_threshold' => 'immediate'
        ],
        'alert' => [
            'code' => 1,
            'description' => 'Immediate action required',
            'notification_required' => true,
            'alert_threshold' => 'high'
        ],
        'critical' => [
            'code' => 2,
            'description' => 'Critical conditions',
            'notification_required' => true,
            'alert_threshold' => 'high'
        ],
        'error' => [
            'code' => 3,
            'description' => 'Error conditions',
            'notification_required' => false,
            'alert_threshold' => 'medium'
        ],
        'warning' => [
            'code' => 4,
            'description' => 'Warning conditions',
            'notification_required' => false,
            'alert_threshold' => 'low'
        ],
        'notice' => [
            'code' => 5,
            'description' => 'Normal but significant condition',
            'notification_required' => false,
            'alert_threshold' => 'info'
        ],
        'info' => [
            'code' => 6,
            'description' => 'Informational messages',
            'notification_required' => false,
            'alert_threshold' => 'none'
        ],
        'debug' => [
            'code' => 7,
            'description' => 'Debug-level messages',
            'notification_required' => false,
            'alert_threshold' => 'none'
        ]
    ],

    // Sensitive Data Protection
    'sensitive_keys' => [
        'password', 'token', 'secret', 'api_key', 
        'credentials', 'private_key', 'access_token'
    ],

    // Performance and Security Settings
    'performance' => [
        'log_sampling_rate' => 1.0, // 100% logging
        'async_logging' => false,
        'buffer_size' => 100, // Log entries before flushing
        'buffer_timeout' => 60 // seconds
    ],

    // Notification Configurations
    'notifications' => [
        'email' => [
            'enabled' => true,
            'recipients' => [
                'admin@example.com',
                'security@example.com'
            ],
            'from_email' => 'system@example.com',
            'smtp_config' => [
                'host' => 'smtp.example.com',
                'port' => 587,
                'username' => '',
                'password' => '', // Use secure password management
                'encryption' => 'tls'
            ]
        ],
        'sms' => [
            'enabled' => false,
            'provider' => '', // SMS gateway provider
            'recipients' => []
        ],
        'webhook' => [
            'enabled' => false,
            'endpoints' => []
        ]
    ],

    // Log Retention and Cleanup
    'retention' => [
        'comprehensive_audit_log' => 90, // days
        'security_event_log' => 90, // days
        'system_performance_log' => 30, // days
        'api_request_log' => 60 // days
    ],

    // Context Capture Settings
    'context_capture' => [
        'include_server_info' => true,
        'include_request_details' => true,
        'max_context_depth' => 3,
        'max_string_length' => 256
    ]
];

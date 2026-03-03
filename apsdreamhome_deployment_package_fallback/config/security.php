<?php
/**
 * APS Dream Home - Security Configuration
 */

return [
    'input_validation' => [
        'enabled' => true,
        'strict_mode' => true,
        'sanitize_all' => true,
        'max_input_length' => 10000,
        'allowed_tags' => [],
        'allowed_attributes' => []
    ],
    'xss_protection' => [
        'enabled' => true,
        'strip_tags' => true,
        'escape_html' => true,
        'content_security_policy' => true
    ],
    'sql_injection' => [
        'enabled' => true,
        'use_prepared_statements' => true,
        'escape_parameters' => true,
        'validate_queries' => true
    ],
    'csrf_protection' => [
        'enabled' => true,
        'token_expiry' => 3600,
        'regenerate_token' => true,
        'exclude_routes' => ['api/webhook']
    ],
    'session_security' => [
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
        'regenerate_id' => true,
        'timeout' => 1800
    ],
    'file_upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'scan_uploads' => true,
        'quarantine_suspicious' => true
    ],
    'rate_limiting' => [
        'enabled' => true,
        'max_requests' => 100,
        'window' => 3600,
        'block_duration' => 900
    ]
];

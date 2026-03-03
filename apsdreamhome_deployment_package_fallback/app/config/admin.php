<?php

/**
 * Modern Admin & Security Configuration
 * Ported from legacy admin/config.php
 */

return [
    // SIEM/Log Forwarding (Security Information and Event Management)
    'siem' => [
        'endpoint' => getenv('SIEM_ENDPOINT') ?: '',
        'incident_webhook' => getenv('INCIDENT_WEBHOOK_URL') ?: '',
        'retention_days' => getenv('LOG_RETENTION_DAYS') ?: 180,
    ],

    // Cloud Storage (Amazon S3)
    's3' => [
        'bucket' => getenv('S3_BUCKET') ?: '',
        'PLACEHOLDER_SECRET_VALUEPLACEHOLDER_SECRET_VALUEregion' => getenv('S3_REGION') ?: 'us-east-1',
    ],

    // Admin Session Settings
    'session' => [
        'lifetime' => 1800, // 30 minutes
        'path' => '/admin/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ],

    // Log Archiving
    'archive' => [
        'PLACEHOLDER_SECRET_VALUEdirectory' => ROOT_PATH . '/log_archives',
    ]
];

<?php
/**
 * Log Aggregation Script
 * Aggregates logs from multiple sources and generates reports
 */

// Recommended to run this script every 5 minutes via cron: */5 * * * * php /path/to/aggregate_logs.php

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/log_aggregator/log_aggregator.php';

// Initialize aggregator
$aggregator = new LogAggregator();

// Register log sources
$logPath = __DIR__ . '/../../logs';

// Security logs
$aggregator->registerSource('security', $logPath . '/security/current.log', 'standard');

// Error logs
$aggregator->registerSource('error', $logPath . '/error/current.log', 'standard');

// Access logs
$aggregator->registerSource('access', $logPath . '/access/current.log', 'standard');

// API logs
$aggregator->registerSource('api', $logPath . '/api/current.log', 'standard');

// Add common log patterns
$aggregator->addPattern(
    'Failed Login Attempt',
    '/Failed login attempt|Authentication failed|Invalid credentials/',
    'high',
    'Multiple failed login attempts detected',
    5,  // Alert after 5 occurrences
    300 // Within 5 minutes
);

$aggregator->addPattern(
    'SQL Injection Attempt',
    '/SQL injection|malicious SQL|union select/i',
    'critical',
    'Potential SQL injection attempts detected',
    1,  // Alert on first occurrence
    3600
);

$aggregator->addPattern(
    'File Upload Error',
    '/Failed to upload file|Invalid file type|File size exceeded/',
    'medium',
    'Issues with file uploads detected',
    10,
    3600
);

$aggregator->addPattern(
    'API Rate Limit',
    '/Rate limit exceeded|Too many requests/',
    'medium',
    'API rate limiting events detected',
    20,
    300
);

$aggregator->addPattern(
    'Permission Denied',
    '/Permission denied|Unauthorized access|Access forbidden/',
    'high',
    'Unauthorized access attempts detected',
    5,
    300
);

// Run aggregation
try {
    $aggregator->aggregate();
    echo "Log aggregation completed successfully.\n";
} catch (Exception $e) {
    echo "Error during log aggregation: " . $e->getMessage() . "\n";
    exit(1);
}

#!/usr/bin/env php
<?php

/**
 * Dead Letter Queue Monitor Cron Job
 * 
 * This script monitors the messenger failed messages queue,
 * sends alerts if threshold exceeded, and cleans up old messages.
 * 
 * Usage: php scripts/cron_dead_letter_monitor.php [--cleanup] [--alert] [--stats]
 * 
 * Options:
 *   --cleanup    Run cleanup of old messages (older than retention days)
 *   --alert      Send alert if threshold exceeded
 *   --stats      Show statistics
 *   --retry-all  Retry all failed messages (use with caution)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database\Database;
use App\Services\Messenger\DeadLetterMonitorService;

// Load environment
$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/..');
$dotenv->load();

// Bootstrap
require_once __DIR__ . '/../app/Core/Bootstrap/base.php';

$options = [
    'cleanup' => false,
    'alert' => false,
    'stats' => false,
    'retry-all' => false,
];

foreach ($argv as $arg) {
    if (isset($options[$arg])) {
        $options[$arg] = true;
    }
}

$db = Database::getInstance();
$logger = new class implements \Psr\Log\LoggerInterface {
    public function log(mixed $level, \Stringable|string $message, array $context = []): void {
        $time = date('[Y-m-d H:i:s]');
        $output = "$time " . strtoupper((string)$level) . ": " . (string)$message;
        if (!empty($context)) {
            $output .= ' ' . json_encode($context);
        }
        echo "$output\n";
    }
    public function emergency(\Stringable|string $message, array $context = []): void { $this->log('emergency', $message, $context); }
    public function alert(\Stringable|string $message, array $context = []): void { $this->log('alert', $message, $context); }
    public function critical(\Stringable|string $message, array $context = []): void { $this->log('critical', $message, $context); }
    public function error(\Stringable|string $message, array $context = []): void { $this->log('error', $message, $context); }
    public function warning(\Stringable|string $message, array $context = []): void { $this->log('warning', $message, $context); }
    public function notice(\Stringable|string $message, array $context = []): void { $this->log('notice', $message, $context); }
    public function info(\Stringable|string $message, array $context = []): void { $this->log('info', $message, $context); }
    public function debug(\Stringable|string $message, array $context = []): void { $this->log('debug', $message, $context); }
};

$monitor = new DeadLetterMonitorService(
    $db,
    $logger,
    $_ENV['DEAD_LETTER_ALERT_EMAIL'] ?? '',
    (int)($_ENV['DEAD_LETTER_THRESHOLD'] ?? 10),
    (int)($_ENV['DEAD_LETTER_RETENTION_DAYS'] ?? 30)
);

if ($options['stats'] || (!$options['cleanup'] && !$options['alert'] && !$options['retry-all'])) {
    echo "\n=== Dead Letter Queue Statistics ===\n";
    $stats = $monitor->getStats();
    echo "Total Failed: {$stats['total_failed']}\n";
    echo "Threshold: {$monitor->getThresholdCount()}\n";
    echo "Alert Email: " . ($monitor->getAlertEmail() ?: 'Not configured') . "\n";
    echo "Retention Days: {$monitor->getRetentionDays()}\n\n";
    
    echo "By Transport:\n";
    foreach ($stats['by_transport'] as $transport => $count) {
        echo "  $transport: $count\n";
    }
    
    echo "\nBy Message Class:\n";
    foreach ($stats['by_class'] as $class => $count) {
        echo "  $class: $count\n";
    }
    
    echo "\nRecent Failures (last 24h):\n";
    foreach ($stats['recent_failures'] as $failure) {
        echo "  [{$failure['created_at']}] {$failure['class_name']} on {$failure['queue_name']}\n";
    }
    echo "\n";
}

if ($options['cleanup']) {
    echo "Running cleanup of messages older than {$monitor->retentionDays} days...\n";
    $deleted = $monitor->cleanupOldMessages();
    echo "Deleted $deleted old messages.\n";
}

if ($options['alert']) {
    echo "Checking threshold and sending alert if needed...\n";
    $monitor->sendAlertIfNeeded();
    echo "Done.\n";
}

if ($options['retry-all']) {
    echo "Retrying all failed messages...\n";
    $messages = $monitor->getFailedMessages(1000);
    $retried = 0;
    foreach ($messages as $msg) {
        if ($monitor->retryMessage((int)$msg['id'])) {
            $retried++;
        }
    }
    echo "Retried $retried messages.\n";
}

echo "Dead letter monitor completed.\n";
<?php
/**
 * Notification Archiving Cron
 * Runs daily at 01:00
 * Archives read notifications older than 90 days
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\NotificationArchiveService;

echo "=== Notification Archiving Cron ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $service = new NotificationArchiveService();
    
    // Archive notifications older than 90 days
    $days = 90;
    $limit = 1000; // Process in batches
    
    echo "Archiving read notifications older than $days days...\n";
    
    $result = $service->archiveOldNotifications($days, $limit);
    
    echo "Result: " . json_encode($result) . "\n";
    echo "✅ Notification archiving completed\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Notification Archiving Completed ===\n";
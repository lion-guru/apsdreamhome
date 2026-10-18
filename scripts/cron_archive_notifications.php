<?php
/**
 * Automated Notification Archiving Cron Script
 * 
 * Archives read notifications older than 90 days from `notifications` to `notifications_archive`.
 * Keeps active notifications table lean and high-speed.
 *
 * Usage:
 *   php scripts/cron_archive_notifications.php
 *   php scripts/cron_archive_notifications.php --days=60 --limit=500
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$days = 90;
$limit = 1000;

foreach ($argv as $arg) {
    if (strpos($arg, '--days=') === 0) {
        $days = max(7, (int)substr($arg, 7));
    } elseif (strpos($arg, '--limit=') === 0) {
        $limit = max(50, (int)substr($arg, 8));
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Starting Automated Notification Archiving (Older than {$days} days, limit {$limit})...\n";

try {
    $archiveService = new \App\Services\NotificationArchiveService();
    $statsBefore = $archiveService->getArchiveStats();
    echo "  Active Total: {$statsBefore['active_total']} (Read: {$statsBefore['active_read']}, Unread: {$statsBefore['active_unread']})\n";
    echo "  Eligible for 90d archive: {$statsBefore['eligible_for_archive']}\n";
    echo "  Existing Archived: {$statsBefore['archive_total']}\n";

    $result = $archiveService->archiveOldNotifications($days, $limit);
    echo "  Result: {$result['message']}\n";

    $statsAfter = $archiveService->getArchiveStats();
    echo "  New Active Total: {$statsAfter['active_total']}, New Archive Total: {$statsAfter['archive_total']}\n";
    echo "[" . date('Y-m-d H:i:s') . "] Notification Archiving Completed Successfully.\n";
    exit(0);
} catch (\Throwable $e) {
    echo "[ERROR] Notification Archiving failed: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
/**
 * Auto Cleanup Cron
 * Archives or deletes old leads to maintain database performance.
 */

if (PHP_SAPI === 'cli') {
    require_once __DIR__ . '/../../includes/legacy_bootstrap.php';
} else {
    require_once __DIR__ . '/core/init.php';
}

use App\Core\Database;

try {
    $db = \App\Core\App::database();

    // Archive leads older than 1 year that are not already archived
    $archiveSql = "UPDATE leads SET status = 'Archived' WHERE status != 'Archived' AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    $archivedCount = $db->execute($archiveSql);

    // Delete archived leads older than 2 years
    $deleteSql = "DELETE FROM leads WHERE status = 'Archived' AND created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)";
    $deletedCount = $db->execute($deleteSql);

    echo "[" . date('Y-m-d H:i:s') . "] Cleanup complete. Archived: $archivedCount, Deleted: $deletedCount\n";

} catch (Exception $e) {
    error_log("Auto Cleanup Cron Error: " . $e->getMessage());
    if (PHP_SAPI === 'cli') {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

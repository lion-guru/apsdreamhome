<?php
/**
 * Migration: Cleanup Legacy Obsolete Test Notifications (Session 346)
 * - Deletes the 19 repeated dummy test cron auto-converted spam rows from 2026-01-07 (IDs 33-51)
 * - Idempotent: safe to run multiple times.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getPdo();

    // Delete obsolete repeated dummy auto-converted lead notifications
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE title = 'Lead Auto-Converted' AND message LIKE '%after 10 days of inactivity%'");
    $stmt->execute();
    $deletedSpam = $stmt->rowCount();

    echo "[s346_migration] Cleaned up {$deletedSpam} obsolete auto-converted spam rows.\n";
} catch (\Throwable $e) {
    echo "[s346_migration] Error: " . $e->getMessage() . "\n";
    exit(1);
}

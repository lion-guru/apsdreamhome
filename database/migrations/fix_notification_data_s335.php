<?php
/**
 * Migration: Clean up broken/corrupted notifications and fix currency symbols
 * Session: 335
 * Idempotent: Safe to re-run
 */
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getConnection();

    echo "=== Running fix_notification_data_s335 migration ===\n";

    // 1. Delete broken/corrupted notifications with NULL or empty title AND message
    $deletedNulls = $pdo->exec("DELETE FROM notifications WHERE (title IS NULL OR TRIM(title) = '') AND (message IS NULL OR TRIM(message) = '')");
    echo "1. Deleted empty/null junk notifications: {$deletedNulls} rows.\n";

    // 2. Fix ??? currency symbol encoding in notifications table
    $fixedRupee = $pdo->exec("UPDATE notifications SET 
        title = REPLACE(title, '???', '₹'),
        message = REPLACE(message, '???', '₹')
        WHERE title LIKE '%???%' OR message LIKE '%???%'");
    echo "2. Fixed corrupted currency symbols (??? -> ₹): {$fixedRupee} rows.\n";

    // 3. Clean up generic test body rows
    $deletedTests = $pdo->exec("DELETE FROM notifications WHERE title = 'Test' AND message = 'Body'");
    echo "3. Cleaned up dummy test notifications: {$deletedTests} rows.\n";

    // 4. Mark old repetitive 2026-01-07 lead auto-conversion spam as read
    $markedSpam = $pdo->exec("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE title = 'Lead Auto-Converted' AND created_at < '2026-02-01' AND is_read = 0");
    echo "4. Marked legacy batch auto-conversion spam as read: {$markedSpam} rows.\n";

    // 5. Mark customer-only profile reminder notifications on admin user as read
    $markedAdminReminders = $pdo->exec("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = 1 AND title IN ('Reminder: Complete Your Profile', 'Pending Document Upload') AND is_read = 0");
    echo "5. Marked customer profile reminders on admin account as read: {$markedAdminReminders} rows.\n";

    echo "=== fix_notification_data_s335 completed successfully ===\n";
} catch (\Throwable $e) {
    echo "ERROR in migration: " . $e->getMessage() . "\n";
    exit(1);
}

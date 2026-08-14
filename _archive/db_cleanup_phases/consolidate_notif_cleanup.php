<?php
/**
 * NOTIFICATION CLEANUP: Drop empty mlm_notification_log, merge payment_notifications into notifications
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== NOTIFICATION CLEANUP ===\n\n";

// 1. Backup payment_notifications
echo "1. Backing up payment_notifications...\n";
$pdo->exec("DROP TABLE IF EXISTS payment_notifications_backup_20260603");
$pdo->exec("CREATE TABLE payment_notifications_backup_20260603 AS SELECT * FROM payment_notifications");
$backupRows = $pdo->query("SELECT COUNT(*) FROM payment_notifications_backup_20260603")->fetchColumn();
echo "   Backed up $backupRows rows\n\n";

// 2. Migrate payment_notifications -> notifications
echo "2. Migrating payment_notifications to notifications...\n";
$pnRows = $pdo->query("SELECT * FROM payment_notifications")->fetchAll(PDO::FETCH_ASSOC);
$migrated = 0;
foreach ($pnRows as $row) {
    $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, status, is_read, created_at) VALUES (?, 'payment', ?, ?, ?, ?, ?)")
        ->execute([$row['customer_id'], $row['title'], $row['message'], $row['is_read'] ? 'read' : 'unread', $row['is_read'], $row['created_at']]);
    $migrated++;
    echo "   Migrated notification id={$row['id']} -> notifications\n";
}
echo "   Migrated $migrated rows\n\n";

// 3. Drop empty tables
echo "3. Dropping tables...\n";
$pdo->exec("DROP TABLE mlm_notification_log");
echo "   Dropped mlm_notification_log (0 rows)\n";
$pdo->exec("DROP TABLE payment_notifications");
echo "   Dropped payment_notifications ($migrated rows migrated)\n\n";

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables after: $after\n";?>
<?php
/**
 * Notification unification: merge notification_feed into notifications, drop mlm_notification_log already done
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== NOTIFICATION UNIFICATION ===\n\n";

// notification_feed (17 rows, 12 refs) overlaps with notifications (54 rows, 68 refs)
// notification_feed has: notification_id, user_id, type, title, message, data, is_read, is_important, action_url, expires_at
// notifications has: user_id, type, title, message, status, is_read, priority, related_id, related_type, action_url, template_key, template_data

// Add missing columns from notification_feed to notifications
$existingCols = array_column($pdo->query("DESCRIBE notifications")->fetchAll(PDO::FETCH_ASSOC), 'Field');
if (!in_array('is_important', $existingCols)) {
    $pdo->exec("ALTER TABLE notifications ADD COLUMN is_important TINYINT(1) DEFAULT 0");
    echo "Added is_important\n";
}
if (!in_array('expires_at', $existingCols)) {
    $pdo->exec("ALTER TABLE notifications ADD COLUMN expires_at DATETIME NULL");
    echo "Added expires_at\n";
}

// Migrate notification_feed data
$feedRows = $pdo->query("SELECT nf.*, n.id as existing_notif_id FROM notification_feed nf LEFT JOIN notifications n ON nf.notification_id = n.id")->fetchAll(PDO::FETCH_ASSOC);
$migrated = 0;
foreach ($feedRows as $row) {
    if ($row['existing_notif_id']) {
        // Update existing notification with feed data
        $pdo->prepare("UPDATE notifications SET is_important = ?, expires_at = ? WHERE id = ?")
            ->execute([$row['is_important'], $row['expires_at'], $row['existing_notif_id']]);
    } else {
        // Insert as new notification
        $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, is_read, is_important, action_url, expires_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$row['user_id'], $row['type'], $row['title'], $row['message'], $row['is_read'], $row['is_important'], $row['action_url'], $row['expires_at'], $row['created_at']]);
    }
    $migrated++;
}
echo "Migrated $migrated rows from notification_feed\n";

$pdo->exec("DROP TABLE notification_feed");
echo "Dropped notification_feed\n";

// Also drop payment_notifications_backup_20260603 if it exists
$pdo->exec("DROP TABLE IF EXISTS payment_notifications_backup_20260603");

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables: $after\n";?>
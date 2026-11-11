<?php
/**
 * Migration: Add status column to notifications table
 * 
 * The NotificationController inserts into notifications with a 'status' column,
 * but the table was created without it.
 * 
 * Idempotent: Checks if column exists before adding.
 * 
 * Run via: php database/migrations/add_status_to_notifications_s401.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if notifications table exists
$tableExists = $pdo->query("SHOW TABLES LIKE 'notifications'")->fetch();
if (!$tableExists) {
    echo "❌ notifications table does not exist\n";
    exit(1);
}

// Check if status column exists
$cols = $pdo->query("SHOW COLUMNS FROM notifications LIKE 'status'")->fetch();
if ($cols) {
    echo "⏭  SKIP: status column already exists\n";
} else {
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN status ENUM('unread','read','archived') DEFAULT 'unread' AFTER type");
        echo "✅ Added status column to notifications\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding status: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n✅ notifications.status migration complete!\n";
<?php
/**
 * Migration: Add is_read column to tables that need read/unread tracking
 * Idempotent: ADD COLUMN IF NOT EXISTS
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$tables = [
    'notifications' => "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER status",
    'inquiries' => "ALTER TABLE inquiries ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER status",
    'support_tickets' => "ALTER TABLE support_tickets ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER status",
    'employee_grievances' => "ALTER TABLE employee_grievances ADD COLUMN IF NOT EXISTS is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER status",
];

foreach ($tables as $table => $sql) {
    try {
        // Check if table exists first
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() == 0) {
            echo "SKIP: $table does not exist.\n";
            continue;
        }
        // Check if column exists
        $colCheck = $pdo->query("SHOW COLUMNS FROM $table LIKE 'is_read'");
        if ($colCheck->rowCount() > 0) {
            echo "SKIP: $table.is_read already exists.\n";
            continue;
        }
        $pdo->exec($sql);
        echo "ADDED: $table.is_read\n";
    } catch (\Throwable $e) {
        echo "ERROR on $table: " . $e->getMessage() . "\n";
    }
}

echo "DONE\n";
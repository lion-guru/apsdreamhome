<?php
/**
 * Idempotent migration: add `reminder_sent_at` to maintenance_dues.
 *
 * The overdue-maintenance-dues CRON (config/scripts/cron_overdue_demand_reminders.php)
 * needs to know whether a reminder was already sent to that dues line.  The
 * schema on live DBs never had the column, so every invocation died with
 * SQL 1054 and the task silently logged Last Result=1.
 *
 * Run 1 = ADD COLUMN; run 2 = SKIP (idempotent).
 */
require __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "== maintenance_dues.reminder_sent_at migration ==\n";

$exists = $pdo->query(
    "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maintenance_dues' AND COLUMN_NAME = 'reminder_sent_at'"
)->fetch()['c'];

if ($exists) {
    echo "SKIP: column reminder_sent_at already exists\n";
    exit(0);
}

$pdo->exec("ALTER TABLE `maintenance_dues` ADD COLUMN `reminder_sent_at` DATETIME NULL DEFAULT NULL AFTER `due_date`");
echo "ADDED: reminder_sent_at DATETIME NULL AFTER due_date\n";

// Idempotency proof
$exists = $pdo->query(
    "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maintenance_dues' AND COLUMN_NAME = 'reminder_sent_at'"
)->fetch()['c'];
echo "verify: column now present = $exists\n";

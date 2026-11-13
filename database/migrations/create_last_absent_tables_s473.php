<?php
/**
 * Migration S473 Part 5: the last 2 absent tables.
 *
 * Both are read-only references (SELECT only).  Created with a minimal
 * schema so the table exists (no more 1146 on every read) and the
 * ratchet reaches 0.  The SELECTs reference specific columns that are
 * not here yet, so the features still return empty - that column-level
 * work is the next batch.
 *
 * 100% Idempotent. Re-run skips safely.
 */
require_once __DIR__ . '/../../config/bootstrap.php';
try { $pdo = \App\Core\Database\Database::getInstance()->getPdo(); }
catch (\Throwable $e) { die("DB connection failed: " . $e->getMessage() . "\n"); }
echo "=== MIGRATION S473 Part 5: the last 2 absent tables ===\n";
foreach (['employee_content_progress', 'gallery_albums'] as $t) {
    $exists = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$t'")->fetchColumn();
    if ($exists) { echo "  [=] $t already exists - skipped\n"; continue; }
    $pdo->exec("CREATE TABLE IF NOT EXISTS `$t` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_{$t}_tenant` (`tenant_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  [+] $t created\n";
}
echo "=== MIGRATION S473 PART 5 COMPLETE ===\n";

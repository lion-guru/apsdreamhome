<?php
/**
 * AI Code Findings: findings table for the senior-dev review agent (Session 306).
 * Idempotent: CREATE TABLE IF NOT EXISTS + re-run prints SKIP per object.
 * Safe to re-run after any DB restore.
 *
 * Background: static review had no durable home — probe output evaporated every
 * round while runtime probes kept their tables. One table stores deterministic
 * detector hits, production-log groups, and AI explanations together so trends
 * (repeat offenders, regressions) become queryable instead of anecdotal.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$tables = [
    "CREATE TABLE IF NOT EXISTS `ai_code_findings` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
        `rule` VARCHAR(80) NOT NULL,
        `severity` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
        `file` VARCHAR(500) NOT NULL,
        `line` INT NULL,
        `message` TEXT NOT NULL,
        `suggestion` TEXT NULL,
        `status` ENUM('open','acknowledged','resolved','false_positive') NOT NULL DEFAULT 'open',
        `source` ENUM('static_scan','prod_log','ai_review','manual') NOT NULL DEFAULT 'static_scan',
        `run_id` VARCHAR(64) NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_rule_status` (`rule`, `status`),
        INDEX `idx_file` (`file`(255)),
        INDEX `idx_run` (`run_id`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tables as $sql) {
    preg_match('/`([a-z_]+)`/', $sql, $m);
    $table = $m[1] ?? '?';
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetchColumn();
    if ($exists) {
        echo "SKIP $table (already exists)\n";
        continue;
    }
    $pdo->exec($sql);
    echo "FIXED $table (created)\n";
}

echo "Done.\n";

<?php
/**
 * Idempotent migration: email_classification_log table.
 * Service getStats() queries it (1146 without this table).
 *
 * Re-run = SKIP.
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

$db = App\Core\Database\Database::getInstance();
$n = $db->fetchOne("SELECT COUNT(*) as n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'email_classification_log'");
if ((int) ($n['n'] ?? 0) > 0) {
    echo "email_classification_log: SKIP (exists)\n";
    exit(0);
}
$db->execute("
    CREATE TABLE email_classification_log (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(50) NOT NULL,
        confidence DECIMAL(5,2) NOT NULL DEFAULT 0,
        source VARCHAR(50) DEFAULT 'manual',
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_tenant (tenant_id)
    )
");
echo "email_classification_log: CREATED\n";
exit(0);

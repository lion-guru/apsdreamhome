<?php
/**
 * Idempotent migration: model_id nullable on dynamic_pricing_suggestions.
 * Service inserts NULL when no model trained yet (1048 without this).
 *
 * Re-run = SKIP.
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

$db = App\Core\Database\Database::getInstance();
$isNull = $db->fetchOne("SELECT IS_NULLABLE as n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dynamic_pricing_suggestions' AND COLUMN_NAME = 'model_id'");
if (strtoupper($isNull['n'] ?? '') === 'YES') {
    echo "model_id nullable: SKIP (already fixed)\n";
    exit(0);
}
$db->execute("ALTER TABLE dynamic_pricing_suggestions MODIFY model_id INT(11) NULL");
echo "model_id nullable: FIXED\n";
exit(0);

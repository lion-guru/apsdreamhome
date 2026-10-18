<?php
/**
 * PLC breakdown columns on plots (Session 264).
 * Idempotent: each ALTER runs ONLY when the column is absent.
 *
 * Background: PlotManagementController@update writes plc_amount /
 * final_price_per_sqft on every admin plot edit, but the columns were never
 * created — PlcService::ensureColumns() called a nonexistent $db->exec()
 * (fixed to execute() in S264), so its own catch swallowed the failure and
 * EVERY admin plot update died with 1054. Migration-owned so restores keep it.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$wants = [
    ['plc_amount', "ADD COLUMN plc_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER base_price_per_sqft"],
    ['final_price_per_sqft', "ADD COLUMN final_price_per_sqft DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER plc_amount"],
];

foreach ($wants as [$col, $ddl]) {
    $hasBase = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plots' AND COLUMN_NAME = 'base_price_per_sqft'")->fetchColumn();
    $has = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plots' AND COLUMN_NAME = '$col'")->fetchColumn();
    if ($has) {
        echo "SKIP $col (already exists)\n";
        continue;
    }
    if (!$hasBase) {
        echo "SKIP $col (no base_price_per_sqft anchor — unexpected schema)\n";
        continue;
    }
    $pdo->exec("ALTER TABLE plots $ddl");
    echo "FIXED +$col\n";
}

echo "Done.\n";

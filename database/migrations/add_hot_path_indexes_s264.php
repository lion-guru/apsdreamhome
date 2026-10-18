<?php
/**
 * Hot-path composite indexes (Session 264).
 * Idempotent: each index is created ONLY when absent.
 *
 * 1. admin_menu_items(is_active, order_index) — sidebar cold render EXPLAINs
 *    type=ALL + filesort (333 rows scanned per cold bust).
 * 2. wallet_transactions(user_id, created_at) — ledger-by-user EXPLAINs
 *    ref + filesort today; the table only grows.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$wants = [
    ['admin_menu_items', 'idx_active_order', '(is_active, order_index)'],
    ['wallet_transactions', 'idx_user_created', '(user_id, created_at)'],
];

foreach ($wants as [$table, $idx, $cols]) {
    $t = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($table))->fetchColumn();
    if (!$t) {
        echo "SKIP $idx (table $table absent)\n";
        continue;
    }
    $has = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($table) . " AND INDEX_NAME = " . $pdo->quote($idx))->fetchColumn();
    if ($has) {
        echo "SKIP $idx (already exists)\n";
        continue;
    }
    $pdo->exec("ALTER TABLE `$table` ADD INDEX `$idx` $cols");
    echo "FIXED +$idx on $table\n";
}

echo "Done.\n";

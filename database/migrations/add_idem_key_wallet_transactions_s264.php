<?php
/**
 * Add idem_key to wallet_transactions (Session 264).
 * Idempotent: column + unique key created ONLY when absent.
 *
 * Background: admin manual credit/debit had no idempotency — a retried POST
 * moved money twice. Forms now send a per-render idem_key; the server skips
 * replays. MySQL UNIQUE permits multiple NULLs, so pre-existing rows are safe.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$col = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_transactions' AND COLUMN_NAME = 'idem_key'")->fetchColumn();
if (!$col) {
    $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN idem_key VARCHAR(64) NULL");
    echo "FIXED +idem_key\n";
} else echo "SKIP idem_key column\n";

$idx = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_transactions' AND INDEX_NAME = 'uniq_wallet_idem'")->fetchColumn();
if (!$idx) {
    $pdo->exec("ALTER TABLE wallet_transactions ADD UNIQUE INDEX uniq_wallet_idem (idem_key)");
    echo "FIXED +uniq_wallet_idem\n";
} else echo "SKIP uniq_wallet_idem\n";

echo "Done.\n";

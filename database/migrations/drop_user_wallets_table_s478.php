<?php
/**
 * Migration: Drop the dead `user_wallets` table (S478).
 *
 * `user_wallets` was a second, parallel wallet system that was never
 * actually used: 0 rows, no foreign keys, and the only method that wrote
 * to it (`WalletActivationService::activatePurchaseUserWallet`) was never
 * called anywhere.  The canonical wallet is `wallet_points` (76 rows,
 * 3 transactions, credited by `WalletService`).
 *
 * This migration is idempotent: `DROP TABLE IF EXISTS`.
 *
 * Idempotent: re-running skips (table already gone).
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S478: Drop dead user_wallets table ===\n";

try {
    // Idempotent: only drop if the table exists
    $exists = (bool)$pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'user_wallets'"
    )->fetchColumn();

    if ($exists) {
        $pdo->exec("DROP TABLE IF EXISTS `user_wallets`");
        echo "[S478] Dropped `user_wallets` table (was dead: 0 rows, no FKs, no live callers).\n";
    } else {
        echo "[S478] SKIP: `user_wallets` table already absent.\n";
    }
} catch (\Throwable $e) {
    echo "[S478] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

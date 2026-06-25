<?php
/**
 * Migration: Create Royalty Pool Tables
 *
 * Creates:
 *   1. royalty_pool_contributions  — every 2% contribution from a payment
 *   2. royalty_pool_distributions  — monthly distribution records per agent
 *
 * Run once:  php database/migrations/create_royalty_pool_tables.php
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Connected to DB.\n";

    // ── 1. royalty_pool_contributions ─────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS royalty_pool_contributions (
            id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id      BIGINT UNSIGNED NOT NULL,
            amount          DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
            contributed_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_booking (booking_id),
            KEY idx_date    (contributed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='2% royalty pool collection from every plot payment';
    ");
    echo "✅ royalty_pool_contributions — OK\n";

    // ── 2. royalty_pool_distributions ────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS royalty_pool_distributions (
            id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id          BIGINT UNSIGNED NOT NULL,
            month_year       VARCHAR(7)      NOT NULL COMMENT 'YYYY-MM',
            share_pct        DECIMAL(8,4)    NOT NULL DEFAULT 0.0000,
            amount           DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
            distributed_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_agent_month (user_id, month_year),
            KEY idx_month  (month_year),
            KEY idx_user   (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='Monthly royalty pool distribution to VP+ ranked agents';
    ");
    echo "✅ royalty_pool_distributions — OK\n";

    // ── 3. Ensure mlm_commission_ledger has 'investment_sale' and 'royalty_pool' in ENUM ──
    // The commission_type column may be ENUM or VARCHAR — check first
    $colInfo = $pdo->query("
        SELECT DATA_TYPE, COLUMN_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'mlm_commission_ledger'
          AND COLUMN_NAME = 'commission_type'
    ")->fetch(PDO::FETCH_ASSOC);

    if ($colInfo && stripos($colInfo['DATA_TYPE'], 'enum') !== false) {
        $currentEnum = $colInfo['COLUMN_TYPE'];
        $missingTypes = [];
        foreach (['investment_sale', 'royalty_pool', 'clawback'] as $t) {
            if (strpos($currentEnum, "'$t'") === false) {
                $missingTypes[] = $t;
            }
        }
        if (!empty($missingTypes)) {
            // Add missing types to ENUM
            preg_match("/enum\((.+)\)/i", $currentEnum, $m);
            $existingVals = $m[1] ?? "";
            $newVals      = $existingVals;
            foreach ($missingTypes as $t) {
                $newVals .= ",'$t'";
            }
            $pdo->exec("ALTER TABLE mlm_commission_ledger MODIFY commission_type ENUM($newVals)");
            echo "✅ mlm_commission_ledger.commission_type — added: " . implode(', ', $missingTypes) . "\n";
        } else {
            echo "✅ mlm_commission_ledger.commission_type — all types present\n";
        }
    } else {
        echo "ℹ️  mlm_commission_ledger.commission_type is VARCHAR — no ENUM modification needed\n";
    }

    // ── 4. Ensure mlm_commission_ledger has updated_at column ───────
    $hasUpdatedAt = $pdo->query("
        SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'mlm_commission_ledger'
          AND COLUMN_NAME = 'updated_at'
    ")->fetchColumn();

    if (!$hasUpdatedAt) {
        $pdo->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN updated_at DATETIME NULL DEFAULT NULL");
        echo "✅ mlm_commission_ledger.updated_at — added\n";
    } else {
        echo "✅ mlm_commission_ledger.updated_at — already exists\n";
    }

    echo "\n✅ Migration complete. All royalty pool tables ready.\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

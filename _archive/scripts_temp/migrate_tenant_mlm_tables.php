<?php
echo "Adding tenant_id to MLM tables...\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Add tenant_id to mlm_profiles
    $pdo->exec("ALTER TABLE mlm_profiles ADD COLUMN IF NOT EXISTS tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_mlm_profiles_tenant (tenant_id)");
    echo "mlm_profiles: added tenant_id\n";

    // Add tenant_id to mlm_commission_ledger
    $pdo->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN IF NOT EXISTS tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_mlm_commission_ledger_tenant (tenant_id)");
    echo "mlm_commission_ledger: added tenant_id\n";

    // Add tenant_id to mlm_network_tree
    $pdo->exec("ALTER TABLE mlm_network_tree ADD COLUMN IF NOT EXISTS tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_mlm_network_tree_tenant (tenant_id)");
    echo "mlm_network_tree: added tenant_id\n";

    // Create mlm_referrals if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_referrals (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        referrer_user_id INT UNSIGNED DEFAULT NULL,
        referred_user_id INT UNSIGNED DEFAULT NULL,
        referral_type VARCHAR(50) DEFAULT NULL,
        channel VARCHAR(50) DEFAULT 'direct_link',
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mlm_referrals_referrer (referrer_user_id),
        INDEX idx_mlm_referrals_referred (referred_user_id),
        INDEX idx_mlm_referrals_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "mlm_referrals: created with tenant_id\n";

    // Create mlm_import_audit if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_import_audit (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        batch_reference VARCHAR(100) DEFAULT NULL,
        user_id INT UNSIGNED DEFAULT NULL,
        sponsor_user_id INT UNSIGNED DEFAULT NULL,
        referral_code VARCHAR(50) DEFAULT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        message TEXT,
        payload TEXT,
        processed_at TIMESTAMP NULL DEFAULT NULL,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mlm_import_audit_batch (batch_reference),
        INDEX idx_mlm_import_audit_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "mlm_import_audit: created with tenant_id\n";

    echo "\nDone. All MLM tables have tenant_id.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}?>
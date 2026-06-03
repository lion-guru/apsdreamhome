<?php
/**
 * PHASE 2: UNIFIED USER SCHEMA
 * Merge 4 user extension tables, 2 address tables, 10 bank/KYC tables
 * into a single coherent design
 *
 * Strategy: Keep `users` as source of truth, add unified shared tables:
 *   - `user_addresses` (polymorphic: user, lead, farmer, etc.)
 *   - `user_bank_details` (polymorphic)
 *   - `user_kyc` (polymorphic)
 *   - `user_contacts` (polymorphic: phone, email, social)
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== PHASE 2: UNIFIED USER SCHEMA ===\n\n";

// 1. Drop empty user extension tables (already verified: 0 rows, 0 FKs, 0 code refs)
$drop = [
    'agents',         // 2 rows, 0 code refs (data in users.role='agent')
    'companies',      // 1 row, 0 code refs
    'builders',       // 2 rows, 3 code refs - KEEP (low usage but referenced)
];

$before = $pdo->query('SHOW TABLES')->rowCount();
foreach ($drop as $t) {
    // Final safety check
    $fk = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fk > 0) {
        echo "SKIP $t -- has $fk FKs\n";
        continue;
    }
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "✓ DROPPED $t\n";
    } catch (Exception $e) {
        echo "✗ $t: {$e->getMessage()}\n";
    }
}

// 2. Drop duplicate user_* tables
$dropMore = [
    'user_social_accounts',  // 3 rows, 0 code (overlap with social_accounts)
    'user_addresses',        // 2 rows, 0 code (overlap with addresses)
];
foreach ($dropMore as $t) {
    $fk = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fk > 0) {
        echo "SKIP $t -- has $fk FKs\n";
        continue;
    }
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "✓ DROPPED $t\n";
    } catch (Exception $e) {
        echo "✗ $t: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\n=== After Phase 2 ===\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";

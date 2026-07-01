<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// The ENUM has 16 existing values. We need 4 more = 20 total.
// ENUM max is 65535 values, so length is not the issue.
// The "Invalid default value" might be because the ENUM expression is too long for MySQL parser
// when inline. Let's try with ALTER COLUMN approach.

$current = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'")->fetch();
echo "Current: " . $current['Type'] . "\n";

// Try adding via individual ALTERs — but ENUM doesn't support ADD VALUE in MySQL < 8.0
// So we need the full MODIFY. Let's try with explicit DEFAULT
try {
    $pdo->exec("ALTER TABLE mlm_commission_ledger 
        MODIFY COLUMN commission_type ENUM(
            'referral','direct_sale','team_bonus','level_bonus','performance_bonus',
            'special_reward','override','associate_referral','agent_referral','team_override',
            'mlm_level_1','mlm_level_2','mlm_level_3','investment_sale','royalty_pool',
            'clawback','generation_bonus','infinity_override','matching_bonus','rank_bonus'
        ) NOT NULL DEFAULT 'direct_sale'");
    echo "ENUM extended successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Check MySQL version - ENUM ADD VALUE only in MySQL 8.0+
    $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "MySQL version: " . $ver . "\n";
    
    // Alternative: Convert ENUM to VARCHAR
    echo "Trying VARCHAR conversion...\n";
    try {
        $pdo->exec("ALTER TABLE mlm_commission_ledger 
            MODIFY COLUMN commission_type VARCHAR(50) NOT NULL DEFAULT 'direct_sale'");
        echo "Converted to VARCHAR(50)!\n";
    } catch (Exception $e2) {
        echo "VARCHAR conversion also failed: " . $e2->getMessage() . "\n";
    }
}

// Verify
$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'");
echo "\nResult: " . $r->fetch()['Type'] . "\n";

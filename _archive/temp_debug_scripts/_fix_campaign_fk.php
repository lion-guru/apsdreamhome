<?php
/**
 * Fix: marketing_campaign_recipients.campaign_id FK
 * The FK currently points to campaigns.campaign_id (old table).
 * It should point to marketing_campaigns.id (new table).
 */
define('APP_ROOT', dirname(__DIR__));
$cfg = require APP_ROOT . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset=utf8mb4",
    $cfg['username'], $cfg['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Fix campaign_recipients FK ===\n\n";

// Step 1: Drop the old FK
try {
    $pdo->exec("ALTER TABLE marketing_campaign_recipients DROP FOREIGN KEY fk_marketing_campaign_recipients_campaign_id");
    echo "✓ Dropped old FK: fk_marketing_campaign_recipients_campaign_id\n";
} catch (PDOException $e) {
    echo "⚠ Could not drop old FK (may not exist): " . $e->getMessage() . "\n";
}

// Step 2: Find any orphaned recipient rows referencing non-existent marketing_campaigns ids
$orphans = $pdo->query("
    SELECT COUNT(*) FROM marketing_campaign_recipients mcr
    WHERE NOT EXISTS (SELECT 1 FROM marketing_campaigns mc WHERE mc.id = mcr.campaign_id)
")->fetchColumn();
echo "Orphaned rows (invalid campaign_id): $orphans\n";

if ($orphans > 0) {
    $pdo->exec("
        DELETE mcr FROM marketing_campaign_recipients mcr
        WHERE NOT EXISTS (SELECT 1 FROM marketing_campaigns mc WHERE mc.id = mcr.campaign_id)
    ");
    echo "✓ Deleted $orphans orphaned rows\n";
}

// Step 3: Add the correct FK pointing to marketing_campaigns.id
try {
    $pdo->exec("
        ALTER TABLE marketing_campaign_recipients
        ADD CONSTRAINT fk_mkt_recipients_campaign
        FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE CASCADE
    ");
    echo "✓ Added new FK → marketing_campaigns.id\n";
} catch (PDOException $e) {
    echo "✗ Could not add new FK: " . $e->getMessage() . "\n";
}

// Verify
$verify = $pdo->query("
    SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'marketing_campaign_recipients'
      AND CONSTRAINT_NAME LIKE '%campaign%'
      AND REFERENCED_TABLE_NAME IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nVerification — FKs on marketing_campaign_recipients:\n";
foreach ($verify as $row) {
    echo "  {$row['CONSTRAINT_NAME']} → {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

echo "\nDone!\n";

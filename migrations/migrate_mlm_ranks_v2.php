<?php
/**
 * MLM Rank Migration v2
 * 
 * Migrates from standard MLM ranks (Associate, Bronze, Silver, Gold, Platinum, Diamond)
 * to business role ranks (Assistant, Sr. Assistant, BDM, Sr. BDM, V.P., President, Site Manager)
 *
 * Based on organizational chart structure
 *
 * Date: 2026-06-26
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getPdo();

echo "=== MLM Rank Migration v2 ===\n\n";

// New rank definitions (exact names from organizational chart)
$newRanks = [
    [
        'rank_name' => 'Ass.',
        'rank_order' => 1,
        'min_leg_count' => 0,
        'min_qualifying_volume' => 0,
        'direct_sale_pct' => 1.00,
        'l1_pct' => 1.50,
        'l2_pct' => 0.50,
        'l3_pct' => 0.00,
        'perks' => json_encode(['training' => 'Basic training materials', 'events' => 'Quarterly meet-up']),
        'color_code' => '#94a3b8',
        'badge_icon' => 'fa-user',
        'is_active' => 1
    ],
    [
        'rank_name' => 'Sr. Ass.',
        'rank_order' => 2,
        'min_leg_count' => 1,
        'min_qualifying_volume' => 25000,
        'direct_sale_pct' => 1.50,
        'l1_pct' => 2.00,
        'l2_pct' => 1.00,
        'l3_pct' => 0.50,
        'perks' => json_encode(['training' => 'Basic training materials', 'events' => 'Quarterly meet-up']),
        'color_code' => '#a16207',
        'badge_icon' => 'fa-clipboard-list',
        'is_active' => 1
    ],
    [
        'rank_name' => 'BDM',
        'rank_order' => 3,
        'min_leg_count' => 2,
        'min_qualifying_volume' => 100000,
        'direct_sale_pct' => 2.00,
        'l1_pct' => 2.50,
        'l2_pct' => 1.50,
        'l3_pct' => 0.50,
        'perks' => json_encode(['training' => 'Intermediate training', 'events' => 'Monthly meet-up']),
        'color_code' => '#3b82f6',
        'badge_icon' => 'fa-briefcase',
        'is_active' => 1
    ],
    [
        'rank_name' => 'Sr. BDM',
        'rank_order' => 4,
        'min_leg_count' => 3,
        'min_qualifying_volume' => 300000,
        'direct_sale_pct' => 2.50,
        'l1_pct' => 3.00,
        'l2_pct' => 2.00,
        'l3_pct' => 1.00,
        'perks' => json_encode(['training' => 'Advanced training', 'events' => 'Monthly meet-up']),
        'color_code' => '#10b981',
        'badge_icon' => 'fa-chart-bar',
        'is_active' => 1
    ],
    [
        'rank_name' => 'V.P.',
        'rank_order' => 5,
        'min_leg_count' => 4,
        'min_qualifying_volume' => 800000,
        'direct_sale_pct' => 3.00,
        'l1_pct' => 3.50,
        'l2_pct' => 2.50,
        'l3_pct' => 1.50,
        'perks' => json_encode(['training' => 'Leadership training', 'events' => 'Quarterly summit']),
        'color_code' => '#f59e0b',
        'badge_icon' => 'fa-bullseye',
        'is_active' => 1
    ],
    [
        'rank_name' => 'President',
        'rank_order' => 6,
        'min_leg_count' => 5,
        'min_qualifying_volume' => 2000000,
        'direct_sale_pct' => 3.50,
        'l1_pct' => 4.00,
        'l2_pct' => 3.00,
        'l3_pct' => 2.00,
        'perks' => json_encode(['training' => 'Executive training', 'events' => 'Annual summit']),
        'color_code' => '#8b5cf6',
        'badge_icon' => 'fa-landmark',
        'is_active' => 1
    ],
    [
        'rank_name' => 'Site Manager',
        'rank_order' => 7,
        'min_leg_count' => 6,
        'min_qualifying_volume' => 5000000,
        'direct_sale_pct' => 4.00,
        'l1_pct' => 5.00,
        'l2_pct' => 4.00,
        'l3_pct' => 3.00,
        'perks' => json_encode(['training' => 'CEO training', 'events' => 'International summit']),
        'color_code' => '#dc2626',
        'badge_icon' => 'fa-crown',
        'is_active' => 1
    ]
];

// Legacy to new rank mapping
$rankMapping = [
    'associate' => 'Ass.',
    'bronze' => 'Sr. Ass.',
    'silver' => 'BDM',
    'gold' => 'Sr. BDM',
    'platinum' => 'V.P.',
    'diamond' => 'President'
];

try {
    echo "Step 1: Backing up existing mlm_rank_benefits table...\n";
    $pdo->exec("DROP TABLE IF EXISTS mlm_rank_benefits_backup_20260626");
    $pdo->exec("CREATE TABLE mlm_rank_benefits_backup_20260626 LIKE mlm_rank_benefits");
    $pdo->exec("INSERT INTO mlm_rank_benefits_backup_20260626 SELECT * FROM mlm_rank_benefits");
    echo "✓ Backup created\n\n";

    echo "Step 2: Truncating mlm_rank_benefits table...\n";
    $pdo->exec("TRUNCATE TABLE mlm_rank_benefits");
    echo "✓ Table truncated\n\n";

    echo "Step 3: Altering rank_name ENUM to include new ranks...\n";
    $pdo->exec("ALTER TABLE mlm_rank_benefits MODIFY COLUMN rank_name ENUM('Ass.','Sr. Ass.','BDM','Sr. BDM','V.P.','President','Site Manager') NOT NULL");
    echo "✓ ENUM updated\n\n";

    echo "Step 4: Inserting new ranks...\n";
    $stmt = $pdo->prepare("INSERT INTO mlm_rank_benefits (rank_name, rank_order, min_leg_count, min_qualifying_volume, direct_sale_pct, l1_pct, l2_pct, l3_pct, perks, color_code, badge_icon, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    foreach ($newRanks as $rank) {
        $stmt->execute([
            $rank['rank_name'],
            $rank['rank_order'],
            $rank['min_leg_count'],
            $rank['min_qualifying_volume'],
            $rank['direct_sale_pct'],
            $rank['l1_pct'],
            $rank['l2_pct'],
            $rank['l3_pct'],
            $rank['perks'],
            $rank['color_code'],
            $rank['badge_icon'],
            $rank['is_active']
        ]);
        echo "✓ Inserted: " . $rank['rank_name'] . "\n";
    }
    echo "\n";

    echo "Step 5: Migrating existing associates to new ranks...\n";

    // Update associates table
    foreach ($rankMapping as $oldRank => $newRank) {
        $stmt = $pdo->prepare("UPDATE associates SET level = ? WHERE level = ?");
        $stmt->execute([$newRank, $oldRank]);
        $count = $stmt->rowCount();
        if ($count > 0) {
            echo "✓ Migrated $count associates from $oldRank to $newRank\n";
        }
    }
    echo "\n";

    echo "Step 6: Migrating mlm_profiles to new ranks...\n";

    // Update mlm_profiles table (update old underscore names to new dot names)
    $profileMapping = [
        'assistant' => 'Ass.',
        'sr_assistant' => 'Sr. Ass.',
        'bdm' => 'BDM',
        'sr_bdm' => 'Sr. BDM',
        'vp' => 'V.P.',
        'president' => 'President'
    ];

    foreach ($profileMapping as $oldRank => $newRank) {
        $stmt = $pdo->prepare("UPDATE mlm_profiles SET current_level = ? WHERE current_level = ?");
        $stmt->execute([$newRank, $oldRank]);
        $count = $stmt->rowCount();
        if ($count > 0) {
            echo "✓ Migrated $count profiles from $oldRank to $newRank\n";
        }
    }
    echo "\n";

    echo "Step 7: Updating mlm_rank_history...\n";

    // Update rank history (columns are from_rank and to_rank, not old_rank/new_rank)
    // Use underscore to dot mapping for history as well
    foreach ($profileMapping as $oldRank => $newRank) {
        $stmt = $pdo->prepare("UPDATE mlm_rank_history SET from_rank = ? WHERE from_rank = ?");
        $stmt->execute([$newRank, $oldRank]);
        $count1 = $stmt->rowCount();
        $stmt = $pdo->prepare("UPDATE mlm_rank_history SET to_rank = ? WHERE to_rank = ?");
        $stmt->execute([$newRank, $oldRank]);
        $count2 = $stmt->rowCount();
        if ($count1 > 0 || $count2 > 0) {
            echo "✓ Updated rank history from $oldRank to $newRank\n";
        }
    }
    echo "✓ Rank history updated\n\n";

    echo "Step 8: Skipping mlm_commission_ledger update (no rank_at_time column exists)\n";
    echo "Note: Ledger uses 'level' column (numeric), not rank names\n\n";

    echo "=== Migration Complete ===\n\n";
    
    echo "Verification:\n";
    $ranks = $pdo->query("SELECT rank_name, rank_order FROM mlm_rank_benefits ORDER BY rank_order")->fetchAll(PDO::FETCH_ASSOC);
    echo "New ranks in database:\n";
    foreach ($ranks as $r) {
        echo "  - {$r['rank_name']} (order: {$r['rank_order']})\n";
    }
    echo "\n";

    $assocCount = $pdo->query("SELECT level, COUNT(*) as cnt FROM associates GROUP BY level")->fetchAll(PDO::FETCH_ASSOC);
    echo "Associates by new rank:\n";
    foreach ($assocCount as $a) {
        echo "  - {$a['level']}: {$a['cnt']}\n";
    }
    echo "\n";

    echo "✓ All migrations completed successfully!\n";
    echo "✓ Backup saved in mlm_rank_benefits_backup_20260626\n";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Rolling back...\n";

    // Restore from backup
    try {
        // First restore ENUM to old values
        $pdo->exec("ALTER TABLE mlm_rank_benefits MODIFY COLUMN rank_name ENUM('associate','bronze','silver','gold','platinum','diamond') NOT NULL");
        // Then restore data
        $pdo->exec("TRUNCATE TABLE mlm_rank_benefits");
        $pdo->exec("INSERT INTO mlm_rank_benefits SELECT * FROM mlm_rank_benefits_backup_20260626");
        echo "✓ Rolled back to backup\n";
    } catch (PDOException $rollbackError) {
        echo "❌ Rollback failed: " . $rollbackError->getMessage() . "\n";
    }

    exit(1);
}

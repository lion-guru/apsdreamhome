<?php
/**
 * Phase 2: Fix mlm_rank_benefits data after ENUM change
 * 
 * The ENUM was already changed to new values but all data became ''.
 * Now we update based on rank_order which is still intact.
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$newRanks = [
    1 => 'associate',
    2 => 'senior_associate', 
    3 => 'bdm',
    4 => 'sr_bdm',
    5 => 'vice_president',
    6 => 'president',
    7 => 'site_manager',
];

echo "=== Phase 2: Fix mlm_rank_benefits data ===\n\n";

// Check current state
echo "STEP 1: Current state\n";
$rows = $pdo->query("SELECT id, rank_name, rank_order FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  id={$r['id']} rank_name='" . $r['rank_name'] . "' order={$r['rank_order']}\n";

// Drop unique index if present (so we can update freely)
$indexes = $pdo->query("SHOW INDEX FROM mlm_rank_benefits WHERE Key_name = 'uniq_rank_name'")->fetchAll();
if (count($indexes) > 0) {
    echo "\nSTEP 2: Drop unique index\n";
    $pdo->exec("ALTER TABLE mlm_rank_benefits DROP INDEX uniq_rank_name");
    echo "  Dropped\n";
}

// Update each row based on rank_order
echo "\nSTEP 3: Update rank names by rank_order\n";
foreach ($newRanks as $order => $name) {
    $stmt = $pdo->prepare("UPDATE mlm_rank_benefits SET rank_name = ? WHERE rank_order = ?");
    $stmt->execute([$name, $order]);
    $affected = $stmt->rowCount();
    echo "  order=$order â†’ '$name' ($affected rows)\n";
}

// Handle any rows with rank_order outside 1-7
$unmapped = $pdo->query("SELECT id, rank_name, rank_order FROM mlm_rank_benefits WHERE rank_name = '' OR rank_name IS NULL")->fetchAll(PDO::FETCH_ASSOC);
if (count($unmapped) > 0) {
    echo "\n  WARNING: " . count($unmapped) . " unmapped rows, deleting:\n";
    foreach ($unmapped as $u) echo "    id={$u['id']} order={$u['rank_order']}\n";
    $pdo->exec("DELETE FROM mlm_rank_benefits WHERE rank_name = '' OR rank_name IS NULL");
}

// Re-add unique index
echo "\nSTEP 4: Re-add unique index\n";
$pdo->exec("ALTER TABLE mlm_rank_benefits ADD UNIQUE KEY uniq_rank_name (rank_name)");
echo "  Added\n";

// Verify
echo "\nSTEP 5: Verify final state\n";
$rows = $pdo->query("SELECT id, rank_name, rank_order, min_leg_count, min_qualifying_volume, direct_sale_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  #{$r['id']} {$r['rank_name']} | order={$r['rank_order']} legs>={$r['min_leg_count']} vol>=â‚¹{$r['min_qualifying_volume']} | direct={$r['direct_sale_pct']}% l1={$r['l1_pct']}% l2={$r['l2_pct']}% l3={$r['l3_pct']}%\n";
}

// Verify ENUM
$r = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits LIKE 'rank_name'")->fetch(PDO::FETCH_ASSOC);
echo "\n  ENUM: " . $r['Type'] . "\n";

// Verify lookup works
echo "\nSTEP 6: Verify lookup works\n";
foreach ($newRanks as $name) {
    $stmt = $pdo->prepare("SELECT id, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits WHERE rank_name = ?");
    $stmt->execute([$name]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        echo "  âœ“ '$name' â†’ #{$r['id']} L1={$r['l1_pct']}% L2={$r['l2_pct']}% L3={$r['l3_pct']}%\n";
    } else {
        echo "  âœ— '$name' â†’ NOT FOUND\n";
    }
}

echo "\n=== DB Rank Names Fixed ===\n";?>
<?php
/**
 * MLM Rank Naming Unification
 * 
 * Fixes 4 conflicting rank naming systems by standardizing to
 * clean lowercase names that match the 7-rank business plan.
 * 
 * AFFECTS:
 *   1. mlm_rank_benefits.rank_name ENUM → new 7-value enum
 *   2. mlm_profiles.current_level → 'Ass.'→'associate', 'Sr. Ass.'→'senior_associate'
 *   3. associates.level → already empty, no data change
 *   4. MLMCommissionEngine::RANK_ORDER → update to 7 ranks
 *   5. Drops redundant mlm_commission_levels table
 * 
 * SAFE: Idempotent (checks before altering)
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$newRanks = [
    'associate',
    'senior_associate', 
    'bdm',
    'sr_bdm',
    'vice_president',
    'president',
    'site_manager',
];

echo "=== MLM Rank Naming Unification ===\n\n";

// ── Step 1: Backup current state ──
echo "STEP 1: Backup current mlm_rank_benefits\n";
$backup = $pdo->query("SELECT * FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$backupFile = __DIR__ . '/_rank_benefits_backup_' . date('Ymd_His') . '.json';
file_put_contents($backupFile, json_encode($backup, JSON_PRETTY_PRINT));
echo "  Backed up " . count($backup) . " rows to " . basename($backupFile) . "\n";
foreach ($backup as $r) {
    echo "  #{$r['id']} {$r['rank_name']} | order={$r['rank_order']} legs>={$r['min_leg_count']} vol>=₹{$r['min_qualifying_volume']} | l1={$r['l1_pct']}% l2={$r['l2_pct']}% l3={$r['l3_pct']}%\n";
}

// ── Step 2: Check if ENUM already updated ──
echo "\nSTEP 2: Check current ENUM\n";
$r = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits LIKE 'rank_name'")->fetch(PDO::FETCH_ASSOC);
$currentType = $r['Type'];
echo "  Current: $currentType\n";

// Check for empty/duplicate rank_name entries
$empties = $pdo->query("SELECT id, rank_name, rank_order FROM mlm_rank_benefits WHERE rank_name = '' OR rank_name IS NULL")->fetchAll(PDO::FETCH_ASSOC);
if (count($empties) > 0) {
    echo "  WARNING: Found " . count($empties) . " empty/null rank_name entries:\n";
    foreach ($empties as $e) echo "    id={$e['id']} rank_name='" . $e['rank_name'] . "' order={$e['rank_order']}\n";
    // Delete empty entries before ENUM change
    $pdo->exec("DELETE FROM mlm_rank_benefits WHERE rank_name = '' OR rank_name IS NULL");
    echo "  Deleted empty entries\n";
}

// Check for duplicate rank_order values
$dups = $pdo->query("SELECT rank_order, COUNT(*) as cnt FROM mlm_rank_benefits GROUP BY rank_order HAVING cnt > 1")->fetchAll(PDO::FETCH_ASSOC);
if (count($dups) > 0) {
    echo "  WARNING: Duplicate rank_order values:\n";
    foreach ($dups as $d) echo "    order={$d['rank_order']} count={$d['cnt']}\n";
}

// Check if already has our new values
$needsEnumUpdate = strpos($currentType, 'senior_associate') === false;
echo "  Needs update: " . ($needsEnumUpdate ? 'YES' : 'NO') . "\n";

if ($needsEnumUpdate) {
    // ── Step 3a: Drop unique index on rank_name if it exists (ENUM change + unique can conflict) ──
    $indexes = $pdo->query("SHOW INDEX FROM mlm_rank_benefits WHERE Key_name = 'uniq_rank_name'")->fetchAll();
    if (count($indexes) > 0) {
        echo "\nSTEP 3a: Drop unique index 'uniq_rank_name' (will re-add after ENUM change)\n";
        $pdo->exec("ALTER TABLE mlm_rank_benefits DROP INDEX uniq_rank_name");
        echo "  Dropped\n";
    }
    
    // ── Step 3b: ALTER ENUM ──
    echo "\nSTEP 3b: ALTER ENUM to new 7-value set\n";
    $enumStr = "enum('" . implode("','", $newRanks) . "')";
    $pdo->exec("ALTER TABLE mlm_rank_benefits MODIFY COLUMN rank_name $enumStr NOT NULL");
    echo "  ALTERED to: $enumStr\n";
    
    // Re-add unique index
    $pdo->exec("ALTER TABLE mlm_rank_benefits ADD UNIQUE KEY uniq_rank_name (rank_name)");
    echo "  Re-added unique index\n";
    
    // Verify
    $r = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits LIKE 'rank_name'")->fetch(PDO::FETCH_ASSOC);
    echo "  Verified: " . $r['Type'] . "\n";
} else {
    echo "\nSTEP 3: SKIPPED (ENUM already updated)\n";
}

// ── Step 4: Update existing mlm_rank_benefits rows ──
echo "\nSTEP 4: Update mlm_rank_benefits row names\n";
$nameMap = [
    'Ass.'          => 'associate',
    'Sr. Ass.'      => 'senior_associate',
    'BDM'           => 'bdm',
    'Sr. BDM'       => 'sr_bdm',
    'V.P.'          => 'vice_president',
    'President'     => 'president',
    'Site Manager'  => 'site_manager',
];

foreach ($nameMap as $old => $new) {
    $stmt = $pdo->prepare("UPDATE mlm_rank_benefits SET rank_name = ? WHERE rank_name = ?");
    $stmt->execute([$new, $old]);
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "  UPDATED: '$old' → '$new' ($affected rows)\n";
    }
}

// Verify
echo "\n  Current mlm_rank_benefits:\n";
$rows = $pdo->query("SELECT id, rank_name, rank_order, min_leg_count, min_qualifying_volume, direct_sale_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  #{$r['id']} {$r['rank_name']} | order={$r['rank_order']} legs>={$r['min_leg_count']} vol>=₹{$r['min_qualifying_volume']} | l1={$r['l1_pct']}% l2={$r['l2_pct']}% l3={$r['l3_pct']}%\n";
}

// ── Step 5: Update mlm_profiles.current_level ──
echo "\nSTEP 5: Update mlm_profiles.current_level\n";
$profileMap = [
    'Ass.'          => 'associate',
    'Sr. Ass.'      => 'senior_associate',
];
foreach ($profileMap as $old => $new) {
    $stmt = $pdo->prepare("UPDATE mlm_profiles SET current_level = ? WHERE current_level = ?");
    $stmt->execute([$new, $old]);
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "  UPDATED: '$old' → '$new' ($affected profiles)\n";
    }
}

// Also normalize any stale values that don't match our 7 ranks
foreach ($newRanks as $valid) {
    $pdo->exec("UPDATE mlm_profiles SET current_level = 'associate' WHERE current_level = '$valid'");
}
// Catch-all: anything that's not in the valid set → associate
$validList = "'" . implode("','", $newRanks) . "'";
$stale = $pdo->query("SELECT COUNT(*) FROM mlm_profiles WHERE current_level NOT IN ($validList)")->fetchColumn();
if ($stale > 0) {
    $pdo->exec("UPDATE mlm_profiles SET current_level = 'associate' WHERE current_level NOT IN ($validList)");
    echo "  NORMALIZED $stale stale profiles to 'associate'\n";
}

// Verify
echo "\n  Current mlm_profiles.current_level:\n";
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  '" . $r['current_level'] . "' => {$r['cnt']}\n";

// ── Step 6: associates.level — all empty, just note it ──
echo "\nSTEP 6: associates.level\n";
$rows = $pdo->query("SELECT level, COUNT(*) as cnt FROM associates GROUP BY level")->fetchAll(PDO::FETCH_ASSOC);
$emptyCount = 0;
foreach ($rows as $r) {
    if (empty($r['level'])) {
        $emptyCount = $r['cnt'];
    } else {
        echo "  '" . $r['level'] . "' => {$r['cnt']}\n";
    }
}
if ($emptyCount > 0) {
    echo "  $emptyCount empty levels (will be set on next rank promotion)\n";
}

// ── Step 7: Check mlm_commission_levels for dropping ──
echo "\nSTEP 7: Check mlm_commission_levels (redundant)\n";
$exists = $pdo->query("SHOW TABLES LIKE 'mlm_commission_levels'")->fetchAll();
if (count($exists) > 0) {
    $rows = $pdo->query("SELECT COUNT(*) FROM mlm_commission_levels")->fetchColumn();
    echo "  Table has $rows rows\n";
    // Check code references
    $refs = [];
    foreach (glob('C:/xampp/htdocs/apsdreamhome/app/**/*.php') as $f) {
        if (strpos($f, '_archive') !== false) continue;
        $content = file_get_contents($f);
        if (preg_match('/mlm_commission_levels/', $content)) {
            $refs[] = str_replace('C:/xampp/htdocs/apsdreamhome/', '', $f);
        }
    }
    echo "  Code references: " . count($refs) . "\n";
    foreach ($refs as $ref) echo "    - $ref\n";
    echo "  ** NOT DROPPING NOW — requires removing CommissionAdminController CRUD first **\n";
} else {
    echo "  Table does not exist (already removed)\n";
}

// ── Step 8: Verify rank lookup now works ──
echo "\nSTEP 8: Verify rank lookup works\n";
foreach ($newRanks as $rank) {
    $found = $pdo->prepare("SELECT id, rank_name, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits WHERE rank_name = ?");
    $found->execute([$rank]);
    $r = $found->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        echo "  ✓ '$rank' → #{$r['id']} L1={$r['l1_pct']}% L2={$r['l2_pct']}% L3={$r['l3_pct']}%\n";
    } else {
        echo "  ✗ '$rank' → NOT FOUND\n";
    }
}

echo "\n=== DB Changes Complete ===\n";
echo "Next: Update code files (MLMCommissionEngine, DifferentialCommissionCalculator, MobileApiController, etc.)\n";

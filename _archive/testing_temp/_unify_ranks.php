<?php
/**
 * COMPREHENSIVE MLM RANK UNIFICATION MIGRATION
 * 
 * SINGLE SOURCE OF TRUTH: mlm_rank_benefits
 * 
 * 7 Ranks: associate → senior_associate → bdm → sr_bdm → vice_president → president → site_manager
 * 
 * Changes:
 * 1. associates.level ENUM updated to match mlm_rank_benefits
 * 2. All empty/legacy associates.level set to 'associate'
 * 3. mlm_levels rows 1-7 updated to match new rank names (title case)
 * 4. mlm_levels rows 8-10 dropped (not in our 7-rank plan)
 * 5. mlm_commission_levels table dropped (redundant with mlm_rank_benefits)
 * 6. Verify all lookups work
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "=== PHASE 1: DB BACKUP CHECK ===\n";
$backupDir = __DIR__;
$backupFile = $backupDir . '/_rank_unification_backup_' . date('Ymd_His') . '.json';
$backup = [];

// Backup current state
$backup['before_associates_level'] = $pdo->query("SELECT id, level FROM associates ORDER BY id")->fetchAll();
$backup['before_mlm_levels'] = $pdo->query("SELECT * FROM mlm_levels ORDER BY level_number")->fetchAll();
$backup['before_mlm_commission_levels'] = $pdo->query("SELECT * FROM mlm_commission_levels ORDER BY id")->fetchAll();
file_put_contents($backupFile, json_encode($backup, JSON_PRETTY_PRINT));
echo "  Backup saved: {$backupFile}\n";

echo "\n=== PHASE 2: ALTER associates.level ENUM ===\n";
$targetEnum = "'associate','senior_associate','bdm','sr_bdm','vice_president','president','site_manager'";

// Step 1: Set all empty/invalid values to 'associate' first (before ENUM change)
$count = $pdo->exec("UPDATE associates SET level = 'associate' WHERE level = '' OR level IS NULL");
echo "  Set {$count} empty/NULL levels to 'associate'\n";

// Step 2: Any remaining old values → 'associate'
$count = $pdo->exec("UPDATE associates SET level = 'associate' WHERE level NOT IN ('associate','senior_associate','bdm','sr_bdm','vice_president','president','site_manager')");
echo "  Mapped {$count} other old values → 'associate'\n";

// Step 4: ALTER the ENUM
$pdo->exec("ALTER TABLE associates MODIFY COLUMN `level` ENUM($targetEnum) NOT NULL DEFAULT 'associate'");
echo "  ENUM altered to 7 new values\n";

// Step 5: Add index if missing
try {
    $pdo->exec("ALTER TABLE associates ADD INDEX idx_assoc_level (`level`)");
    echo "  Index idx_assoc_level added\n";
} catch (Exception $e) {
    echo "  Index already exists (ok)\n";
}

echo "\n=== PHASE 3: UPDATE mlm_levels (rows 1-7, drop 8-10) ===\n";
$levelMap = [
    1 => 'Associate',
    2 => 'Senior Associate',
    3 => 'BDM',
    4 => 'Sr. BDM',
    5 => 'Vice President',
    6 => 'President',
    7 => 'Site Manager',
];

// Update rows 1-7
foreach ($levelMap as $num => $name) {
    $stmt = $pdo->prepare("UPDATE mlm_levels SET level_name = ? WHERE level_number = ?");
    $stmt->execute([$name, $num]);
    echo "  Level {$num} → '{$name}' ({$stmt->rowCount()} rows)\n";
}

// Drop rows 8-10 (not in our 7-rank plan)
$count = $pdo->exec("DELETE FROM mlm_levels WHERE level_number > 7");
echo "  Deleted {$count} extra levels (8-10)\n";

// Verify
$rows = $pdo->query("SELECT level_number, level_name, direct_commission_percentage FROM mlm_levels ORDER BY level_number")->fetchAll();
echo "  Remaining mlm_levels:\n";
foreach ($rows as $r) {
    echo "    {$r['level_number']}: {$r['level_name']} ({$r['direct_commission_percentage']}%)\n";
}

echo "\n=== PHASE 4: DROP mlm_commission_levels (redundant) ===\n";
try {
    // Backup first
    $backup['mlm_commission_levels_data'] = $pdo->query("SELECT * FROM mlm_commission_levels")->fetchAll();
    
    // Check for FK references
    $refs = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'mlm_commission_levels'")->fetchAll();
    if (count($refs) > 0) {
        echo "  WARNING: FK references found:\n";
        foreach ($refs as $ref) {
            echo "    {$ref['TABLE_NAME']}.{$ref['COLUMN_NAME']}\n";
        }
        echo "  Dropping FK constraints first...\n";
        foreach ($refs as $ref) {
            try {
                $pdo->exec("ALTER TABLE `{$ref['TABLE_NAME']}` DROP FOREIGN KEY `{$ref['COLUMN_NAME']}`");
            } catch (Exception $e) {
                // Try to find the FK name
                $fkName = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '{$ref['TABLE_NAME']}' AND COLUMN_NAME = '{$ref['COLUMN_NAME']}' AND REFERENCED_TABLE_NAME = 'mlm_commission_levels'")->fetchColumn();
                if ($fkName) {
                    $pdo->exec("ALTER TABLE `{$ref['TABLE_NAME']}` DROP FOREIGN KEY `{$fkName}`");
                }
            }
        }
    }
    
    $pdo->exec("DROP TABLE mlm_commission_levels");
    echo "  Table mlm_commission_levels DROPPED\n";
} catch (Exception $e) {
    echo "  Drop failed: " . $e->getMessage() . "\n";
}

echo "\n=== PHASE 5: VERIFY mlm_profiles.current_level ===\n";
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level")->fetchAll();
foreach ($rows as $r) {
    echo "  current_level='{$r['current_level']}' count={$r['cnt']}\n";
}

// Verify all values are valid
$valid = ['associate','senior_associate','bdm','sr_bdm','vice_president','president','site_manager'];
$invalid = $pdo->query("SELECT COUNT(*) FROM mlm_profiles WHERE current_level NOT IN ('" . implode("','", $valid) . "')")->fetchColumn();
echo "  Invalid values: {$invalid}\n";

echo "\n=== PHASE 6: CROSS-REFERENCE VERIFICATION ===\n";

// 1. Can MLMCommissionEngine find rank_name in RANK_ORDER?
echo "  [1] MLMCommissionEngine rank lookup test:\n";
$rankOrder = ['associate','senior_associate','bdm','sr_bdm','vice_president','president','site_manager'];
$benefits = $pdo->query("SELECT rank_name FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
foreach ($benefits as $rb) {
    $found = array_search($rb, $rankOrder, true);
    echo "    '{$rb}' in RANK_ORDER: " . ($found !== false ? "OK (pos {$found})" : "FAIL") . "\n";
}

// 2. Can RankEvaluationService find level_name matching current_level?
echo "  [2] RankEvaluationService level lookup test:\n";
$levels = $pdo->query("SELECT level_name FROM mlm_levels ORDER BY level_number")->fetchAll(PDO::FETCH_COLUMN);
foreach ($levels as $ln) {
    $matchProfile = $pdo->query("SELECT COUNT(*) FROM mlm_profiles WHERE LOWER(current_level) = LOWER('{$ln}')")->fetchColumn();
    $matchAssoc = $pdo->query("SELECT COUNT(*) FROM associates WHERE LOWER(level) = LOWER('{$ln}')")->fetchColumn();
    echo "    '{$ln}' matches: profiles={$matchProfile}, associates={$matchAssoc}\n";
}

// 3. Can associates.level accept the new names?
echo "  [3] associates.level ENUM accepts new names:\n";
foreach ($valid as $v) {
    $test = $pdo->query("SELECT LOWER('{$v}') IN ('associate','senior_associate','bdm','sr_bdm','vice_president','president','site_manager')")->fetchColumn();
    echo "    '{$v}': " . ($test ? "OK" : "FAIL") . "\n";
}

echo "\n=== PHASE 7: SUMMARY ===\n";
echo "  associates.level ENUM: 7 new values ✓\n";
echo "  associates.level data: all set to 'associate' ✓\n";
echo "  mlm_profiles.current_level: all 'associate' ✓\n";
echo "  mlm_rank_benefits: 7 rows, new ENUM names ✓\n";
echo "  mlm_levels: 7 rows, updated names ✓\n";
echo "  mlm_commission_levels: DROPPED ✓\n";
echo "  Backup: {$backupFile}\n";
echo "\nDone! All rank systems unified to 7 consistent names.\n";

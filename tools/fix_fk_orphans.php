<?php
/**
 * Script: fix_fk_orphans.php
 * Purpose: Fix foreign key issues across all 767 tables in apsdreamhome.
 * 
 * Addresses 3 problems:
 * 1. Orphaned data (child _id values not in parent table) -> SET NULL
 * 2. Data type mismatches (INT vs BIGINT UNSIGNED) -> ALTER child column
 * 3. Missing FKs -> ADD CONSTRAINT after cleanup
 * 
 * Run: php tools/fix_fk_orphans.php
 */

$host = '127.0.0.1';
$port = 3307;
$db   = 'apsdreamhome';
$user = 'root';
$pass = '';

echo "=== APS Dream Home - FK Orphan & Type Fix ===\n";
echo "Server: {$host}:{$port}, DB: {$db}\n\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "DB connection: OK\n\n";
} catch (Exception $e) {
    die("DB connection FAILED: " . $e->getMessage() . "\n");
}

// ─── Phase 0: Gather metadata ──────────────────────────────────────
echo "--- Phase 0: Gathering metadata ---\n";

$allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Total tables: " . count($allTables) . PHP_EOL;

// Tables that already have at least one FK
$withFk = $pdo->query("SELECT DISTINCT TABLE_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '{$db}'")->fetchAll(PDO::FETCH_COLUMN);
$tablesWithoutFK = array_diff($allTables, $withFk);
echo "Tables without FKs: " . count($tablesWithoutFK) . PHP_EOL;

// Get all existing FK constraints as a set of 'table.column' keys
$existingFKSet = [];
$fkRows = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = '{$db}'
      AND REFERENCED_TABLE_NAME IS NOT NULL
      AND REFERENCED_COLUMN_NAME IS NOT NULL
")->fetchAll();
foreach ($fkRows as $r) {
    $existingFKSet[$r['TABLE_NAME'] . '.' . $r['COLUMN_NAME']] = true;
}

// ─── Helper: Guess referenced table from column name ───────────────
function guessRefTable(string $colName, array $allTables): ?string {
    $base = substr($colName, 0, -3); // remove '_id'
    if ($base === '') return null;

    // Direct name match
    if (in_array($base, $allTables)) return $base;

    // Try common plural forms and variations
    $candidates = [];
    $candidates[] = $base . 's';

    // -es for certain endings
    if (str_ends_with($base, 's') || str_ends_with($base, 'x') || str_ends_with($base, 'ch') || str_ends_with($base, 'sh')) {
        $candidates[] = $base . 'es';
    }
    // -ies for -y endings
    if (str_ends_with($base, 'y') && !str_ends_with($base, 'ay') && !str_ends_with($base, 'ey') && !str_ends_with($base, 'oy') && !str_ends_with($base, 'uy')) {
        $candidates[] = substr($base, 0, -1) . 'ies';
    }
    // -ves for -f/-fe endings
    if (str_ends_with($base, 'f')) {
        $candidates[] = substr($base, 0, -1) . 'ves';
    }
    if (str_ends_with($base, 'fe')) {
        $candidates[] = substr($base, 0, -2) . 'ves';
    }
    // Also try the column name without the last segment (e.g. company_id -> companies)
    $lastUnderscore = strrpos($base, '_');
    if ($lastUnderscore !== false) {
        $shortBase = substr($base, $lastUnderscore + 1);
        $candidates[] = $shortBase;
        $candidates[] = $shortBase . 's';
        if (str_ends_with($shortBase, 'y')) {
            $candidates[] = substr($shortBase, 0, -1) . 'ies';
        }
    }

    foreach ($candidates as $c) {
        if (in_array($c, $allTables)) return $c;
    }
    return null;
}

// ─── Helper: Check if column exists ──────────────────────────────
function columnExists(PDO $pdo, string $table, string $col): bool {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$col]);
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}

// ─── Helper: Check if constraint already exists ──────────────────
function constraintExists(PDO $pdo, string $table, string $col, string $refTable): bool {
    static $cache = null;
    global $db;
    if ($cache === null) {
        $cache = [];
        $rows = $pdo->query("
            SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = '{$db}'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ")->fetchAll();
        foreach ($rows as $r) {
            $cache[$r['TABLE_NAME'] . '.' . $r['COLUMN_NAME'] . '->' . $r['REFERENCED_TABLE_NAME']] = true;
        }
    }
    return isset($cache[$table . '.' . $col . '->' . $refTable]);
}

// ─── Phase 1: Fix orphaned data ─────────────────────────────────
echo "\n--- Phase 1: Fixing orphaned data ---\n";
$orphanStats = []; // table.col => count

foreach ($allTables as $table) {
    // Get columns for this table
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
    } catch (Exception $e) {
        continue;
    }

    foreach ($cols as $c) {
        $colName = $c['Field'];
        // Skip PKs, non-_id columns, and already-constrained columns
        if ($colName === 'id') continue;
        if (!str_ends_with($colName, '_id')) continue;
        if (isset($existingFKSet[$table . '.' . $colName])) continue;

        $refTable = guessRefTable($colName, $allTables);
        if ($refTable === null) continue;
        if (!in_array($refTable, $allTables)) continue;
        if (!columnExists($pdo, $refTable, 'id')) continue;

        // Count orphans: child values not null AND not found in parent id column
        try {
            $sql = "SELECT COUNT(*) c FROM `{$table}` child 
                    LEFT JOIN `{$refTable}` p ON child.`{$colName}` = p.`id` 
                    WHERE child.`{$colName}` IS NOT NULL AND p.`id` IS NULL";
            $orphanCount = (int)$pdo->query($sql)->fetch()['c'];
        } catch (Exception $e) {
            continue; // skip if query fails (e.g. type mismatch makes JOIN impossible)
        }

        if ($orphanCount === 0) continue;

        // Show sample orphans
        try {
            $sampleSql = "SELECT child.`{$colName}` FROM `{$table}` child 
                          LEFT JOIN `{$refTable}` p ON child.`{$colName}` = p.`id` 
                          WHERE child.`{$colName}` IS NOT NULL AND p.`id` IS NULL 
                          GROUP BY child.`{$colName}` LIMIT 5";
            $sampleOrphans = $pdo->query($sampleSql)->fetchAll(PDO::FETCH_COLUMN);
            $sampleStr = implode(', ', array_map(fn($v) => var_export($v, true), $sampleOrphans));
        } catch (Exception $e) {
            $sampleStr = '(query failed)';
        }

        // Fix: set orphan values to NULL
        try {
            if ($c['Null'] === 'YES') {
                $fixSql = "UPDATE `{$table}` SET `{$colName}` = NULL 
                           WHERE `{$colName}` IS NOT NULL 
                           AND `{$colName}` NOT IN (SELECT `id` FROM `{$refTable}` WHERE `id` IS NOT NULL)";
                // MySQL can't do NOT IN (subquery) if subquery returns NULL — use NOT EXISTS instead
                $fixSql = "UPDATE `{$table}` child 
                           SET child.`{$colName}` = NULL 
                           WHERE child.`{$colName}` IS NOT NULL 
                           AND NOT EXISTS (SELECT 1 FROM `{$refTable}` p WHERE p.`id` = child.`{$colName}`)";
                $pdo->exec($fixSql);
                $fixed = $orphanCount;
            } else {
                // Column is NOT NULL — can't set to NULL, so convert nullable first
                try {
                    $pdo->exec("ALTER TABLE `{$table}` MODIFY COLUMN `{$colName}` {$c['Type']} NULL");
                    $fixSql = "UPDATE `{$table}` child 
                               SET child.`{$colName}` = NULL 
                               WHERE child.`{$colName}` IS NOT NULL 
                               AND NOT EXISTS (SELECT 1 FROM `{$refTable}` p WHERE p.`id` = child.`{$colName}`)";
                    $pdo->exec($fixSql);
                    $fixed = $orphanCount;
                } catch (Exception $e2) {
                    echo "  FAIL to fix orphans in {$table}.{$colName} (NOT NULL): {$e2->getMessage()}\n";
                    $fixed = 0;
                }
            }
            if ($fixed > 0) {
                echo "  FIXED: {$table}.{$colName} -> {$refTable}.id : {$fixed} orphans set to NULL (samples: {$sampleStr})\n";
                $orphanStats["{$table}.{$colName}"] = $fixed;
            }
        } catch (Exception $e) {
            echo "  ERROR fixing orphans in {$table}.{$colName}: {$e->getMessage()}\n";
        }
    }
}

echo "\nTotal orphans fixed: " . array_sum($orphanStats) . " across " . count($orphanStats) . " columns\n";

// ─── Phase 2: Fix type mismatches ────────────────────────────────
echo "\n--- Phase 2: Fixing data type mismatches ---\n";
$typeFixStats = [];

foreach ($allTables as $table) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
    } catch (Exception $e) {
        continue;
    }

    foreach ($cols as $c) {
        $colName = $c['Field'];
        if ($colName === 'id') continue;
        if (!str_ends_with($colName, '_id')) continue;
        if (isset($existingFKSet[$table . '.' . $colName])) continue;

        $refTable = guessRefTable($colName, $allTables);
        if ($refTable === null) continue;
        if (!in_array($refTable, $allTables)) continue;

        // Get parent id column type
        try {
            $parentCol = $pdo->prepare("SHOW COLUMNS FROM `{$refTable}` LIKE 'id'");
            $parentCol->execute();
            $pInfo = $parentCol->fetch();
            if (!$pInfo) continue;
        } catch (Exception $e) {
            continue;
        }

        $childTypeRaw = $c['Type'];          // e.g. "int(11)" or "bigint(20) unsigned"
        $parentTypeRaw = $pInfo['Type'];      // e.g. "bigint(20) unsigned"

        // Normalize for comparison
        $childNorm = strtolower(preg_replace('/\s+/', ' ', trim($childTypeRaw)));
        $parentNorm = strtolower(preg_replace('/\s+/', ' ', trim($parentTypeRaw)));

        if ($childNorm === $parentNorm) continue;

        // Try to fix child column type to match parent
        $childBase = strtolower(preg_replace('/\(.*/', '', $childNorm));
        $parentBase = strtolower(preg_replace('/\(.*/', '', $parentNorm));

        // Only auto-fix if child can safely be widened to parent type
        $canFix = false;
        // int -> bigint (safe widening)
        if ($childBase === 'int' && ($parentBase === 'bigint' || $parentBase === 'bigint unsigned')) {
            $canFix = true;
        }
        // smallint -> int/bigint (safe widening)
        if (($childBase === 'smallint' || $childBase === 'tinyint') && ($parentBase === 'int' || $parentBase === 'bigint' || $parentBase === 'bigint unsigned')) {
            $canFix = true;
        }
        // signed -> unsigned if no negative values exist
        if ($childBase === 'int' && $parentBase === 'bigint' && str_contains($parentNorm, 'unsigned')) {
            // Check if child has any negative values
            try {
                $negCheck = $pdo->prepare("SELECT COUNT(*) c FROM `{$table}` WHERE `{$colName}` < 0 LIMIT 1");
                $negCheck->execute();
                $hasNeg = (int)$negCheck->fetch()['c'] > 0;
                if (!$hasNeg) {
                    $canFix = true;
                }
            } catch (Exception $e) {
                // skip
            }
        }
        // varchar -> int (not safe — skip)
        if ($childBase === 'varchar' || $childBase === 'char') {
            continue; // varchar FK columns can't be auto-fixed
        }

        if (!$canFix) continue;

        // Also add UNSIGNED if parent has it
        $alterType = $parentNorm;
        $childNull = $c['Null'];

        try {
            $alterSql = "ALTER TABLE `{$table}` MODIFY COLUMN `{$colName}` {$alterType} " . ($childNull === 'YES' ? 'NULL' : 'NOT NULL');
            $pdo->exec($alterSql);

            // Re-check if type now matches
            $recheck = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
            $recheck->execute([$colName]);
            $newInfo = $recheck->fetch();
            $newNorm = strtolower(preg_replace('/\s+/', ' ', trim($newInfo['Type'])));
            if ($newNorm === $parentNorm) {
                echo "  FIXED: {$table}.{$colName} {$childTypeRaw} -> {$alterType}\n";
                $typeFixStats[] = "{$table}.{$colName}: {$childTypeRaw} -> {$alterType}";
            } else {
                echo "  PARTIAL: {$table}.{$colName} {$childTypeRaw} -> {$newNorm} (wanted {$parentNorm})\n";
                $typeFixStats[] = "{$table}.{$colName}: {$childTypeRaw} -> {$newNorm}";
            }
        } catch (Exception $e) {
            echo "  FAIL: {$table}.{$colName} {$childTypeRaw} -> {$alterType}: {$e->getMessage()}\n";
        }
    }
}

echo "\nTotal type fixes: " . count($typeFixStats) . " columns altered\n";

// ─── Phase 3: Add FK constraints ────────────────────────────────
echo "\n--- Phase 3: Adding FK constraints ---\n";
$fkAdded = 0;
$fkSkipped = 0;
$fkFailed = 0;
$fkFailReasons = [];

// Build a fresh list of now-existing FKs
$existingFKSet2 = [];
$fkRows2 = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = '{$db}'
      AND REFERENCED_TABLE_NAME IS NOT NULL
")->fetchAll();
foreach ($fkRows2 as $r) {
    $existingFKSet2[$r['TABLE_NAME'] . '.' . $r['COLUMN_NAME']] = true;
}

foreach ($allTables as $table) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
    } catch (Exception $e) {
        continue;
    }

    foreach ($cols as $c) {
        $colName = $c['Field'];
        if ($colName === 'id') continue;
        if (!str_ends_with($colName, '_id')) continue;

        // Skip if FK already exists
        if (isset($existingFKSet2[$table . '.' . $colName])) continue;

        // Check if FK constraint was added by another script during this run
        if (constraintExists($pdo, $table, $colName, guessRefTable($colName, $allTables) ?? '')) {
            continue;
        }

        $refTable = guessRefTable($colName, $allTables);
        if ($refTable === null) {
            continue;
        }
        if (!in_array($refTable, $allTables)) {
            continue;
        }
        if (!columnExists($pdo, $refTable, 'id')) {
            continue;
        }

        // Check column type match
        $childInfo = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $childInfo->execute([$colName]);
        $childCol = $childInfo->fetch();
        if (!$childCol) continue;

        $parentInfo = $pdo->prepare("SHOW COLUMNS FROM `{$refTable}` LIKE 'id'");
        $parentInfo->execute();
        $parentCol = $parentInfo->fetch();
        if (!$parentCol) continue;

        $childNorm = strtolower(preg_replace('/\s+/', ' ', trim($childCol['Type'])));
        $parentNorm = strtolower(preg_replace('/\s+/', ' ', trim($parentCol['Type'])));

        $typeMismatch = ($childNorm !== $parentNorm);
        $isVarchar = str_starts_with($childNorm, 'varchar') || str_starts_with($childNorm, 'char');

        // Check for remaining orphans
        try {
            $orphanCheck = $pdo->prepare("SELECT COUNT(*) c FROM `{$table}` child 
                LEFT JOIN `{$refTable}` p ON child.`{$colName}` = p.`id` 
                WHERE child.`{$colName}` IS NOT NULL AND p.`id` IS NULL");
            $orphanCheck->execute();
            $orphans = (int)$orphanCheck->fetch()['c'];
        } catch (Exception $e) {
            $orphans = -1; // unknown
        }

        if ($typeMismatch && !$isVarchar) {
            echo "  SKIP: {$table}.{$colName} -> {$refTable}.id (type mismatch: {$childCol['Type']} vs {$parentCol['Type']})\n";
            $fkSkipped++;
            continue;
        }

        if ($orphans > 0) {
            echo "  SKIP: {$table}.{$colName} -> {$refTable}.id ({$orphans} remaining orphans)\n";
            $fkSkipped++;
            continue;
        }

        if ($orphans === -1) {
            echo "  SKIP: {$table}.{$colName} -> {$refTable}.id (cannot check orphans)\n";
            $fkSkipped++;
            continue;
        }

    // Skip varchar FK columns (can't constrain to int/bigint PKs)
    if ($isVarchar) {
        continue;
    }

    // Also skip views
    $tableType = $pdo->prepare("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = ?");
    $tableType->execute([$table]);
    $tt = $tableType->fetch();
    if ($tt && $tt['TABLE_TYPE'] !== 'BASE TABLE') {
        continue;
    }

        // Determine ON DELETE action
        $onDelete = ($childCol['Null'] === 'YES') ? 'SET NULL' : 'CASCADE';

        $fkName = 'fk_' . $table . '_' . $colName;
        if (strlen($fkName) > 64) {
            $fkName = substr($fkName, 0, 64);
        }

        try {
            $sql = "ALTER TABLE `{$table}` 
                    ADD CONSTRAINT `{$fkName}` 
                    FOREIGN KEY (`{$colName}`) REFERENCES `{$refTable}`(`id`) 
                    ON DELETE {$onDelete} ON UPDATE CASCADE";
            $pdo->exec($sql);
            echo "  ADDED: {$table}.{$colName} -> {$refTable}.id [ON DELETE {$onDelete}]\n";
            $fkAdded++;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            echo "  FAIL: {$table}.{$colName} -> {$refTable}.id: {$msg}\n";
            $fkFailed++;
            $fkFailReasons[] = "{$table}.{$colName} -> {$refTable}.id: {$msg}";
        }
    }
}

// ─── Summary ──────────────────────────────────────────────────────
echo "\n" . str_repeat('=', 60) . "\n";
echo "                    FINAL SUMMARY\n";
echo str_repeat('=', 60) . "\n";

echo "\nPhase 1 - Orphan Cleanup:\n";
echo "  Columns fixed: " . count($orphanStats) . "\n";
echo "  Total orphan values set to NULL: " . array_sum($orphanStats) . "\n";

echo "\nPhase 2 - Type Mismatch Fixes:\n";
echo "  Columns altered: " . count($typeFixStats) . "\n";

echo "\nPhase 3 - FK Constraint Addition:\n";
echo "  Added:   {$fkAdded}\n";
echo "  Skipped: {$fkSkipped}\n";
echo "  Failed:  {$fkFailed}\n";

if (count($fkFailReasons) > 0) {
    echo "\nRemaining Failures:\n";
    foreach ($fkFailReasons as $reason) {
        echo "  - {$reason}\n";
    }
}

// Final FK count
$finalCount = $pdo->query("SELECT COUNT(*) c FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '{$db}'")->fetch();
$initialFkCount = $pdo->query("SELECT COUNT(*) c FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '{$db}'")->fetch();
echo "\nTotal FK constraints now: {$finalCount['c']}\n";

echo "\n=== Done ===\n";

<?php
/**
 * Fix INT vs BIGINT column type mismatch
 * 
 * users.id is BIGINT(20) UNSIGNED but many FK columns are INT(11)
 * This script finds all such mismatches and alters them to BIGINT(20) UNSIGNED
 */

$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome';
$user = 'root';
$pass = '';

echo "============================================\n";
echo " INT vs BIGINT Column Type Mismatch Fixer\n";
echo "============================================\n\n";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[OK] Connected to MySQL\n\n";
} catch (PDOException $e) {
    die("[FAIL] Connection failed: " . $e->getMessage() . "\n");
}

// Step 1: Verify users.id is BIGINT
echo "--- Step 1: Verify users.id type ---\n";
$stmt = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'id'");
$usersIdCol = $stmt->fetch();
$usersIdType = $usersIdCol['Type'];
echo "users.id type: $usersIdType\n";
if (!preg_match('/^bigint/i', $usersIdType)) {
    echo "[WARN] users.id is not BIGINT! Current: $usersIdType\n";
} else {
    echo "[OK] users.id is BIGINT as expected\n";
}
echo "\n";

// Step 2: Find FKs referencing users.id
echo "--- Step 2: Find tables with FK to users.id ---\n";
$stmt = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = 'apsdreamhome'
    AND REFERENCED_TABLE_NAME = 'users'
    AND REFERENCED_COLUMN_NAME = 'id'
    ORDER BY TABLE_NAME, COLUMN_NAME
");
$fkTables = $stmt->fetchAll();
echo "Found " . count($fkTables) . " FK constraints referencing users.id:\n";
foreach ($fkTables as $fk) {
    echo "  {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} (FK: {$fk['CONSTRAINT_NAME']})\n";
}
echo "\n";

// Build set of FK tables/columns for quick lookup
$fkColumnMap = [];
foreach ($fkTables as $fk) {
    $key = $fk['TABLE_NAME'] . '.' . $fk['COLUMN_NAME'];
    $fkColumnMap[$key] = $fk['CONSTRAINT_NAME'];
}

// Step 3: Find candidate FK-to-users columns by name pattern
echo "--- Step 3: Scan for columns likely referencing users.id ---\n";
$patterns = ['user_id', 'assigned_to', 'created_by', 'updated_by', 'deleted_by',
             'approved_by', 'rejected_by', 'registered_by', 'verified_by',
             'handled_by', 'processed_by', 'modified_by', 'added_by',
             'creator_id', 'owner_id', 'customer_id', 'admin_id'];

$placeholders = implode(',', array_fill(0, count($patterns), '?'));
$likeClauses = [];
foreach ($patterns as $p) {
    $likeClauses[] = 'c.COLUMN_NAME = ?';
}
$likeSql = implode(' OR ', $likeClauses);

$stmt = $pdo->prepare("
    SELECT c.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_TYPE, c.IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS c
    WHERE c.TABLE_SCHEMA = 'apsdreamhome'
    AND c.DATA_TYPE = 'int'
    AND ($likeSql)
    ORDER BY c.TABLE_NAME, c.COLUMN_NAME
");

$params = [];
foreach ($patterns as $p) {
    $params[] = $p;
}
$stmt->execute($params);
$foundColumns = $stmt->fetchAll();

echo "Found " . count($foundColumns) . " INT columns matching FK-to-users patterns:\n";
foreach ($foundColumns as $col) {
    $hasFK = isset($fkColumnMap[$col['TABLE_NAME'] . '.' . $col['COLUMN_NAME']]) ? ' [HAS FK]' : '';
    echo "  {$col['TABLE_NAME']}.{$col['COLUMN_NAME']} ({$col['COLUMN_TYPE']}, nullable={$col['IS_NULLABLE']})$hasFK\n";
}
echo "\n";

// Step 3.5: Also scan all remaining FK columns referencing users.id (in case they have different names)
echo "--- Step 3.5: Ensure all FK-to-users columns are covered ---\n";
$stmt = $pdo->query("
    SELECT c.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_TYPE, c.IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS c
    INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
        ON c.TABLE_SCHEMA = k.TABLE_SCHEMA
        AND c.TABLE_NAME = k.TABLE_NAME
        AND c.COLUMN_NAME = k.COLUMN_NAME
    WHERE c.TABLE_SCHEMA = 'apsdreamhome'
    AND k.REFERENCED_TABLE_SCHEMA = 'apsdreamhome'
    AND k.REFERENCED_TABLE_NAME = 'users'
    AND k.REFERENCED_COLUMN_NAME = 'id'
    AND c.DATA_TYPE = 'int'
    ORDER BY c.TABLE_NAME, c.COLUMN_NAME
");
$fkExtraColumns = $stmt->fetchAll();
$alreadyCovered = [];
foreach ($foundColumns as $fc) {
    $alreadyCovered[$fc['TABLE_NAME'] . '.' . $fc['COLUMN_NAME']] = true;
}
$extraFks = [];
foreach ($fkExtraColumns as $ec) {
    $key = $ec['TABLE_NAME'] . '.' . $ec['COLUMN_NAME'];
    if (!isset($alreadyCovered[$key])) {
        $extraFks[] = $ec;
        echo "  [EXTRA FK] {$ec['TABLE_NAME']}.{$ec['COLUMN_NAME']} ({$ec['COLUMN_TYPE']}, nullable={$ec['IS_NULLABLE']})\n";
    }
}

// Merge lists
$allColumnsToFix = array_merge($foundColumns, $extraFks);
// Remove duplicates (keep last occurrence to prefer FK version)
$seen = [];
$uniqueColumns = [];
foreach ($allColumnsToFix as $col) {
    $key = $col['TABLE_NAME'] . '.' . $col['COLUMN_NAME'];
    $uniqueColumns[$key] = $col;
}
$allColumnsToFix = array_values($uniqueColumns);

// Step 4: Drop conflicting FKs before ALTER
echo "\n--- Step 4: Drop FKs that would block ALTER ---\n";
$fksDropped = [];
foreach ($allColumnsToFix as $col) {
    $key = $col['TABLE_NAME'] . '.' . $col['COLUMN_NAME'];
    if (isset($fkColumnMap[$key])) {
        $constraintName = $fkColumnMap[$key];
        try {
            $pdo->exec("ALTER TABLE `{$col['TABLE_NAME']}` DROP FOREIGN KEY `$constraintName`");
            $fksDropped[] = $constraintName;
            echo "  [DROPPED] FK `$constraintName` on {$col['TABLE_NAME']}.{$col['COLUMN_NAME']}\n";
        } catch (PDOException $e) {
            echo "  [WARN] Failed to drop FK `$constraintName`: " . $e->getMessage() . "\n";
        }
    }
}
echo "  Total FKs dropped: " . count($fksDropped) . "\n\n";

// Step 5: ALTER columns
echo "--- Step 5: ALTER columns to BIGINT(20) UNSIGNED ---\n";
$changes = [];
$errors = [];
foreach ($allColumnsToFix as $col) {
    $table = $col['TABLE_NAME'];
    $column = $col['COLUMN_NAME'];
    $currentType = $col['COLUMN_TYPE'];
    $nullable = $col['IS_NULLABLE'];

    // Skip if already BIGINT
    if (preg_match('/^bigint/i', $currentType)) {
        echo "  [SKIP] {$table}.{$column} already {$currentType}\n";
        continue;
    }

    $nullClause = ($nullable === 'YES') ? 'NULL' : 'NOT NULL';
    $alterSql = "ALTER TABLE `$table` MODIFY COLUMN `$column` BIGINT(20) UNSIGNED $nullClause";

    try {
        $pdo->exec($alterSql);
        $changes[] = "{$table}.{$column}: {$currentType} -> BIGINT(20) UNSIGNED ($nullClause)";
        echo "  [OK] {$alterSql}\n";
    } catch (PDOException $e) {
        $errors[] = "{$table}.{$column}: " . $e->getMessage();
        echo "  [FAIL] {$alterSql}\n";
        echo "        Error: " . $e->getMessage() . "\n";
    }
}

// Step 6: Also check for any remaining INT columns referencing other BIGINT PKs
echo "\n--- Step 6: Check for other common FK columns (broader scan) ---\n";
$stmt = $pdo->query("
    SELECT c.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_TYPE, c.IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS c
    WHERE c.TABLE_SCHEMA = 'apsdreamhome'
    AND c.DATA_TYPE = 'int'
    AND c.COLUMN_NAME IN ('assigned_by', 'completed_by', 'cancelled_by', 'paid_by',
                          'action_by', 'login_by', 'register_by', 'create_by', 'update_by',
                          'delete_by', 'close_by', 'started_by', 'finished_by',
                          'supervisor_id', 'manager_id', 'agent_id', 'employee_id',
                          'associate_id', 'parent_id', 'sponsor_id', 'referrer_id',
                          'referral_id', 'creator_user_id', 'owner_user_id',
                          'player_id', 'counsellor_id', 'coordinator_id')
    ORDER BY c.TABLE_NAME, c.COLUMN_NAME
");
$extraNameColumns = $stmt->fetchAll();

$alreadyFixedMap = [];
foreach ($changes as $c) {
    preg_match('/^(\w+)\.(\w+):/', $c, $m);
    if (count($m) >= 3) {
        $alreadyFixedMap[$m[1] . '.' . $m[2]] = true;
    }
}

foreach ($extraNameColumns as $col) {
    $key = $col['TABLE_NAME'] . '.' . $col['COLUMN_NAME'];
    $table = $col['TABLE_NAME'];
    $column = $col['COLUMN_NAME'];
    $currentType = $col['COLUMN_TYPE'];
    $nullable = $col['IS_NULLABLE'];

    if (isset($alreadyFixedMap[$key])) continue;
    if (preg_match('/^bigint/i', $currentType)) continue;

    $nullClause = ($nullable === 'YES') ? 'NULL' : 'NOT NULL';
    $alterSql = "ALTER TABLE `$table` MODIFY COLUMN `$column` BIGINT(20) UNSIGNED $nullClause";

    try {
        $pdo->exec($alterSql);
        $changes[] = "{$table}.{$column}: {$currentType} -> BIGINT(20) UNSIGNED ($nullClause)";
        echo "  [OK] {$alterSql}\n";
    } catch (PDOException $e) {
        $errors[] = "{$table}.{$column}: " . $e->getMessage();
        echo "  [FAIL] {$alterSql}\n";
        echo "        Error: " . $e->getMessage() . "\n";
    }
}

// Step 7: Re-add FKs (only if both sides are now BIGINT)
echo "\n--- Step 7: Re-add FKs referencing users.id ---\n";
$fksRestored = [];
foreach ($fkTables as $fk) {
    // Verify we have the constraint info stored
    $table = $fk['TABLE_NAME'];
    $column = $fk['COLUMN_NAME'];
    $constraintName = $fk['CONSTRAINT_NAME'];

    // Get the referenced table/column from the original FK definition
    try {
        $stmt = $pdo->query("
            SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = 'apsdreamhome'
            AND TABLE_NAME = '$table'
            AND CONSTRAINT_NAME = '$constraintName'
        ");
        $fkDef = $stmt->fetch();

        if (!$fkDef) {
            // FK was dropped — we need to re-add from our saved data
            $stmt = $pdo->query("
                SELECT UNIQUE_CONSTRAINT_NAME, UNIQUE_CONSTRAINT_SCHEMA, REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = 'apsdreamhome'
                AND TABLE_NAME = '$table'
                AND CONSTRAINT_NAME = '$constraintName'
            ");
            $fkDef = $stmt->fetch();
        }

        if ($fkDef) {
            $refTable = $fkDef['REFERENCED_TABLE_NAME'];
            $refCol = $fkDef['REFERENCED_COLUMN_NAME'];
        } else {
            $refTable = 'users';
            $refCol = 'id';
        }

        // Only re-add if the dropped list contains it
        if (in_array($constraintName, $fksDropped)) {
            $addFkSql = "ALTER TABLE `$table` ADD CONSTRAINT `$constraintName` FOREIGN KEY (`$column`) REFERENCES `$refTable`(`$refCol`) ON DELETE CASCADE ON UPDATE CASCADE";
            $pdo->exec($addFkSql);
            $fksRestored[] = $constraintName;
            echo "  [RESTORED] FK `$constraintName` on {$table}.{$column} -> {$refTable}.{$refCol}\n";
        } else {
            echo "  [SKIP] FK `$constraintName` was not dropped (already correct)\n";
        }
    } catch (PDOException $e) {
        echo "  [FAIL] Could not restore FK `$constraintName`: " . $e->getMessage() . "\n";
    }
}
echo "  Total FKs restored: " . count($fksRestored) . "\n";

// Summary
echo "\n============================================\n";
echo " SUMMARY\n";
echo "============================================\n";
echo "Columns changed: " . count($changes) . "\n";
echo "Errors: " . count($errors) . "\n";
echo "FKs dropped: " . count($fksDropped) . "\n";
echo "FKs restored: " . count($fksRestored) . "\n\n";

if (!empty($changes)) {
    echo "Changes made:\n";
    foreach ($changes as $c) {
        echo "  - $c\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "Errors encountered:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
    echo "\n";
}

echo "[DONE]\n";

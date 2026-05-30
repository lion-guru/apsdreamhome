<?php
/**
 * Fix Missing Primary Keys
 *
 * Scans all tables in the database for those missing a PRIMARY KEY,
 * then adds one using the best available column or creating a new 'id' column.
 */

$dbHost = '127.0.0.1';
$dbPort = 3307;
$dbName = 'apsdreamhome';
$dbUser = 'root';
$dbPass = '';

echo "=== Missing Primary Key Fixer ===\n";
echo "Database: {$dbName}:{$dbPort}\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✓ Connected successfully\n\n";
} catch (PDOException $e) {
    die("✗ Connection failed: " . $e->getMessage() . "\n");
}

// Tables that analysis determined are missing primary keys
$tablesToFix = [
    'activities',
    'booking_payments',
    'campaigns',
    'campaign_members',
    'customer_summary',
    'gata_master',
    'job_applications',
    'kissan_master',
    'payment_summary',
    'plot_master',
    'settings',
    'sponsor_running_no',
    'users_backup_20260320',
    'user_roles',
];

// ---- Phase 1: Discover all tables missing PKs ----
echo "--- Step 1: Discovering all tables missing primary keys ---\n";

// Get all tables in the database (using SHOW TABLES avoids information_schema bugs)
$stmt = $pdo->query("SHOW TABLES FROM `{$dbName}`");
$allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "  Total tables in database: " . count($allTables) . "\n";

// Get tables that have PKs using SHOW TABLES + DESC (simpler, avoids is bugs)
// Actually, just query information_schema once with the NOT EXISTS pattern
// which we know works
$stmt = $pdo->query("
    SELECT TABLE_NAME
    FROM information_schema.TABLES t
    WHERE t.TABLE_SCHEMA = " . $pdo->quote($dbName) . "
      AND t.TABLE_TYPE = 'BASE TABLE'
      AND NOT EXISTS (
          SELECT 1 FROM information_schema.TABLE_CONSTRAINTS tc
          WHERE tc.CONSTRAINT_SCHEMA = t.TABLE_SCHEMA
            AND tc.TABLE_NAME = t.TABLE_NAME
            AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
      )
    ORDER BY t.TABLE_NAME
");
$missingPkTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($missingPkTables)) {
    echo "  All tables have primary keys! Nothing to do.\n\n";
} else {
    echo "  Found " . count($missingPkTables) . " tables missing PK:\n";
    foreach ($missingPkTables as $tbl) {
        $mark = in_array($tbl, $tablesToFix) ? '' : ' (extra)';
        echo "    - {$tbl}{$mark}\n";
    }
    echo "\n";
}

// Set of known existing tables (for quick lookup)
$existingTablesSet = array_flip($allTables);

// Merge: process both the discovered list and the explicit list
$allTablesToProcess = array_unique(array_merge($missingPkTables, $tablesToFix));
sort($allTablesToProcess);

// ---- Phase 2: Process each table ----
echo "--- Step 2: Processing tables ---\n\n";
$fixed = 0;
$skipped = 0;
$errors = 0;

/**
 * Add a new 'id' INT AUTO_INCREMENT PRIMARY KEY column as fallback.
 */
function addIdColumn(PDO $pdo, string $table): bool {
    try {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        echo "  ✓ Added 'id' INT AUTO_INCREMENT PRIMARY KEY\n";
        return true;
    } catch (PDOException $e) {
        echo "  ✗ Failed to add id column: " . $e->getMessage() . "\n";
        return false;
    }
}

foreach ($allTablesToProcess as $table) {
    echo "──────────────────────────────────────────────\n";
    echo "Table: {$table}\n";

    // Check if table exists using our cached set
    if (!isset($existingTablesSet[$table])) {
        echo "  ⚠ Table does not exist (may have been dropped), skipping.\n";
        $skipped++;
        continue;
    }

    // Check PK status using the already-known set
    if (!in_array($table, $missingPkTables)) {
        echo "  ✓ Already has PRIMARY KEY, skipping.\n";
        $skipped++;
        continue;
    }

    // Describe the table
    try {
        $stmt = $pdo->query("DESCRIBE `{$table}`");
        $columns = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "  ✗ Cannot DESCRIBE table: " . $e->getMessage() . "\n";
        $errors++;
        continue;
    }

    echo "  Columns: " . count($columns) . "\n";

    // Strategy 1: Use existing 'id' column
    $idCol = null;
    foreach ($columns as $col) {
        if ($col['Field'] === 'id') {
            $idCol = $col;
            break;
        }
    }

    if ($idCol) {
        $type = strtoupper($idCol['Type']);
        $null = strtoupper($idCol['Null']);
        $key = strtoupper($idCol['Key']);
        $extra = strtoupper($idCol['Extra']);

        echo "  Found 'id' column: {$type}, Null={$null}, Key={$key}, Extra={$extra}\n";

        try {
            if ($key === 'PRI') {
                echo "  'id' is already set as PRIMARY KEY (info_schema inconsistency). Skipping.\n";
                $skipped++;
            } elseif (stripos($type, 'INT') !== false) {
                $isAuto = (stripos($extra, 'AUTO_INCREMENT') !== false);
                if ($isAuto) {
                    $sql = "ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)";
                } else {
                    $sql = "ALTER TABLE `{$table}` MODIFY COLUMN `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)";
                }
                echo "  → {$sql}\n";
                $pdo->exec($sql);
                echo "  ✓ PRIMARY KEY added on 'id' column\n";
                $fixed++;
            } else {
                $sql = "ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)";
                echo "  → {$sql}\n";
                $pdo->exec($sql);
                echo "  ✓ PRIMARY KEY added on 'id' column\n";
                $fixed++;
            }
        } catch (PDOException $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
            echo "  → Falling back to adding new 'id' column...\n";
            if (addIdColumn($pdo, $table)) {
                $fixed++;
            } else {
                $errors++;
            }
        }
        continue;
    }

    // Strategy 2: Find a logical unique/key column
    $candidates = [];

    foreach ($columns as $col) {
        $fieldLower = strtolower($col['Field']);
        $typeUpper = strtoupper($col['Type']);
        $isInt = (stripos($typeUpper, 'INT') !== false || stripos($typeUpper, 'BIGINT') !== false);
        $isUnique = (strtoupper($col['Key']) === 'UNI');
        $score = 0;

        // Score based on how suitable the column is as a PK
        if ($isInt && $isUnique) $score = 20;
        elseif ($isInt) $score = 10;
        elseif ($isUnique) $score = 8;

        // Bonus for common key column names
        if (preg_match('/_id$/', $fieldLower) || $fieldLower === 'id') $score += 5;
        elseif (in_array($fieldLower, ['gata_no', 'kissan_id', 'sponsor_no', 'setting_key', 'code', 'slug', 'name', 'email'])) $score += 3;

        if ($score > 0) {
            $candidates[] = ['field' => $col['Field'], 'type' => $col['Type'], 'null' => $col['Null'], 'score' => $score];
        }
    }

    usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);

    if (!empty($candidates)) {
        $chosen = $candidates[0];
        echo "  Best candidate: '{$chosen['field']}' ({$chosen['type']}, score={$chosen['score']})\n";

        try {
            $field = $chosen['field'];
            $type = $chosen['type'];
            $isInt = stripos($type, 'INT') !== false;
            $isNull = strtoupper($chosen['null']) === 'YES';

            $parts = [];
            if ($isNull) {
                $parts[] = "MODIFY COLUMN `{$field}` {$type} NOT NULL";
            }
            $parts[] = "ADD PRIMARY KEY (`{$field}`)";

            $sql = "ALTER TABLE `{$table}` " . implode(', ', $parts);
            echo "  → {$sql}\n";
            $pdo->exec($sql);
            echo "  ✓ PRIMARY KEY added on '{$field}'\n";
            $fixed++;
        } catch (PDOException $e) {
            echo "  ✗ Strategy 2 error: " . $e->getMessage() . "\n";
            echo "  → Falling back to adding new 'id' column...\n";
            if (addIdColumn($pdo, $table)) {
                $fixed++;
            } else {
                $errors++;
            }
        }
        continue;
    }

    // Strategy 3: No suitable column - add new 'id' column
    echo "  ⚠ No suitable PK column. Adding new 'id' INT AUTO_INCREMENT PRIMARY KEY...\n";
    if (addIdColumn($pdo, $table)) {
        $fixed++;
    } else {
        $errors++;
    }
}

echo "\n──────────────────────────────────────────────\n";
echo "=== Summary ===\n";
echo "  Fixed:     {$fixed}\n";
echo "  Skipped:   {$skipped}\n";
echo "  Errors:    {$errors}\n";

// Final verification
echo "\n--- Step 3: Verification ---\n";
$stmt = $pdo->query("
    SELECT TABLE_NAME
    FROM information_schema.TABLES t
    WHERE t.TABLE_SCHEMA = " . $pdo->quote($dbName) . "
      AND t.TABLE_TYPE = 'BASE TABLE'
      AND NOT EXISTS (
          SELECT 1 FROM information_schema.TABLE_CONSTRAINTS tc
          WHERE tc.CONSTRAINT_SCHEMA = t.TABLE_SCHEMA
            AND tc.TABLE_NAME = t.TABLE_NAME
            AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
      )
    ORDER BY t.TABLE_NAME
");
$stillMissing = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($stillMissing)) {
    echo "✓ All tables now have primary keys!\n";
} else {
    echo "⚠ The following tables still lack a primary key:\n";
    foreach ($stillMissing as $tbl) {
        echo "  - {$tbl}\n";
        // Try strategy 3 as a last resort fallback
        echo "    → Attempting fallback fix for {$tbl}...\n";
        try {
            $pdo->exec("ALTER TABLE `{$tbl}` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
            echo "    ✓ Added 'id' PRIMARY KEY\n";
            $fixed++;
        } catch (PDOException $e2) {
            echo "    ✗ Fallback also failed: " . $e2->getMessage() . "\n";
            $errors++;
        }
    }
}

$pdo = null;
echo "\nDone.\n";

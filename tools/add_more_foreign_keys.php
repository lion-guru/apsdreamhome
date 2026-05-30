<?php
/**
 * Add more foreign key constraints to improve referential integrity
 * Scans all tables for *_id columns and adds FK constraints where possible
 */

$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Connected to MySQL (apsdreamhome)\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// Mapping of column suffixes -> referenced tables
$referenceMap = [
    'user_id'    => ['table' => 'users',       'column' => 'id'],
    'property_id'=> ['table' => 'properties',   'column' => 'id'],
    'plot_id'    => ['table' => 'plots',        'column' => 'id'],
    'project_id' => ['table' => 'projects',     'column' => 'id'],
    'lead_id'    => ['table' => 'leads',        'column' => 'id'],
    'booking_id' => ['table' => 'bookings',     'column' => 'id'],
    'colony_id'  => ['table' => 'colonies',     'column' => 'id'],
    'district_id'=> ['table' => 'districts',    'column' => 'id'],
    'state_id'   => ['table' => 'states',       'column' => 'id'],
    'city_id'    => ['table' => 'cities',       'column' => 'id'],
    'category_id'=> ['table' => 'categories',   'column' => 'id'],
];

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Found " . count($tables) . " tables\n\n";

// Get all existing FK constraints
$existingFKs = [];
$fkStmt = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'apsdreamhome'
      AND REFERENCED_TABLE_NAME IS NOT NULL
");
foreach ($fkStmt as $row) {
    $key = $row['TABLE_NAME'] . '.' . $row['COLUMN_NAME'];
    $existingFKs[$key] = $row['CONSTRAINT_NAME'];
}
echo "Existing FK constraints: " . count($existingFKs) . "\n\n";

$added = 0;
$skipped = 0;
$errors = 0;

foreach ($tables as $table) {
    // Get columns for this table
    $columns = $pdo->prepare("SHOW COLUMNS FROM `$table`");
    $columns->execute();
    $colData = $columns->fetchAll();

    foreach ($colData as $col) {
        $colName = $col['Field'];

        // Check if column ends with _id
        if (!str_ends_with($colName, '_id')) {
            continue;
        }

        // Check if FK already exists
        $fkKey = $table . '.' . $colName;
        if (isset($existingFKs[$fkKey])) {
            echo "  SKIP: $fkKey (FK already exists: {$existingFKs[$fkKey]})\n";
            $skipped++;
            continue;
        }

        // Determine referenced table from suffix or full column name
        $ref = null;
        if (isset($referenceMap[$colName])) {
            $ref = $referenceMap[$colName];
        } else {
            // Try to derive: remove _id suffix, pluralize
            $base = substr($colName, 0, -3); // remove _id
            $candidates = [$base, $base . 's', $base . 'es'];
            // Handle special plural forms
            if (str_ends_with($base, 'y')) {
                $candidates[] = substr($base, 0, -1) . 'ies';
            }
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $tables)) {
                    $ref = ['table' => $candidate, 'column' => 'id'];
                    break;
                }
            }
        }

        if ($ref === null) {
            echo "  SKIP: $fkKey (no mapping found for column)\n";
            $skipped++;
            continue;
        }

        $refTable = $ref['table'];
        $refColumn = $ref['column'];

        // Verify referenced table exists
        if (!in_array($refTable, $tables)) {
            echo "  SKIP: $fkKey (referenced table '$refTable' does not exist)\n";
            $skipped++;
            continue;
        }

        // Verify referenced column exists
        try {
            $checkCol = $pdo->prepare("SHOW COLUMNS FROM `$refTable` LIKE ?");
            $checkCol->execute([$refColumn]);
            if (!$checkCol->fetch()) {
                echo "  SKIP: $fkKey (column '$refTable.$refColumn' does not exist)\n";
                $skipped++;
                continue;
            }
        } catch (PDOException $e) {
            echo "  SKIP: $fkKey (error checking ref column: " . $e->getMessage() . ")\n";
            $skipped++;
            continue;
        }

        // Check column nullable status to determine ON DELETE behavior
        $isNullable = ($col['Null'] === 'YES');
        $onDelete = $isNullable ? 'SET NULL' : 'CASCADE';

        // Build constraint name
        $constraintName = 'fk_' . $table . '_' . $colName;
        if (strlen($constraintName) > 64) {
            $constraintName = substr($constraintName, 0, 64);
        }

        // Add FK constraint
        $sql = "ALTER TABLE `$table` ADD CONSTRAINT `$constraintName` 
                FOREIGN KEY (`$colName`) REFERENCES `$refTable`(`$refColumn`) 
                ON DELETE $onDelete ON UPDATE CASCADE";

        try {
            $pdo->exec($sql);
            echo "  ADDED: $fkKey → $refTable($refColumn) [ON DELETE $onDelete]\n";
            $added++;
        } catch (PDOException $e) {
            echo "  ERROR: $fkKey → $refTable($refColumn): " . $e->getMessage() . "\n";
            $errors++;
        }
    }
}

echo "\n=== Summary ===\n";
echo "Added:   $added\n";
echo "Skipped: $skipped\n";
echo "Errors:  $errors\n";
echo "Total existing FK before: " . count($existingFKs) . "\n";

// Count new total
$totalStmt = $pdo->query("
    SELECT COUNT(*) as cnt
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'apsdreamhome'
      AND REFERENCED_TABLE_NAME IS NOT NULL
");
$total = $totalStmt->fetch()['cnt'];
echo "Total FK constraints now: $total\n";

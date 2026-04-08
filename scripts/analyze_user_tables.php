<?php
/**
 * Deep Table Analysis - Users/Customers/Admin Tables
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

function getTableColumns($pdo, $table) {
    try {
        $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        return $cols;
    } catch (Exception $e) {
        return [];
    }
}

function getTableIndexes($pdo, $table) {
    try {
        $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        return $indexes;
    } catch (Exception $e) {
        return [];
    }
}

function getRowCount($pdo, $table) {
    try {
        return $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (Exception $e) {
        return -1;
    }
}

function getSampleData($pdo, $table, $limit = 3) {
    try {
        return $pdo->query("SELECT * FROM `$table` LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

$tables = ['users', 'customers', 'admin_users'];

echo "=== DEEP ANALYSIS: USER TABLES ===\n\n";

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    echo str_repeat("=", 60) . "\n";
    echo "Rows: " . getRowCount($pdo, $table) . "\n\n";
    
    echo "COLUMNS:\n";
    $cols = getTableColumns($pdo, $table);
    foreach ($cols as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL');
        if ($col['Key']) echo " [{$col['Key']}]";
        if ($col['Default']) echo " DEFAULT: {$col['Default']}";
        echo "\n";
    }
    
    echo "\nINDEXES:\n";
    $indexes = getTableIndexes($pdo, $table);
    $uniqueIndexes = [];
    foreach ($indexes as $idx) {
        if (!in_array($idx['Key_name'], $uniqueIndexes)) {
            $uniqueIndexes[] = $idx['Key_name'];
            echo "  - {$idx['Key_name']} (";
            $idxCols = array_filter($indexes, fn($i) => $i['Key_name'] === $idx['Key_name']);
            echo implode(', ', array_column($idxCols, 'Column_name'));
            echo ")\n";
        }
    }
    
    echo "\nSAMPLE DATA:\n";
    $samples = getSampleData($pdo, $table);
    foreach ($samples as $row) {
        echo "  " . json_encode($row) . "\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Now analyze relationships
echo "\n=== RELATIONSHIP ANALYSIS ===\n\n";

$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$relatedTables = [];

foreach ($allTables as $t) {
    try {
        $cols = getTableColumns($pdo, $t);
        foreach ($cols as $col) {
            if (preg_match('/user_id|customer_id|admin_id|created_by/', $col['Field'])) {
                if (!isset($relatedTables[$t])) $relatedTables[$t] = [];
                $relatedTables[$t][] = $col['Field'];
            }
        }
    } catch (Exception $e) {}
}

echo "Tables referencing user tables:\n";
foreach ($relatedTables as $t => $cols) {
    echo "  - $t: " . implode(', ', $cols) . "\n";
}

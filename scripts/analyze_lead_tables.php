<?php
/**
 * Deep Table Analysis - Lead Tables
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

// Get all lead-related tables
$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$leadTables = array_filter($allTables, fn($t) => preg_match('/^lead/i', $t));

echo "=== DEEP ANALYSIS: LEAD TABLES ===\n";
echo "Found " . count($leadTables) . " lead-related tables\n\n";

foreach ($leadTables as $table) {
    echo "TABLE: $table\n";
    echo str_repeat("=", 60) . "\n";
    echo "Rows: " . getRowCount($pdo, $table) . "\n\n";
    
    echo "COLUMNS:\n";
    $cols = getTableColumns($pdo, $table);
    foreach ($cols as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL');
        if ($col['Key']) echo " [{$col['Key']}]";
        echo "\n";
    }
    
    echo "\nSAMPLE DATA:\n";
    $samples = getSampleData($pdo, $table, 2);
    foreach ($samples as $row) {
        $row_display = array_map(fn($v) => is_string($v) ? substr($v, 0, 50) : $v, $row);
        echo "  " . json_encode($row_display) . "\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

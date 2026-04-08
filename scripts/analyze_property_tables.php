<?php
/**
 * Deep Table Analysis - Property Tables
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

$tables = ['properties', 'plots', 'plot_master'];

echo "=== DEEP ANALYSIS: PROPERTY TABLES ===\n\n";

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
    
    echo "\nSAMPLE DATA:\n";
    $samples = getSampleData($pdo, $table);
    foreach ($samples as $row) {
        echo "  " . json_encode($row) . "\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Analyze overlaps
echo "\n=== OVERLAP ANALYSIS ===\n\n";

$properties_cols = array_column(getTableColumns($pdo, 'properties'), 'Field');
$plots_cols = array_column(getTableColumns($pdo, 'plots'), 'Field');
$plot_master_cols = array_column(getTableColumns($pdo, 'plot_master'), 'Field');

echo "Common columns across all tables:\n";
$common = array_intersect($properties_cols, $plots_cols, $plot_master_cols);
foreach ($common as $col) {
    echo "  ✓ $col\n";
}

echo "\nOnly in properties:\n";
$only_props = array_diff($properties_cols, $plots_cols, $plot_master_cols);
foreach ($only_props as $col) echo "  - $col\n";

echo "\nOnly in plots:\n";
$only_plots = array_diff($plots_cols, $properties_cols, $plot_master_cols);
foreach ($only_plots as $col) echo "  - $col\n";

echo "\nOnly in plot_master:\n";
$only_master = array_diff($plot_master_cols, $properties_cols, $plots_cols);
foreach ($only_master as $col) echo "  - $col\n";

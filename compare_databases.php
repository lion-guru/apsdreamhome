<?php
// Database comparison script
echo "=== Database Comparison Analysis ===\n\n";

// Read the SQL file
$sqlFile = 'C:\\xampp\\htdocs\\apsdreamhome\\database\\apsdreamhome.sql';
$sqlContent = file_get_contents($sqlFile);

// Extract table names and their data from SQL file
preg_match_all('/CREATE TABLE `([^`]+)`/', $sqlContent, $createTableMatches);
$sqlTables = $createTableMatches[1];

preg_match_all('/INSERT INTO `([^`]+)`/', $sqlContent, $insertMatches);
$sqlTablesWithData = array_unique($insertMatches[1]);

echo "Tables in SQL file: " . count($sqlTables) . "\n";
echo "Tables with data in SQL file: " . count($sqlTablesWithData) . "\n\n";

// Get current database tables
require_once 'config.php';
$currentTables = [];
$result = $con->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    $currentTables[] = $row[0];
}

echo "Tables in current database: " . count($currentTables) . "\n\n";

// Compare tables
$missingTables = array_diff($sqlTables, $currentTables);
$extraTables = array_diff($currentTables, $sqlTables);
$commonTables = array_intersect($sqlTables, $currentTables);

echo "=== Table Structure Analysis ===\n";
if (!empty($missingTables)) {
    echo "❌ Missing tables in current database:\n";
    foreach ($missingTables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
}

if (!empty($extraTables)) {
    echo "⚠️  Extra tables in current database (not in SQL file):\n";
    foreach ($extraTables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
}

echo "=== Data Analysis ===\n";
echo "Tables with data in SQL file:\n";
foreach ($sqlTablesWithData as $table) {
    echo "  - $table\n";
}
echo "\n";

// Check current data in common tables
foreach ($commonTables as $table) {
    $result = $con->query("SELECT COUNT(*) as count FROM `$table`");
    $currentCount = $result->fetch_assoc()['count'];
    
    // Count records in SQL file for this table
    preg_match_all("/INSERT INTO `$table`.*;/", $sqlContent, $tableInserts);
    $sqlCount = count($tableInserts[0]);
    
    $status = $currentCount == 0 ? "⚠️  EMPTY" : "✓ Has data";
    $sqlStatus = $sqlCount > 0 ? "✓ Has data ($sqlCount records)" : "⚠️  No data";
    
    echo "Table '$table': Current: $status | SQL file: $sqlStatus\n";
}

echo "\n=== Summary ===\n";
echo "Total tables in SQL: " . count($sqlTables) . "\n";
echo "Total tables in current DB: " . count($currentTables) . "\n";
echo "Missing tables: " . count($missingTables) . "\n";
echo "Tables needing data: " . count(array_filter($commonTables, function($table) use ($con) {
    $result = $con->query("SELECT COUNT(*) as count FROM `$table`");
    return $result->fetch_assoc()['count'] == 0;
})) . "\n";

echo "\n=== Recommendations ===\n";
if (!empty($missingTables)) {
    echo "1. Create missing tables\n";
}
echo "2. Import data from SQL file for empty tables\n";
echo "3. Verify data integrity after import\n";
?>
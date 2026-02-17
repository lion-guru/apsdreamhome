<?php
// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhome',
    'user' => 'root',
    'pass' => '',
];

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};",
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get all tables from database
    $tablesInDb = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tablesInDb[] = $row[0];
    }
    sort($tablesInDb);

    // Get tables from schema file
    $schemaFile = __DIR__ . '/db_schema.sql';
    $schemaContent = file_get_contents($schemaFile);
    
    preg_match_all('/CREATE\s+TABLE\s+[`"]([^`"]+)[`"]/i', $schemaContent, $matches);
    $tablesInSchema = $matches[1] ?? [];
    sort($tablesInSchema);

    // Find differences
    $onlyInDb = array_diff($tablesInDb, $tablesInSchema);
    $onlyInSchema = array_diff($tablesInSchema, $tablesInDb);

    // Start output
    echo "=== Database Schema Comparison ===\n\n";
    
    // 1. Tables only in database
    echo "=== Tables only in database (missing from schema): ===\n";
    if (!empty($onlyInDb)) {
        foreach ($onlyInDb as $table) {
            echo "- $table\n";
        }
    } else {
        echo "None\n";
    }
    
    // 2. Tables only in schema (not in database)
    echo "\n=== Tables only in schema (not in database): ===\n";
    if (!empty($onlyInSchema)) {
        foreach ($onlyInSchema as $table) {
            echo "- $table\n";
        }
    } else {
        echo "None\n";
    }
    
    // 3. Check MLM tables specifically
    $mlmTables = [
        'associates', 'mlm_tree', 'mlm_commissions', 
        'mlm_commission_ledger', 'commission_payouts', 
        'commission_transactions', 'associate_levels', 'team_hierarchy'
    ];
    
    echo "\n=== MLM Tables Status ===\n";
    foreach ($mlmTables as $table) {
        $inDb = in_array($table, $tablesInDb) ? "✓" : "✗";
        $inSchema = in_array($table, $tablesInSchema) ? "✓" : "✗";
        echo str_pad("$table", 30) . " | DB: $inDb | Schema: $inSchema\n";
    }
    
    // 4. Generate SQL to update schema
    $updateSql = "-- SQL to update schema\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Add tables that are missing from schema
    if (!empty($onlyInDb)) {
        $updateSql .= "-- Tables to add to schema (exist in database but not in schema)\n";
        foreach ($onlyInDb as $table) {
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch()["Create Table"];
            $updateSql .= "\n-- Table: $table\n";
            $updateSql .= "$createTable;\n";
        }
    }
    
    // Save the update SQL
    $updateFile = 'schema_update_' . date('Ymd_His') . '.sql';
    file_put_contents($updateFile, $updateSql);
    
    echo "\nUpdate SQL generated: $updateFile\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

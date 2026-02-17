<?php
// Import essential data from SQL file
echo "=== Importing Essential Data ===\n\n";

require_once 'config.php';

// Read the SQL file
$sqlFile = 'C:\\xampp\\htdocs\\apsdreamhome\\database\\apsdreamhome.sql';
$sqlContent = file_get_contents($sqlFile);

// Tables that need essential data (in order of importance)
$essentialTables = [
    'accounting_settings',
    'admin', 
    'admin_activity_log',
    'about',
    'properties',
    'property_types',
    'locations',
    'settings',
    'testimonials',
    'services'
];

// Extract and execute INSERT statements for essential tables
foreach ($essentialTables as $table) {
    echo "Processing table: $table\n";
    
    // Check if table exists and has data
    $result = $con->query("SELECT COUNT(*) as count FROM `$table`");
    $currentCount = $result->fetch_assoc()['count'];
    
    if ($currentCount > 0) {
        echo "  ✓ Table already has $currentCount records - skipping\n";
        continue;
    }
    
    // Extract INSERT statements for this table
    preg_match_all("/INSERT INTO `$table`.*;/", $sqlContent, $insertMatches);
    
    if (empty($insertMatches[0])) {
        echo "  ⚠️  No INSERT statements found for $table\n";
        continue;
    }
    
    echo "  Found " . count($insertMatches[0]) . " INSERT statements\n";
    
    // Execute each INSERT statement
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($insertMatches[0] as $insertStatement) {
        try {
            // Clean up the statement (remove any comments or extra whitespace)
            $cleanStatement = trim($insertStatement);
            if (substr($cleanStatement, -1) == ';') {
                $cleanStatement = substr($cleanStatement, 0, -1);
            }
            
            $con->query($cleanStatement);
            $successCount++;
        } catch (Exception $e) {
            $errorCount++;
            echo "    Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "  ✓ Successfully inserted: $successCount records\n";
    if ($errorCount > 0) {
        echo "  ❌ Failed: $errorCount records\n";
    }
    echo "\n";
}

echo "=== Import Complete ===\n";

// Verify the import
echo "\n=== Verification ===\n";
foreach ($essentialTables as $table) {
    $result = $con->query("SELECT COUNT(*) as count FROM `$table`");
    $count = $result->fetch_assoc()['count'];
    echo "$table: $count records\n";
}
?>
<?php
/**
 * APS Dream Home Database Optimization Script
 * 
 * This script optimizes the database by:
 * 1. Removing duplicate records
 * 2. Fixing orphaned records
 * 3. Optimizing table structures
 * 4. Cleaning up temporary data
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Start output buffering
ob_start();

// HTML header
echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home Database Optimization</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #2c3e50; }
        h2 { color: #3498db; margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .section { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .progress-bar { 
            height: 20px; 
            background-color: #e0e0e0; 
            border-radius: 10px; 
            margin: 10px 0; 
            overflow: hidden; 
        }
        .progress { 
            height: 100%; 
            background-color: #3498db; 
            width: 0%; 
            transition: width 0.5s;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
        }
    </style>
    <script>
        function updateProgress(percent) {
            document.getElementById('progress').style.width = percent + '%';
            document.getElementById('progress-text').innerText = percent + '%';
        }
    </script>
</head>
<body>
    <h1>APS Dream Home Database Optimization</h1>
    <p>Started at: " . date('Y-m-d H:i:s') . "</p>
    
    <div class='section'>
        <h2>Progress</h2>
        <div class='progress-bar'>
            <div id='progress' class='progress'></div>
        </div>
        <p id='progress-text'>0%</p>
    </div>
    
    <div class='section'>
        <h2>Optimization Log</h2>
        <pre id='log'>";

// Flush output
ob_flush();
flush();

// Function to update progress
function updateProgress($percent) {
    echo "<script>updateProgress($percent);</script>";
    ob_flush();
    flush();
}

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
    exit;
}

echo "Connected successfully to database\n";
updateProgress(10);

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
}

echo "Found " . count($tables) . " tables in database\n";
updateProgress(15);

// Step 1: Remove duplicate records
echo "\n=== Removing duplicate records ===\n";
$duplicatesRemoved = 0;

foreach ($tables as $i => $table) {
    echo "Checking $table for duplicates...\n";
    
    // Get primary key
    $primaryKey = null;
    $result = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $primaryKey = $row['Column_name'];
    }
    
    if ($primaryKey) {
        // Get all columns except primary key
        $columns = [];
        $result = $conn->query("DESCRIBE `$table`");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row['Field'] != $primaryKey) {
                    $columns[] = $row['Field'];
                }
            }
        }
        
        if (!empty($columns)) {
            // Check if this table has foreign key constraints pointing to it
            $hasForeignKeys = false;
            $checkReferencesQuery = "SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                                   WHERE REFERENCED_TABLE_SCHEMA = '$dbname' 
                                   AND REFERENCED_TABLE_NAME = '$table'";
            $referencesResult = $conn->query($checkReferencesQuery);
            
            if ($referencesResult && $referencesResult->num_rows > 0) {
                $hasForeignKeys = true;
                echo "Table $table has foreign key references - using safe duplicate removal method\n";
                while ($refRow = $referencesResult->fetch_assoc()) {
                    echo "  - Referenced by {$refRow['TABLE_NAME']}.{$refRow['COLUMN_NAME']}\n";
                }
            }
            
            // Find duplicates based on all columns
            $columnList = implode(', ', $columns);
            $query = "SELECT $columnList, COUNT(*) as count FROM `$table` GROUP BY $columnList HAVING count > 1";
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                echo "Found duplicates in $table\n";
                
                // Keep only one record for each duplicate set
                while ($row = $result->fetch_assoc()) {
                    $conditions = [];
                    foreach ($columns as $column) {
                        if ($row[$column] === null) {
                            $conditions[] = "`$column` IS NULL";
                        } else {
                            $conditions[] = "`$column` = '" . $row[$column]. "'";
                        }
                    }
                    
                    $whereClause = implode(' AND ', $conditions);
                    
                    if ($hasForeignKeys) {
                        // For tables with foreign key references, use a safer approach
                        // First, identify the record to keep (with the minimum primary key value)
                        $findMinQuery = "SELECT MIN(`$primaryKey`) as min_id FROM `$table` WHERE $whereClause";
                        $minResult = $conn->query($findMinQuery);
                        $minRow = $minResult->fetch_assoc();
                        $minId = $minRow['min_id'];
                        
                        // Then find all duplicate IDs except the one to keep
                        $findDuplicatesQuery = "SELECT `$primaryKey` FROM `$table` WHERE $whereClause AND `$primaryKey` != $minId";
                        $duplicatesResult = $conn->query($findDuplicatesQuery);
                        
                        $duplicateCount = 0;
                        if ($duplicatesResult && $duplicatesResult->num_rows > 0) {
                            while ($dupRow = $duplicatesResult->fetch_assoc()) {
                                $dupId = $dupRow[$primaryKey];
                                
                                // For each duplicate, update any foreign key references to point to the record we're keeping
                                foreach ($referencesResult as $refRow) {
                                    $updateRefQuery = "UPDATE `{$refRow['TABLE_NAME']}` 
                                                      SET `{$refRow['COLUMN_NAME']}` = $minId 
                                                      WHERE `{$refRow['COLUMN_NAME']}` = $dupId";
                                    $conn->query($updateRefQuery);
                                }
                                
                                // Now it's safe to delete the duplicate
                                $safeDeleteQuery = "DELETE FROM `$table` WHERE `$primaryKey` = $dupId";
                                $deleteResult = $conn->query($safeDeleteQuery);
                                if ($deleteResult) {
                                    $duplicateCount++;
                                }
                            }
                            $duplicatesRemoved += $duplicateCount;
                            echo "Safely removed $duplicateCount duplicates from $table\n";
                        }
                    } else {
                        // For tables without foreign key references, use the faster method
                        $deleteQuery = "DELETE FROM `$table` WHERE $whereClause AND `$primaryKey` NOT IN (SELECT MIN(`$primaryKey`) FROM `$table` t WHERE $whereClause)";
                        $deleteResult = $conn->query($deleteQuery);
                        if ($deleteResult) {
                            $duplicatesRemoved += $conn->affected_rows;
                        }
                    }
                }
            } else {
                echo "No duplicates found in $table\n";
            }
        }
    } else {
        echo "No primary key found for $table, skipping duplicate check\n";
    }
    
    // Update progress
    updateProgress(15 + (25 * ($i + 1) / count($tables)));
}

echo "Total duplicates removed: $duplicatesRemoved\n";
updateProgress(40);

// Step 2: Fix orphaned records
echo "\n=== Fixing orphaned records ===\n";
$orphanedFixed = 0;

// Check for foreign keys
$foreignKeys = [];
$result = $conn->query("
    SELECT 
        TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
        REFERENCED_TABLE_SCHEMA = '$dbname'
        AND REFERENCED_TABLE_NAME IS NOT NULL
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $foreignKeys[] = [
            'table' => $row['TABLE_NAME'],
            'column' => $row['COLUMN_NAME'],
            'referenced_table' => $row['REFERENCED_TABLE_NAME'],
            'referenced_column' => $row['REFERENCED_COLUMN_NAME']
        ];
    }
}

echo "Found " . count($foreignKeys) . " foreign key relationships\n";

foreach ($foreignKeys as $i => $fk) {
    echo "Checking {$fk['table']}.{$fk['column']} -> {$fk['referenced_table']}.{$fk['referenced_column']} for orphaned records...\n";
    
    // Find orphaned records
    $query = "
        SELECT t.* FROM `{$fk['table']}` t
        LEFT JOIN `{$fk['referenced_table']}` r ON t.`{$fk['column']}` = r.`{$fk['referenced_column']}`
        WHERE t.`{$fk['column']}` IS NOT NULL AND r.`{$fk['referenced_column']}` IS NULL
    ";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $orphanedCount = $result->num_rows;
        echo "Found $orphanedCount orphaned records in {$fk['table']}\n";
        
        // Fix orphaned records by setting foreign key to NULL if possible
        $updateQuery = "
            UPDATE `{$fk['table']}` t
            LEFT JOIN `{$fk['referenced_table']}` r ON t.`{$fk['column']}` = r.`{$fk['referenced_column']}`
            SET t.`{$fk['column']}` = NULL
            WHERE t.`{$fk['column']}` IS NOT NULL AND r.`{$fk['referenced_column']}` IS NULL
        ";
        
        $updateResult = $conn->query($updateQuery);
        if ($updateResult) {
            $orphanedFixed += $conn->affected_rows;
            echo "Fixed {$conn->affected_rows} orphaned records in {$fk['table']}\n";
        } else {
            echo "Error fixing orphaned records: " . $conn->error . "\n";
        }
    } else {
        echo "No orphaned records found in {$fk['table']}\n";
    }
    
    // Update progress
    updateProgress(40 + (20 * ($i + 1) / count($foreignKeys)));
}

echo "Total orphaned records fixed: $orphanedFixed\n";
updateProgress(60);

// Step 3: Optimize tables
echo "\n=== Optimizing tables ===\n";

foreach ($tables as $i => $table) {
    echo "Optimizing $table...\n";
    $result = $conn->query("OPTIMIZE TABLE `$" . $table . "`");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Result: " . $row['Msg_text'] . "\n";
    } else {
        echo "Error optimizing table: " . $conn->error . "\n";
    }
    
    // Update progress
    updateProgress(60 + (20 * ($i + 1) / count($tables)));
}

updateProgress(80);

// Step 4: Clean up temporary data
echo "\n=== Cleaning up temporary data ===\n";

// Define temporary tables or data patterns
$tempPatterns = [
    'temp_', 'tmp_', 'test_', 'bak_'
];

$tempTablesRemoved = 0;
foreach ($tables as $table) {
    foreach ($tempPatterns as $pattern) {
        if (strpos($table, $pattern) === 0) {
            // Check if the table has any foreign key references pointing to it
            $checkReferencesQuery = "SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                                   WHERE REFERENCED_TABLE_SCHEMA = '$dbname' 
                                   AND REFERENCED_TABLE_NAME = '$table'";
            $referencesResult = $conn->query($checkReferencesQuery);
            
            if ($referencesResult && $referencesResult->num_rows > 0) {
                echo "Cannot remove table $table - it has foreign key references:\n";
                while ($refRow = $referencesResult->fetch_assoc()) {
                    echo "  - Referenced by {$refRow['TABLE_NAME']}.{$refRow['COLUMN_NAME']}\n";
                }
            } else {
                echo "Removing temporary table $table...\n";
                $result = $conn->query("DROP TABLE `$" . $table . "`");
                if ($result) {
                    $tempTablesRemoved++;
                } else {
                    echo "Error removing table: " . $conn->error . "\n";
                }
            }
            break;
        }
    }
}

echo "Total temporary tables removed: $tempTablesRemoved\n";
updateProgress(90);

// Step 5: Final database status
echo "\n=== Final Database Status ===\n";

// Get database size
$result = $conn->query("
    SELECT 
        SUM(data_length + index_length) / 1024 / 1024 AS size_mb 
    FROM 
        information_schema.TABLES 
    WHERE 
        table_schema = '$dbname'
");

if ($result) {
    $row = $result->fetch_assoc();
    echo "Database size: " . round($row['size_mb'], 2) . " MB\n";
}

// Get table counts
echo "\nTable record counts:\n";
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM `$" . $table . "`");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "$table: " . $row['count'] . " records\n";
    }
}

// Close connection
$conn->close();

echo "\nOptimization completed at: " . date('Y-m-d H:i:s') . "\n";
updateProgress(100);

// End output
echo "</pre>
    </div>
    
    <div class='section'>
        <h2>Summary</h2>
        <p><strong>Duplicates Removed:</strong> $duplicatesRemoved</p>
        <p><strong>Orphaned Records Fixed:</strong> $orphanedFixed</p>
        <p><strong>Temporary Tables Removed:</strong> $tempTablesRemoved</p>
        <p><strong>All Tables Optimized:</strong> " . count($tables) . "</p>
        <p class='success'>Database optimization completed successfully!</p>
        <a href='index.php' class='btn'>Return to Database Management Hub</a>
    </div>
</body>
</html>";

ob_end_flush();
?>

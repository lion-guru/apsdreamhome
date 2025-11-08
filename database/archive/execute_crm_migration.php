<?php
require_once __DIR__ . '/../config.php';

// Function to check if a table exists
function tableExists($tableName) {
    global $con;
    $result = $con->query("SHOW TABLES LIKE '$tableName'")->num_rows;
    return $result > 0;
}

// Function to execute SQL from file
function executeSQLFile($filePath) {
    global $con;
    
    try {
        // Read and execute SQL file
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new Exception("Error reading SQL file: $filePath");
        }
        
        // Execute each SQL statement
        if ($con->multi_query($sql)) {
            do {
                // Store first result set
                if ($result = $con->store_result()) {
                    $result->free();
                }
            } while ($con->more_results() && $con->next_result());
        }
        
        if ($con->error) {
            throw new Exception("Error executing SQL: " . $con->error);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Migration Error: " . $e->getMessage());
        return false;
    }
}

// Main migration execution
try {
    // Check if CRM tables already exist
    if (!tableExists('leads') && !tableExists('opportunities')) {
        // Execute CRM tables migration
        $crmTablesFile = __DIR__ . '/crm_tables.sql';
        if (executeSQLFile($crmTablesFile)) {
            echo "CRM tables migration successful!\n";
        } else {
            throw new Exception("Failed to create CRM tables");
        }
    } else {
        echo "CRM tables already exist. Skipping migration.\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
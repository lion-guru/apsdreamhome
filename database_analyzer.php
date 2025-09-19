<?php
/**
 * Database Structure Analyzer
 * Analyzes the database structure and identifies any issues
 */

// Include required files
require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/config/config.php';

// Function to get all tables in the database
function getAllTables($conn) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    return $tables;
}

// Function to check if a table exists
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0;
}

// Function to get table structure
function getTableStructure($conn, $table) {
    $structure = [];
    $result = $conn->query("DESCRIBE `$table`");
    while ($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
    return $structure;
}

// Function to analyze the database
function analyzeDatabase($conn) {
    $analysis = [];
    
    // Get all tables
    $tables = getAllTables($conn);
    $analysis['tables'] = $tables;
    
    // Check for required tables
    $requiredTables = [
        'users', 'properties', 'bookings', 'customers', 'leads',
        'transactions', 'notifications', 'property_visits', 'mlm_commissions'
    ];
    
    $missingTables = [];
    foreach ($requiredTables as $table) {
        if (!in_array($table, $tables)) {
            $missingTables[] = $table;
        }
    }
    $analysis['missing_tables'] = $missingTables;
    
    // Check table structures
    $tableStructures = [];
    foreach ($tables as $table) {
        $tableStructures[$table] = getTableStructure($conn, $table);
    }
    $analysis['table_structures'] = $tableStructures;
    
    return $analysis;
}

// Main execution
try {
    // Get database connection
    $conn = getDbConnection();
    
    if ($conn === null) {
        throw new Exception("Failed to connect to the database");
    }
    
    // Analyze the database
    $analysis = analyzeDatabase($conn);
    
    // Output the analysis
    header('Content-Type: application/json');
    echo json_encode($analysis, JSON_PRETTY_PRINT);
    
    // Close the connection
    $conn->close();
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

// End of script
?>

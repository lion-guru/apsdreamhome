<?php
/**
 * Database Structure Analyzer
 * Analyzes the database structure and identifies any issues
 */

// Include required files
require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/config/config.php';

// Function to get all tables in the database using prepared statement
function getAllTables($conn) {
    $tables = [];
    $result = $conn->prepare("SHOW TABLES");
    $result->execute();
    $table_result = $result->get_result();
    while ($row = $table_result->fetch_array()) {
        $tables[] = $row[0];
    }
    $result->close();
    return $tables;
}

// Function to check if a table exists using prepared statement
function tableExists($conn, $table) {
    $stmt = $conn->prepare("SHOW TABLES LIKE ?");
    $stmt->bind_param("s", $table);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Function to get table structure using prepared statement
function getTableStructure($conn, $table) {
    $structure = [];
    $stmt = $conn->prepare("DESCRIBE `$table`");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
    $stmt->close();
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
    $conn = getMysqliConnection();
    
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

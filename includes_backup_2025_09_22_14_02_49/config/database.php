<?php
/**
 * Database Configuration and Connection
 * 
 * This file initializes the database connection using the DatabaseConfig class
 * and makes the database connection available throughout the application.
 */

// Include the DatabaseConfig class
require_once __DIR__ . '/DatabaseConfig.php';

// Initialize database configuration
DatabaseConfig::init();

// Get the database connection
try {
    $db = DatabaseConfig::getConnection();
    
    // Set character set to ensure proper encoding
    $db->set_charset("utf8mb4");
    
    // Set timezone for database connection
    $db->query("SET time_zone = '+05:30'");
    
    // Set SQL mode to be less strict for better compatibility
    $db->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    
} catch (Exception $e) {
    // Log the error
    error_log('Database connection error: ' . $e->getMessage());
    
    // Show user-friendly error message
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        die('Database connection error: ' . $e->getMessage());
    } else {
        die('Unable to connect to the database. Please try again later.');
    }
}

// Make database connection available globally
$GLOBALS['db'] = $db;

// Function to get database connection
function getDbConnection() {
    global $db;
    return $db;
}

// Close database connection at the end of script execution
register_shutdown_function(function() use ($db) {
    if ($db instanceof mysqli) {
        $db->close();
    }
});

// Helper function for prepared statements
function executeQuery($sql, $params = [], $types = '') {
    global $db;
    
    // Check if database connection is valid
    if (!($db instanceof mysqli)) {
        throw new Exception('Database connection is not valid');
    }
    
    $stmt = $db->prepare($sql);
    
    if ($stmt === false) {
        $error = $db->error ?? 'Unknown database error';
        throw new Exception('Failed to prepare statement: ' . $error);

    }
    
    if (!empty($params)) {
        if (empty($types)) {
            // Auto-detect parameter types if not provided
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b'; // blob
                }
            }
        }
        
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    }
    
    $stmt->execute();
    return $stmt;
}

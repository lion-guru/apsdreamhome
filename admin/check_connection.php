<?php
/**
 * Database Connection Checker for Admin Panel
 * This file checks and validates the database connection
 * for the APS Dream Homes admin panel.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to test database connection
function testDatabaseConnection() {
    try {
        $conn = getDbConnection();
        
        if ($conn instanceof mysqli && !$conn->connect_error) {
            // Test query to verify connection and permissions
            $test_query = "SELECT 1";
            $result = $conn->query($test_query);
            
            if ($result) {
                return array(
                    'status' => 'success',
                    'message' => 'Database connection successful',
                    'details' => array(
                        'host' => DB_HOST,
                        'database' => DB_NAME,
                        'user' => DB_USER
                    )
                );
            }
        }
        
        return array(
            'status' => 'error',
            'message' => 'Database connection failed: ' . ($conn->connect_error ?? 'Unknown error'),
            'details' => null
        );
        
    } catch (Exception $e) {
        return array(
            'status' => 'error',
            'message' => 'Exception occurred: ' . $e->getMessage(),
            'details' => null
        );
    }
}

// Check connection and output results
$connection_status = testDatabaseConnection();

// Output results in JSON format
header('Content-Type: application/json');
echo json_encode($connection_status, JSON_PRETTY_PRINT);
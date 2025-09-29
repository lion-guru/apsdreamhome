<?php
/**
 * Test Database Connection Script
 * 
 * This script tests the database connection and displays basic information.
 */

// Include configuration
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Create response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Test database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Get server info
    $response['data']['server_info'] = $conn->server_info;
    $response['data']['host_info'] = $conn->host_info;
    $response['data']['protocol_version'] = $conn->protocol_version;
    $response['data']['character_set'] = $conn->character_set_name();
    
    // Get tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        $result->free();
    }
    $response['data']['tables'] = $tables;
    
    // Get users count
    $result = $conn->query("SELECT COUNT(*) as user_count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        $response['data']['user_count'] = (int)$row['user_count'];
        $result->free();
    }
    
    // Get leads count
    $result = $conn->query("SELECT COUNT(*) as lead_count FROM leads");
    if ($result) {
        $row = $result->fetch_assoc();
        $response['data']['lead_count'] = (int)$row['lead_count'];
        $result->free();
    }
    
    // Close connection
    $conn->close();
    
    $response['success'] = true;
    $response['message'] = 'Database connection successful';
    
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    $response['data']['error'] = [
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
}

// Output JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>

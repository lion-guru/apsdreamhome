<?php
/**
 * Test Authentication Script
 * 
 * This script tests the authentication system and database connection.
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Include configuration
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_config.php';

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
    
    // Get a test user (first admin user)
    $sql = "SELECT id, name, email, type, status FROM users WHERE type = 'admin' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['type'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        $response['data']['user'] = $user;
        $response['data']['session'] = [
            'user_id' => $_SESSION['user_id'],
            'user_role' => $_SESSION['user_role'],
            'user_email' => $_SESSION['user_email'],
            'user_name' => $_SESSION['user_name']
        ];
        
        $response['success'] = true;
        $response['message'] = 'Authentication successful';
    } else {
        throw new Exception("No admin user found in the database");
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
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

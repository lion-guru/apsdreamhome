<?php
require_once '../includes/config/db_config.php';

// Check if admin user exists
function checkAdminUser() {
    $conn = getDbConnection();
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows === 0) {
        return [
            'status' => 'error',
            'message' => 'Users table does not exist.'
        ];
    }
    
    // Check for admin user
    $stmt = $conn->prepare("SELECT id, username, email, role, status FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [
            'status' => 'warning',
            'message' => 'No admin user found.'
        ];
    }
    
    $admin = $result->fetch_assoc();
    return [
        'status' => 'success',
        'message' => 'Admin user found.',
        'data' => $admin
    ];
}

// Check database connection
try {
    $conn = getDbConnection();
    $dbStatus = $conn ? 'success' : 'error';
    $dbError = $conn ? '' : $conn->connect_error;
    
    $adminCheck = checkAdminUser();
    
    // Output results
    header('Content-Type: application/json');
    echo json_encode([
        'database' => [
            'status' => $dbStatus,
            'error' => $dbError,
            'host' => DB_HOST,
            'name' => DB_NAME
        ],
        'admin_user' => $adminCheck
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>

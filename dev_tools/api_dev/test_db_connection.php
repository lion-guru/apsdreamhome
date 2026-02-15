<?php
require_once __DIR__ . '/../app/core/App.php';
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
    // Test database connection using centralized connection
    $db = \App\Core\App::database();
    
    // Check connection
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    // Get server info
    $response['data']['server_info'] = $db->getConnection()->getAttribute(PDO::ATTR_SERVER_INFO);
    $response['data']['host_info'] = $db->getConnection()->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    $response['data']['protocol_version'] = $db->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
    $response['data']['character_set'] = 'utf8mb4'; // Standard for our ORM
    
    // Get tables
    $tables = [];
    $result = $db->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
    }
    $response['data']['tables'] = $tables;
    
    // Get users count
    $result = $db->query("SELECT COUNT(*) as user_count FROM users");
    if ($result) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $response['data']['user_count'] = (int)$row['user_count'];
    }
    
    // Get leads count
    $result = $db->query("SELECT COUNT(*) as lead_count FROM leads");
    if ($result) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $response['data']['lead_count'] = (int)$row['lead_count'];
    }
    
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

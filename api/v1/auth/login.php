<?php
/**
 * User Login API Endpoint
 * 
 * Handles user authentication and returns a JWT token
 */

// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include required files
require_once __DIR__ . '/../../../includes/db_config.php';
require_once __DIR__ . '/../../../includes/ApiAuth.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'token' => null,
    'user' => null
];

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input['email']) || empty($input['password'])) {
        throw new Exception('Email and password are required', 400);
    }
    
    // Get database connection
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Get auth instance
    $auth = ApiAuth::getInstance();
    
    // Authenticate user
    $user = $auth->authenticateUser($input['email'], $input['password']);
    
    if (!$user) {
        throw new Exception('Invalid email or password', 401);
    }
    
    // Generate token
    $token = $auth->generateToken($user);
    
    // Update last login timestamp
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // Prepare response
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['token'] = $token;
    $response['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user'
    ];
    
    // Set HTTP status code
    http_response_code(200);
    
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 400;
    http_response_code($statusCode);
    $response['message'] = $e->getMessage();
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT);

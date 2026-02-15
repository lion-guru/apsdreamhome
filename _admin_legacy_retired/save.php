<?php
session_start();
require_once __DIR__ . '/includes/config/config.php';
global $con;

header('Content-Type: application/json');

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

try {
    $conn = $con;
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    $table = $data['table'];
    $fields = $data['fields'];

    // Prepare column names and values
    $columns = [];
    $values = [];
    $types = '';
    $params = [];

    foreach ($fields as $key => $value) {
        $columns[] = $key;
        $values[] = '?';
        $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $params[] = $value;
    }

    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $columns),
        implode(', ', $values)
    );

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }

    $insertId = $stmt->insert_id;
    $stmt->close();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Data saved successfully',
        'id' => $insertId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
<?php
/**
 * Database API for MCP Integration
 * 
 * Handle all database operations via HTTP requests
 */

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'apsdreamhome';

// Connect using MySQLi (works in XAMPP)
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path_info = $_SERVER['PATH_INFO'] ?? '';
$path = explode('/', trim($path_info, '/'));

// Log request for debugging
error_log("Database API Request: $method " . $path_info);

// Handle different endpoints
try {
    switch ($method) {
        case 'GET':
            handleGetRequest($conn, $path);
            break;
        case 'POST':
            handlePostRequest($conn, $path);
            break;
        case 'PUT':
            handlePutRequest($conn, $path);
            break;
        case 'DELETE':
            handleDeleteRequest($conn, $path);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

$conn->close();

// Handle GET requests
function handleGetRequest($conn, $path) {
    switch ($path[0] ?? '') {
        case 'tables':
            // List all tables
            $result = $conn->query('SHOW TABLES');
            if ($result) {
                $tables = [];
                while ($row = $result->fetch_row()) {
                    $tables[] = $row[0];
                }
                echo json_encode([
                    'success' => true,
                    'data' => $tables,
                    'count' => count($tables)
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $conn->error
                ]);
            }
            break;
            
        case 'table':
            // Get table structure or data
            $tableName = $path[1] ?? '';
            if (empty($tableName)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Table name required']);
                return;
            }
            
            $action = $path[2] ?? 'structure';
            
            if ($action === 'structure') {
                // Get table structure
                $result = $conn->query("DESCRIBE `$tableName`");
                if ($result) {
                    $structure = [];
                    while ($row = $result->fetch_assoc()) {
                        $structure[] = $row;
                    }
                    echo json_encode([
                        'success' => true,
                        'data' => $structure
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => $conn->error
                    ]);
                }
            } elseif ($action === 'data') {
                // Get table data
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                
                $result = $conn->query("SELECT * FROM `$tableName` LIMIT $limit OFFSET $offset");
                if ($result) {
                    $data = [];
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                    
                    // Get total count
                    $countResult = $conn->query("SELECT COUNT(*) as total FROM `$tableName`");
                    $total = $countResult->fetch_assoc()['total'];
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $data,
                        'total' => $total,
                        'limit' => $limit,
                        'offset' => $offset
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => $conn->error
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
            }
            break;
            
        case 'info':
            // Get database information
            $info = [];
            
            // Get database name
            $result = $conn->query('SELECT DATABASE() as db_name');
            $info['database'] = $result->fetch_assoc()['db_name'];
            
            // Get table count
            $result = $conn->query('SHOW TABLES');
            $info['table_count'] = $result->num_rows;
            
            // Get MySQL version
            $result = $conn->query('SELECT VERSION() as version');
            $info['mysql_version'] = $result->fetch_assoc()['version'];
            
            // Get database size
            $result = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = '$info[database]'");
            $info['size_mb'] = $result->fetch_assoc()['size'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'data' => $info
            ]);
            break;
            
        case 'status':
            // Check API status
            echo json_encode([
                'success' => true,
                'data' => [
                    'status' => 'online',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'path' => $path_info,
                    'database' => 'connected'
                ]
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
    }
}

// Handle POST requests
function handlePostRequest($conn, $path) {
    switch ($path[0] ?? '') {
        case 'query':
            // Execute SQL query
            $input = json_decode(file_get_contents('php://input'), true);
            $sql = $input['sql'] ?? '';
            
            if (empty($sql)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'SQL query required']);
                return;
            }
            
            // Log query for debugging
            error_log("Executing SQL: " . substr($sql, 0, 100) . "...");
            
            $result = $conn->query($sql);
            
            if ($result) {
                if (stripos($sql, 'SELECT') === 0 || stripos($sql, 'SHOW') === 0) {
                    // Return data for SELECT queries
                    $data = [];
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                    echo json_encode([
                        'success' => true,
                        'data' => $data,
                        'count' => count($data),
                        'sql' => $sql
                    ]);
                } else {
                    // Return affected rows for INSERT, UPDATE, DELETE
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'affected_rows' => $conn->affected_rows,
                            'insert_id' => $conn->insert_id
                        ],
                        'sql' => $sql
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $conn->error,
                    'sql' => $sql
                ]);
            }
            break;
            
        case 'tables':
            // Create new table
            $input = json_decode(file_get_contents('php://input'), true);
            $tableName = $input['table_name'] ?? '';
            $columns = $input['columns'] ?? [];
            
            if (empty($tableName) || empty($columns)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Table name and columns required']);
                return;
            }
            
            // Build CREATE TABLE SQL
            $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (";
            $columnDefs = [];
            
            foreach ($columns as $column) {
                $name = $column['name'];
                $type = $column['type'];
                $null = isset($column['null']) && $column['null'] ? 'NULL' : 'NOT NULL';
                $default = isset($column['default']) ? "DEFAULT " . $conn->real_escape_string($column['default']) : '';
                $auto_increment = isset($column['auto_increment']) && $column['auto_increment'] ? 'AUTO_INCREMENT' : '';
                
                $columnDefs[] = "`$name` $type $null $default $auto_increment";
            }
            
            $sql .= implode(', ', $columnDefs);
            
            // Add primary key if specified
            if (isset($input['primary_key'])) {
                $sql .= ", PRIMARY KEY (`" . $input['primary_key'] . "`)";
            }
            
            $sql .= ")";
            
            $result = $conn->query($sql);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'table' => $tableName,
                        'sql' => $sql
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $conn->error,
                    'sql' => $sql
                ]);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
    }
}

// Handle PUT requests
function handlePutRequest($conn, $path) {
    // Similar to POST but for updates
    http_response_code(501);
    echo json_encode(['success' => false, 'error' => 'PUT requests not implemented yet']);
}

// Handle DELETE requests
function handleDeleteRequest($conn, $path) {
    // Similar to POST but for deletions
    http_response_code(501);
    echo json_encode(['success' => false, 'error' => 'DELETE requests not implemented yet']);
}
?>

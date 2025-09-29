<?php
/**
 * Lead Activities API Endpoint
 * 
 * Handles CRUD operations for lead activities
 */

// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration and authentication
require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Authenticate the request
    $auth = new Auth();
    $user = $auth->authenticate();
    
    if (!$user) {
        throw new Exception('Authentication failed', 401);
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
    
    // Get request method and ID
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $leadId = isset($_GET['lead_id']) ? (int)$_GET['lead_id'] : null;
    
    // Process the request
    switch ($method) {
        case 'GET':
            // Get activity by ID or list activities for a lead
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM lead_activities WHERE id = ? AND (user_id = ? OR ? IN (SELECT id FROM users WHERE role = 'admin'))");
                $stmt->execute([$id, $user['id'], $user['id']]);
                $activity = $stmt->fetch();
                
                if ($activity) {
                    $response['success'] = true;
                    $response['data'] = $activity;
                } else {
                    throw new Exception('Activity not found or access denied', 404);
                }
            } elseif ($leadId) {
                // Get activities for a specific lead
                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
                $offset = ($page - 1) * $limit;
                
                // Check if user has access to this lead
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leads WHERE id = ? AND (assigned_to = ? OR ? IN (SELECT id FROM users WHERE role = 'admin'))");
                $stmt->execute([$leadId, $user['id'], $user['id']]);
                $result = $stmt->fetch();
                
                if ($result['count'] == 0) {
                    throw new Exception('Lead not found or access denied', 403);
                }
                
                // Get paginated activities
                $stmt = $conn->prepare("SELECT * FROM lead_activities WHERE lead_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
                $stmt->execute([$leadId, $limit, $offset]);
                $activities = $stmt->fetchAll();
                
                // Get total count for pagination
                $totalStmt = $conn->prepare("SELECT COUNT(*) as total FROM lead_activities WHERE lead_id = ?");
                $totalStmt->execute([$leadId]);
                $total = $totalStmt->fetch()['total'];
                
                $response['success'] = true;
                $response['data'] = [
                    'items' => $activities,
                    'pagination' => [
                        'total' => (int)$total,
                        'page' => $page,
                        'limit' => $limit,
                        'pages' => ceil($total / $limit)
                    ]
                ];
            } else {
                throw new Exception('Lead ID is required', 400);
            }
            break;
            
        case 'POST':
            // Create a new activity
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['lead_id']) || empty($data['activity_type']) || empty($data['title'])) {
                throw new Exception('Missing required fields', 400);
            }
            
            // Check if user has access to this lead
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leads WHERE id = ? AND (assigned_to = ? OR ? IN (SELECT id FROM users WHERE role = 'admin'))");
            $stmt->execute([$data['lead_id'], $user['id'], $user['id']]);
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                throw new Exception('Lead not found or access denied', 403);
            }
            
            // Insert the new activity
            $stmt = $conn->prepare("
                INSERT INTO lead_activities 
                (lead_id, user_id, activity_type, title, description, due_date, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW())
            ");
            
            $stmt->execute([
                $data['lead_id'],
                $user['id'],
                $data['activity_type'],
                $data['title'],
                $data['description'] ?? null,
                !empty($data['due_date']) ? date('Y-m-d H:i:s', strtotime($data['due_date'])) : null
            ]);
            
            $activityId = $conn->lastInsertId();
            
            // Log the activity creation
            $logStmt = $conn->prepare("
                INSERT INTO lead_events 
                (lead_id, user_id, event_type, event_data, created_at)
                VALUES (?, ?, 'activity_created', ?, NOW())
            ");
            
            $logStmt->execute([
                $data['lead_id'],
                $user['id'],
                json_encode([
                    'activity_id' => $activityId,
                    'activity_type' => $data['activity_type'],
                    'title' => $data['title']
                ])
            ]);
            
            // Update lead's last_contact field
            $updateLeadStmt = $conn->prepare("
                UPDATE leads SET last_contact = NOW(), updated_at = NOW() WHERE id = ?
            ");
            $updateLeadStmt->execute([$data['lead_id']]);
            
            $response['success'] = true;
            $response['message'] = 'Activity created successfully';
            $response['data'] = ['id' => $activityId];
            http_response_code(201);
            break;
            
        case 'PUT':
            // Update an existing activity
            if (!$id) {
                throw new Exception('Activity ID is required', 400);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Check if activity exists and user has access
            $stmt = $conn->prepare("
                SELECT la.* FROM lead_activities la
                JOIN leads l ON la.lead_id = l.id
                WHERE la.id = ? AND (la.user_id = ? OR ? IN (SELECT id FROM users WHERE role = 'admin'))
            ");
            $stmt->execute([$id, $user['id'], $user['id']]);
            $activity = $stmt->fetch();
            
            if (!$activity) {
                throw new Exception('Activity not found or access denied', 404);
            }
            
            // Build the update query
            $updates = [];
            $params = [];
            
            $allowedFields = ['activity_type', 'title', 'description', 'due_date', 'status'];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updates)) {
                throw new Exception('No valid fields to update', 400);
            }
            
            $updates[] = 'updated_at = NOW()';
            $params[] = $id; // For the WHERE clause
            
            $sql = "UPDATE lead_activities SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            // Log the update
            $logStmt = $conn->prepare("
                INSERT INTO lead_events 
                (lead_id, user_id, event_type, event_data, created_at)
                VALUES (?, ?, 'activity_updated', ?, NOW())
            ");
            
            $logStmt->execute([
                $activity['lead_id'],
                $user['id'],
                json_encode([
                    'activity_id' => $id,
                    'changes' => $data
                ])
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Activity updated successfully';
            break;
            
        case 'DELETE':
            // Delete an activity
            if (!$id) {
                throw new Exception('Activity ID is required', 400);
            }
            
            // Check if activity exists and user has access
            $stmt = $conn->prepare("
                SELECT la.* FROM lead_activities la
                JOIN leads l ON la.lead_id = l.id
                WHERE la.id = ? AND (la.user_id = ? OR ? IN (SELECT id FROM users WHERE role = 'admin'))
            ");
            $stmt->execute([$id, $user['id'], $user['id']]);
            $activity = $stmt->fetch();
            
            if (!$activity) {
                throw new Exception('Activity not found or access denied', 404);
            }
            
            // Log the deletion before actually deleting
            $logStmt = $conn->prepare("
                INSERT INTO lead_events 
                (lead_id, user_id, event_type, event_data, created_at)
                VALUES (?, ?, 'activity_deleted', ?, NOW())
            ");
            
            $logStmt->execute([
                $activity['lead_id'],
                $user['id'],
                json_encode([
                    'activity_id' => $id,
                    'activity_type' => $activity['activity_type'],
                    'title' => $activity['title']
                ])
            ]);
            
            // Delete the activity
            $deleteStmt = $conn->prepare("DELETE FROM lead_activities WHERE id = ?");
            $deleteStmt->execute([$id]);
            
            $response['success'] = true;
            $response['message'] = 'Activity deleted successfully';
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
    
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

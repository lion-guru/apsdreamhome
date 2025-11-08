<?php
/**
 * Leads API Endpoint
 * 
 * Handles CRUD operations for leads
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

// Include required files
require_once __DIR__ . '/../../../includes/db_config.php';
require_once __DIR__ . '/../../../includes/ApiAuth.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'pagination' => null
];

try {
    // Authenticate the request
    $auth = new ApiAuth();
    $user = $auth->authenticate();
    
    if (!$user) {
        throw new Exception('Authentication required', 401);
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
    
    // Process the request
    switch ($method) {
        case 'GET':
            // Get lead by ID or list leads with pagination and filters
            if ($id) {
                // Get single lead by ID
                $stmt = $this->getLeadQuery($conn, $user, $id);
                $stmt->execute([$id]);
                $lead = $stmt->fetch();
                
                if ($lead) {
                    // Get related data
                    $lead['activities'] = $this->getLeadActivities($conn, $id);
                    $lead['tasks'] = $this->getLeadTasks($conn, $id);
                    $lead['notes'] = $this->getLeadNotes($conn, $id);
                    $lead['documents'] = $this->getLeadDocuments($conn, $id);
                    
                    $response['success'] = true;
                    $response['data'] = $lead;
                } else {
                    throw new Exception('Lead not found', 404);
                }
            } else {
                // List leads with pagination and filters
                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 20;
                $offset = ($page - 1) * $limit;
                
                // Build WHERE clause based on filters
                $where = [];
                $params = [];
                $allowedFilters = ['status', 'source', 'assigned_to', 'priority'];
                
                foreach ($allowedFilters as $filter) {
                    if (isset($_GET[$filter])) {
                        $where[] = "l.$filter = ?";
                        $params[] = $_GET[$filter];
                    }
                }
                
                // Add search query
                if (isset($_GET['q']) && !empty($_GET['q'])) {
                    $search = "%{$_GET['q']}%";
                    $where[] = "(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ? OR l.company LIKE ?)";
                    $params = array_merge($params, [$search, $search, $search, $search]);
                }
                
                // Restrict to assigned leads for non-admin users
                if ($user['role'] !== 'admin') {
                    $where[] = "(l.assigned_to = ? OR l.created_by = ?)";
                    $params[] = $user['id'];
                    $params[] = $user['id'];
                }
                
                // Build the query
                $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
                
                // Get paginated leads
                $sql = "SELECT l.*, u.name as assigned_to_name 
                        FROM leads l 
                        LEFT JOIN users u ON l.assigned_to = u.id 
                        $whereClause 
                        ORDER BY l.updated_at DESC 
                        LIMIT ? OFFSET ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute(array_merge($params, [$limit, $offset]));
                $leads = $stmt->fetchAll();
                
                // Get total count for pagination
                $countSql = "SELECT COUNT(*) as total FROM leads l $whereClause";
                $countStmt = $conn->prepare($countSql);
                $countStmt->execute($params);
                $total = $countStmt->fetch()['total'];
                
                $response['success'] = true;
                $response['data'] = $leads;
                $response['pagination'] = [
                    'total' => (int)$total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ];
            }
            break;
            
        case 'POST':
            // Create a new lead
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $requiredFields = ['name', 'email', 'phone', 'source'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required", 400);
                }
            }
            
            // Prepare lead data
            $leadData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'company' => $data['company'] ?? null,
                'source' => $data['source'],
                'status' => $data['status'] ?? 'New',
                'priority' => $data['priority'] ?? 'Medium',
                'assigned_to' => $data['assigned_to'] ?? $user['id'],
                'created_by' => $user['id'],
                'notes' => $data['notes'] ?? null,
                'budget' => $data['budget'] ?? null,
                'tags' => !empty($data['tags']) ? implode(',', $data['tags']) : null
            ];
            
            // Start transaction
            $conn->beginTransaction();
            
            try {
                // Insert lead
                $fields = array_keys($leadData);
                $placeholders = array_map(fn($field) => ":$field", $fields);
                
                $sql = "INSERT INTO leads (" . implode(', ', $fields) . ", created_at, updated_at) 
                        VALUES (" . implode(', ', $placeholders) . ", NOW(), NOW())";
                
                $stmt = $conn->prepare($sql);
                
                foreach ($leadData as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                
                $stmt->execute();
                $leadId = $conn->lastInsertId();
                
                // Log the creation
                $this->logLeadEvent($conn, $leadId, $user['id'], 'lead_created', [
                    'lead_id' => $leadId,
                    'created_by' => $user['id']
                ]);
                
                // Add initial note if provided
                if (!empty($data['initial_note'])) {
                    $noteStmt = $conn->prepare("
                        INSERT INTO lead_notes (lead_id, user_id, note, created_at, updated_at)
                        VALUES (?, ?, ?, NOW(), NOW())
                    ");
                    $noteStmt->execute([$leadId, $user['id'], $data['initial_note']]);
                }
                
                // Commit transaction
                $conn->commit();
                
                // Get the created lead
                $stmt = $this->getLeadQuery($conn, $user, $leadId);
                $stmt->execute([$leadId]);
                $lead = $stmt->fetch();
                
                $response['success'] = true;
                $response['message'] = 'Lead created successfully';
                $response['data'] = $lead;
                http_response_code(201);
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        case 'PUT':
            // Update an existing lead
            if (!$id) {
                throw new Exception('Lead ID is required', 400);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Check if lead exists and user has permission
            $lead = $this->getLeadById($conn, $id, $user);
            if (!$lead) {
                throw new Exception('Lead not found or access denied', 404);
            }
            
            // Prepare update data
            $allowedFields = [
                'name', 'email', 'phone', 'company', 'source', 'status', 
                'priority', 'assigned_to', 'notes', 'budget', 'tags', 'next_followup'
            ];
            
            $updates = [];
            $updateData = ['id' => $id];
            $changes = [];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data) && $data[$field] != $lead[$field]) {
                    $updates[] = "$field = :$field";
                    $updateData[$field] = $data[$field];
                    $changes[$field] = [
                        'old' => $lead[$field],
                        'new' => $data[$field]
                    ];
                }
            }
            
            if (empty($updates)) {
                throw new Exception('No changes to update', 400);
            }
            
            // Add updated_at to updates
            $updates[] = 'updated_at = NOW()';
            
            // Start transaction
            $conn->beginTransaction();
            
            try {
                // Update lead
                $sql = "UPDATE leads SET " . implode(', ', $updates) . " WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute($updateData);
                
                // Log the update
                if (!empty($changes)) {
                    $this->logLeadEvent($conn, $id, $user['id'], 'lead_updated', [
                        'changes' => $changes,
                        'updated_by' => $user['id']
                    ]);
                }
                
                // Commit transaction
                $conn->commit();
                
                // Get the updated lead
                $stmt = $this->getLeadQuery($conn, $user, $id);
                $stmt->execute([$id]);
                $updatedLead = $stmt->fetch();
                
                $response['success'] = true;
                $response['message'] = 'Lead updated successfully';
                $response['data'] = $updatedLead;
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Delete a lead (soft delete)
            if (!$id) {
                throw new Exception('Lead ID is required', 400);
            }
            
            // Check if lead exists and user has permission
            $lead = $this->getLeadById($conn, $id, $user);
            if (!$lead) {
                throw new Exception('Lead not found or access denied', 404);
            }
            
            // Only admins can delete leads
            if ($user['role'] !== 'admin') {
                throw new Exception('Insufficient permissions', 403);
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            try {
                // Log the deletion before soft deleting
                $this->logLeadEvent($conn, $id, $user['id'], 'lead_deleted', [
                    'deleted_by' => $user['id'],
                    'lead_data' => $lead
                ]);
                
                // Soft delete the lead
                $stmt = $conn->prepare("UPDATE leads SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'Lead deleted successfully';
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log('Database error in leads API: ' . $e->getMessage());
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 400;
    http_response_code($statusCode);
    $response['message'] = $e->getMessage();
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT);

/**
 * Get a lead by ID with proper permission checks
 */
function getLeadById($conn, $id, $user) {
    $stmt = $this->getLeadQuery($conn, $user, $id);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get the base query for fetching a lead with permission checks
 */
function getLeadQuery($conn, $user, $leadId = null) {
    $sql = "SELECT l.*, u.name as assigned_to_name, 
                   uc.name as created_by_name, u.email as assigned_to_email
            FROM leads l
            LEFT JOIN users u ON l.assigned_to = u.id
            LEFT JOIN users uc ON l.created_by = uc.id
            WHERE l.id = ?";
    
    // Add permission check for non-admin users
    if ($user['role'] !== 'admin') {
        $sql .= " AND (l.assigned_to = ? OR l.created_by = ?)";
    }
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    if ($user['role'] !== 'admin') {
        $stmt->bindValue(1, $leadId);
        $stmt->bindValue(2, $user['id']);
        $stmt->bindValue(3, $user['id']);
    } else {
        $stmt->bindValue(1, $leadId);
    }
    
    return $stmt;
}

/**
 * Get activities for a lead
 */
function getLeadActivities($conn, $leadId, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT la.*, u.name as user_name, u.email as user_email
        FROM lead_activities la
        JOIN users u ON la.user_id = u.id
        WHERE la.lead_id = ?
        ORDER BY la.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$leadId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get tasks for a lead
 */
function getLeadTasks($conn, $leadId, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT lt.*, u1.name as assigned_to_name, u2.name as assigned_by_name
        FROM lead_tasks lt
        LEFT JOIN users u1 ON lt.assigned_to = u1.id
        LEFT JOIN users u2 ON lt.assigned_by = u2.id
        WHERE lt.lead_id = ?
        ORDER BY lt.due_date ASC, lt.priority DESC
        LIMIT ?
    ");
    $stmt->execute([$leadId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get notes for a lead
 */
function getLeadNotes($conn, $leadId, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT ln.*, u.name as user_name, u.email as user_email
        FROM lead_notes ln
        JOIN users u ON ln.user_id = u.id
        WHERE ln.lead_id = ?
        ORDER BY ln.is_pinned DESC, ln.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$leadId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get documents for a lead
 */
function getLeadDocuments($conn, $leadId, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT ld.*, u.name as uploaded_by_name
        FROM lead_documents ld
        LEFT JOIN users u ON ld.user_id = u.id
        WHERE ld.lead_id = ?
        ORDER BY ld.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$leadId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Log an event for a lead
 */
function logLeadEvent($conn, $leadId, $userId, $eventType, $eventData) {
    $stmt = $conn->prepare("
        INSERT INTO lead_events 
        (lead_id, user_id, event_type, event_data, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    return $stmt->execute([
        $leadId,
        $userId,
        $eventType,
        json_encode($eventData)
    ]);
}

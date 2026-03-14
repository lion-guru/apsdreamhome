<?php

/**
 * APS Dream Home - Simple API Test
 * Direct API endpoint for testing MobileApiController
 */

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$request_uri = strtok($request_uri, '?');

// Remove /api_test.php from URI
if (strpos($request_uri, '/api_test.php') === 0) {
    $request_uri = substr($request_uri, strlen('/api_test.php'));
}

// Route mapping
$routes = [
    'GET /api/v2/mobile/properties' => 'getProperties',
    'GET /api/v2/mobile/properties/' => 'getProperty',
    'POST /api/v2/mobile/sync' => 'sync',
    'GET /api/v2/mobile/leads' => 'getLeads',
    'POST /api/v2/mobile/leads' => 'submitLead',
    'GET /api/v2/mobile/commissions' => 'getCommissions',
    'GET /api/v2/mobile/user/profile' => 'getUserProfile',
];

$route_key = $method . ' ' . $request_uri;

if (array_key_exists($route_key, $routes)) {
    $function_name = $routes[$route_key];
    $function_name($pdo);
} else {
    // Try to match dynamic routes
    if (strpos($request_uri, '/api/v2/mobile/properties/') === 0 && $method === 'GET') {
        $id = substr($request_uri, strlen('/api/v2/mobile/properties/'));
        getProperty($pdo, $id);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'API endpoint not found',
            'route' => $route_key,
            'available_routes' => array_keys($routes)
        ]);
    }
}

// API Functions
function getProperties($pdo) {
    try {
        $page = (int)($_GET['page'] ?? 1);
        $limit = min((int)($_GET['limit'] ?? 10), 50);
        $offset = ($page - 1) * $limit;
        
        // V2 Sync Logic - Handle sync parameters
        $sync_mode = $_GET['sync_mode'] ?? 'normal';
        $last_sync = $_GET['last_sync'] ?? null;
        $user_id = $_GET['user_id'] ?? null;

        // Build filters
        $filters = [];
        $property_type = $_GET['property_type'] ?? null;
        if ($property_type !== null && $property_type !== '') {
            $filters['property_type'] = $property_type;
        }
        $city = $_GET['city'] ?? null;
        if ($city !== null && $city !== '') {
            $filters['city'] = $city;
        }
        $min_price = $_GET['min_price'] ?? null;
        if ($min_price !== null && $min_price !== '') {
            $filters['min_price'] = $min_price;
        }
        $max_price = $_GET['max_price'] ?? null;
        if ($max_price !== null && $max_price !== '') {
            $filters['max_price'] = $max_price;
        }
        $featured = $_GET['featured'] ?? null;
        if ($featured !== null && $featured === 'true') {
            $filters['featured'] = true;
        }

        // Get properties with V2 sync support
        if ($sync_mode === 'sync' && $last_sync) {
            // V2 Sync Mode - Return only updated properties since last sync
            $properties = getPropertiesUpdatedSince($pdo, $last_sync, $filters, $limit, $offset);
            $total_count = getUpdatedPropertiesCount($pdo, $last_sync, $filters);
        } else {
            // Legacy Mode - Normal property browsing
            $properties = getPropertiesWithFilters($pdo, $filters, $limit, $offset);
            $total_count = getPropertiesCount($pdo, $filters);
        }

        // Add V2 sync metadata
        $sync_metadata = [];
        if ($sync_mode === 'sync') {
            $sync_metadata = [
                'sync_mode' => 'sync',
                'last_sync' => $last_sync,
                'current_timestamp' => date('Y-m-d H:i:s'),
                'has_updates' => count($properties) > 0,
                'sync_queue_size' => getSyncQueueSize($pdo, $user_id)
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'properties' => $properties,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_pages' => ceil($total_count / $limit),
                    'total_count' => $total_count
                ],
                'filters' => $filters,
                'sync_metadata' => $sync_metadata
            ]
        ], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Properties API error: ' . $e->getMessage()
        ]);
    }
}

function getPropertiesWithFilters($pdo, $filters = [], $limit = 10, $offset = 0) {
    $sql = "
        SELECT
            p.*,
            pt.type as property_type_name
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        WHERE p.status = 'active'
    ";

    $params = [];

    // Add filters
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($key === 'property_type') {
                $sql .= " AND p.type = :property_type";
                $params['property_type'] = $value;
            } elseif ($key === 'city') {
                $sql .= " AND p.city = :city";
                $params['city'] = $value;
            } elseif ($key === 'min_price') {
                $sql .= " AND p.price >= :min_price";
                $params['min_price'] = $value;
            } elseif ($key === 'max_price') {
                $sql .= " AND p.price <= :max_price";
                $params['max_price'] = $value;
            } elseif ($key === 'featured') {
                $sql .= " AND p.featured = :featured";
                $params['featured'] = 1;
            }
        }
    }

    $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPropertiesCount($pdo, $filters = []) {
    $sql = "SELECT COUNT(*) as count FROM properties WHERE status = 'active'";
    $params = [];

    // Add filters
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($key === 'property_type') {
                $sql .= " AND type = :property_type";
                $params['property_type'] = $value;
            } elseif ($key === 'city') {
                $sql .= " AND city = :city";
                $params['city'] = $value;
            } elseif ($key === 'min_price') {
                $sql .= " AND price >= :min_price";
                $params['min_price'] = $value;
            } elseif ($key === 'max_price') {
                $sql .= " AND price <= :max_price";
                $params['max_price'] = $value;
            } elseif ($key === 'featured') {
                $sql .= " AND featured = :featured";
                $params['featured'] = 1;
            }
        }
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();

    return (int)$stmt->fetchColumn();
}

function getPropertiesUpdatedSince($pdo, $last_sync, $filters = [], $limit = 10, $offset = 0) {
    $sql = "
        SELECT
            p.*,
            pt.type as property_type_name
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        WHERE p.status = 'active' AND p.sync_updated_at > :last_sync
    ";

    $params = ['last_sync' => $last_sync];

    // Add filters
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($key === 'property_type') {
                $sql .= " AND p.type = :property_type";
                $params['property_type'] = $value;
            } elseif ($key === 'city') {
                $sql .= " AND p.city = :city";
                $params['city'] = $value;
            } elseif ($key === 'min_price') {
                $sql .= " AND p.price >= :min_price";
                $params['min_price'] = $value;
            } elseif ($key === 'max_price') {
                $sql .= " AND p.price <= :max_price";
                $params['max_price'] = $value;
            } elseif ($key === 'featured') {
                $sql .= " AND p.featured = :featured";
                $params['featured'] = 1;
            }
        }
    }

    $sql .= " ORDER BY p.sync_updated_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUpdatedPropertiesCount($pdo, $last_sync, $filters = []) {
    $sql = "SELECT COUNT(*) as count FROM properties WHERE status = 'active' AND sync_updated_at > :last_sync";
    $params = ['last_sync' => $last_sync];

    // Add filters
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if ($key === 'property_type') {
                $sql .= " AND type = :property_type";
                $params['property_type'] = $value;
            } elseif ($key === 'city') {
                $sql .= " AND city = :city";
                $params['city'] = $value;
            } elseif ($key === 'min_price') {
                $sql .= " AND price >= :min_price";
                $params['min_price'] = $value;
            } elseif ($key === 'max_price') {
                $sql .= " AND price <= :max_price";
                $params['max_price'] = $value;
            } elseif ($key === 'featured') {
                $sql .= " AND featured = :featured";
                $params['featured'] = 1;
            }
        }
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();

    return (int)$stmt->fetchColumn();
}

function getSyncQueueSize($pdo, $user_id) {
    if (!$user_id) {
        return 0;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM sync_queue 
        WHERE user_id = :user_id AND status = 'pending'
    ");
    $stmt->execute(['user_id' => $user_id]);

    return (int)$stmt->fetchColumn();
}

function getProperty($pdo, $id) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                p.*,
                pt.type as property_type_name
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            WHERE p.id = :id AND p.status = 'active'
        ");
        $stmt->execute(['id' => $id]);

        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($property) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'property' => $property
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Property not found'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Property API error: ' . $e->getMessage()
        ]);
    }
}

function sync($pdo) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sync_type = $data['sync_type'] ?? 'download';
        $user_id = $data['user_id'] ?? null;
        $last_sync = $data['last_sync'] ?? null;

        if (!$user_id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'User ID required'
            ]);
            return;
        }

        $result = [];
        
        switch ($sync_type) {
            case 'download':
                $result = downloadSyncData($pdo, $user_id, $last_sync);
                break;
                
            case 'upload':
                $result = uploadSyncData($pdo, $data['changes'] ?? []);
                break;
                
            case 'status':
                $result = getSyncStatus($pdo, $user_id);
                break;
                
            default:
                throw new Exception('Invalid sync type');
        }

        echo json_encode([
            'success' => true,
            'data' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Sync API error: ' . $e->getMessage()
        ]);
    }
}

function downloadSyncData($pdo, $user_id, $last_sync = null) {
    $data = [
        'properties' => [],
        'leads' => [],
        'commissions' => [],
        'user_profile' => []
    ];

    // Get updated properties
    if ($last_sync) {
        $data['properties'] = getPropertiesUpdatedSince($pdo, $last_sync);
    } else {
        $data['properties'] = getPropertiesWithFilters($pdo, [], 100, 0);
    }

    // Get user's leads
    $stmt = $pdo->prepare("
        SELECT * FROM leads 
        WHERE source_user_id = :user_id 
        " . ($last_sync ? "AND updated_at > :last_sync" : "") . "
        ORDER BY updated_at DESC
    ");
    $params = ['user_id' => $user_id];
    if ($last_sync) {
        $params['last_sync'] = $last_sync;
    }
    $stmt->execute($params);
    $data['leads'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user's commissions
    $stmt = $pdo->prepare("
        SELECT * FROM commissions 
        WHERE user_id = :user_id 
        " . ($last_sync ? "AND updated_at > :last_sync" : "") . "
        ORDER BY updated_at DESC
    ");
    $params = ['user_id' => $user_id];
    if ($last_sync) {
        $params['last_sync'] = $last_sync;
    }
    $stmt->execute($params);
    $data['commissions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}

function getSyncStatus($pdo, $user_id) {
    return [
        'pending_changes' => getSyncQueueSize($pdo, $user_id),
        'last_server_sync' => date('Y-m-d H:i:s'),
        'sync_enabled' => true
    ];
}

function uploadSyncData($pdo, $changes) {
    $uploaded_count = 0;
    $errors = [];

    foreach ($changes as $change) {
        try {
            $entity_type = $change['entity_type'];
            $entity_id = $change['entity_id'];
            $action = $change['action'];
            $data = $change['data'];

            switch ($entity_type) {
                case 'leads':
                    processLeadChange($pdo, $entity_id, $action, $data);
                    $uploaded_count++;
                    break;
                    
                case 'properties':
                    processPropertyChange($pdo, $entity_id, $action, $data);
                    $uploaded_count++;
                    break;
                    
                default:
                    $errors[] = "Unknown entity type: $entity_type";
            }
        } catch (Exception $e) {
            $errors[] = "Error processing {$change['entity_type']} {$change['entity_id']}: " . $e->getMessage();
        }
    }

    return [
        'uploaded_count' => $uploaded_count,
        'errors' => $errors
    ];
}

function processLeadChange($pdo, $lead_id, $action, $data) {
    switch ($action) {
        case 'create':
            $stmt = $pdo->prepare("
                INSERT INTO leads (name, email, phone, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $data['name'] ?? '',
                $data['email'] ?? '',
                $data['phone'] ?? '',
                $data['status'] ?? 'new'
            ]);
            break;
            
        case 'update':
            $stmt = $pdo->prepare("
                UPDATE leads 
                SET name = ?, email = ?, phone = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'] ?? '',
                $data['email'] ?? '',
                $data['phone'] ?? '',
                $data['status'] ?? 'new',
                $lead_id
            ]);
            break;
    }
}

function processPropertyChange($pdo, $property_id, $action, $data) {
    if ($action === 'update') {
        $stmt = $pdo->prepare("
            UPDATE properties 
            SET status = ?, sync_updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $data['status'] ?? 'active',
            $property_id
        ]);
    }
}

function getLeads($pdo) {
    try {
        $user_id = $_GET['user_id'] ?? null;
        
        $sql = "SELECT id, name, email, phone, property_interest, budget, status, assigned_to as source_user_id, notes, created_at, updated_at FROM leads";
        $params = [];
        
        if ($user_id) {
            $sql .= " WHERE assigned_to = :user_id";
            $params['user_id'] = $user_id;
        }
        
        $sql .= " ORDER BY updated_at DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'leads' => $leads
            ]
        ], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Leads API error: ' . $e->getMessage()
        ]);
    }
}

function submitLead($pdo) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare("
            INSERT INTO leads (name, email, phone, status, created_at, updated_at)
            VALUES (?, ?, ?, 'new', NOW(), NOW())
        ");
        $stmt->execute([
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? ''
        ]);

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $pdo->lastInsertId(),
                'message' => 'Lead created successfully'
            ]
        ], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Lead creation error: ' . $e->getMessage()
        ]);
    }
}

function getCommissions($pdo) {
    try {
        $user_id = $_GET['user_id'] ?? null;
        
        $sql = "SELECT * FROM commissions";
        $params = [];
        
        if ($user_id) {
            $sql .= " WHERE user_id = :user_id";
            $params['user_id'] = $user_id;
        }
        
        $sql .= " ORDER BY updated_at DESC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        $commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'commissions' => $commissions
            ]
        ], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Commissions API error: ' . $e->getMessage()
        ]);
    }
}

function getUserProfile($pdo) {
    try {
        $user_id = $_GET['user_id'] ?? 1;
        
        $stmt = $pdo->prepare("
            SELECT id, name, email, phone, role, mlm_rank, commission_rate, mlm_target, created_at, updated_at
            FROM users 
            WHERE id = :user_id AND status = 'active'
        ");
        $stmt->execute(['user_id' => $user_id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'User profile API error: ' . $e->getMessage()
        ]);
    }
}

?>

<?php

/**
 * Mobile API Controller
 * Provides REST API endpoints for mobile applications
 * Standalone controller (doesn't extend BaseController for API use)
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use App\Services\WebSocketBroadcaster;
use App\Traits\TenantAwareTrait;
use UploadValidator;

use EmailNotification;
use Exception;
use PDO;

class MobileApiController extends BaseController
{
    use TenantAwareTrait;

    protected $apiAuthService;
    protected $syncService;
    protected $jwtService;

    public function __construct()
    {
        parent::__construct();
        $this->apiAuthService = new \App\Services\Auth\ApiAuthService();
        $this->syncService = new \App\Services\SyncService();
        $this->jwtService = new \App\Services\Auth\JWTAuthService();

        if (!$this->db) {
            error_log('MobileApiController: Failed to initialize database connection');
        }
    }

    /**
     * Mobile API uses JWT (stateless) — no session-based CSRF.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * API Login for Mobile
     */
    public function login()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $result = $this->apiAuthService->login($email, $password);
        
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(401);
            echo json_encode($result);
        }
    }

    /**
     * API Register for Mobile
     */
    public function register()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'customer';

        if ($name === '' || $email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name, email and password are required']);
            return;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }

        if (!$this->tenantEnforce('add_user')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $_SESSION['error'] ?? 'User limit reached for your plan']);
            return;
        }

        try {
            $pdo = $this->db->getConnection();

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email already registered']);
                return;
            }

            // Insert new user
            $hash = Security::hashPassword($password);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())
            ");
            $stmt->execute([$name, $email, $phone, $hash, $role]);
            $userId = $pdo->lastInsertId();

            $this->tenantTrackUsage('users');

            // Auto-login after registration
            $result = $this->apiAuthService->login($email, $password);

            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Registration succeeded but auto-login failed']);
            }
        } catch (\Throwable $e) {
            error_log('MobileApiController::register failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * API Logout for Mobile
     */
    public function logout()
    {
        $this->setCorsHeaders();
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $header);

        if ($token) {
            $this->apiAuthService->logout($token);
        }

        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    }

    /**
     * Universal API endpoint for mobile app - Get updates since last sync
     * Returns a consolidated package of changed properties, leads, and MLM stats.
     */
    public function getUpdates()
    {
        $this->setCorsHeaders();
        
        $userId = $GLOBALS['api_user_id'] ?? null;
        $lastSync = \App\Core\Security::sanitize($_GET['last_sync'] ?? null) ?? '2000-01-01 00:00:00';

        try {
            $syncPackage = $this->syncService->getSyncPackage($lastSync, $userId);
            echo json_encode([
                'success' => true,
                'data' => $syncPackage
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Sync Updates API error');
        }
    }

    /**
     * API endpoint for mobile app - Get properties with pagination and filters
     * MERGED: Supports both legacy property browsing and new V2 sync logic
     */
    public function properties()
    {
        $this->setCorsHeaders();

        try {
            $page = (int)(\App\Core\Security::sanitize($_GET['page'] ?? 1) ?? 1);
            $limit = min((int)(\App\Core\Security::sanitize($_GET['limit'] ?? 10) ?? 10), 50); // Max 50 per page
            $offset = ($page - 1) * $limit;

            // V2 Sync Logic - Handle sync parameters
            $sync_mode = \App\Core\Security::sanitize($_GET['sync_mode'] ?? 'normal') ?? 'normal';
            $last_sync = \App\Core\Security::sanitize($_GET['last_sync'] ?? null) ?? null;
            $user_id = $GLOBALS['api_user_id'] ?? null;

            // Build filters
            $filters = [];
            $property_type = \App\Core\Security::sanitize($_GET['property_type'] ?? null);
            if ($property_type !== null && $property_type !== '') {
                $filters['property_type'] = $property_type;
            }
            $city = \App\Core\Security::sanitize($_GET['city'] ?? null);
            if ($city !== null && $city !== '') {
                $filters['city'] = $city;
            }
            $min_price = \App\Core\Security::sanitize($_GET['min_price'] ?? null);
            if ($min_price !== null && $min_price !== '') {
                $filters['min_price'] = $min_price;
            }
            $max_price = \App\Core\Security::sanitize($_GET['max_price'] ?? null);
            if ($max_price !== null && $max_price !== '') {
                $filters['max_price'] = $max_price;
            }
            $featured = \App\Core\Security::sanitize($_GET['featured'] ?? null);
            if ($featured !== null && $featured === 'true') {
                $filters['featured'] = true;
            }

            // Get properties using SyncService if in sync mode
            if ($sync_mode === 'sync' && $last_sync) {
                // Return only updated properties since last sync
                $properties = $this->syncService->getDeltaUpdates('properties', $last_sync, ['limit' => $limit, 'offset' => $offset]);
                
                // For simplified count, we use the specific method if it still exists or base logic
                $total_count = $this->getUpdatedPropertiesCount($last_sync, $filters);
            } else {
                // Legacy Mode - Normal property browsing
                $properties = $this->getPropertiesWithFilters($filters, $limit, $offset);
                $total_count = $this->getPropertiesCount($filters);
            }

            // Add V2 sync metadata
            $sync_metadata = [];
            if ($sync_mode === 'sync') {
                $sync_metadata = [
                    'sync_mode' => 'sync',
                    'last_sync' => $last_sync,
                    'current_timestamp' => date('Y-m-d H:i:s'),
                    'has_updates' => count($properties) > 0,
                    'sync_queue_size' => $this->getSyncQueueSize($user_id)
                ];
            }

            $response = [
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
            ];

            echo json_encode($response);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Properties API error');
        }
    }

    /**
     * Set CORS headers for API endpoints
     */
    private function setCorsHeaders()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24 hours

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Handle API errors consistently
     */
    private function handleApiError($exception, $context = 'API Error')
    {
        error_log($context . ': ' . $exception->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal server error',
            'message' => $exception->getMessage(),
            'context' => $context
        ]);
    }

    /**
     * API endpoint for mobile app - Get single property details
     */
    public function property($id)
    {
        $this->setCorsHeaders();

        try {
            $property = $this->getPropertyById($id);

            if (!$property) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Property not found'
                ]);
                return;
            }

            // Get property images
            $property['images'] = $this->getPropertyImages($id);

            // Get property features
            $property['features'] = $this->getPropertyFeatures($id);

            $response = [
                'success' => true,
                'data' => $property
            ];

            echo json_encode($response);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Single Property API error');
        }
    }

    /**
     * API endpoint for mobile app - Submit property inquiry
     */
    public function submitInquiry()
    {
        $this->setCorsHeaders();

        try {
            // Get POST data
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                $input = $_POST;
            }

            // Validate required fields
            $required_fields = ['property_id', 'name', 'email', 'phone', 'message'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Missing required field: ' . $field
                    ]);
                    return;
                }
            }

            // Create inquiry
            $inquiry_id = $this->createInquiry($input);

            if ($inquiry_id) {
                // Send email notification
                $emailNotification = new \EmailNotification();
                $emailNotification->sendInquiryNotification($inquiry_id);

                echo json_encode([
                    'success' => true,
                    'message' => 'Inquiry submitted successfully',
                    'inquiry_id' => $inquiry_id
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to submit inquiry'
                ]);
            }
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Inquiry Submission API error');
        }
    }

    /**
     * API endpoint for mobile app - Toggle property favorite
     */
    public function toggleFavorite()
    {
        $this->setCorsHeaders();

        try {
            // Get POST data
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                $input = $_POST;
            }

            $property_id = (int)($input['property_id'] ?? 0);
            $user_id = (int)($input['user_id'] ?? 0);

            if (!$property_id || !$user_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Property ID and User ID are required'
                ]);
                return;
            }

            // Check if property exists
            if (!$this->propertyExists($property_id)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Property not found'
                ]);
                return;
            }

            // Toggle favorite
            if ($this->isFavorited($user_id, $property_id)) {
                $this->removeFavoriteInternal($user_id, $property_id);
                $is_favorited = false;
                $message = 'Removed from favorites';
            } else {
                $this->addFavoriteInternal($user_id, $property_id);
                $is_favorited = true;
                $message = 'Added to favorites';
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'is_favorited' => $is_favorited
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Toggle Favorite API error');
        }
    }

    /**
     * API endpoint for mobile app - Get user's favorite properties
     */
    public function userFavorites()
    {
        $this->setCorsHeaders();

        try {
            $user_id = (int)($GLOBALS['api_user_id'] ?? 0);

            if (!$user_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'User ID is required'
                ]);
                return;
            }

            $favorites = $this->getUserFavorites($user_id);

            echo json_encode([
                'success' => true,
                'data' => $favorites
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'User Favorites API error');
        }
    }

    /**
     * API endpoint for mobile app - Get property types for filter dropdown
     */
    public function propertyTypes()
    {
        $this->setCorsHeaders();

        try {
            $property_types = $this->getPropertyTypes();

            echo json_encode([
                'success' => true,
                'data' => $property_types
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Property Types API error');
        }
    }

    /**
     * API endpoint for mobile app - Get cities for filter dropdown
     */
    public function cities()
    {
        $this->setCorsHeaders();

        try {
            $cities = $this->getAvailableCities();

            echo json_encode([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Cities API error');
        }
    }

    /**
     * Get properties with filters for mobile API
     */
    public function getPropertiesWithFilters($filters, $limit, $offset)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "
                SELECT
                    p.id,
                    p.title,
                    p.price,
                    p.city,
                    p.state,
                    p.bedrooms,
                    p.bathrooms,
                    p.area_sqft,
                    p.featured,
                    p.created_at,
                    pt.type as property_type,
                    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status IN ('active', '')
            ";

            $params = [];

            // Apply filters
            if (isset($filters['property_type'])) {
                $sql .= " AND p.property_type_id = :propertyType";
                $params['propertyType'] = $filters['property_type'];
            }

            if (isset($filters['city'])) {
                $sql .= " AND p.city LIKE :city";
                $params['city'] = '%' . $filters['city'] . '%';
            }

            if (isset($filters['min_price'])) {
                $sql .= " AND p.price >= :minPrice";
                $params['minPrice'] = $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $sql .= " AND p.price <= :maxPrice";
                $params['maxPrice'] = $filters['max_price'];
            }

            if (isset($filters['featured']) && $filters['featured']) {
                $sql .= " AND p.featured = 1";
            }

            $sql .= " ORDER BY p.featured DESC, p.created_at DESC LIMIT :limit OFFSET :offset";
            $params['limit'] = (int)$limit;
            $params['offset'] = (int)$offset;

            $stmt = $this->db->prepare($sql);

            // Bind limit and offset as integers specifically to avoid issues with some PDO drivers
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            // Bind other parameters
            foreach ($params as $key => $value) {
                if ($key !== 'limit' && $key !== 'offset') {
                    $stmt->bindValue(':' . $key, $value);
                }
            }

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get properties with filters error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property by ID
     */
    private function getPropertyById($id)
    {
        try {
            if (!$this->db) {
                return null;
            }

            $sql = "
                SELECT
                    p.*,
                    pt.type as property_type_name,
                    NULL as property_type_icon
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.id = :id AND p.status IN ('active', '')
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get property by ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get property images
     */
    private function getPropertyImages($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $stmt = $this->db->prepare("SELECT * FROM property_images WHERE property_id = :propertyId ORDER BY is_primary DESC, sort_order ASC");
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get property images error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property features
     */
    private function getPropertyFeatures($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $stmt = $this->db->prepare("SELECT * FROM property_features WHERE property_id = :propertyId ORDER BY feature_category, feature_name");
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get property features error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * V2 Sync Methods - Smart Sync Implementation
     */
    
    /**
     * Get properties updated since last sync timestamp
     */
    private function getPropertiesUpdatedSince($last_sync, $filters = [], $limit = 10, $offset = 0)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "
                SELECT
                    p.*,
                    pt.type as property_type_name,
                    NULL as property_type_icon
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.updated_at > :last_sync AND p.status IN ('active', '')
            ";

            $params = ['last_sync' => $last_sync];

            // Add filters
            if (!empty($filters)) {
                foreach ($filters as $key => $value) {
                    if ($key === 'property_type') {
                        $sql .= " AND p.property_type = :property_type";
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

            $sql .= " ORDER BY p.updated_at DESC LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;

            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get properties updated since error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get count of properties updated since last sync
     */
    private function getUpdatedPropertiesCount($last_sync, $filters = [])
    {
        try {
            if (!$this->db) {
                return 0;
            }

            $sql = "SELECT COUNT(*) as count FROM properties WHERE updated_at > :last_sync AND status IN ('active', '')";
            $params = ['last_sync' => $last_sync];

            // Add filters
            if (!empty($filters)) {
                foreach ($filters as $key => $value) {
                    if ($key === 'property_type') {
                        $sql .= " AND property_type = :property_type";
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

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('Get updated properties count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get sync queue size for user
     */
    private function getSyncQueueSize($user_id)
    {
        try {
            if (!$this->db || !$user_id) {
                return 0;
            }

            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM sync_queue 
                WHERE user_id = :user_id AND status = 'pending'
            ");
            $stmt->execute(['user_id' => $user_id]);

            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('Get sync queue size error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * V2 Sync endpoint - Handle sync queue operations
     */
    public function sync()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
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
                    // Download latest data using SyncService
                    $result = $this->syncService->getSyncPackage($last_sync, $user_id);
                    break;
                    
                case 'upload':
                    // Upload offline changes
                    $result = $this->uploadSyncData($data['changes'] ?? []);
                    break;
                    
                case 'status':
                    // Get sync status
                    $result = $this->getSyncStatus($user_id);
                    break;
                    
                default:
                    throw new Exception('Invalid sync type');
            }

            echo json_encode([
                'success' => true,
                'data' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Sync API error');
        }
    }

    /**
     * Download sync data for user
     */
    private function downloadSyncData($user_id, $last_sync = null)
    {
        $data = [
            'properties' => [],
            'leads' => [],
            'commissions' => [],
            'user_profile' => []
        ];

        // Get updated properties
        if ($last_sync) {
            $data['properties'] = $this->getPropertiesUpdatedSince($last_sync);
        } else {
            $data['properties'] = $this->getPropertiesWithFilters([], 100, 0); // Initial sync
        }

        // Get user's leads
        $stmt = $this->db->prepare("
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

        // Get user's commissions (using real data)
        $stmt = $this->db->prepare("
            SELECT * FROM commissions 
            WHERE user_id = :user_id 
            " . ($last_sync ? "AND created_at > :last_sync" : "") . "
            ORDER BY created_at DESC
        ");
        $params = ['user_id' => $user_id];
        if ($last_sync) {
            $params['last_sync'] = $last_sync;
        }
        $stmt->execute($params);
        $data['commissions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add rank info
        $perfCalculator = new \App\Services\PerformanceRankCalculator();
        $data['rank_info'] = $perfCalculator->calculateRank($user_id);

        return $data;
    }

    /**
     * Upload sync data from offline changes
     */
    private function uploadSyncData($changes)
    {
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
                        $this->processLeadChange($entity_id, $action, $data);
                        $uploaded_count++;
                        break;
                        
                    case 'properties':
                        $this->processPropertyChange($entity_id, $action, $data);
                        $uploaded_count++;
                        break;
                        
                    default:
                        $errors[] = "Unknown entity type: $entity_type";
                }
            } catch (\Exception $e) {
                error_log("[MobileApiController] uploadSyncData() exception: " . $e->getMessage());
                $errors[] = "Error processing {$change['entity_type']} {$change['entity_id']}: " . $e->getMessage();
            }
        }

        return [
            'uploaded_count' => $uploaded_count,
            'errors' => $errors
        ];
    }

    /**
     * Process lead change from sync
     */
    private function processLeadChange($lead_id, $action, $data)
    {
        switch ($action) {
            case 'create':
                $stmt = $this->db->prepare("
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
                $stmt = $this->db->prepare("
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

    /**
     * Process property change from sync
     */
    private function processPropertyChange($property_id, $action, $data)
    {
        if ($action === 'update') {
            $stmt = $this->db->prepare("
                UPDATE properties 
                SET status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['status'] ?? 'available',
                $property_id
            ]);
        }
    }

    /**
     * Get sync status for user
     */
    private function getSyncStatus($user_id)
    {
        return [
            'pending_changes' => $this->getSyncQueueSize($user_id),
            'last_server_sync' => date('Y-m-d H:i:s'),
            'sync_enabled' => true
        ];
    }

    /**
     * Create inquiry
     */
    private function createInquiry($data)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "
                INSERT INTO property_inquiries (
                    property_id, guest_name, guest_email, guest_phone,
                    subject, message, inquiry_type, status, priority, created_at
                ) VALUES (:propertyId, :name, :email, :phone, :subject, :message, :inquiryType, :status, :priority, NOW())
            ";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'propertyId' => $data['property_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'subject' => $data['subject'] ?? 'Property Inquiry',
                'message' => $data['message'],
                'inquiryType' => $data['inquiry_type'] ?? 'general',
                'status' => 'new',
                'priority' => $data['priority'] ?? 'medium'
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (\Exception $e) {
            error_log('Create inquiry error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if property is favorited by user
     */
    private function isFavorited($user_id, $property_id)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $stmt = $this->db->prepare("SELECT id FROM property_favorites WHERE user_id = :userId AND property_id = :propertyId");
            $stmt->execute(['userId' => $user_id, 'propertyId' => $property_id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Check favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add property to favorites (internal helper)
     */
    private function addFavoriteInternal($user_id, $property_id)
    {
        try {
            if (!$this->db) {
                throw new Exception('Database connection not available');
            }

            $stmt = $this->db->prepare("INSERT INTO property_favorites (user_id, property_id) VALUES (:userId, :propertyId)");
            $stmt->execute(['userId' => $user_id, 'propertyId' => $property_id]);
        } catch (\Exception $e) {
            error_log('Add favorite error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove property from favorites (internal helper)
     */
    private function removeFavoriteInternal($user_id, $property_id)
    {
        try {
            if (!$this->db) {
                throw new Exception('Database connection not available');
            }

            $stmt = $this->db->prepare("DELETE FROM property_favorites WHERE user_id = :userId AND property_id = :propertyId");
            $stmt->execute(['userId' => $user_id, 'propertyId' => $property_id]);
        } catch (\Exception $e) {
            error_log('Remove favorite error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if property exists
     */
    private function propertyExists($property_id)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $stmt = $this->db->prepare("SELECT id FROM properties WHERE id = :propertyId AND status IN ('active', '')");
            $stmt->execute(['propertyId' => $property_id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Property exists check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's favorite properties
     */
    private function getUserFavorites($user_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "
                SELECT
                    p.id,
                    p.title,
                    p.price,
                    p.city,
                    p.state,
                    p.bedrooms,
                    p.bathrooms,
                    p.area_sqft,
                    p.featured,
                    p.created_at,
                    pt.type as property_type,
                    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM property_favorites pf
                JOIN properties p ON pf.property_id = p.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE pf.user_id = :userId
                ORDER BY pf.created_at DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['userId' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get user favorites error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property types for mobile filters
     */
    public function getPropertyTypes()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $stmt = $this->db->query("SELECT id, name, icon FROM property_types WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get property types error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available cities for mobile filters
     */
    private function getAvailableCities()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $stmt = $this->db->query("SELECT DISTINCT city FROM properties WHERE status IN ('active', '') AND city IS NOT NULL ORDER BY city");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            error_log('Get available cities error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get properties count for pagination
     */
    private function getPropertiesCount($filters)
    {
        try {
            if (!$this->db) {
                return 0;
            }

            $sql = "SELECT COUNT(*) as count FROM properties WHERE status IN ('active', '')";
            $params = [];

            // Apply filters
            if (isset($filters['property_type'])) {
                $sql .= " AND property_type_id = :propertyType";
                $params['propertyType'] = $filters['property_type'];
            }

            if (isset($filters['city'])) {
                $sql .= " AND city LIKE :city";
                $params['city'] = '%' . $filters['city'] . '%';
            }

            if (isset($filters['min_price'])) {
                $sql .= " AND price >= :minPrice";
                $params['minPrice'] = $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $sql .= " AND price <= :maxPrice";
                $params['maxPrice'] = $filters['max_price'];
            }

            if (isset($filters['featured']) && $filters['featured']) {
                $sql .= " AND featured = 1";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            error_log('Get properties count error: ' . $e->getMessage());
            return 0;
        }
    }

    // ==========================================
    // V2 SYNC & AUTH ENDPOINTS (Added for Mobile V2)
    // ==========================================


    /**
     * Get Property Status for Sync (V2) - Enhanced
     */
    public function syncProperties()
    {
        $this->setCorsHeaders();
        try {
            // Enhanced sync: Get all details for offline DB
            $stmt = $this->db->prepare("
                SELECT p.id, p.title as property_name, pt.type as property_type, p.status, p.price, p.city as location, p.area_sqft, p.updated_at
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                ORDER BY p.updated_at DESC
            ");
            $stmt->execute();
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->successResponse($properties, 'Properties fetched for sync');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            return $this->errorResponse('Failed to fetch properties: ' . $e->getMessage());
        }
    }

    /**
     * Batch Submit Leads from Offline App (V2)
     */
    public function batchSyncLeads()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $leads = $input['leads'] ?? [];
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            return $this->errorResponse('Authentication required', 401);
        }

        if (empty($leads)) {
            return $this->errorResponse('No leads provided', 400);
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                INSERT INTO leads (name, email, phone, source, assigned_to, created_by, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
            ");

            foreach ($leads as $lead) {
                $stmt->execute([
                    $lead['name'] ?? '',
                    $lead['email'] ?? '',
                    $lead['phone'] ?? '',
                    $lead['source'] ?? 'mobile_app',
                    $userId,
                    $userId
                ]);
            }

            $this->db->commit();

            // Real-time WebSocket broadcast to admin panel
            WebSocketBroadcaster::broadcastToAdmins([
                'type'       => 'leads_synced',
                'event'      => 'lead.created',
                'count'      => count($leads),
                'agent_id'   => $userId,
                'message'    => count($leads) . ' new lead(s) synced from mobile app',
                'created_at' => date('Y-m-d H:i:s'),
            ], 'admin');

            return $this->successResponse(['synced_count' => count($leads)], 'Leads batch synced successfully');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            $this->db->rollBack();
            return $this->errorResponse('Batch sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Submit Lead from Offline App (V2)
     */
    public function submitLead()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID not found in session', 401);
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO leads (name, email, phone, source_user_id, status, created_at) 
                VALUES (?, ?, ?, ?, 'new', NOW())
            ");
            $stmt->execute([
                $data['name'] ?? '',
                $data['email'] ?? '',
                $data['phone'] ?? '',
                $userId
            ]);

            return $this->successResponse(['id' => $this->db->lastInsertId()], 'Lead synced successfully');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            return $this->errorResponse('Lead sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Get MLM Performance Summary for Mobile (V2)
     */
    public function getMlmSummary()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID required', 401);
        }

        try {
            $perfCalculator = new \App\Services\PerformanceRankCalculator();
            $summary = $perfCalculator->calculateRank($userId);
            
            return $this->successResponse($summary, 'MLM performance summary fetched');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            return $this->errorResponse('Failed to fetch MLM summary: ' . $e->getMessage());
        }
    }

    /**
     * Get Payout/Commission History for Mobile (V2)
     */
    public function getMlmPayouts()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID required', 401);
        }

        try {
            $stmt = $this->db->prepare("
                SELECT amount, type, status, created_at as payout_date 
                FROM commissions 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $stmt->execute([$userId]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->successResponse($history, 'Payout history fetched');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            return $this->errorResponse('Failed to fetch payout history: ' . $e->getMessage());
        }
    }

    /**
     * Get User Profile for Mobile (V2)
     */
    public function getUserProfile()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            return $this->errorResponse('Unauthorized', 401);
        }

        try {
            $stmt = $this->db->prepare("
                SELECT u.id as user_id, u.name, u.email, u.phone, u.role, u.profile_image, u.created_at, u.updated_at,
                       mp.current_level as rank, mp.referral_code, mp.status as mlm_status
                FROM users u
                LEFT JOIN mlm_profiles mp ON u.id = mp.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $user['avatar'] = $user['profile_image'] ?? null;

            return $this->successResponse($user, 'User profile fetched');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            return $this->errorResponse('Failed to fetch profile: ' . $e->getMessage());
        }
    }

    protected function successResponse($data, $message = 'Success')
    {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }

    protected function errorResponse($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit();
    }

    /**
     * Get Monthly MLM Incentives (Salary Dashboard)
     */
    public function getMlmIncentives()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID required', 401);
        }

        try {
            $incentiveService = new \App\Services\MLMIncentiveService();
            $summary = $incentiveService->getIncentiveSummary($userId);

            return $this->successResponse($summary, 'Monthly incentives fetched');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            return $this->errorResponse('Failed to fetch incentives: ' . $e->getMessage());
        }
    }

    /**
     * Get User Documents from Digital Locker
     */
    public function getDocuments()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID required', 401);
        }

        try {
            $lockerService = new \App\Services\DocumentLockerService();
            $documents = $lockerService->getUserDocuments($userId);

            return $this->successResponse($documents, 'Documents fetched from locker');
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            return $this->errorResponse('Failed to fetch documents: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint for mobile app - Upload documents (Scanned copies, ID proofs, etc.)
     */
    public function uploadDocument()
    {
        $this->setCorsHeaders();
        
        $userId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_POST['user_id']) ?? null;
        $documentType = \App\Core\Security::sanitize($_POST['document_type']) ?? 'general';
        
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if (!isset($_FILES['document'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No document uploaded']);
            return;
        }

        $file = $_FILES['document'];

        $v = UploadValidator::validate($file, 'documents');
        if ($v !== true) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $v]);
            return;
        }

        $uploadDir = __DIR__ . '/../../../../public/uploads/documents/' . $userId . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = UploadValidator::safeFilename($file['name']);
        $filename = $documentType . '_' . time() . '_' . $safeName;
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $fileUrl = '/uploads/documents/' . $userId . '/' . $filename;
            
            // Record in Document Locker
            try {
                $lockerService = new \App\Services\DocumentLockerService();
                $title = \App\Core\Security::sanitize($_POST['title']) ?? (ucfirst($documentType) . ' Document');
                $lockerService->addDocument($userId, $title, $documentType, $fileUrl);
            } catch (\Exception $e) {
                // Log error but continue since file is moved
                error_log("Failed to record document in locker: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'url' => $fileUrl
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to save document']);
        }
    }


    /**
     * Get Customer Documents (KYC, Booking Agreements, Payment Receipts, etc.)
     */
    public function getCustomerDocuments()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            // Fetch documents from multiple sources (each resilient to missing tables)
            $documents = [];

            // 1. KYC Documents from kyc_requests
            try {
                $kycSql = "
                    SELECT id,
                           CASE
                               WHEN pan_document IS NOT NULL THEN 'PAN Document'
                               WHEN aadhaar_front_document IS NOT NULL THEN 'Aadhaar Document'
                               ELSE 'KYC Document'
                           END as name,
                           'kyc' as type, 'kyc' as category,
                           COALESCE(pan_document, aadhaar_front_document, aadhaar_back_document) as url,
                           created_at as uploaded_at, status,
                           'kyc' as source
                    FROM kyc_requests
                    WHERE user_id = ?
                    ORDER BY created_at DESC
                ";
                $kycStmt = $this->db->prepare($kycSql);
                $kycStmt->execute([$userId]);
                $documents = array_merge($documents, $kycStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) {
                error_log("[getCustomerDocuments] kyc skipped: " . $e->getMessage());
            }

            // 2. Booking Agreements from bookings
            try {
                $bookingSql = "
                    SELECT b.id, CONCAT('Booking Agreement - ', p.title) as name,
                           'agreement' as type, 'booking' as category,
                           ba.agreement_file as url, ba.created_at as uploaded_at, 'verified' as status,
                           'booking' as source
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    LEFT JOIN booking_agreements ba ON b.id = ba.booking_id
                    WHERE b.customer_id = ? AND ba.agreement_file IS NOT NULL
                    ORDER BY ba.created_at DESC
                ";
                $bookingStmt = $this->db->prepare($bookingSql);
                $bookingStmt->execute([$userId]);
                $documents = array_merge($documents, $bookingStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) {
                error_log("[getCustomerDocuments] booking agreements skipped: " . $e->getMessage());
            }

            // 3. Payment Receipts from payments
            try {
                $paymentSql = "
                    SELECT pay.id, CONCAT('Payment Receipt - ', p.title) as name,
                           'receipt' as type, 'payment' as category,
                           pay.receipt_file as url, pay.created_at as uploaded_at, 'verified' as status,
                           'payment' as source
                    FROM payments pay
                    JOIN bookings b ON pay.booking_id = b.id
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.customer_id = ? AND pay.receipt_file IS NOT NULL
                    ORDER BY pay.created_at DESC
                ";
                $paymentStmt = $this->db->prepare($paymentSql);
                $paymentStmt->execute([$userId]);
                $documents = array_merge($documents, $paymentStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) {
                error_log("[getCustomerDocuments] payments skipped: " . $e->getMessage());
            }

            // 4. Plot Allotment Letters
            try {
                $allotmentSql = "
                    SELECT pa.id, CONCAT('Allotment Letter - ', p.title) as name,
                           'allotment' as type, 'booking' as category,
                           pa.letter_file as url, pa.created_at as uploaded_at, pa.status,
                           'allotment' as source
                    FROM plot_allotments pa
                    JOIN bookings b ON pa.booking_id = b.id
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.customer_id = ? AND pa.letter_file IS NOT NULL
                    ORDER BY pa.created_at DESC
                ";
                $allotmentStmt = $this->db->prepare($allotmentSql);
                $allotmentStmt->execute([$userId]);
                $documents = array_merge($documents, $allotmentStmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Throwable $e) {
                error_log("[getCustomerDocuments] allotments skipped: " . $e->getMessage());
            }

            // Sort by uploaded_at descending
            usort($documents, function($a, $b) {
                return strtotime($b['uploaded_at'] ?? 0) - strtotime($a['uploaded_at'] ?? 0);
            });

            echo json_encode([
                'success' => true,
                'data' => $documents
            ]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch documents: ' . $e->getMessage()]);
        }
    }


    /**
     * Start a site visit session
     */
    public function startSiteVisit()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $agentId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($input['user_id'] ?? null) ?? null;
        $leadId = \App\Core\Security::sanitize($input['lead_id'] ?? null) ?? null;
        $propertyId = \App\Core\Security::sanitize($input['property_id'] ?? null) ?? null;
        $destLat = \App\Core\Security::sanitize($input['dest_lat'] ?? null) ?? null;
        $destLng = \App\Core\Security::sanitize($input['dest_lng'] ?? null) ?? null;

        if (!$agentId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $visitService = new \App\Services\SiteVisitService();
            $result = $visitService->startVisit($agentId, $leadId, $propertyId, $destLat, $destLng);

            // Real-time WebSocket broadcast to admin panel
            WebSocketBroadcaster::broadcastToAdmins([
                'type'        => 'site_visit_started',
                'event'       => 'visit.started',
                'agent_id'    => $agentId,
                'lead_id'     => $leadId,
                'property_id' => $propertyId,
                'message'     => "Agent started a site visit",
                'created_at'  => date('Y-m-d H:i:s'),
            ], 'admin');

            echo json_encode($result);
        } catch (\Exception $e) {
            error_log("[MobileApiController] startSiteVisit() exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to start site visit: ' . $e->getMessage()]);
        }
    }

    /**
     * Update current GPS location for an active visit
     */
    public function updateSiteVisitLocation()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $visitId = \App\Core\Security::sanitize($input['visit_id'] ?? null) ?? null;
        $lat = \App\Core\Security::sanitize($input['lat'] ?? null) ?? null;
        $lng = \App\Core\Security::sanitize($input['lng'] ?? null) ?? null;

        if (!$visitId || !$lat || !$lng) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing location parameters']);
            return;
        }

        try {
            $visitService = new \App\Services\SiteVisitService();
            $visitService->updateLocation($visitId, $lat, $lng);
            echo json_encode(['success' => true, 'message' => 'Location updated']);
        } catch (\Exception $e) {
            error_log("[MobileApiController] updateSiteVisitLocation() exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete an active site visit session
     */
    public function completeSiteVisit()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $visitId = \App\Core\Security::sanitize($input['visit_id'] ?? null) ?? null;

        if (!$visitId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Visit ID required']);
            return;
        }

        try {
            $visitService = new \App\Services\SiteVisitService();
            $visitService->completeVisit($visitId);

            // Real-time WebSocket broadcast to admin panel
            WebSocketBroadcaster::broadcastToAdmins([
                'type'      => 'site_visit_completed',
                'event'     => 'visit.completed',
                'visit_id'  => $visitId,
                'message'   => "Site visit #$visitId completed",
                'created_at' => date('Y-m-d H:i:s'),
            ], 'admin');

            echo json_encode(['success' => true, 'message' => 'Site visit completed']);
        } catch (\Exception $e) {
            error_log("[MobileApiController] completeSiteVisit() exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to complete visit: ' . $e->getMessage()]);
        }
    }

    /**
     * Get site visit status
     */
    public function getSiteVisitStatus()
    {
        $this->setCorsHeaders();
        $visitId = \App\Core\Security::sanitize($_GET['visit_id'] ?? null) ?? null;

        if (!$visitId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Visit ID required']);
            return;
        }

        try {
            $visitService = new \App\Services\SiteVisitService();
            $status = $visitService->getVisitStatus($visitId);
            echo json_encode(['success' => true, 'data' => $status]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fetch failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get available time slots for site visit booking
     */
    public function getAvailableSlots()
    {
        $this->setCorsHeaders();
        $date = trim($_GET['date'] ?? '');
        $colonyId = (int)($_GET['colony_id'] ?? 0);

        if (!$date) {
            $date = date('Y-m-d');
        }

        try {
            $pdo = $this->db;
            // Check if visit_time_slots table has data for this date
            $stmt = $pdo->prepare("SELECT id FROM visit_time_slots WHERE date = ? AND is_available = 1 LIMIT 1");
            $stmt->execute([$date]);
            $hasSlots = $stmt->fetch();

            if (!$hasSlots) {
                // Auto-generate default slots for this date (9 AM to 6 PM, 1-hour intervals)
                $this->generateDefaultSlots($pdo, $date);
            }

            $sql = "SELECT id, time_slot, max_bookings, current_bookings, (max_bookings - current_bookings) as remaining
                    FROM visit_time_slots WHERE date = ? AND is_available = 1 AND current_bookings < max_bookings
                    ORDER BY time_slot ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format time slots for display
            foreach ($slots as &$slot) {
                $slot['time_slot'] = date('H:i', strtotime($slot['time_slot']));
                $slot['display_time'] = date('h:i A', strtotime($slot['time_slot']));
                $slot['remaining'] = (int)$slot['remaining'];
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'slots' => $slots,
                ]
            ]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] getAvailableSlots() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch slots']);
        }
    }

    /**
     * Generate default time slots for a date (9 AM - 6 PM, 1-hour intervals)
     */
    private function generateDefaultSlots($pdo, string $date): void
    {
        $slots = [
            '09:00:00', '10:00:00', '11:00:00', '12:00:00',
            '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'
        ];
        $insertStmt = $pdo->prepare("INSERT IGNORE INTO visit_time_slots (date, time_slot, max_bookings, current_bookings, is_available) VALUES (?, ?, 3, 0, 1)");
        foreach ($slots as $slot) {
            $insertStmt->execute([$date, $slot]);
        }
    }

    /**
     * Book a site visit
     */
    public function bookSiteVisitApi()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $colonyId = (int)($input['colony_id'] ?? 0);
        $visitDate = trim($input['visit_date'] ?? '');
        $visitTime = trim($input['visit_time'] ?? '');
        $name = trim($input['name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $email = trim($input['email'] ?? '');
        $needPickup = !empty($input['need_pickup']);
        $pickupAddress = trim($input['pickup_address'] ?? '');
        $guestCount = max(1, (int)($input['guest_count'] ?? 1));
        $notes = trim($input['notes'] ?? '');
        $plotId = (int)($input['plot_id'] ?? 0);

        // Validation
        if (!$visitDate || !$visitTime || !$name || !$phone) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields: visit_date, visit_time, name, phone']);
            return;
        }

        // Get user_id from auth token if available
        $userId = $GLOBALS['api_user_id'] ?? null;

        try {
            $pdo = $this->db;

            // Get colony name
            $colonyName = '';
            if ($colonyId > 0) {
                $cstmt = $pdo->prepare("SELECT name FROM colonies WHERE id = ?");
                $cstmt->execute([$colonyId]);
                $crow = $cstmt->fetch(PDO::FETCH_ASSOC);
                $colonyName = $crow['name'] ?? '';
            }

            // Check slot availability
            $slotStmt = $pdo->prepare("SELECT id, current_bookings, max_bookings FROM visit_time_slots WHERE date = ? AND time_slot = ? AND is_available = 1 FOR UPDATE");
            $slotStmt->execute([$visitDate, $visitTime]);
            $slot = $slotStmt->fetch(PDO::FETCH_ASSOC);

            if ($slot && $slot['current_bookings'] >= $slot['max_bookings']) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Time slot is fully booked. Please choose another time.']);
                return;
            }

            // Insert into property_visits
            $insertStmt = $pdo->prepare("INSERT INTO property_visits
                (customer_id, property_id, customer_name, customer_email, customer_phone,
                 visit_date, visit_time, visit_type, status, notes, assigned_to, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'site_visit', 'scheduled', ?, ?, ?)");
            $insertStmt->execute([
                $userId,
                $plotId > 0 ? $plotId : null,
                $name,
                $email,
                $phone,
                $visitDate . ' ' . $visitTime,
                $visitTime,
                ($notes ? $notes . ' | ' : '') . "Colony: $colonyName | Guests: $guestCount" . ($needPickup ? " | Pickup: $pickupAddress" : ''),
                null, // assigned_to (auto-assign later)
                $userId
            ]);
            $visitId = (int)$pdo->lastInsertId();

            // Update slot booking count
            if ($slot) {
                $pdo->prepare("UPDATE visit_time_slots SET current_bookings = current_bookings + 1 WHERE id = ?")->execute([$slot['id']]);
            }

            // Auto-assign an available agent (round-robin from associates)
            $agentStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'associate' AND status = 'active' ORDER BY RAND() LIMIT 1");
            $agentStmt->execute();
            $agent = $agentStmt->fetch(PDO::FETCH_ASSOC);
            if ($agent) {
                $pdo->prepare("UPDATE property_visits SET assigned_to = ? WHERE id = ?")->execute([$agent['id'], $visitId]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Site visit booked successfully!',
                'data' => [
                    'visit_id' => $visitId,
                    'visit_date' => $visitDate,
                    'visit_time' => date('h:i A', strtotime($visitTime)),
                    'colony' => $colonyName,
                    'status' => 'scheduled',
                ]
            ]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] bookSiteVisitApi() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get current user's site visits
     */
    public function getMySiteVisits()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $pdo = $this->db;
            $status = trim($_GET['status'] ?? '');

            $sql = "SELECT v.id, v.visit_date, v.visit_time, v.status, v.visit_type, v.notes,
                           v.feedback_rating, v.feedback_comments, v.customer_name, v.customer_phone,
                           v.created_at,
                           c.name as colony_name, c.id as colony_id,
                           u.name as agent_name, u.phone as agent_phone
                    FROM property_visits v
                    LEFT JOIN colonies c ON c.id = (SELECT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(v.notes, 'Colony: ', -1), ' | ', 1) AS CHAR))
                    LEFT JOIN users u ON u.id = v.assigned_to
                    WHERE v.customer_id = ? ";
            $params = [$userId];

            if ($status) {
                $sql .= "AND v.status = ? ";
                $params[] = $status;
            }

            $sql .= "ORDER BY v.visit_date DESC LIMIT 50";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format visits
            foreach ($visits as &$visit) {
                $visit['visit_date'] = date('Y-m-d', strtotime($visit['visit_date']));
                $visit['visit_time'] = date('h:i A', strtotime($visit['visit_time']));
                $visit['display_date'] = date('D, d M Y', strtotime($visit['visit_date']));
                $visit['is_upcoming'] = strtotime($visit['visit_date'] . ' ' . $visit['visit_time']) > time();
            }

            echo json_encode([
                'success' => true,
                'data' => $visits,
                'count' => count($visits)
            ]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] getMySiteVisits() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch visits']);
        }
    }

    /**
     * Cancel a site visit
     */
    public function cancelSiteVisitApi()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $visitId = (int)($input['visit_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$visitId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Visit ID required']);
            return;
        }

        try {
            $pdo = $this->db;
            $userId = $GLOBALS['api_user_id'] ?? null;

            // Verify ownership
            $stmt = $pdo->prepare("SELECT id, customer_id, visit_date, visit_time FROM property_visits WHERE id = ?");
            $stmt->execute([$visitId]);
            $visit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visit) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Visit not found']);
                return;
            }

            if ($userId && $visit['customer_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Not authorized']);
                return;
            }

            // Cancel the visit
            $pdo->prepare("UPDATE property_visits SET status = 'cancelled', cancellation_reason = ? WHERE id = ?")->execute([$reason, $visitId]);

            // Release slot
            $pdo->prepare("UPDATE visit_time_slots SET current_bookings = GREATEST(0, current_bookings - 1) WHERE date = DATE(?) AND time_slot = ?")->execute([$visit['visit_date'], $visit['visit_time']]);

            echo json_encode(['success' => true, 'message' => 'Visit cancelled successfully']);
        } catch (\Exception $e) {
            error_log("[MobileApiController] cancelSiteVisitApi() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Cancel failed']);
        }
    }

    /**
     * Reschedule a site visit
     */
    public function rescheduleSiteVisitApi()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $visitId = (int)($input['visit_id'] ?? 0);
        $newDate = trim($input['new_date'] ?? '');
        $newTime = trim($input['new_time'] ?? '');

        if (!$visitId || !$newDate || !$newTime) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing visit_id, new_date, or new_time']);
            return;
        }

        try {
            $pdo = $this->db;
            $userId = $GLOBALS['api_user_id'] ?? null;

            // Verify ownership
            $stmt = $pdo->prepare("SELECT id, customer_id, visit_date, visit_time FROM property_visits WHERE id = ?");
            $stmt->execute([$visitId]);
            $visit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visit) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Visit not found']);
                return;
            }

            if ($userId && $visit['customer_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Not authorized']);
                return;
            }

            // Ensure slots exist for the target date (mirror booking flow)
            $this->generateDefaultSlots($pdo, $newDate);

            $pdo->beginTransaction();

            // Check new slot availability
            $slotStmt = $pdo->prepare("SELECT id, current_bookings, max_bookings FROM visit_time_slots WHERE date = ? AND time_slot = ? AND is_available = 1 FOR UPDATE");
            $slotStmt->execute([$newDate, $newTime]);
            $slot = $slotStmt->fetch(PDO::FETCH_ASSOC);

            if (!$slot || $slot['current_bookings'] >= $slot['max_bookings']) {
                $pdo->rollBack();
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'New time slot not available']);
                return;
            }

            // Release old slot
            $pdo->prepare("UPDATE visit_time_slots SET current_bookings = GREATEST(0, current_bookings - 1) WHERE date = DATE(?) AND time_slot = ?")->execute([$visit['visit_date'], $visit['visit_time']]);

            // Update visit
            $pdo->prepare("UPDATE property_visits SET visit_date = ?, visit_time = ?, status = 'rescheduled' WHERE id = ?")->execute([$newDate . ' ' . $newTime, $newTime, $visitId]);

            // Book new slot
            $pdo->prepare("UPDATE visit_time_slots SET current_bookings = current_bookings + 1 WHERE id = ?")->execute([$slot['id']]);

            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Visit rescheduled successfully']);
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("[MobileApiController] rescheduleSiteVisitApi() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Reschedule failed']);
        }
    }

    /**
     * Get pending payouts summary
     */
    public function getPendingPayouts()
    {
        $this->setCorsHeaders();
        try {
            $payoutService = new \App\Services\AutoPayoutService();
            $pending = $payoutService->getPendingPayouts();
            echo json_encode(['success' => true, 'data' => $pending]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Process bulk payouts (One-Click)
     */
    public function processPayouts()
    {
        $this->setCorsHeaders();
        $adminId = $GLOBALS['api_user_id'] ?? null;
        if (!$adminId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $payoutService = new \App\Services\AutoPayoutService();
            $result = $payoutService->processPayouts($adminId);
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get payout history
     */
    public function getPayoutHistory()
    {
        $this->setCorsHeaders();
        try {
            $payoutService = new \App\Services\AutoPayoutService();
            $history = $payoutService->getPayoutHistory();
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Genealogy/Team Tree Data (Phase 5)
     */
    public function getGenealogy()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            $mlmService = new \App\Services\MLMNetworkService(); // Assuming this service exists or will be created
            $tree = $mlmService->getDownline($userId);
            echo json_encode(['success' => true, 'data' => $tree]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get detailed business breakdown for associate (Phase 5)
     */
    public function getBusinessBreakdown()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            $mlmService = new \App\Services\MLMNetworkService();
            $data = $mlmService->getBusinessBreakdown($userId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get associate's direct team members (My Team)
     */
    public function getMyTeam()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            $mlmService = new \App\Services\MLMNetworkService();
            
            // Get direct referrals from mlm_network_tree
            $directSql = "SELECT nt.associate_id, u.name, u.email, u.phone, u.profile_image, nt.level, nt.created_at as joined_at
                FROM mlm_network_tree nt
                JOIN users u ON u.id = nt.associate_id
                WHERE nt.parent_id = ?
                ORDER BY nt.created_at DESC";
            $directReferrals = $this->db->fetchAll($directSql, [$userId]) ?? [];
            
            // Get team stats
            $teamSize = $mlmService->getTeamSize($userId);
            $directCount = $mlmService->getDirectCount($userId);
            
            // Get active/inactive counts via mlm_network_tree
            $activeSql = "SELECT COUNT(*) FROM mlm_network_tree nt JOIN users u ON u.id = nt.associate_id WHERE nt.parent_id = ? AND u.status = 'active'";
            $inactiveSql = "SELECT COUNT(*) FROM mlm_network_tree nt JOIN users u ON u.id = nt.associate_id WHERE nt.parent_id = ? AND u.status != 'active'";
            
            $activeStmt = $this->db->prepare($activeSql);
            $activeStmt->execute([$userId]);
            $activeCount = (int)$activeStmt->fetchColumn();
            
            $inactiveStmt = $this->db->prepare($inactiveSql);
            $inactiveStmt->execute([$userId]);
            $inactiveCount = (int)$inactiveStmt->fetchColumn();

            // Get recent joinings (last 30 days)
            $recentSql = "SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $recentStmt = $this->db->prepare($recentSql);
            $recentStmt->execute([$userId]);
            $recentCount = (int)$recentStmt->fetchColumn();

            // Get total team business from plot_bookings
            $businessSql = "
                SELECT COALESCE(SUM(pb.total_plot_value), 0) as total_business
                FROM plot_bookings pb
                WHERE pb.associate_id IN (
                    SELECT nt.associate_id FROM mlm_network_tree nt WHERE nt.parent_id = ?
                )
            ";
            $businessStmt = $this->db->prepare($businessSql);
            $businessStmt->execute([$userId]);
            $totalBusiness = (float)$businessStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'data' => [
                    'direct_referrals' => $directReferrals,
                    'stats' => [
                        'total_team_size' => $teamSize,
                        'direct_referrals' => $directCount,
                        'active_members' => $activeCount,
                        'inactive_members' => $inactiveCount,
                        'recent_joinings_30d' => $recentCount,
                        'total_team_business' => $totalBusiness
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get associate's rank progress
     */
    public function getRankProgress()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            // Get current rank from mlm_profiles
            $profileSql = "SELECT current_level, lifetime_sales as total_sales, total_commission FROM mlm_profiles WHERE user_id = ?";
            $profileStmt = $this->db->prepare($profileSql);
            $profileStmt->execute([$userId]);
            $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                echo json_encode([
                    'success' => true,
                    'data' => ['current_rank' => 'Associate', 'overall_progress_pct' => 0],
                ]);
                return;
            }

            $currentRank = $profile['current_level'] ?? 'associate';
            $totalSales = (float)($profile['total_sales'] ?? 0);
            $totalCommission = (float)($profile['total_commission'] ?? 0);

            // Define rank thresholds (matching mlm_rank_benefits)
            $rankThresholds = [
                'associate' => ['sales' => 0, 'commission' => 0, 'directs' => 0],
                'senior_associate' => ['sales' => 25000, 'commission' => 5000, 'directs' => 1],
                'bdm' => ['sales' => 100000, 'commission' => 25000, 'directs' => 2],
                'sr_bdm' => ['sales' => 300000, 'commission' => 75000, 'directs' => 3],
                'vice_president' => ['sales' => 800000, 'commission' => 250000, 'directs' => 4],
                'president' => ['sales' => 2000000, 'commission' => 750000, 'directs' => 5],
                'site_manager' => ['sales' => 5000000, 'commission' => 2000000, 'directs' => 6],
            ];

            $rankOrder = array_keys($rankThresholds);
            $currentIndex = array_search(strtolower($currentRank), $rankOrder);
            if ($currentIndex === false) $currentIndex = 0;

            $nextRank = $currentIndex < count($rankOrder) - 1 ? $rankOrder[$currentIndex + 1] : null;
            
            $progress = [
                'current_rank' => $currentRank,
                'total_sales' => $totalSales,
                'total_commission' => $totalCommission,
                'direct_count' => (int)($this->db->fetchOne("SELECT COUNT(*) FROM network_tree WHERE parent_id = ?", [$userId])['COUNT(*)'] ?? 0),
                'next_rank' => $nextRank,
            ];

            if ($nextRank) {
                $nextThreshold = $rankThresholds[$nextRank];
                $salesProgress = min(100, ($totalSales / max(1, $nextThreshold['sales'])) * 100);
                $commissionProgress = min(100, ($totalCommission / max(1, $nextThreshold['commission'])) * 100);
                $directsProgress = min(100, ($progress['direct_count'] / max(1, $nextThreshold['directs'])) * 100);
                
                $progress['next_rank_thresholds'] = $nextThreshold;
                $progress['sales_progress_pct'] = round($salesProgress, 1);
                $progress['commission_progress_pct'] = round($commissionProgress, 1);
                $progress['directs_progress_pct'] = round($directsProgress, 1);
                $progress['overall_progress_pct'] = round(($salesProgress + $commissionProgress + $directsProgress) / 3, 1);
                $progress['sales_remaining'] = max(0, $nextThreshold['sales'] - $totalSales);
                $progress['commission_remaining'] = max(0, $nextThreshold['commission'] - $totalCommission);
                $progress['directs_remaining'] = max(0, $nextThreshold['directs'] - $progress['direct_count']);
            } else {
                $progress['next_rank_thresholds'] = null;
                $progress['sales_progress_pct'] = 100;
                $progress['commission_progress_pct'] = 100;
                $progress['directs_progress_pct'] = 100;
                $progress['overall_progress_pct'] = 100;
                $progress['sales_remaining'] = 0;
                $progress['commission_remaining'] = 0;
                $progress['directs_remaining'] = 0;
            }

            echo json_encode(['success' => true, 'data' => $progress]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Request a commission payout (Phase 5)
     */
    public function requestPayout()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? Security::sanitize($_POST['user_id']) ?? null;
        $amount = Security::sanitize($_POST['amount']) ?? 0;
        $remarks = Security::sanitize($_POST['remarks'] ?? 'Mobile app request');

        if (!$userId || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
            return;
        }

        try {
            // Check if user has enough pending balance
            $payoutService = new \App\Services\AutoPayoutService();
            $check = $this->db->fetchOne("SELECT SUM(amount) FROM mlm_commission_ledger WHERE user_id = ? AND status = 'pending'", [$userId]);
            $pending = $check[0] ?? 0;

            if ($amount > $pending) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Requested amount exceeds pending balance (₹' . $pending . ')']);
                return;
            }

            $sql = "INSERT INTO mlm_payout_requests (user_id, amount, status, remarks) VALUES (?, ?, 'pending', ?)";
            $this->db->query($sql, [$userId, $amount, $remarks]);

            echo json_encode(['success' => true, 'message' => 'Payout request submitted successfully']);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get bookings for current customer (Phase 5)
     */
    public function getCustomerBookings()
    {
        $this->setCorsHeaders();
        $customerId = $GLOBALS['api_user_id'] ?? Security::sanitize($_GET['customer_id']) ?? null;

        if (!$customerId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Customer identification required']);
            return;
        }

        try {
            $customerService = new \App\Services\CustomerService();
            $data = $customerService->getCustomerBookings($customerId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get EMI schedule for a booking (Phase 5)
     */
    public function getEmiSchedule()
    {
        $this->setCorsHeaders();
        $bookingId = Security::sanitize($_GET['booking_id'] ?? null) ?? null;

        if (!$bookingId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Booking ID required']);
            return;
        }

        try {
            $customer = new \App\Models\Customer();
            $data = $customer->getEmiSchedule($bookingId);
            echo json_encode($data);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Pay an EMI installment (Phase 5)
     */
    public function makeEmiPayment()
    {
        $this->setCorsHeaders();
        $emiId = Security::sanitize($_POST['emi_id']) ?? null;
        $amount = Security::sanitize($_POST['amount']) ?? 0;
        $method = Security::sanitize($_POST['method'] ?? 'Simulated');

        if (!$emiId || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid payment parameters']);
            return;
        }

        try {
            $customer = new \App\Models\Customer();
            $result = $customer->recordEmiPayment($emiId, $amount, $method);
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Submit a property for approval (Phase 5)
     */
    public function submitProperty()
    {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $userId = $GLOBALS['api_user_id'] ?? $input['user_id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User identification required']);
            return;
        }

        try {
            // Determine submitter type based on user rank
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $type = ($user && $user['role'] != 'customer' && $user['role'] != '') ? 'agent' : 'customer';

            $submissionService = new \App\Services\PropertySubmissionService();
            $data = [
                'submitter_id' => $userId,
                'submitter_type' => $type,
                'title' => Security::sanitize($input['title'] ?? ''),
                'description' => Security::sanitize($input['description'] ?? ''),
                'price' => Security::sanitize($input['price'] ?? ''),
                'property_type' => Security::sanitize($input['property_type'] ?? 'Plot'),
                'location' => Security::sanitize($input['location'] ?? ''),
                'images' => $input['images'] ?? []
            ];

            $result = $submissionService->submitProperty($data);
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get user's own submissions (Phase 5)
     */
    public function getSubmissions()
    {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;

        try {
            $submissionService = new \App\Services\PropertySubmissionService();
            $data = $submissionService->getUserSubmissions($userId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function leads()
    {
        header('Content-Type: application/json');
        $db = \App\Core\Database::getInstance();
        $leads = $db->fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT 20");
        echo json_encode(['success' => true, 'data' => $leads]);
        exit;
    }

    public function userProfile()
    {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        $db = \App\Core\Database::getInstance();
        $user = $db->fetch("SELECT id, name, email, phone, created_at FROM users WHERE id = ?", [$userId]);
        echo json_encode(['success' => true, 'data' => $user ?: []]);
        exit;
    }

    // ============================================================
    // V2 JWT-AUTH ENDPOINTS (Mobile API V2)
    // ============================================================

    /**
     * Extract Bearer token from Authorization header.
     * Returns null when missing or malformed.
     */
    private function extractBearerToken()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? null;
        if (!$header && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $name => $value) {
                if (strcasecmp($name, 'Authorization') === 0) {
                    $header = $value;
                    break;
                }
            }
        }

        if (!$header) {
            return null;
        }
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return null;
    }

    /**
     * Authenticate the request with JWT and apply per-user rate limit.
     * Sets $GLOBALS['api_user_id'] / $GLOBALS['api_user_role'] on success.
     * Echoes JSON error + exits on failure.
     */
    private function authenticateAndRateLimit()
    {
        // If ApiAuthMiddleware already set the user, skip JWT verification
        if (!empty($GLOBALS['api_user_id'])) {
            return;
        }

        $token = $this->extractBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authorization Bearer token required', 'code' => 401]);
            exit;
        }

        $payload = $this->jwtService->verifyToken($token);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired token', 'code' => 401]);
            exit;
        }

        $userId = (int) ($payload['sub'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Token missing subject', 'code' => 401]);
            exit;
        }

        $rateKey = 'mobile_user_' . $userId;
        if (!$this->jwtService->rateLimit($rateKey, 60, 60)) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded. Max 60 requests per minute.',
                'code' => 429,
            ]);
            exit;
        }

        $GLOBALS['api_user_id'] = $userId;
        $GLOBALS['api_user_role'] = $payload['role'] ?? 'customer';
        return $payload;
    }

    /**
     * POST /api/mobile/auth/login
     * Body: { "email": "...", "password": "..." }
     */
    public function loginV2()
    {
        $this->setCorsHeaders();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'email and password are required', 'code' => 400]);
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('SELECT id, name, email, role, status, password FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials', 'code' => 401]);
                return;
            }

            $hash = $user['password'] ?? '';
            $valid = $hash !== '' && (
                password_verify($password, $hash)
                || (\App\Core\Security::verifyPassword($password, $hash) ?? false)
                || hash_equals($hash, $password)
            );

            if (!$valid) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials', 'code' => 401]);
                return;
            }

            if (isset($user['status']) && $user['status'] === 'suspended') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Account suspended', 'code' => 403]);
                return;
            }

            $role = $user['role'] ?? 'customer';
            $tokens = $this->jwtService->generateToken((int) $user['id'], $role);

            echo json_encode([
                'success' => true,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => $tokens['token_type'],
                'expires_in' => $tokens['expires_in'],
                'user_id' => (int) $user['id'],
                'role' => $role,
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? $email,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::loginV2 failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Login failed: ' . $e->getMessage(), 'code' => 500]);
        }
    }

    /**
     * POST /api/mobile/auth/refresh
     * Body: { "refresh_token": "<jwt>" }
     */
    public function refreshV2()
    {
        $this->setCorsHeaders();
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $refreshToken = (string) ($data['refresh_token'] ?? '');

        if ($refreshToken === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'refresh_token required', 'code' => 400]);
            return;
        }

        $tokens = $this->jwtService->refreshToken($refreshToken);
        if (!$tokens) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired refresh token', 'code' => 401]);
            return;
        }

        echo json_encode([
            'success' => true,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'token_type' => $tokens['token_type'] ?? 'Bearer',
            'expires_in' => $tokens['expires_in'] ?? 86400,
        ]);
    }

    /**
     * GET /api/mobile/profile
     * Requires Bearer token.
     */
    public function profileV2()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('SELECT id, name, email, phone, role, status, created_at, updated_at, mlm_rank, mlm_points, wallet_balance FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$GLOBALS['api_user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found', 'code' => 404]);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Throwable $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Profile fetch failed: ' . $e->getMessage(), 'code' => 500]);
        }
    }

    /**
     * GET /api/mobile/properties
     * Paginated (20 per page) user-scoped property list.
     */
    public function mobileProperties()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $userId = (int) $GLOBALS['api_user_id'];

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM user_properties WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $total = (int) $countStmt->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT id, user_id, property_type, listing_type, address, area_sqft, price, status, created_at, updated_at
                FROM user_properties
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT {$perPage} OFFSET {$offset}
            ");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => [
                    'properties' => $rows,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => (int) ceil($total / $perPage),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Properties fetch failed: ' . $e->getMessage(), 'code' => 500]);
        }
    }

    /**
     * GET /api/mobile/dashboard
     * Aggregated stats for the mobile home screen.
     */
    public function dashboardV2()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];

        $stats = [
            'property_count' => 0,
            'lead_count' => 0,
            'unread_notifications' => 0,
            'wallet_balance' => 0,
            'mlm_points' => 0,
        ];

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_properties WHERE user_id = ?');
                $stmt->execute([$userId]);
                $stats['property_count'] = (int) $stmt->fetchColumn();
            } catch (\Throwable $e) {
                error_log("[MobileApiController] exception: " . $e->getMessage());
}

            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM leads WHERE created_by = ? OR source_id = ?');
                $stmt->execute([$userId, $userId]);
                $stats['lead_count'] = (int) $stmt->fetchColumn();
            } catch (\Throwable $e) {
                error_log("[MobileApiController] exception: " . $e->getMessage());
}

            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
                $stmt->execute([$userId]);
                $stats['unread_notifications'] = (int) $stmt->fetchColumn();
            } catch (\Throwable $e) {
                error_log("[MobileApiController] exception: " . $e->getMessage());
}

            try {
                $stmt = $pdo->prepare('SELECT wallet_balance, mlm_points FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$userId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $stats['wallet_balance'] = (float) ($row['wallet_balance'] ?? 0);
                    $stats['mlm_points'] = (int) ($row['mlm_points'] ?? 0);
                }
            } catch (\Throwable $e) {
                error_log("[MobileApiController] exception: " . $e->getMessage());
}

            echo json_encode([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Dashboard fetch failed: ' . $e->getMessage(), 'code' => 500]);
        }
    }

    /**
     * POST /api/mobile/notifications/register
     * Body: { "device_token": "...", "platform": "android|ios|web", "device_id": "..." }
     */
    public function registerPushTokenV2()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $deviceToken = trim((string) ($data['device_token'] ?? ''));
        $platform = trim((string) ($data['platform'] ?? 'android'));
        $deviceId = trim((string) ($data['device_id'] ?? ''));

        if ($deviceToken === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'device_token required', 'code' => 400]);
            return;
        }

        $ok = $this->jwtService->registerPushToken(
            (int) $GLOBALS['api_user_id'],
            (string) $GLOBALS['api_user_role'],
            $deviceToken,
            $platform,
            $deviceId ?: null
        );

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Push token registered' : 'Failed to register push token',
        ]);
    }

    /**
     * POST /api/v2/mobile/fcm/register
     * Body: { "token": "...", "platform": "android|ios" }
     * 
     * This is the endpoint Flutter notification_service.dart calls.
     * It stores the FCM token in push_tokens table AND mobile_devices table
     * so both PushNotificationService and MobileDevice model can find it.
     */
    public function registerFcmToken()
    {
        $this->setCorsHeaders();
        // Auth already handled by ApiAuthMiddleware on this route
        // $GLOBALS['api_user_id'] is set by middleware

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        // Flutter sends "token", backend expects "device_token" — handle both
        $deviceToken = trim((string) ($data['token'] ?? $data['device_token'] ?? ''));
        $platform = trim((string) ($data['platform'] ?? 'android'));
        $appVersion = trim((string) ($data['app_version'] ?? ''));

        if ($deviceToken === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'token required', 'code' => 400]);
            return;
        }

        $userId = (int) $GLOBALS['api_user_id'];
        $userRole = (string) $GLOBALS['api_user_role'];

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // 1. Write to push_tokens (used by JWTAuthService path)
            $stmt = $pdo->prepare("
                INSERT INTO push_tokens (user_id, user_type, device_token, platform, is_active, last_used_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, 1, NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    is_active = 1,
                    platform = VALUES(platform),
                    last_used_at = NOW(),
                    updated_at = NOW()
            ");
            $stmt->execute([$userId, $userRole, $deviceToken, $platform]);

            // 2. Also write to mobile_devices (used by PushNotificationService::sendToUser)
            try {
                $existing = $pdo->prepare("SELECT id FROM mobile_devices WHERE device_token = ? LIMIT 1");
                $existing->execute([$deviceToken]);
                $existingDevice = $existing->fetchColumn();

                if ($existingDevice) {
                    $upd = $pdo->prepare("UPDATE mobile_devices SET user_id = ?, platform = ?, last_used_at = NOW(), is_active = 1 WHERE device_token = ?");
                    $upd->execute([$userId, $platform, $deviceToken]);
                } else {
                    $ins = $pdo->prepare("INSERT INTO mobile_devices (user_id, device_token, platform, last_used_at, is_active, created_at) VALUES (?, ?, ?, NOW(), 1, NOW())");
                    $ins->execute([$userId, $deviceToken, $platform]);
                }
            } catch (\Throwable $e) {
                // mobile_devices table might not exist — push_tokens is sufficient
                error_log('FCM register: mobile_devices write failed: ' . $e->getMessage());
            }

            // 3. Subscribe to role-based topic for broadcast notifications
            try {
                $fcmProjectId = $_ENV['FCM_PROJECT_ID'] ?? '';
                if (!empty($fcmProjectId)) {
                    // Topic subscription handled by PushNotificationService
                    $pushSvc = new \App\Services\Communication\PushNotificationService();
                    $pushSvc->subscribeToTopic($deviceToken, 'role_' . $userRole);
                    $pushSvc->subscribeToTopic($deviceToken, 'all');
                }
            } catch (\Throwable $e) {
                // Non-critical — token is already saved
            }

            echo json_encode([
                'success' => true,
                'message' => 'FCM token registered',
            ]);

        } catch (\Throwable $e) {
            error_log('registerFcmToken error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error', 'code' => 500]);
        }
    }

    // ============================================================
    // PROPERTY BROWSING ENDPOINTS (Approved properties for customers)
    // ============================================================

    /**
     * GET /api/mobile/v2/properties
     * List approved properties with filters (type, location, price range, pagination)
     */
    public function browseProperties()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(max(1, (int) ($_GET['per_page'] ?? 20)), 50);
        $offset = ($page - 1) * $perPage;

        $type = $_GET['type'] ?? null;
        $location = $_GET['location'] ?? null;
        $minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;
        $bedrooms = isset($_GET['bedrooms']) ? (int) $_GET['bedrooms'] : null;
        $sort = $_GET['sort'] ?? 'newest';

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $where = "WHERE p.status IN ('active', '')";
            $params = [];

            if ($type) {
                $where .= " AND p.type = ?";
                $params[] = $type;
            }
            if ($location) {
                $where .= " AND (p.location LIKE ? OR p.city LIKE ?)";
                $params[] = '%' . $location . '%';
                $params[] = '%' . $location . '%';
            }
            if ($minPrice !== null) {
                $where .= " AND p.price >= ?";
                $params[] = $minPrice;
            }
            if ($maxPrice !== null) {
                $where .= " AND p.price <= ?";
                $params[] = $maxPrice;
            }
            if ($bedrooms !== null) {
                $where .= " AND p.bedrooms >= ?";
                $params[] = $bedrooms;
            }

            $orderMap = [
                'newest' => 'p.created_at DESC',
                'price_low' => 'p.price ASC',
                'price_high' => 'p.price DESC',
                'popular' => 'p.featured DESC, p.created_at DESC',
            ];
            $orderBy = $orderMap[$sort] ?? 'p.created_at DESC';

            $countSql = "SELECT COUNT(*) FROM properties p {$where}";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $sql = "
                SELECT p.id, p.title, p.price, p.type, p.location, p.city, p.state,
                       p.bedrooms, p.bathrooms, p.area_sqft, p.featured, p.created_at,
                       (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM properties p
                {$where}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => [
                    'properties' => $properties,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => (int) ceil($total / $perPage),
                    ],
                    'filters' => [
                        'type' => $type,
                        'location' => $location,
                        'min_price' => $minPrice,
                        'max_price' => $maxPrice,
                        'bedrooms' => $bedrooms,
                        'sort' => $sort,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::browseProperties() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch properties', 'code' => 500]);
        }
    }

    /**
     * GET /api/v2/mobile/marketplace
     * Property marketplace with premium/featured/urgent badges
     */
    public function getMarketplace()
    {
        $this->setCorsHeaders();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        $type = trim($_GET['type'] ?? '');
        $city = trim($_GET['city'] ?? '');
        $minPrice = (float)($_GET['min_price'] ?? 0);
        $maxPrice = (float)($_GET['max_price'] ?? 0);
        $sort = trim($_GET['sort'] ?? 'newest');

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $where = ["up.status = 'approved'"];
            $params = [];

            if ($type) { $where[] = 'up.property_type = ?'; $params[] = $type; }
            if ($city) { $where[] = '(up.city_name LIKE ? OR up.address LIKE ?)'; $params[] = "%$city%"; $params[] = "%$city%"; }
            if ($minPrice > 0) { $where[] = 'up.price >= ?'; $params[] = $minPrice; }
            if ($maxPrice > 0) { $where[] = 'up.price <= ?'; $params[] = $maxPrice; }

            $whereSql = implode(' AND ', $where);

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM user_properties up WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $orderMap = [
                'newest' => 'up.created_at DESC',
                'price_low' => 'up.price ASC',
                'price_high' => 'up.price DESC',
                'oldest' => 'up.created_at ASC',
            ];
            $orderBy = $orderMap[$sort] ?? 'up.created_at DESC';

            // Premium/featured first, then by sort order
            $sql = "
                SELECT up.id, up.user_id, up.property_type, up.listing_type, up.name as title,
                       up.description, up.price, up.address, up.city_name as city, up.location,
                       up.area_sqft, up.bedrooms, up.bathrooms, up.status,
                       up.is_featured, up.is_urgent, up.is_premium, up.expires_at,
                       up.created_at, up.updated_at,
                       u.name as owner_name, u.phone as owner_phone,
                       up.image as main_image,
                       CASE WHEN up.is_premium = 1 THEN 3
                            WHEN up.is_featured = 1 THEN 2
                            WHEN up.is_urgent = 1 THEN 1
                            ELSE 0 END as priority
                FROM user_properties up
                LEFT JOIN users u ON u.id = up.user_id
                WHERE $whereSql
                ORDER BY priority DESC, $orderBy
                LIMIT $perPage OFFSET $offset
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add badge info
            foreach ($properties as &$p) {
                $p['badges'] = [];
                $p['price'] = (float)($p['price'] ?? 0);
                if ($p['is_premium']) $p['badges'][] = ['label' => 'Premium', 'color' => '#FFD700', 'icon' => 'star'];
                if ($p['is_featured']) $p['badges'][] = ['label' => 'Featured', 'color' => '#4CAF50', 'icon' => 'trending_up'];
                if ($p['is_urgent']) $p['badges'][] = ['label' => 'Urgent', 'color' => '#FF5722', 'icon' => 'priority_high'];
                $p['is_premium'] = (bool)$p['is_premium'];
                $p['is_featured'] = (bool)$p['is_featured'];
                $p['is_urgent'] = (bool)$p['is_urgent'];
                $p['area_sqft'] = (float)($p['area_sqft'] ?? 0);
                $p['bedrooms'] = (int)($p['bedrooms'] ?? 0);
                $p['bathrooms'] = (int)($p['bathrooms'] ?? 0);
                $p['image_url'] = $p['main_image'] ? (BASE_URL . '/' . ltrim($p['main_image'], '/')) : null;
            }

            // Separate premium listings for top section
            $premiumListings = array_values(array_filter($properties, fn($p) => $p['is_premium']));

            echo json_encode([
                'success' => true,
                'data' => [
                    'properties' => $properties,
                    'premium_count' => count($premiumListings),
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => (int)ceil($total / $perPage),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] getMarketplace() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch marketplace']);
        }
    }

    /**
     * GET /api/v2/mobile/marketplace/premium
     * Get only premium/featured listings
     */
    public function getPremiumProperties()
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));

            $stmt = $pdo->prepare("
                SELECT up.id, up.user_id, up.property_type, up.listing_type, up.name as title,
                       up.description, up.price, up.address, up.city_name as city, up.location,
                       up.area_sqft, up.bedrooms, up.bathrooms,
                       up.is_featured, up.is_urgent, up.is_premium,
                       u.name as owner_name,
                       up.image as main_image
                FROM user_properties up
                LEFT JOIN users u ON u.id = up.user_id
                WHERE up.status = 'approved' AND (up.is_premium = 1 OR up.is_featured = 1 OR up.is_urgent = 1)
                ORDER BY up.is_premium DESC, up.is_featured DESC, up.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($properties as &$p) {
                $p['price'] = (float)($p['price'] ?? 0);
                $p['is_premium'] = (bool)$p['is_premium'];
                $p['is_featured'] = (bool)$p['is_featured'];
                $p['is_urgent'] = (bool)$p['is_urgent'];
                $p['image_url'] = $p['main_image'] ? (BASE_URL . '/' . ltrim($p['main_image'], '/')) : null;
            }

            echo json_encode([
                'success' => true,
                'data' => $properties,
                'count' => count($properties)
            ]);
        } catch (\Exception $e) {
            error_log("[MobileApiController] getPremiumProperties() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch premium listings']);
        }
    }

    /**
     * GET /api/mobile/v2/properties/{id}
     * Property detail with images, features, nearby facilities
     */
    public function propertyDetail($id)
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $id = (int) $id;

            $stmt = $pdo->prepare("
                SELECT p.*, pt.type as property_type_name
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.id = ? AND p.status = 'active'
            ");
            $stmt->execute([$id]);
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$property) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Property not found', 'code' => 404]);
                return;
            }

            // Get images
            $imgStmt = $pdo->prepare("
                SELECT id, image_path, is_primary, sort_order
                FROM property_images
                WHERE property_id = ?
                ORDER BY is_primary DESC, sort_order ASC
            ");
            $imgStmt->execute([$id]);
            $property['images'] = $imgStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get features (table may not exist in all deployments)
            $property['features'] = [];
            try {
                $featStmt = $pdo->prepare("
                    SELECT feature_name, feature_value, feature_category
                    FROM property_features
                    WHERE property_id = ?
                    ORDER BY feature_category, feature_name
                ");
                $featStmt->execute([$id]);
                $property['features'] = $featStmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $fe) {
                // property_features table not present — skip gracefully
                error_log('propertyDetail features skipped: ' . $fe->getMessage());
            }

            // Get similar properties (same type, excluding self)
            $similarStmt = $pdo->prepare("
                SELECT p.id, p.title, p.price, p.type, p.city, p.area_sqft,
                       (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC LIMIT 1) as main_image
                FROM properties p
                WHERE p.type = ? AND p.status = 'active' AND p.id != ?
                ORDER BY RAND()
                LIMIT 4
            ");
            $similarStmt->execute([$property['type'], $id]);
            $property['similar_properties'] = $similarStmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $property,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::propertyDetail() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch property details', 'code' => 500]);
        }
    }

    /**
     * GET /api/mobile/v2/properties/search?q=
     * Search properties by keyword
     */
    public function searchProperties()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        $query = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(max(1, (int) ($_GET['per_page'] ?? 20)), 50);
        $offset = ($page - 1) * $perPage;

        if (strlen($query) < 2) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Search query must be at least 2 characters', 'code' => 400]);
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $like = '%' . $query . '%';

            $countStmt = $pdo->prepare("
                SELECT COUNT(*) FROM properties p
                WHERE p.status = 'active'
                  AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.city LIKE ? OR p.type LIKE ?)
            ");
            $countStmt->execute([$like, $like, $like, $like, $like]);
            $total = (int) $countStmt->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT p.id, p.title, p.price, p.type, p.location, p.city, p.state,
                       p.bedrooms, p.bathrooms, p.area_sqft, p.featured, p.created_at,
                       (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM properties p
                WHERE p.status = 'active'
                  AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.city LIKE ? OR p.type LIKE ?)
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}
            ");
            $stmt->execute([$like, $like, $like, $like, $like]);
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $properties,
                'query' => $query,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $perPage),
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::searchProperties() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Search failed', 'code' => 500]);
        }
    }

    // ============================================================
    // BOOKING ENDPOINTS (Customer bookings with EMI)
    // ============================================================

    /**
     * GET /api/mobile/v2/bookings
     * User's bookings list
     */
    public function listBookings()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM plot_bookings WHERE customer_id = ?");
            $countStmt->execute([$userId]);
            $total = (int) $countStmt->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT pb.id, pb.booking_number, pb.booking_date, pb.total_plot_value,
                       pb.booking_amount, pb.status, pb.channel, pb.created_at,
                       p.plot_code as plot_title, p.total_price as plot_price
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                WHERE pb.customer_id = ?
                ORDER BY pb.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}
            ");
            $stmt->execute([$userId]);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => [
                    'bookings' => $bookings,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => (int) ceil($total / $perPage),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::listBookings() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch bookings', 'code' => 500]);
        }
    }

    /**
     * GET /api/mobile/v2/bookings/{id}
     * Booking detail with EMI schedule
     */
    public function bookingDetail($id)
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];
        $id = (int) $id;

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $stmt = $pdo->prepare("
                SELECT pb.*,
                       p.plot_code as plot_title, p.total_price as plot_price
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                WHERE pb.id = ? AND pb.customer_id = ?
            ");
            $stmt->execute([$id, $userId]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Booking not found', 'code' => 404]);
                return;
            }

            // Get EMI schedule
            $emiStmt = $pdo->prepare("
                SELECT id, installment_no, due_date, amount, principal, interest,
                       opening_balance, closing_balance, status, paid_date, paid_amount, late_fee
                FROM booking_payment_schedules
                WHERE booking_id = ?
                ORDER BY installment_no ASC
            ");
            $emiStmt->execute([$id]);
            $booking['emi_schedule'] = $emiStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get payment receipts
            $receiptStmt = $pdo->prepare("
                SELECT id, receipt_number, receipt_date, amount, payment_mode, status, transaction_ref
                FROM booking_payment_receipts
                WHERE booking_id = ?
                ORDER BY created_at DESC
            ");
            $receiptStmt->execute([$id]);
            $booking['receipts'] = $receiptStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Calculate totals
            $totalPaid = 0;
            $totalPending = 0;
            foreach ($booking['emi_schedule'] as $emi) {
                $totalPaid += (float) ($emi['paid_amount'] ?? 0);
                if ($emi['status'] === 'pending' || $emi['status'] === 'overdue') {
                    $totalPending += (float) $emi['amount'];
                }
            }
            $booking['summary'] = [
                'total_value' => (float) $booking['total_plot_value'],
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
                'completion_pct' => $booking['total_plot_value'] > 0
                    ? round(($totalPaid / $booking['total_plot_value']) * 100, 1)
                    : 0,
            ];

            echo json_encode([
                'success' => true,
                'data' => $booking,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::bookingDetail() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch booking details', 'code' => 500]);
        }
    }

    /**
     * POST /api/mobile/v2/bookings/{id}/pay
     * Record a payment for a booking installment
     * Body: { "installment_id": N, "amount": N, "method": "upi|cash|neft|card|cheque", "transaction_ref": "..." }
     */
    public function recordBookingPayment($id)
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];
        $id = (int) $id;

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $installmentId = (int) ($data['installment_id'] ?? 0);
        $amount = (float) ($data['amount'] ?? 0);
        $method = strtolower(trim((string) ($data['method'] ?? 'cash')));
        $transactionRef = trim((string) ($data['transaction_ref'] ?? ''));

        if ($installmentId <= 0 || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'installment_id and positive amount required', 'code' => 400]);
            return;
        }

        $validMethods = ['cash', 'cheque', 'dd', 'neft', 'rtgs', 'upi', 'card', 'bank_transfer'];
        if (!in_array($method, $validMethods, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid payment method. Allowed: ' . implode(', ', $validMethods), 'code' => 400]);
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $pdo->beginTransaction();

            // Verify booking ownership and get installment details
            $stmt = $pdo->prepare("
                SELECT bps.*, pb.id as booking_id
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON bps.booking_id = pb.id
                WHERE bps.id = ? AND pb.id = ? AND pb.customer_id = ?
                FOR UPDATE
            ");
            $stmt->execute([$installmentId, $id, $userId]);
            $installment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$installment) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Booking or installment not found', 'code' => 404]);
                return;
            }

            if ($installment['status'] === 'paid') {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Installment already paid', 'code' => 400]);
                return;
            }

            if ($amount > (float) $installment['amount']) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Payment amount exceeds installment amount of ₹' . $installment['amount'], 'code' => 400]);
                return;
            }

            // Generate receipt number
            $receiptNumber = 'APS-RCP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

            // Insert receipt
            $receiptStmt = $pdo->prepare("
                INSERT INTO booking_payment_receipts
                    (booking_id, installment_id, receipt_number, receipt_date, amount, payment_mode, transaction_ref, status, created_at)
                VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 'cleared', NOW())
            ");
            $receiptStmt->execute([
                $installment['booking_id'],
                $installmentId,
                $receiptNumber,
                $amount,
                $method,
                $transactionRef ?: null,
            ]);

            // Update installment
            $newPaidAmount = (float) $installment['paid_amount'] + $amount;
            $newStatus = $newPaidAmount >= (float) $installment['amount'] ? 'paid' : 'partial';
            $paidDate = $newStatus === 'paid' ? date('Y-m-d') : null;

            $updateStmt = $pdo->prepare("
                UPDATE booking_payment_schedules
                SET paid_amount = ?, paid_date = COALESCE(?, paid_date), status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$newPaidAmount, $paidDate, $newStatus, $installmentId]);

            // Check if all installments are paid → advance booking status
            $checkStmt = $pdo->prepare("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count
                FROM booking_payment_schedules
                WHERE booking_id = ?
            ");
            $checkStmt->execute([$installment['booking_id']]);
            $check = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($check && $check['total'] == $check['paid_count']) {
                $pdo->prepare("UPDATE plot_bookings SET status = 'fully_paid', updated_at = NOW() WHERE id = ?")
                    ->execute([$installment['booking_id']]);
            } elseif ($newPaidAmount > 0) {
                $pdo->prepare("UPDATE plot_bookings SET status = 'partially_paid', updated_at = NOW() WHERE id = ? AND status = 'emi_active'")
                    ->execute([$installment['booking_id']]);
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'data' => [
                    'receipt_number' => $receiptNumber,
                    'amount_paid' => $amount,
                    'payment_method' => $method,
                    'installment_status' => $newStatus,
                    'total_paid_on_installment' => $newPaidAmount,
                ],
                'message' => 'Payment recorded successfully',
            ]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('MobileApiController::recordBookingPayment() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Payment recording failed', 'code' => 500]);
        }
    }

    // ============================================================
    // INQUIRY ENDPOINTS
    // ============================================================

    /**
     * POST /api/mobile/v2/inquiries
     * Submit a property inquiry
     * Body: { "property_id": N, "name": "...", "email": "...", "phone": "...", "message": "...", "type": "property|project|general" }
     */
    public function submitInquiryV2()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        $propertyId = isset($data['property_id']) ? (int) $data['property_id'] : null;
        $projectId = isset($data['project_id']) ? (int) $data['project_id'] : null;
        $type = strtolower(trim((string) ($data['type'] ?? 'property')));

        if ($name === '' || $phone === '' || $message === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'name, phone, and message are required', 'code' => 400]);
            return;
        }

        if (!in_array($type, ['property', 'project', 'general'], true)) {
            $type = 'general';
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // Auto-fill email from user profile if not provided
            if ($email === '') {
                $uStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $uStmt->execute([$userId]);
                $email = $uStmt->fetchColumn() ?? '';
            }

            $stmt = $pdo->prepare("
                INSERT INTO inquiries (user_id, name, email, phone, message, property_id, project_id, type, status, priority, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'medium', NOW())
            ");
            $stmt->execute([$userId, $name, $email, $phone, $message, $propertyId, $projectId, $type]);
            $inquiryId = (int) $pdo->lastInsertId();

            // Auto-wire to CRM lead
            try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$message,'type'=>$type,'created_by'=>$userId]); } catch (\Exception $e3) {}

            echo json_encode([
                'success' => true,
                'data' => [
                    'inquiry_id' => $inquiryId,
                    'type' => $type,
                    'status' => 'pending',
                ],
                'message' => 'Inquiry submitted successfully',
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::submitInquiryV2() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to submit inquiry', 'code' => 500]);
        }
    }

    /**
     * GET /api/mobile/v2/inquiries
     * User's inquiries list
     */
    public function listInquiries()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inquiries WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $total = (int) $countStmt->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT i.id, i.name, i.email, i.phone, i.message, i.type, i.status,
                       i.priority, i.created_at, i.updated_at,
                       p.title as property_title
                FROM inquiries i
                LEFT JOIN properties p ON i.property_id = p.id
                WHERE i.user_id = ?
                ORDER BY i.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}
            ");
            $stmt->execute([$userId]);
            $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => [
                    'inquiries' => $inquiries,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => (int) ceil($total / $perPage),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::listInquiries() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch inquiries', 'code' => 500]);
        }
    }

    // ============================================================
    // USER PROFILE ENDPOINTS
    // ============================================================

    /**
     * PUT /api/mobile/v2/profile
     * Update user profile
     * Body: { "name": "...", "phone": "...", "email": "..." }
     */
    public function updateProfileV2()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];

        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $name = trim((string) ($data['name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));

        if ($name === '' && $phone === '' && $email === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'At least one field (name, phone, email) is required', 'code' => 400]);
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // Check email uniqueness if changing
            if ($email !== '') {
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
                $check->execute([$email, $userId]);
                if ($check->fetch()) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'error' => 'Email already in use', 'code' => 409]);
                    return;
                }
            }

            $updates = [];
            $params = [];
            if ($name !== '') {
                $updates[] = "name = ?";
                $params[] = $name;
            }
            if ($phone !== '') {
                $updates[] = "phone = ?";
                $params[] = $phone;
            }
            if ($email !== '') {
                $updates[] = "email = ?";
                $params[] = $email;
            }

            if (empty($updates)) {
                echo json_encode(['success' => true, 'message' => 'No changes to update']);
                return;
            }

            $updates[] = "updated_at = NOW()";
            $params[] = $userId;

            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Return updated profile
            $fetchStmt = $pdo->prepare("SELECT id, name, email, phone, role, created_at, updated_at FROM users WHERE id = ?");
            $fetchStmt->execute([$userId]);
            $user = $fetchStmt->fetch(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $user,
                'message' => 'Profile updated successfully',
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::updateProfileV2() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Profile update failed', 'code' => 500]);
        }
    }

    /**
     * POST /api/v2/mobile/user/profile/avatar
     * Upload/update user profile avatar (multipart: avatar field)
     */
    public function uploadAvatar()
    {
        $this->setCorsHeaders();
        $userId = (int) $GLOBALS['api_user_id'];
    
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Avatar file is required', 'code' => 400]);
            return;
        }
    
        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed), 'code' => 400]);
            return;
        }
    
        if ($file['size'] > 2 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File too large. Max 2MB', 'code' => 400]);
            return;
        }
    
        try {
            $uploadDir = __DIR__ . '/../../../../public/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
    
            $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . $filename;
    
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to save file', 'code' => 500]);
                return;
            }
    
            $avatarUrl = '/uploads/avatars/' . $filename;
    
            $stmt = $this->db->prepare("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$avatarUrl, $userId]);
    
            echo json_encode([
                'success' => true,
                'data' => ['avatar' => $avatarUrl],
                'message' => 'Avatar updated successfully',
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::uploadAvatar() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Avatar upload failed', 'code' => 500]);
        }
    }

    /**
     * GET /api/mobile/v2/dashboard
     * Enhanced dashboard stats (properties, bookings, inquiries, wallet)
     */
    public function dashboardV3()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];

        $stats = [
            'properties' => ['total' => 0, 'active' => 0],
            'bookings' => ['total' => 0, 'active' => 0, 'total_value' => 0],
            'inquiries' => ['total' => 0, 'pending' => 0],
            'wallet' => ['balance' => 0, 'mlm_points' => 0],
            'notifications' => ['unread' => 0],
        ];

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // User properties
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active FROM user_properties WHERE user_id = ?");
                $stmt->execute([$userId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stats['properties'] = [
                    'total' => (int) ($row['total'] ?? 0),
                    'active' => (int) ($row['active'] ?? 0),
                ];
            } catch (\Throwable $e) { /* table may not exist */ }

            // Bookings
            try {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as total,
                           SUM(CASE WHEN status IN ('token_paid','agreement_signed','emi_active','partially_paid') THEN 1 ELSE 0 END) as active,
                           COALESCE(SUM(total_plot_value), 0) as total_value
                    FROM plot_bookings WHERE customer_id = ?
                ");
                $stmt->execute([$userId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stats['bookings'] = [
                    'total' => (int) ($row['total'] ?? 0),
                    'active' => (int) ($row['active'] ?? 0),
                    'total_value' => (float) ($row['total_value'] ?? 0),
                ];
            } catch (\Throwable $e) { /* table may not exist */ }

            // Inquiries
            try {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as total,
                           SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                    FROM inquiries WHERE user_id = ?
                ");
                $stmt->execute([$userId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stats['inquiries'] = [
                    'total' => (int) ($row['total'] ?? 0),
                    'pending' => (int) ($row['pending'] ?? 0),
                ];
            } catch (\Throwable $e) { /* table may not exist */ }

            // Wallet
            try {
                $stmt = $pdo->prepare("SELECT wallet_balance, mlm_points FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $stats['wallet'] = [
                        'balance' => (float) ($row['wallet_balance'] ?? 0),
                        'mlm_points' => (int) ($row['mlm_points'] ?? 0),
                    ];
                }
            } catch (\Throwable $e) { /* column may not exist */ }

            // Notifications
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $stmt->execute([$userId]);
                $stats['notifications'] = ['unread' => (int) $stmt->fetchColumn()];
            } catch (\Throwable $e) { /* table may not exist */ }

            echo json_encode([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::dashboardV3() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Dashboard fetch failed', 'code' => 500]);
        }
    }

    // ================================================================
    // Attendance API — geo-fenced punch in/out
    // ================================================================

    /** Office coordinates (Kunraghat) */
    private $officeLat = 26.8402;
    private $officeLng = 83.3012;
    private $allowedRadius = 100; // metres

    /**
     * POST /api/attendance/punch-in
     * Body: { "latitude": 26.84, "longitude": 83.30 }
     */
    public function punchIn()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $lat = (float) ($_POST['latitude'] ?? 0);
            $lng = (float) ($_POST['longitude'] ?? 0);

            if ($lat == 0 || $lng == 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Latitude and longitude are required']);
                return;
            }

            $distance = $this->haversineDistance($lat, $lng, $this->officeLat, $this->officeLng);
            if ($distance > $this->allowedRadius) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'You are outside the office geofence',
                    'distance_meters' => round($distance, 1),
                    'allowed_meters' => $this->allowedRadius,
                ]);
                return;
            }

            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // Check if already punched in today without punching out
            $today = date('Y-m-d');
            $stmt = $pdo->prepare(
                "SELECT id FROM employee_attendance WHERE employee_id = ? AND attendance_date = ? AND check_out_time IS NULL LIMIT 1"
            );
            $stmt->execute([$userId, $today]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Already punched in today. Punch out first.']);
                return;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO employee_attendance (employee_id, attendance_date, check_in_time, status)
                 VALUES (?, CURDATE(), NOW(), 'present')"
            );
            $stmt->execute([$userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Punched in successfully',
                'punch_in_time' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::punchIn() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Punch-in failed']);
        }
    }

    /**
     * POST /api/attendance/punch-out
     * Body: { "latitude": 26.84, "longitude": 83.30 }
     */
    public function punchOut()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $lat = (float) ($_POST['latitude'] ?? 0);
            $lng = (float) ($_POST['longitude'] ?? 0);

            if ($lat == 0 || $lng == 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Latitude and longitude are required']);
                return;
            }

            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $today = date('Y-m-d');
            $stmt = $pdo->prepare(
                "SELECT id, check_in_time FROM employee_attendance WHERE employee_id = ? AND attendance_date = ? AND check_out_time IS NULL LIMIT 1"
            );
            $stmt->execute([$userId, $today]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'No active punch-in found for today']);
                return;
            }

            $inTime = new \DateTime($row['check_in_time']);
            $now = new \DateTime();
            $hoursWorked = round($now->diff($inTime)->h + $now->diff($inTime)->i / 60, 2);

            $stmt = $pdo->prepare(
                "UPDATE employee_attendance SET check_out_time = NOW(), hours_worked = ? WHERE id = ?"
            );
            $stmt->execute([$hoursWorked, $row['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Punched out successfully',
                'punch_out_time' => date('Y-m-d H:i:s'),
                'hours_worked' => $hoursWorked,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::punchOut() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Punch-out failed']);
        }
    }

    /**
     * GET /api/attendance/status
     * Returns today's attendance record for the authenticated user.
     */
    public function attendanceStatus()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $today = date('Y-m-d');
            $stmt = $pdo->prepare(
                "SELECT check_in_time as punch_in_time, check_out_time as punch_out_time, status, hours_worked
                 FROM employee_attendance WHERE employee_id = ? AND attendance_date = ?
                 ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([$userId, $today]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $record ? [
                    'punched_in' => true,
                    'punch_in_time' => $record['punch_in_time'],
                    'punched_out' => !empty($record['punch_out_time']),
                    'punch_out_time' => $record['punch_out_time'] ?? null,
                    'hours_worked' => $record['hours_worked'] ?? null,
                    'status' => $record['status'],
                ] : [
                    'punched_in' => false,
                    'punched_out' => false,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::attendanceStatus() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not fetch attendance status']);
        }
    }

    // ================================================================
    // Employee Dashboard API (mobile v2)
    // ================================================================

    /**
     * GET /api/mobile/v2/employee/dashboard
     * Aggregated employee dashboard: tasks summary, today attendance, recent announcements
     */
    public function employeeDashboard()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];

        $data = [
            'tasks' => ['pending' => 0, 'completed_today' => 0, 'overdue' => 0],
            'attendance' => ['punched_in' => false, 'punched_out' => false, 'hours_worked' => null, 'punch_in_time' => null, 'punch_out_time' => null, 'distance_meters' => null],
            'announcements' => [],
        ];

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // Tasks summary (from lead_pipeline assigned to this employee)
            try {
                $stmt = $pdo->prepare(
                    "SELECT
                        SUM(CASE WHEN status NOT IN ('closed_won','closed_lost') THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'closed_won' AND DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as completed_today,
                        SUM(CASE WHEN status NOT IN ('closed_won','closed_lost') AND follow_up_date < CURDATE() THEN 1 ELSE 0 END) as overdue
                     FROM lead_pipeline WHERE assigned_to = ?"
                );
                $stmt->execute([$userId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $data['tasks'] = [
                        'pending' => (int) ($row['pending'] ?? 0),
                        'completed_today' => (int) ($row['completed_today'] ?? 0),
                        'overdue' => (int) ($row['overdue'] ?? 0),
                    ];
                }
            } catch (\Throwable $e) { /* table may not exist */ }

            // Today's attendance
            try {
                $today = date('Y-m-d');
                $stmt = $pdo->prepare(
                    "SELECT check_in_time as punch_in_time, check_out_time as punch_out_time, status, hours_worked
                     FROM employee_attendance WHERE employee_id = ? AND attendance_date = ?
                     ORDER BY id DESC LIMIT 1"
                );
                $stmt->execute([$userId, $today]);
                $record = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($record) {
                    $data['attendance'] = [
                        'punched_in' => true,
                        'punch_in_time' => $record['punch_in_time'],
                        'punched_out' => !empty($record['punch_out_time']),
                        'punch_out_time' => $record['punch_out_time'] ?? null,
                        'hours_worked' => $record['hours_worked'] ?? null,
                        'status' => $record['status'],
                    ];
                }
            } catch (\Throwable $e) { /* table may not exist */ }

            // Recent announcements (from daily_operations_log, last 5)
            try {
                $stmt = $pdo->prepare(
                    "SELECT id, operation_type as title, notes as subtitle, created_at
                     FROM daily_operations_log
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                     ORDER BY created_at DESC LIMIT 5"
                );
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $r) {
                    $created = new \DateTime($r['created_at']);
                    $diff = $created->diff(new \DateTime());
                    $timeAgo = '';
                    if ($diff->d > 0) $timeAgo = $diff->d . 'd ago';
                    elseif ($diff->h > 0) $timeAgo = $diff->h . 'h ago';
                    else $timeAgo = max(1, $diff->i) . 'm ago';
                    $data['announcements'][] = [
                        'title' => $r['title'] ?? 'Update',
                        'subtitle' => $r['subtitle'] ?? '',
                        'time' => $timeAgo,
                    ];
                }
            } catch (\Throwable $e) { /* table may not exist */ }

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::employeeDashboard() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not fetch employee dashboard']);
        }
    }

    /**
     * GET /api/mobile/v2/employee/tasks
     * Task list for the authenticated employee
     */
    public function employeeTasks()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];
        $status = $_GET['status'] ?? null;
        $limit = min((int) ($_GET['limit'] ?? 20), 50);

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $where = "lp.assigned_to = ? AND lp.deleted_at IS NULL";
            $params = [$userId];

            if ($status && $status !== 'all') {
                $where .= " AND lp.status = ?";
                $params[] = $status;
            }

            $stmt = $pdo->prepare(
                "SELECT lp.id, lp.lead_number, lp.name as lead_name, lp.status, lp.priority,
                        lp.next_activity_date as next_followup_date, lp.lead_score, lp.source, lp.created_at
                 FROM leads lp
                 WHERE {$where}
                 ORDER BY
                    CASE lp.priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 WHEN 'low' THEN 2 ELSE 3 END,
                    COALESCE(lp.next_activity_date, '9999-12-31') ASC
                 LIMIT {$limit}"
            );
            $stmt->execute($params);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => ['tasks' => $tasks]]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::employeeTasks() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not fetch tasks: ' . $e->getMessage()]);
        }
    }

    /**
     * GET /api/mobile/v2/employee/attendance
     * Attendance history for the authenticated employee (last 30 days)
     */
    public function employeeAttendance()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();
        $userId = (int) $GLOBALS['api_user_id'];
        $days = min((int) ($_GET['days'] ?? 30), 90);

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $stmt = $pdo->prepare(
                "SELECT DATE(check_in_time) as date, check_in_time as punch_in_time, check_out_time as punch_out_time,
                        hours_worked, status
                 FROM employee_attendance
                 WHERE employee_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 ORDER BY check_in_time DESC"
            );
            $stmt->execute([$userId, $days]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Summary stats
            $totalDays = count($records);
            $presentDays = 0;
            $totalHours = 0.0;
            $lateDays = 0;

            foreach ($records as $r) {
                if ($r['status'] === 'present' || $r['status'] === 'late') {
                    $presentDays++;
                }
                $totalHours += (float) ($r['hours_worked'] ?? 0);
                if ($r['status'] === 'late') {
                    $lateDays++;
                }
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'records' => $records,
                    'summary' => [
                        'total_days' => $totalDays,
                        'present_days' => $presentDays,
                        'total_hours' => round($totalHours, 1),
                        'late_days' => $lateDays,
                        'avg_hours' => $presentDays > 0 ? round($totalHours / $presentDays, 1) : 0,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::employeeAttendance() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not fetch attendance history']);
        }
    }

    /**
     * Haversine formula — returns distance in metres between two lat/lng points.
     */
    private function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // metres
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    // ================================================================
    // Referral Tracking API
    // ================================================================

    /**
     * POST /api/referral/track
     * Body: { "referral_code": "ABC123", "property_id": 42, "source": "whatsapp" }
     */
    public function trackReferral()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $referrerUserId = (int) $GLOBALS['api_user_id'];
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;

            $referralCode = trim($data['referral_code'] ?? '');
            $propertyId = (int) ($data['property_id'] ?? 0);
            $source = trim($data['source'] ?? 'whatsapp');

            if (empty($referralCode)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'referral_code is required']);
                return;
            }

            $validSources = ['whatsapp', 'sms', 'email', 'direct', 'social'];
            if (!in_array($source, $validSources)) {
                $source = 'direct';
            }

            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            // Validate referral code exists and belongs to a different user
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
            $stmt->execute([$referrerUserId]);
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$referrer) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Referrer not found']);
                return;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO customer_referrals
                    (referrer_user_id, referral_code, property_id, source, status, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, 'pending', ?, ?, NOW())"
            );
            $stmt->execute([
                $referrerUserId,
                $referralCode,
                $propertyId ?: null,
                $source,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Referral tracked',
                'referral_id' => (int) $pdo->lastInsertId(),
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::trackReferral() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Referral tracking failed']);
        }
    }

    /**
     * GET /api/v2/mobile/user/favorites
     * Get user's favorite properties
     */
    public function getFavorites()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $limit = (int) ($_GET['limit'] ?? 50);

            $favoriteModel = new \App\Models\PropertyFavorite();
            $favorites = $favoriteModel->getUserFavorites($userId, $limit);

            // Transform to match Flutter expectations
            $data = [];
            foreach ($favorites as $fav) {
                $data[] = [
                    'id' => $fav['id'] ?? 0,
                    'property_id' => $fav['property_id'] ?? $fav['id'],
                    'title' => $fav['title'] ?? 'Property',
                    'type' => $fav['type'] ?? 'plot',
                    'price' => (float)($fav['price'] ?? 0),
                    'size' => (float)($fav['area_sqft'] ?? 0),
                    'area' => $fav['city'] ?? '',
                    'image_url' => $fav['main_image'] ?? '',
                    'location' => $fav['city'] ?? '',
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::getFavorites() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch favorites']);
        }
    }

    /**
     * DELETE /api/v2/mobile/user/favorites/{id}
     * Remove property from favorites
     */
    public function removeFavorite($id = null)
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $propertyId = (int) ($id ?? $_GET['id'] ?? $_GET['property_id'] ?? 0);

            if ($propertyId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID required']);
                return;
            }

            $favoriteModel = new \App\Models\PropertyFavorite();
            $result = $favoriteModel->removeFavorite($userId, $propertyId);

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Removed from favorites' : 'Failed to remove',
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::removeFavorite() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to remove favorite']);
        }
    }

    /**
     * POST /api/v2/mobile/user/favorites
     * Add property to favorites
     */
    public function addFavorite()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $propertyId = (int) ($_POST['property_id'] ?? 0);
            if ($propertyId <= 0) {
                $body = json_decode(file_get_contents('php://input'), true);
                $propertyId = (int) ($body['property_id'] ?? 0);
            }

            if ($propertyId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID required']);
                return;
            }

            $favoriteModel = new \App\Models\PropertyFavorite();
            $result = $favoriteModel->addFavorite($userId, $propertyId);

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Added to favorites' : 'Already in favorites',
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::addFavorite() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to add favorite']);
        }
    }

    /**
     * GET /api/v2/mobile/user/favorites/check
     * Check if property is favorited
     */
    public function checkFavorite()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $propertyId = (int) ($_GET['property_id'] ?? 0);

            if ($propertyId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID required']);
                return;
            }

            $favoriteModel = new \App\Models\PropertyFavorite();
            $isFavorited = $favoriteModel->isFavorited($userId, $propertyId);

            echo json_encode([
                'success' => true,
                'data' => ['is_favorited' => $isFavorited],
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::checkFavorite() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to check favorite']);
        }
    }

    /**
     * GET /api/v2/mobile/user/favorites/stats
     * Get favorites statistics
     */
    public function getFavoritesStats()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $favoriteModel = new \App\Models\PropertyFavorite();
            $stats = $favoriteModel->getStats();

            // Also get user-specific stats
            $userFavorites = $favoriteModel->getUserFavorites($userId, 1000);
            $stats['user_total'] = count($userFavorites);

            echo json_encode([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            error_log('MobileApiController::getFavoritesStats() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch stats']);
        }
    }

    /**
     * GET /api/v2/mobile/colonies
     * List all active colonies with summary stats
     */
    public function getColonies()
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = trim($_GET['search'] ?? '');
            $status = trim($_GET['status'] ?? '');
            $districtId = (int)($_GET['district_id'] ?? 0);

            $where = ['1=1'];
            $params = [];

            if ($search !== '') {
                $where[] = 'c.name LIKE ?';
                $params[] = "%$search%";
            }
            if ($status !== '') {
                $where[] = 'c.is_active = ?';
                $params[] = $status === 'active' ? 1 : 0;
            }
            if ($districtId > 0) {
                $where[] = 'c.district_id = ?';
                $params[] = $districtId;
            }

            $whereSql = implode(' AND ', $where);

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM colonies c WHERE $whereSql");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT c.id, c.name, c.slug, c.description, c.total_plots, c.available_plots,
                       c.starting_price, c.image_path, c.is_featured, c.is_active,
                       d.name as district_name, d.id as district_id
                FROM colonies c
                LEFT JOIN districts d ON d.id = c.district_id
                WHERE $whereSql
                ORDER BY c.is_featured DESC, c.name ASC
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            $colonies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($colonies as &$colony) {
                $colony['total_plots'] = (int)($colony['total_plots'] ?? 0);
                $colony['available_plots'] = (int)($colony['available_plots'] ?? 0);
                $colony['starting_price'] = (float)($colony['starting_price'] ?? 0);
                $colony['is_featured'] = (bool)($colony['is_featured'] ?? false);
                $colony['is_active'] = (bool)($colony['is_active'] ?? false);
                $colony['image_url'] = $colony['image_path'] ? BASE_URL . '/' . ltrim($colony['image_path'], '/') : null;
            }

            echo json_encode([
                'success' => true,
                'data' => $colonies,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => (int)ceil($total / $limit),
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('getColonies error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch colonies']);
        }
    }

    /**
     * GET /api/v2/mobile/colonies/search?q=...
     */
    public function searchColonies()
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $q = trim($_GET['q'] ?? $_GET['search'] ?? '');
            if ($q === '') {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }
            $stmt = $pdo->prepare("
                SELECT c.id, c.name, c.slug, c.image_path, c.total_plots, c.available_plots, c.starting_price
                FROM colonies c
                WHERE c.is_active = 1 AND c.name LIKE ?
                ORDER BY c.name ASC LIMIT 20
            ");
            $stmt->execute(["%$q%"]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (\Throwable $e) {
            error_log('searchColonies error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    /**
     * GET /api/v2/mobile/colonies/{id}
     */
    public function getColonyDetail($id)
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT c.*, d.name as district_name
                FROM colonies c
                LEFT JOIN districts d ON d.id = c.district_id
                WHERE c.id = ?
            ");
            $stmt->execute([(int)$id]);
            $colony = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$colony) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Colony not found']);
                return;
            }
            $colony['image_url'] = $colony['image_path'] ? BASE_URL . '/' . ltrim($colony['image_path'], '/') : null;
            echo json_encode(['success' => true, 'data' => $colony]);
        } catch (\Throwable $e) {
            error_log('getColonyDetail error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch colony']);
        }
    }

    /**
     * GET /api/v2/mobile/colonies/{id}/stats
     */
    public function getColonyStats($id)
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $total = $pdo->prepare("SELECT COUNT(*) FROM plots WHERE colony_id = ?");
            $total->execute([(int)$id]);
            $totalPlots = (int)$total->fetchColumn();

            $available = $pdo->prepare("SELECT COUNT(*) FROM plots WHERE colony_id = ? AND status = 'available'");
            $available->execute([(int)$id]);
            $availablePlots = (int)$available->fetchColumn();

            $booked = $pdo->prepare("SELECT COUNT(*) FROM plots WHERE colony_id = ? AND status = 'booked'");
            $booked->execute([(int)$id]);
            $bookedPlots = (int)$booked->fetchColumn();

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_plots' => $totalPlots,
                    'available_plots' => $availablePlots,
                    'booked_plots' => $bookedPlots,
                    'sold_plots' => $totalPlots - $availablePlots - $bookedPlots,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('getColonyStats error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => ['total_plots' => 0, 'available_plots' => 0, 'booked_plots' => 0, 'sold_plots' => 0]]);
        }
    }

    /**
     * GET /api/v2/mobile/colonies/{id}/plots
     */
    public function getColonyPlots($id)
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
            $offset = ($page - 1) * $limit;
            $status = trim($_GET['status'] ?? '');

            $where = ['p.colony_id = ?'];
            $params = [(int)$id];
            if ($status !== '') {
                $where[] = 'p.status = ?';
                $params[] = $status;
            }
            $whereSql = implode(' AND ', $where);

            $stmt = $pdo->prepare("
                SELECT p.id, p.plot_number, p.block, p.area_sqft, p.status,
                       p.total_price, p.price_per_sqft, p.facing, p.corner_plot, p.width_ft, p.length_ft
                FROM plots p WHERE $whereSql
                ORDER BY p.block, p.plot_number
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (\Throwable $e) {
            error_log('getColonyPlots error (colony_id=' . $id . '): ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to fetch plots', 'data' => []]);
        }
    }

    /**
     * GET /api/v2/mobile/colonies/{id}/health
     * Colony health score for mobile app
     */
    public function getColonyHealth($id)
    {
        $this->setCorsHeaders();
        try {
            $healthService = new \App\Services\Land\ColonyHealthService();
            $result = $healthService->getColonyHealth((int)$id);

            if (!$result['success']) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Colony not found']);
                return;
            }

            // Simplified response for mobile (strip heavy data)
            echo json_encode([
                'success' => true,
                'data' => [
                    'colony_id'     => $result['colony_id'],
                    'colony_name'   => $result['colony_name'],
                    'current_stage' => $result['current_stage'],
                    'overall_score' => $result['overall_score'],
                    'grade'         => $result['grade'],
                    'risks'         => array_map(function($r) {
                        return [
                            'level'   => $r['level'],
                            'message' => $r['message'],
                            'type'    => $r['type'] ?? '',
                        ];
                    }, $result['risks']),
                    'recommendations' => array_slice(array_map(function($r) {
                        return [
                            'priority' => $r['priority'],
                            'action'   => $r['action'],
                            'detail'   => $r['detail'],
                        ];
                    }, $result['recommendations']), 0, 3),
                    'stages' => array_map(function($s) {
                        return [
                            'key'    => $s['key'],
                            'label'  => $s['label'],
                            'status' => $s['status'],
                            'score'  => $s['score'],
                        ];
                    }, $result['stages']),
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('getColonyHealth error (colony_id=' . $id . '): ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch colony health']);
        }
    }

    /**
     * GET /api/v2/mobile/colonies/health/all
     * All colonies health overview for mobile app
     */
    public function getAllColoniesHealth()
    {
        $this->setCorsHeaders();
        try {
            $healthService = new \App\Services\Land\ColonyHealthService();
            $result = $healthService->getAllColoniesHealth();

            echo json_encode([
                'success'  => $result['success'],
                'colonies' => $result['colonies'] ?? [],
            ]);
        } catch (\Throwable $e) {
            error_log('getAllColoniesHealth error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'colonies' => []]);
        }
    }

    /**
     * POST /api/attendance/punch-in
     * Body: { "latitude": 26.84, "longitude": 83.30 }
     */

    /**
     * GET /api/v2/mobile/plots/{id}
     */
    public function getPlotDetail($id) {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as colony_name, c.id as colony_id, d.name as district_name
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                LEFT JOIN districts d ON d.id = c.district_id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $plot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$plot) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Plot not found']);
                return;
            }
            echo json_encode(['success' => true, 'data' => $plot]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/v2/mobile/plots/all — Public listing of all plots across all active colonies
     */
    public function getAllPlots() {
        $this->setCorsHeaders();
        try {
            $page = (int)(\App\Core\Security::sanitize($_GET['page'] ?? 1) ?? 1);
            $limit = min((int)(\App\Core\Security::sanitize($_GET['limit'] ?? 20) ?? 20), 100);
            $offset = ($page - 1) * $limit;
            $status = \App\Core\Security::sanitize($_GET['status'] ?? null) ?? null;
            $colonyId = \App\Core\Security::sanitize($_GET['colony_id'] ?? null) ?? null;

            $where = ['c.is_active = 1'];
            $params = [];
            if ($status) {
                $where[] = 'p.status = ?';
                $params[] = $status;
            }
            if ($colonyId) {
                $where[] = 'p.colony_id = ?';
                $params[] = $colonyId;
            }
            $whereClause = implode(' AND ', $where);

            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id WHERE {$whereClause}");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $params[] = $limit;
            $params[] = $offset;
            $stmt = $this->db->prepare("
                SELECT p.id, p.plot_number, p.colony_id, p.block, p.area_sqft, p.status, 
                       p.total_price, p.price_per_sqft, p.facing, p.corner_plot,
                       p.width_ft, p.length_ft,
                        c.name as colony_name, c.slug as colony_slug, d.name as district_name
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                LEFT JOIN districts d ON d.id = c.district_id
                WHERE {$whereClause}
                ORDER BY c.name, p.block, p.plot_number
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            $plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $plots,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => (int)ceil($total / $limit),
                ],
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/v2/mobile/plots/{id}/hold
     */
    public function holdPlot($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("SELECT status FROM plots WHERE id = ?");
            $stmt->execute([$id]);
            $plot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$plot) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Plot not found']);
                return;
            }
            if ($plot['status'] !== 'available') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Plot is not available']);
                return;
            }
            $stmt = $this->db->prepare("UPDATE plots SET status = 'hold', held_by = ?, held_at = NOW() WHERE id = ?");
            $stmt->execute([$userId, $id]);
            echo json_encode(['success' => true, 'message' => 'Plot held successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/v2/mobile/plots/{id}/release
     */
    public function releasePlot($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("UPDATE plots SET status = 'available', held_by = NULL, held_at = NULL WHERE id = ? AND held_by = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Plot released successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/v2/mobile/user/notifications
     */
    public function getCustomerNotifications() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("
                SELECT id, title, message, type, is_read, created_at 
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 50
            ");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $notifications]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    /**
     * POST /api/v2/mobile/user/notifications/read
     */
    public function markNotificationsRead() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'Notifications marked as read']);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'message' => 'Done']);
        }
    }

    /**
     * POST /api/v2/mobile/bookings
     */
    public function createBookingRequest() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $plotId = $input['plot_id'] ?? null;
        if (!$plotId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Plot ID is required']);
            return;
        }
        try {
            $stmt = $this->db->prepare("SELECT id, status, total_price, colony_id FROM plots WHERE id = ?");
            $stmt->execute([$plotId]);
            $plot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$plot) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Plot not found']);
                return;
            }
            if ($plot['status'] !== 'available') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Plot is not available']);
                return;
            }
            $stmt = $this->db->prepare("SELECT name, email, phone FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $bookingNumber = 'BK-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $bookingAmount = ($plot['total_price'] ?? 0) * 0.10;
            $stmt = $this->db->prepare("INSERT INTO plot_bookings (booking_number, plot_id, colony_id, customer_id, customer_name, customer_email, customer_phone, total_plot_value, booking_amount, status, channel, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'mobile_app', NOW())");
            $stmt->execute([$bookingNumber, $plotId, $plot['colony_id'], $userId, $user['name'] ?? '', $user['email'] ?? '', $user['phone'] ?? '', $plot['total_price'] ?? 0, $bookingAmount]);
            $bookingId = $this->db->lastInsertId();
            $stmt = $this->db->prepare("UPDATE plots SET status = 'booked' WHERE id = ?");
            $stmt->execute([$plotId]);
            echo json_encode(['success' => true, 'data' => ['booking_id' => $bookingId, 'booking_number' => $bookingNumber, 'plot_id' => $plotId, 'booking_amount' => $bookingAmount, 'total_plot_value' => $plot['total_price'] ?? 0, 'status' => 'pending']]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API — User Bank Accounts
    // ============================================================
    public function getUserBankAccounts() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT id, bank_name, account_holder_name, account_number, ifsc_code, branch_name, is_primary, created_at FROM user_bank_accounts WHERE user_id = ? ORDER BY is_primary DESC, created_at DESC");
            $stmt->execute([$userId]);
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($accounts as &$a) {
                $last4 = substr($a['account_number'], -4);
                $a['masked'] = 'XXXX' . $last4;
                $a['account_number'] = $last4;
            }
            echo json_encode(['success' => true, 'data' => $accounts]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function saveUserBankAccount() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $bankName = $input['bank_name'] ?? '';
        $accountHolder = $input['account_holder_name'] ?? $input['account_holder'] ?? '';
        $accountNumber = $input['account_number'] ?? '';
        $ifscCode = $input['ifsc_code'] ?? '';
        $branch = $input['branch_name'] ?? $input['branch'] ?? '';
        $isPrimary = !empty($input['is_primary']) ? 1 : 0;
        // Fallback: use user name as account holder if not provided
        $nameStmt = $this->db->prepare("SELECT name FROM users WHERE id = ?");
        $nameStmt->execute([$userId]);
        $accountHolder = $accountHolder ?: ($nameStmt->fetchColumn() ?: "Unknown");

        if (empty($bankName) || empty($accountNumber) || empty($ifscCode)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'All fields required']); return;
        }
        try {
            if ($isPrimary) {
                $this->db->prepare("UPDATE user_bank_accounts SET is_primary = 0 WHERE user_id = ?")->execute([$userId]);
            }
            if ($id) {
                $stmt = $this->db->prepare("UPDATE user_bank_accounts SET bank_name=?, account_holder_name=?, account_number=?, ifsc_code=?, branch_name=?, is_primary=? WHERE id=? AND user_id=?");
                $stmt->execute([$bankName, $accountHolder, $accountNumber, $ifscCode, $branch, $isPrimary, $id, $userId]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO user_bank_accounts (user_id, bank_name, account_holder_name, account_number, ifsc_code, branch_name, is_primary, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$userId, $bankName, $accountHolder, $accountNumber, $ifscCode, $branch, $isPrimary]);
                $id = $this->db->lastInsertId();
            }
            echo json_encode(['success' => true, 'data' => ['id' => $id]]);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function deleteUserBankAccount() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'ID required']); return; }
        try {
            $stmt = $this->db->prepare("DELETE FROM user_bank_accounts WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true]);
        }
    }

    // ============================================================
    // MOBILE API — User Addresses
    // ============================================================
    public function getUserAddresses() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT id, address_line1 AS address, address_line2, city, state, pincode, address_type AS type, label, is_primary, created_at FROM user_addresses WHERE user_id = ? ORDER BY is_primary DESC, created_at DESC");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function saveUserAddress() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $address = $input['address'] ?? '';
        $city = $input['city'] ?? '';
        $state = $input['state'] ?? '';
        $pincode = $input['pincode'] ?? '';
        $type = $input['type'] ?? 'home';
        $isPrimary = !empty($input['is_primary']) ? 1 : 0;
        if (empty($address) || empty($city)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Address and city required']); return;
        }
        try {
            if ($isPrimary) {
                $this->db->prepare("UPDATE user_addresses SET is_primary = 0 WHERE user_id = ?")->execute([$userId]);
            }
            $validTypes = ['home', 'office', 'billing', 'shipping', 'other'];
            $addressType = in_array($type, $validTypes, true) ? $type : 'home';
            $label = ucfirst($addressType);
            if ($id) {
                $stmt = $this->db->prepare("UPDATE user_addresses SET address_line1=?, city=?, state=?, pincode=?, address_type=?, label=?, is_primary=? WHERE id=? AND user_id=?");
                $stmt->execute([$address, $city, $state, $pincode, $addressType, $label, $isPrimary, $id, $userId]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO user_addresses (user_id, address_line1, city, state, pincode, address_type, label, is_primary, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$userId, $address, $city, $state, $pincode, $addressType, $label, $isPrimary]);
                $id = $this->db->lastInsertId();
            }
            echo json_encode(['success' => true, 'data' => ['id' => $id]]);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function deleteUserAddress() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'ID required']); return; }
        try {
            $stmt = $this->db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true]);
        }
    }

    // ============================================================
    // MOBILE API — Payment History
    // ============================================================
    public function getPaymentHistory() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT p.payment_id AS id, p.booking_id, b.booking_number, b.plot_id, p.payment_amount AS amount, p.payment_method, p.transaction_id, p.payment_notes, p.payment_date FROM booking_payments p JOIN plot_bookings b ON p.booking_id = b.id WHERE b.customer_id = ? ORDER BY p.payment_date DESC LIMIT 50");
            $stmt->execute([$userId]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stats = [
                'total_paid' => 0,
                'total_count' => count($payments),
                'completed' => 0,
                'pending' => 0,
                'failed' => 0,
            ];
            foreach ($payments as &$p) {
                $p['amount'] = (float)$p['amount'];
                // booking_payments has no status column; recorded payments are completed
                $p['status'] = 'completed';
                $stats['total_paid'] += $p['amount'];
                $stats['completed']++;
            }
            echo json_encode(['success' => true, 'data' => ['payments' => $payments, 'stats' => $stats]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => ['payments' => [], 'stats' => ['total_paid' => 0, 'total_count' => 0, 'completed' => 0, 'pending' => 0, 'failed' => 0]]]);
        }
    }

    // ============================================================
    // MOBILE API — Blog/News
    // ============================================================
    public function getBlogPosts() {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT id, title, slug, excerpt, featured_image, category, created_at as published_at, 'APS Dream Home' as author, 5 as reading_time FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 20");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($posts as &$post) {
                if (!empty($post['featured_image'])) {
                    $post['featured_image'] = (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($post['featured_image'], '/');
                }
            }
            echo json_encode(['success' => true, 'data' => $posts]);
        } catch (\Throwable $e) {
            error_log('getBlogPosts error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function getBlogPostDetail($slug) {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT id, title, slug, content, excerpt, featured_image, category, tags, views, created_at as published_at, 'APS Dream Home' as author, 5 as reading_time FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$post) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found']); return; }
            if (!empty($post['featured_image'])) {
                $post['featured_image'] = (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($post['featured_image'], '/');
            }
            echo json_encode(['success' => true, 'data' => $post]);
        } catch (\Throwable $e) {
            error_log('getBlogPostDetail error: ' . $e->getMessage());
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API — Careers/Jobs
    // ============================================================
    public function getJobListings() {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->query("SELECT id, title, department, location, employment_type, experience_required, salary_range, vacancies, description, requirements, created_at FROM careers WHERE status = 'open' ORDER BY created_at DESC");
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $jobs]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function getJobDetail($id) {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE id = ? AND status = 'open' LIMIT 1");
            $stmt->execute([$id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$job) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found']); return; }
            echo json_encode(['success' => true, 'data' => $job]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function submitJobApplication() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $jobId = $input['job_id'] ?? null;
        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $coverLetter = $input['cover_letter'] ?? '';
        $experience = $input['experience'] ?? 0;
        $company = $input['current_company'] ?? '';
        if (!$jobId || empty($name) || empty($email) || empty($phone)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Required fields missing']); return;
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO career_applications (career_id, full_name, email, phone, cover_letter, experience_years, current_company, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'new', NOW())");
            $stmt->execute([$jobId, $name, $email, $phone, $coverLetter, $experience, $company]);
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API — About Us & Team
    // ============================================================
    public function getAboutInfo() {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->query("SELECT content_key, content_value FROM site_content WHERE section = 'about' AND is_active = 1");
            $content = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $team = [];
            try {
                $stmt2 = $this->db->query("SELECT id, name, position, photo, bio, experience, expertise, linkedin, facebook_url, instagram_url, category, group_name, sort_order FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC LIMIT 10");
                $team = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}
            echo json_encode(['success' => true, 'data' => ['content' => $content, 'team' => $team, 'stats' => ['projects' => 4, 'plots' => 5000, 'families' => 500, 'colonies' => 4]]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => ['content' => [], 'team' => [], 'stats' => ['projects' => 4, 'plots' => 5000, 'families' => 500, 'colonies' => 4]]]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Auth (Password/OTP/Phone Check)
    // ============================================================
    public function forgotPassword() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        if (empty($email) && empty($phone)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Email or phone required']); return;
        }
        try {
            $field = !empty($email) ? 'email' : 'phone';
            $value = !empty($email) ? $email : $phone;
            $stmt = $this->db->prepare("SELECT id FROM users WHERE $field = ? LIMIT 1");
            $stmt->execute([$value]);
            if (!$stmt->fetch()) {
                echo json_encode(['success'=>true,'message'=>'If the account exists, reset instructions have been sent']);
                return;
            }
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $stmt = $this->db->prepare("INSERT INTO password_reset_tokens (email, phone, otp, token, expires_at, created_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE otp=?, expires_at=?, created_at=NOW()");
            $token = bin2hex(random_bytes(32));
            $stmt->execute([$email, $phone, $otp, $token, $expires, $otp, $expires]);
            echo json_encode(['success'=>true,'message'=>'OTP sent','otp'=>$otp,'token'=>$token]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function verifyOtp() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $otp = $input['otp'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        if (empty($otp) || (empty($email) && empty($phone))) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'OTP and email/phone required']); return;
        }
        try {
            $field = !empty($email) ? 'email' : 'phone';
            $value = !empty($email) ? $email : $phone;
            $stmt = $this->db->prepare("SELECT * FROM password_reset_tokens WHERE $field = ? AND otp = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$value, $otp]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid or expired OTP']); return;
            }
            echo json_encode(['success'=>true,'message'=>'OTP verified','token'=>$row['token']]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function resendOtp() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        if (empty($email) && empty($phone)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Email or phone required']); return;
        }
        try {
            $field = !empty($email) ? 'email' : 'phone';
            $value = !empty($email) ? $email : $phone;
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $stmt = $this->db->prepare("UPDATE password_reset_tokens SET otp=?, expires_at=?, created_at=NOW() WHERE $field=? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$otp, $expires, $value]);
            echo json_encode(['success'=>true,'message'=>'OTP resent','otp'=>$otp]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function resetPassword() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        $password = $input['password'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        if (empty($token) || empty($password) || (empty($email) && empty($phone))) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Token, password and email/phone required']); return;
        }
        try {
            $field = !empty($email) ? 'email' : 'phone';
            $value = !empty($email) ? $email : $phone;
            $stmt = $this->db->prepare("SELECT * FROM password_reset_tokens WHERE $field = ? AND token = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$value, $token]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid or expired token']); return;
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE $field = ?");
            $stmt->execute([$hash, $value]);
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE $field = ?");
            $stmt->execute([$value]);
            echo json_encode(['success'=>true,'message'=>'Password reset successfully']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function changePassword() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        if (empty($currentPassword) || empty($newPassword)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Current and new password required']); return;
        }
        try {
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                http_response_code(400); echo json_encode(['success'=>false,'error'=>'Current password is incorrect']); return;
            }
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            echo json_encode(['success'=>true,'message'=>'Password changed successfully']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function checkUser() {
        $this->setCorsHeaders();
        $phone = $_GET['phone'] ?? '';
        $email = $_GET['email'] ?? '';
        if (empty($phone) && empty($email)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Phone or email required']); return;
        }
        try {
            $field = !empty($phone) ? 'phone' : 'email';
            $value = !empty($phone) ? $phone : $email;
            $stmt = $this->db->prepare("SELECT id, name, email, phone, role FROM users WHERE $field = ? LIMIT 1");
            $stmt->execute([$value]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'exists'=>!!$user,'user'=>$user]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getReferrer() {
        $this->setCorsHeaders();
        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Referral code required']); return;
        }
        try {
            $stmt = $this->db->prepare("SELECT id, name, phone FROM users WHERE referral_code = ? LIMIT 1");
            $stmt->execute([$code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'found'=>!!$user,'data'=>$user ?: null]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function firebaseLogin() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? '';
        $idToken = $input['id_token'] ?? '';
        if (empty($phone)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Phone required']); return;
        }
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, phone, role, status FROM users WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $name = $input['name'] ?? 'User';
                $stmt = $this->db->prepare("INSERT INTO users (name, phone, role, status, created_at) VALUES (?, ?, 'customer', 'active', NOW())");
                $stmt->execute([$name, $phone]);
                $userId = $this->db->lastInsertId();
                $user = ['id' => $userId, 'name' => $name, 'phone' => $phone, 'role' => 'customer', 'status' => 'active'];
            }
            $token = bin2hex(random_bytes(32));
            $userType = $user['role'] ?? 'customer';
            $stmt = $this->db->prepare("INSERT INTO api_tokens (user_id, user_type, token, device_info, ip_address, expires_at, created_at) VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 90 DAY), NOW())");
            $stmt->execute([
                $user['id'],
                $userType,
                $token,
                'firebase_phone_auth',
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
            echo json_encode(['success'=>true,'token'=>$token,'user'=>$user]);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Lead Management (flat /leads/* pattern)
    // ============================================================
    public function changeLeadStatus($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? '';
        if (empty($status)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Status required']); return; }
        try {
            $stmt = $this->db->prepare("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success'=>true,'message'=>'Status updated']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function scheduleLeadFollowup($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $followupDate = $input['followup_date'] ?? '';
        $notes = $input['notes'] ?? '';
        if (empty($followupDate)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Follow-up date required']); return; }
        try {
            $stmt = $this->db->prepare("UPDATE leads SET next_followup = ?, notes = CONCAT(COALESCE(notes,''), ?), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$followupDate, "\n[Follow-up: $notes]", $id]);
            echo json_encode(['success'=>true,'message'=>'Follow-up scheduled']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function addLeadActivity($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $type = $input['type'] ?? 'note';
        $description = $input['description'] ?? '';
        if (empty($description)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Description required']); return; }
        try {
            $stmt = $this->db->prepare("INSERT INTO lead_activities (lead_id, user_id, type, description, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$id, $userId, $type, $description]);
            echo json_encode(['success'=>true,'message'=>'Activity logged']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function convertLead($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $dealValue = $input['deal_value'] ?? 0;
        try {
            $stmt = $this->db->prepare("UPDATE leads SET status = 'closed_won', converted_at = NOW(), deal_value = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$dealValue, $id]);
            $this->db->prepare("INSERT INTO lead_activities (lead_id, user_id, type, description, created_at) VALUES (?, ?, 'conversion', 'Lead converted to deal', NOW())")->execute([$id, $userId]);
            echo json_encode(['success'=>true,'message'=>'Lead converted']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function markLeadLost($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $reason = $input['reason'] ?? '';
        try {
            $stmt = $this->db->prepare("UPDATE leads SET status = 'closed_lost', lost_reason = ?, lost_at = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$reason, $id]);
            $this->db->prepare("INSERT INTO lead_activities (lead_id, user_id, type, description, created_at) VALUES (?, ?, 'lost', 'Lead lost: $reason', NOW())")->execute([$id, $userId]);
            echo json_encode(['success'=>true,'message'=>'Lead marked as lost']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getLeadStatistics() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
            $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            echo json_encode(['success'=>true,'data'=>[
                'new' => (int)($stats['new'] ?? 0),
                'contacted' => (int)($stats['contacted'] ?? 0),
                'qualified' => (int)($stats['qualified'] ?? 0),
                'proposal' => (int)($stats['proposal'] ?? 0),
                'negotiation' => (int)($stats['negotiation'] ?? 0),
                'closed_won' => (int)($stats['closed_won'] ?? 0),
                'closed_lost' => (int)($stats['closed_lost'] ?? 0),
                'total' => array_sum($stats),
            ]]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function logLeadCall($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $duration = $input['duration'] ?? 0;
        $notes = $input['notes'] ?? '';
        $callType = $input['call_type'] ?? 'outbound';
        try {
            $stmt = $this->db->prepare("INSERT INTO lead_activities (lead_id, user_id, type, description, created_at) VALUES (?, ?, 'call', ?, NOW())");
            $desc = "Call ($callType, " . ($duration ? "{$duration}s" : 'no duration') . "): $notes";
            $stmt->execute([$id, $userId, $desc]);
            $this->db->prepare("UPDATE leads SET call_count = COALESCE(call_count, 0) + 1, updated_at = NOW() WHERE id = ?")->execute([$id]);
            echo json_encode(['success'=>true,'message'=>'Call logged']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Bookings (Update/Cancel)
    // ============================================================
    public function updateBooking($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $fields = [];
            $params = [];
            foreach (['customer_name','customer_email','customer_phone','notes'] as $f) {
                if (isset($input[$f])) { $fields[] = "$f = ?"; $params[] = $input[$f]; }
            }
            if (empty($fields)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'No fields to update']); return; }
            $params[] = $id;
            $this->db->prepare("UPDATE plot_bookings SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND customer_id = ?")->execute([...$params, $userId]);
            echo json_encode(['success'=>true,'message'=>'Booking updated']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function cancelBooking($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT plot_id FROM plot_bookings WHERE id = ? AND customer_id = ?");
            $stmt->execute([$id, $userId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$booking) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Booking not found']); return; }
            $this->db->prepare("UPDATE plot_bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ?")->execute([$id]);
            $this->db->prepare("UPDATE plots SET status = 'available' WHERE id = ?")->execute([$booking['plot_id']]);
            echo json_encode(['success'=>true,'message'=>'Booking cancelled, plot released']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Properties (Similar, Colony Properties)
    // ============================================================
    public function getSimilarProperties($id) {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->prepare("SELECT colony_id, area_sqft, total_price FROM plots WHERE id = ?");
            $stmt->execute([$id]);
            $plot = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$plot) { echo json_encode(['success'=>true,'data'=>[]]); return; }
            $stmt = $this->db->prepare("SELECT id, plot_number, area_sqft, total_price, status, width_ft, length_ft, block FROM plots WHERE colony_id = ? AND id != ? AND status = 'available' ORDER BY ABS(area_sqft - ?) LIMIT 10");
            $stmt->execute([$plot['colony_id'], $id, $plot['area_sqft']]);
            $similar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'data'=>$similar]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true,'data'=>[]]);
        }
    }

    public function getColonyProperties($colonyId) {
        $this->setCorsHeaders();
        try {
            $stmt = $this->db->prepare("SELECT id, plot_number, area_sqft, total_price, status, width_ft, length_ft, block FROM plots WHERE colony_id = ? ORDER BY plot_number ASC");
            $stmt->execute([$colonyId]);
            $plots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'data'=>$plots]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true,'data'=>[]]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Notifications (Individual Read/Delete)
    // ============================================================
    public function markNotificationRead($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")->execute([$id, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function deleteNotification($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $this->db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")->execute([$id, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Referral Dashboard & Share Tracking
    // ============================================================
    public function getReferralDashboard() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, phone, referral_code FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $this->db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status IN ('registered','booked') THEN 1 ELSE 0 END) as active_signups FROM customer_referrals WHERE referrer_user_id = ?");
            $stmt->execute([$userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $this->db->prepare("SELECT COUNT(*) as bookings FROM plot_bookings WHERE referred_by = ? AND status NOT IN ('cancelled')");
            $stmt->execute([$userId]);
            $bookingStats = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'data'=>[
                'referral_code' => $user['referral_code'] ?? '',
                'total_signups' => (int)($stats['total'] ?? 0),
                'active_signups' => (int)($stats['active_signups'] ?? 0),
                'total_bookings' => (int)($bookingStats['bookings'] ?? 0),
                'share_url' => "https://unforced-willena-seclusively.ngrok-free.dev/apsdreamhome/register?ref=" . ($user['referral_code'] ?? ''),
            ]]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true,'data'=>['referral_code'=>'','total_signups'=>0,'active_signups'=>0,'total_bookings'=>0,'share_url'=>'']]);
        }
    }

    public function trackReferralShare() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $platform = $input['platform'] ?? 'unknown';
        try {
            $shares = json_decode(file_get_contents('php://input'), true)['shares'] ?? 1;
            $this->db->prepare("UPDATE users SET share_clicks = COALESCE(share_clicks,0) + ? WHERE id = ?")->execute([$shares, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Support Tickets
    // ============================================================
    public function getSupportTickets() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT id, subject, category, priority, status, created_at, updated_at FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'data'=>$tickets]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true,'data'=>[]]);
        }
    }

    public function createSupportTicket() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $subject = $input['subject'] ?? '';
        $category = $input['category'] ?? 'general';
        $message = $input['message'] ?? '';
        $priority = $input['priority'] ?? 'medium';
        if (empty($subject) || empty($message)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Subject and message required']); return;
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO support_tickets (user_id, subject, category, message, priority, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'open', NOW(), NOW())");
            $stmt->execute([$userId, $subject, $category, $message, $priority]);
            echo json_encode(['success'=>true,'ticket_id'=>$this->db->lastInsertId()]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getSupportTicketDetail($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT * FROM support_tickets WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ticket) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Ticket not found']); return; }
            $stmt = $this->db->prepare("SELECT id, message, is_admin, created_at FROM support_ticket_replies WHERE ticket_id = ? ORDER BY created_at ASC");
            $stmt->execute([$id]);
            $ticket['replies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'data'=>$ticket]);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Settings & Preferences
    // ============================================================
    public function updateNotificationPreferences() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $prefs = json_encode($input);
            $this->db->prepare("UPDATE users SET notification_preferences = ? WHERE id = ?")->execute([$prefs, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true]);
        }
    }

    public function updateUserPreferences() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $prefs = json_encode($input);
            $this->db->prepare("UPDATE users SET preferences = ? WHERE id = ?")->execute([$prefs, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>true]);
        }
    }

    public function deleteAccount() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $password = $input['password'] ?? '';
        try {
            if (!empty($password)) {
                $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && !password_verify($password, $user['password'])) {
                    http_response_code(400); echo json_encode(['success'=>false,'error'=>'Incorrect password']); return;
                }
            }
            // users.status enum has no 'deleted' value; use 'inactive' + anonymize PII
            $this->db->prepare("UPDATE users SET status = 'inactive', name = CONCAT(name, '_deleted_', id), email = CONCAT('deleted_', id, '@deleted.com'), updated_at = NOW() WHERE id = ?")->execute([$userId]);
            // Revoke any active API tokens
            try { $this->db->prepare("DELETE FROM api_tokens WHERE user_id = ?")->execute([$userId]); } catch (\Throwable $t) {}
            echo json_encode(['success'=>true,'message'=>'Account deleted']);
        } catch (\Exception $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API V2 — MLM Operations
    // ============================================================
    public function processMlmSale() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $saleAmount = $input['sale_amount'] ?? 0;
        $plotId = $input['plot_id'] ?? null;
        if ($saleAmount <= 0 || !$plotId) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Sale amount and plot ID required']); return;
        }
        try {
            $commissionPct = 5.00;
            $commissionAmt = $saleAmount * ($commissionPct / 100);
            $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, sale_amount, commission_percentage, status, notes, created_at) VALUES (?, ?, 'direct_sale', ?, ?, ?, 'pending', 'Mobile app sale submission', NOW())");
            $stmt->execute([$userId, $userId, $commissionAmt, $saleAmount, $commissionPct]);
            echo json_encode(['success'=>true,'message'=>'Sale submitted for commission processing','commission_estimate'=>round($saleAmount * 0.05, 2)]);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function upgradeMlmRank() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            // Resolve associate row for this user
            $stmt = $this->db->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $assoc = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$assoc) {
                http_response_code(404); echo json_encode(['success'=>false,'error'=>'Associate record not found']); return;
            }
            $associateId = (int)$assoc['id'];

            // Current rank = latest to_rank in rank history (default 'associate')
            $stmt = $this->db->prepare("SELECT to_rank FROM mlm_rank_history WHERE associate_id = ? ORDER BY promoted_at DESC, id DESC LIMIT 1");
            $stmt->execute([$associateId]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentRank = $current['to_rank'] ?? 'associate';

            $ranks = ['associate','sr_associate','bdm','sr_bdm','vice_president','president','site_manager'];
            $idx = array_search($currentRank, $ranks);
            if ($idx === false) {
                $newRank = 'associate';
                $fromRank = null;
            } elseif ($idx < count($ranks) - 1) {
                $newRank = $ranks[$idx + 1];
                $fromRank = $currentRank;
            } else {
                http_response_code(400); echo json_encode(['success'=>false,'error'=>'Already at highest rank']); return;
            }

            $this->db->prepare("INSERT INTO mlm_rank_history (associate_id, from_rank, to_rank, qualifying_volume_at_promotion, leg_count_at_promotion, promoted_by, is_manual, reason, created_at) VALUES (?, ?, ?, 0, 0, ?, 1, 'Requested via mobile app', NOW())")
                ->execute([$associateId, $fromRank, $newRank, $userId]);
            echo json_encode(['success'=>true,'message'=>"Rank upgraded to $newRank",'from_rank'=>$fromRank,'to_rank'=>$newRank]);
        } catch (\Throwable $e) {
            http_response_code(500); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getForm16() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $year = (int)($_GET['year'] ?? date('Y'));
            // Ledger has no TDS column; TDS is deducted at payout-batch level. Report gross commission here.
            $stmt = $this->db->prepare("SELECT SUM(amount) as total_commission FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND YEAR(created_at) = ? AND status IN ('approved','paid')");
            $stmt->execute([$userId, $year]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $gross = (float)($data['total_commission'] ?? 0);
            // Pull actual TDS from payout batches linked to this user's ledger entries, if any
            $tds = 0.0;
            try {
                $t = $this->db->prepare("SELECT COALESCE(SUM(pe.tds_amount),0) as tds FROM payout_entries pe WHERE pe.beneficiary_user_id = ? AND YEAR(pe.created_at) = ?");
                $t->execute([$userId, $year]);
                $tds = (float)($t->fetch(PDO::FETCH_ASSOC)['tds'] ?? 0);
            } catch (\Throwable $e) { $tds = 0.0; }
            echo json_encode(['success'=>true,'data'=>[
                'financial_year' => "$year-" . ($year + 1),
                'total_commission' => $gross,
                'tds_deducted' => $tds,
                'net_payout' => $gross - $tds,
            ]]);
        } catch (\Throwable $e) {
            $year = (int)($_GET['year'] ?? date('Y'));
            echo json_encode(['success'=>true,'data'=>['financial_year'=>"$year-".($year+1),'total_commission'=>0,'tds_deducted'=>0,'net_payout'=>0]]);
        }
    }

    public function getTaxSummary() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT commission_type, SUM(amount) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND status IN ('approved','paid') GROUP BY commission_type");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalTds = 0.0;
            try {
                $t = $this->db->prepare("SELECT COALESCE(SUM(tds_amount),0) as total_tds FROM payout_entries WHERE beneficiary_user_id = ?");
                $t->execute([$userId]);
                $totalTds = (float)($t->fetch(PDO::FETCH_ASSOC)['total_tds'] ?? 0);
            } catch (\Throwable $e) { $totalTds = 0.0; }
            echo json_encode(['success'=>true,'data'=>[
                'breakdown' => $rows,
                'total_tds' => $totalTds,
                'total_commission' => array_sum(array_column($rows, 'total')),
            ]]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>true,'data'=>['breakdown'=>[],'total_tds'=>0,'total_commission'=>0]]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Notification (lead assignment, etc.)
    // ============================================================
    public function createNotification() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        $targetUserId = $input['user_id'] ?? null;
        $title = $input['title'] ?? 'Notification';
        $message = $input['message'] ?? '';
        $type = $input['type'] ?? 'general';
        if (!$targetUserId || empty($message)) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'user_id and message required']); return;
        }
        try {
            $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())")->execute([$targetUserId, $title, $message, $type]);
            echo json_encode(['success'=>true]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>true]);
        }
    }

    // ============================================================
    // MOBILE API V2 — Company Loans
    // ============================================================
    public function getLoans() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $loans = $svc->listLoans(['customer_id' => $userId]);
            echo json_encode(['success'=>true, 'data' => $loans]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getLoanDetail($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $loan = $svc->getLoanById((int)$id);
            if (!$loan) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found']); return; }
            $installments = $svc->getInstallments((int)$id);
            $guarantors = $svc->getGuarantors((int)$id);
            $documents = $svc->getDocuments((int)$id);
            echo json_encode(['success'=>true, 'data'=>['loan'=>$loan,'installments'=>$installments,'guarantors'=>$guarantors,'documents'=>$documents]]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getLoanInstallments($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $installments = $svc->getInstallments((int)$id);
            echo json_encode(['success'=>true, 'data'=>$installments]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function applyLoan() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = $_POST;
        $input['customer_id'] = $userId;
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $result = $svc->createLoan($input);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getLoanOffers() {
        $this->setCorsHeaders();
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $offers = $svc->getOffers();
            $offers = array_map(function($o) { return ['id'=>$o['id'],'name'=>$o['name'],'description'=>$o['description'],'offer_type'=>$o['offer_type'],'interest_free_months'=>$o['interest_free_months'],'max_tenure_months'=>$o['max_tenure_months'],'max_amount'=>$o['max_amount']]; }, $offers);
            echo json_encode(['success'=>true, 'data'=>$offers]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function calculateLoanEligibility() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = $_GET;
        $amount = (float)($input['amount'] ?? 0);
        $rate = (float)($input['rate'] ?? 10);
        $tenure = (int)($input['tenure_months'] ?? 60);
        $interestFreeMonths = (int)($input['interest_free_months'] ?? 0);
        try {
            $svc = new \App\Services\Loan\InterestFreeOfferService($this->db->getConnection());
            $result = $svc->calculateSavings($amount, $rate, $tenure, $interestFreeMonths);
            echo json_encode(['success'=>true, 'data'=>$result]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getEarlySettlement($id) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $svc = new \App\Services\Loan\CompanyLoanService($this->db->getConnection());
            $result = $svc->calculateEarlySettlement((int)$id);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    // ─── In-App Messaging ───

    /**
     * Find an existing direct conversation between two users, or create one.
     * Returns the conversation_id (required NOT NULL on messages).
     */
    private function getOrCreateDirectConversation(int $userId, int $otherUserId): int
    {
        // Look for a direct conversation both users participate in
        $stmt = $this->db->prepare("
            SELECT cp1.conversation_id
            FROM conversation_participants cp1
            JOIN conversation_participants cp2
              ON cp1.conversation_id = cp2.conversation_id
            JOIN conversations c ON c.id = cp1.conversation_id
            WHERE cp1.user_id = ? AND cp2.user_id = ? AND c.conversation_type = 'direct'
            LIMIT 1
        ");
        $stmt->execute([$userId, $otherUserId]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            return (int)$existing;
        }
        // Create new direct conversation + participants
        $this->db->prepare("INSERT INTO conversations (conversation_type, created_by, is_active, created_at) VALUES ('direct', ?, 1, NOW())")
            ->execute([$userId]);
        $conversationId = (int)$this->db->lastInsertId();
        $ins = $this->db->prepare("INSERT INTO conversation_participants (conversation_id, user_id, joined_at) VALUES (?, ?, NOW())");
        $ins->execute([$conversationId, $userId]);
        $ins->execute([$conversationId, $otherUserId]);
        return $conversationId;
    }

    public function getConversations() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->query("
                SELECT 
                    CASE WHEN m.sender_id = $userId THEN m.receiver_id ELSE m.sender_id END AS other_user_id,
                    MAX(m.sent_at) AS last_message_time,
                    (SELECT content FROM messages WHERE (sender_id = $userId AND receiver_id = m.receiver_id) OR (sender_id = m.receiver_id AND receiver_id = $userId) ORDER BY sent_at DESC LIMIT 1) AS last_message,
                    (SELECT CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END FROM messages WHERE sender_id = m.receiver_id AND receiver_id = $userId ORDER BY sent_at DESC LIMIT 1) AS is_read,
                    (SELECT COUNT(*) FROM messages WHERE receiver_id = $userId AND sender_id = m.receiver_id AND read_at IS NULL) AS unread_count,
                    u.name AS other_user_name,
                    u.email AS other_user_email,
                    u.role AS other_user_role
                FROM messages m
                JOIN users u ON u.id = (CASE WHEN m.sender_id = $userId THEN m.receiver_id ELSE m.sender_id END)
                WHERE m.sender_id = $userId OR m.receiver_id = $userId
                GROUP BY other_user_id
                ORDER BY last_message_time DESC
            ");
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true, 'data'=>$conversations]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getMessages($otherUserId) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, u.name AS sender_name, u.role AS sender_role,
                       m.content AS message, m.sent_at AS created_at,
                       CASE WHEN m.read_at IS NOT NULL THEN 1 ELSE 0 END AS is_read
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.sent_at ASC
            ");
            $stmt->execute([$userId, (int)$otherUserId, (int)$otherUserId, $userId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true, 'data'=>$messages]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function sendMessage() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $receiverId = (int)($input['receiver_id'] ?? 0);
            $message = trim($input['message'] ?? '');
            if (!$receiverId || !$message) {
                echo json_encode(['success'=>false, 'error'=>'receiver_id and message required']);
                return;
            }
            // messages.conversation_id is NOT NULL — resolve/create the direct conversation
            $conversationId = $this->getOrCreateDirectConversation((int)$userId, $receiverId);
            $this->db->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, content, message_type, sent_at) VALUES (?, ?, ?, ?, 'text', NOW())")
                ->execute([$conversationId, $userId, $receiverId, $message]);
            $msgId = $this->db->lastInsertId();
            // Update conversation last-message metadata
            try {
                $this->db->prepare("UPDATE conversations SET last_message_at = NOW(), last_message_preview = ? WHERE id = ?")
                    ->execute([mb_substr($message, 0, 500), $conversationId]);
            } catch (\Throwable $t) {}
            $resp = [
                'id'=>(int)$msgId, 'sender_id'=>$userId, 'receiver_id'=>$receiverId,
                'message'=>$message, 'content'=>$message,
                'created_at'=>date('Y-m-d H:i:s'), 'sent_at'=>date('Y-m-d H:i:s'),
                'is_read'=>0, 'read_at'=>null,
            ];
            echo json_encode(['success'=>true, 'data'=>$resp]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function markMessagesRead($otherUserId) {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $this->db->prepare("UPDATE messages SET read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL")
                ->execute([(int)$otherUserId, $userId]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    public function getUnreadCount() {
        $this->setCorsHeaders();
        $userId = $GLOBALS['api_user_id'] ?? null;
        if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Auth required']); return; }
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND read_at IS NULL");
            $stmt->execute([$userId]);
            $count = (int)$stmt->fetchColumn();
            echo json_encode(['success'=>true, 'data'=>['unread_count'=>$count]]);
        } catch (\Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
    }

    // ============================================================
    // MOBILE API — RERA Verification
    // ============================================================

    public function reraVerify($reraNumber = null) {
        $this->setCorsHeaders();
        $reraNumber = $reraNumber ?? ($_GET['rera_number'] ?? '');
        $stateCode = strtoupper($_GET['state_code'] ?? 'UP');

        if (empty($reraNumber)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'RERA number is required']);
            return;
        }

        try {
            $service = new \App\Services\Legal\RERAVerificationService();
            $result = $service->verifyByReraNumber($reraNumber, $stateCode);

            if (!empty($result['success']) && !empty($result['project'])) {
                $project = $result['project'];
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'rera_number' => $project['rera_number'] ?? $reraNumber,
                        'project_name' => $project['project_name'] ?? '',
                        'builder_name' => $project['builder_name'] ?? '',
                        'status' => $project['status'] ?? 'Unknown',
                        'registration_date' => $project['registration_date'] ?? '',
                        'valid_upto' => $project['valid_upto'] ?? '',
                        'total_area' => $project['total_area'] ?? '',
                        'total_units' => $project['total_units'] ?? 0,
                        'address' => $project['address'] ?? '',
                        'city' => $project['city'] ?? '',
                        'state_code' => $project['state_code'] ?? $stateCode,
                    ],
                    'source' => $result['source'] ?? 'database',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No project found for RERA number: ' . $reraNumber,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('reraVerify error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Verification failed']);
        }
    }

    public function reraSearch() {
        $this->setCorsHeaders();
        $builder = $_GET['builder'] ?? '';
        $city = $_GET['city'] ?? '';
        $stateCode = strtoupper($_GET['state_code'] ?? 'UP');

        try {
            $service = new \App\Services\Legal\RERAVerificationService();
            $criteria = ['state_code' => $stateCode];
            if ($builder) $criteria['builder_name'] = $builder;
            if ($city) $criteria['city'] = $city;

            $result = $service->searchProjects($criteria);
            echo json_encode([
                'success' => true,
                'data' => $result['projects'] ?? [],
            ]);
        } catch (\Throwable $e) {
            error_log('reraSearch error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function reraProjects() {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT id, rera_number, project_name, builder_name, status, registration_date, valid_upto, total_area, total_units, city, state_code FROM rera_projects WHERE is_active = 1 ORDER BY created_at DESC LIMIT 20");
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $projects]);
        } catch (\Throwable $e) {
            error_log('reraProjects error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    // ============================================================
    // MOBILE API — Directory / Services
    // ============================================================

    public function directoryCategories() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\DirectoryService();
            $categories = $service->getActiveCategories();
            $catData = [];
            foreach ($categories as $cat) {
                $catData[] = [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'slug' => $cat['slug'] ?? '',
                    'description' => $cat['description'] ?? '',
                    'icon' => $cat['icon'] ?? 'fas fa-building',
                    'listing_count' => (int)($cat['listing_count'] ?? 0),
                ];
            }
            echo json_encode(['success' => true, 'data' => $catData]);
        } catch (\Throwable $e) {
            error_log('directoryCategories error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function directoryFeatured() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\DirectoryService();
            $listings = $service->getFeaturedListings(10);
            $listingData = [];
            foreach ($listings as $l) {
                $listingData[] = [
                    'id' => $l['id'],
                    'business_name' => $l['business_name'] ?? '',
                    'category_name' => $l['category_name'] ?? '',
                    'rating' => (float)($l['rating'] ?? 0),
                    'review_count' => (int)($l['review_count'] ?? 0),
                    'city' => $l['city'] ?? '',
                    'is_verified' => (bool)($l['is_verified'] ?? false),
                    'description' => $l['description'] ?? '',
                    'phone' => $l['phone'] ?? '',
                ];
            }
            echo json_encode(['success' => true, 'data' => $listingData]);
        } catch (\Throwable $e) {
            error_log('directoryFeatured error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    public function directoryJobs() {
        $this->setCorsHeaders();
        try {
            $service = new \App\Services\DirectoryService();
            $jobs = $service->getJobs('', '', -1, 1);
            $jobData = [];
            foreach ($jobs as $j) {
                $jobData[] = [
                    'id' => $j['id'],
                    'title' => $j['title'] ?? '',
                    'company' => $j['business_name'] ?? '',
                    'location' => $j['location'] ?? $j['city'] ?? '',
                    'salary_min' => (int)($j['salary_min'] ?? 0),
                    'salary_max' => (int)($j['salary_max'] ?? 0),
                    'job_type' => $j['job_type'] ?? '',
                    'category' => $j['category'] ?? '',
                ];
            }
            echo json_encode(['success' => true, 'data' => $jobData]);
        } catch (\Throwable $e) {
            error_log('directoryJobs error: ' . $e->getMessage());
            echo json_encode(['success' => true, 'data' => []]);
        }
    }

    // ============================================================
    // MOBILE API — Property Valuation
    // ============================================================

    public function propertyValuation() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $city = $input['city'] ?? 'Gorakhpur';
        $propertyType = $input['property_type'] ?? 'plot';
        $areaSqft = (float)($input['area_sqft'] ?? 0);
        $locationType = $input['location_type'] ?? 'urban';
        $frontRoad = (float)($input['front_road_ft'] ?? 20);
        $isCorner = !empty($input['is_corner']);
        $isParkFacing = !empty($input['is_park_facing']);

        if ($areaSqft <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Area must be greater than 0']);
            return;
        }

        try {
            $engine = new \App\Services\AI\PropertyValuationEngine();
            $result = $engine->calculateValuation([
                'city' => $city,
                'location' => $city,
                'property_type' => $propertyType,
                'type' => $propertyType,
                'area_sqft' => $areaSqft,
                'location_type' => $locationType,
                'front_road_ft' => $frontRoad,
                'is_corner' => $isCorner,
                'is_park_facing' => $isParkFacing,
            ]);

            $estimatedPrice = $result['estimated_price'] ?? 0;
            $confidence = $result['confidence_score'] ?? 0.85;

            // Calculate per sqft
            $pricePerSqft = $areaSqft > 0 ? $estimatedPrice / $areaSqft : 0;
            $minPrice = $estimatedPrice * 0.85;
            $maxPrice = $estimatedPrice * 1.15;

            // Market trend
            $marketAnalysis = $result['market_analysis'] ?? [];
            $trend = $marketAnalysis['trend'] ?? 'upward';
            $trendPct = $marketAnalysis['trend_percentage'] ?? 8.5;

            echo json_encode([
                'success' => true,
                'data' => [
                    'estimated_price' => round($estimatedPrice),
                    'price_per_sqft' => round($pricePerSqft),
                    'min_price' => round($minPrice),
                    'max_price' => round($maxPrice),
                    'area_sqft' => $areaSqft,
                    'confidence' => round($confidence, 2),
                    'market_trend' => $trend,
                    'trend_percentage' => round($trendPct, 1),
                    'recommendations' => $result['recommendations'] ?? [],
                    'comparable_properties' => $result['comparable_properties'] ?? [],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('propertyValuation error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Valuation failed']);
        }
    }

    public function valuationCities() {
        $this->setCorsHeaders();
        // Return available cities with their base rates from the valuation engine
        try {
            $engine = new \App\Services\AI\PropertyValuationEngine();
            $cities = [
                ['name' => 'Gorakhpur', 'base_rate' => 3000, 'state' => 'UP'],
                ['name' => 'Lucknow', 'base_rate' => 4200, 'state' => 'UP'],
                ['name' => 'Varanasi', 'base_rate' => 3800, 'state' => 'UP'],
                ['name' => 'Kushinagar', 'base_rate' => 2800, 'state' => 'UP'],
                ['name' => 'Kanpur', 'base_rate' => 3500, 'state' => 'UP'],
                ['name' => 'Prayagraj', 'base_rate' => 3200, 'state' => 'UP'],
                ['name' => 'Noida', 'base_rate' => 5500, 'state' => 'UP'],
                ['name' => 'Ghaziabad', 'base_rate' => 4800, 'state' => 'UP'],
                ['name' => 'Agra', 'base_rate' => 3400, 'state' => 'UP'],
                ['name' => 'Meerut', 'base_rate' => 3600, 'state' => 'UP'],
            ];
            echo json_encode(['success' => true, 'data' => $cities]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    }
}

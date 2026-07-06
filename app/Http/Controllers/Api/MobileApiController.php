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
use UploadValidator;

use EmailNotification;
use Exception;
use PDO;

class MobileApiController extends BaseController
{

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
        
        $userId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_GET['user_id']) ?? null;
        $lastSync = \App\Core\Security::sanitize($_GET['last_sync']) ?? '2000-01-01 00:00:00';

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
            $page = (int)(\App\Core\Security::sanitize($_GET['page']) ?? 1);
            $limit = min((int)(\App\Core\Security::sanitize($_GET['limit']) ?? 10), 50); // Max 50 per page
            $offset = ($page - 1) * $limit;
            
            // V2 Sync Logic - Handle sync parameters
            $sync_mode = \App\Core\Security::sanitize($_GET['sync_mode']) ?? 'normal';
            $last_sync = \App\Core\Security::sanitize($_GET['last_sync']) ?? null;
            $user_id = \App\Core\Security::sanitize($_GET['user_id']) ?? null;

            // Build filters
            $filters = [];
            $property_type = \App\Core\Security::sanitize($_GET['property_type']);
            if ($property_type !== null && $property_type !== '') {
                $filters['property_type'] = $property_type;
            }
            $city = \App\Core\Security::sanitize($_GET['city']);
            if ($city !== null && $city !== '') {
                $filters['city'] = $city;
            }
            $min_price = \App\Core\Security::sanitize($_GET['min_price']);
            if ($min_price !== null && $min_price !== '') {
                $filters['min_price'] = $min_price;
            }
            $max_price = \App\Core\Security::sanitize($_GET['max_price']);
            if ($max_price !== null && $max_price !== '') {
                $filters['max_price'] = $max_price;
            }
            $featured = \App\Core\Security::sanitize($_GET['featured']);
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
        } catch (Exception $e) {
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
            $user_id = (int)(\App\Core\Security::sanitize($_GET['user_id']) ?? 0);

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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
            
        } catch (Exception $e) {
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
            } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
                INSERT INTO leads (name, email, phone, source_user_id, status, created_at) 
                VALUES (?, ?, ?, ?, 'new', NOW())
            ");

            foreach ($leads as $lead) {
                $stmt->execute([
                    $lead['name'] ?? '',
                    $lead['email'] ?? '',
                    $lead['phone'] ?? '',
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_GET['user_id']) ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID required', 401);
        }

        try {
            $perfCalculator = new \App\Services\PerformanceRankCalculator();
            $summary = $perfCalculator->calculateRank($userId);
            
            return $this->successResponse($summary, 'MLM performance summary fetched');
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_GET['user_id']) ?? null;

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
        } catch (Exception $e) {
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
                SELECT u.id as user_id, u.name, u.email, u.phone, u.role, u.created_at, u.updated_at,
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

            $user['avatar'] = null; // Placeholder for now

            return $this->successResponse($user, 'User profile fetched');
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_GET['user_id']) ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID required', 401);
        }

        try {
            $incentiveService = new \App\Services\MLMIncentiveService();
            $summary = $incentiveService->getIncentiveSummary($userId);

            return $this->successResponse($summary, 'Monthly incentives fetched');
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_GET['user_id']) ?? null;

        if (!$userId) {
            return $this->errorResponse('User ID required', 401);
        }

        try {
            $lockerService = new \App\Services\DocumentLockerService();
            $documents = $lockerService->getUserDocuments($userId);

            return $this->successResponse($documents, 'Documents fetched from locker');
        } catch (Exception $e) {
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
            } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_GET['user_id']) ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            // Fetch documents from multiple sources
            $documents = [];

            // 1. KYC Documents from document_locker
            $kycSql = "
                SELECT dl.id, dl.title, dl.document_type as type, dl.category, 
                       dl.file_url as url, dl.created_at as uploaded_at, dl.status,
                       'kyc' as source
                FROM document_locker dl
                WHERE dl.user_id = ? AND dl.status = 'verified'
                ORDER BY dl.created_at DESC
            ";
            $kycStmt = $this->db->prepare($kycSql);
            $kycStmt->execute([$userId]);
            $kycDocs = $kycStmt->fetchAll(PDO::FETCH_ASSOC);
            $documents = array_merge($documents, $kycDocs);

            // 2. Booking Agreements from bookings
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
            $bookingDocs = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);
            $documents = array_merge($documents, $bookingDocs);

            // 3. Payment Receipts from payments
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
            $paymentDocs = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);
            $documents = array_merge($documents, $paymentDocs);

            // 4. Plot Allotment Letters
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
            $allotmentDocs = $allotmentStmt->fetchAll(PDO::FETCH_ASSOC);
            $documents = array_merge($documents, $allotmentDocs);

            // Sort by uploaded_at descending
            usort($documents, function($a, $b) {
                return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
            });

            echo json_encode([
                'success' => true,
                'data' => $documents
            ]);
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        $visitId = \App\Core\Security::sanitize($_GET['visit_id']) ?? null;

        if (!$visitId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Visit ID required']);
            return;
        }

        try {
            $visitService = new \App\Services\SiteVisitService();
            $status = $visitService->getVisitStatus($visitId);
            echo json_encode(['success' => true, 'data' => $status]);
        } catch (Exception $e) {
            error_log("[MobileApiController] exception: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fetch failed: ' . $e->getMessage()]);
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? Security::sanitize($_GET['user_id']) ?? null;

        try {
            $mlmService = new \App\Services\MLMNetworkService(); // Assuming this service exists or will be created
            $tree = $mlmService->getDownline($userId);
            echo json_encode(['success' => true, 'data' => $tree]);
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? Security::sanitize($_GET['user_id']) ?? null;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            $mlmService = new \App\Services\MLMNetworkService();
            $data = $mlmService->getBusinessBreakdown($userId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? Security::sanitize($_GET['user_id']) ?? null;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            return;
        }

        try {
            $mlmService = new \App\Services\MLMNetworkService();
            
            // Get direct referrals from network_tree
            $directSql = "SELECT nt.associate_id, u.name, u.email, u.phone, u.profile_image, nt.level, nt.position, nt.joined_at
                FROM network_tree nt
                JOIN users u ON u.id = nt.associate_id
                WHERE nt.parent_id = ?
                ORDER BY nt.joined_at DESC";
            $directReferrals = $this->db->fetchAll($directSql, [$userId]) ?? [];
            
            // Get team stats
            $teamSize = $mlmService->getTeamSize($userId);
            $directCount = $mlmService->getDirectCount($userId);
            
            // Get active/inactive counts via network_tree
            $activeSql = "SELECT COUNT(*) FROM network_tree nt JOIN users u ON u.id = nt.associate_id WHERE nt.parent_id = ? AND u.status = 'active'";
            $inactiveSql = "SELECT COUNT(*) FROM network_tree nt JOIN users u ON u.id = nt.associate_id WHERE nt.parent_id = ? AND u.status != 'active'";
            
            $activeStmt = $this->db->prepare($activeSql);
            $activeStmt->execute([$userId]);
            $activeCount = (int)$activeStmt->fetchColumn();
            
            $inactiveStmt = $this->db->prepare($inactiveSql);
            $inactiveStmt->execute([$userId]);
            $inactiveCount = (int)$inactiveStmt->fetchColumn();

            // Get recent joinings (last 30 days)
            $recentSql = "SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $recentStmt = $this->db->prepare($recentSql);
            $recentStmt->execute([$userId]);
            $recentCount = (int)$recentStmt->fetchColumn();

            // Get total team business from plot_bookings
            $businessSql = "
                SELECT COALESCE(SUM(pb.total_plot_value), 0) as total_business
                FROM plot_bookings pb
                WHERE pb.associate_id IN (
                    SELECT nt.associate_id FROM network_tree nt WHERE nt.parent_id = ?
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
        $userId = $GLOBALS['api_user_id'] ?? Security::sanitize($_GET['user_id'] ?? '') ?: null;

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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        $bookingId = Security::sanitize($_GET['booking_id']) ?? null;

        if (!$bookingId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Booking ID required']);
            return;
        }

        try {
            $customer = new \App\Models\User\Customer();
            $data = $customer->getEmiSchedule($bookingId);
            echo json_encode($data);
        } catch (Exception $e) {
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
            $customer = new \App\Models\User\Customer();
            $result = $customer->recordEmiPayment($emiId, $amount, $method);
            echo json_encode($result);
        } catch (Exception $e) {
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
            $stmt = $this->db->prepare("SELECT rank FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $type = ($user && $user['rank'] != 'Customer' && $user['rank'] != '') ? 'agent' : 'customer';

            $submissionService = new \App\Services\PropertySubmissionService();
            $data = [
                'submitter_id' => $userId,
                'submitter_type' => $type,
                'title' => Security::sanitize($input['title']),
                'description' => Security::sanitize($input['description']),
                'price' => Security::sanitize($input['price']),
                'property_type' => Security::sanitize($input['property_type']),
                'location' => Security::sanitize($input['location']),
                'images' => $input['images'] ?? []
            ];

            $result = $submissionService->submitProperty($data);
            echo json_encode($result);
        } catch (Exception $e) {
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
        $userId = $GLOBALS['api_user_id'] ?? Security::sanitize($_GET['user_id']) ?? null;

        try {
            $submissionService = new \App\Services\PropertySubmissionService();
            $data = $submissionService->getUserSubmissions($userId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
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
        $userId = $_GET['user_id'] ?? ($_POST['user_id'] ?? 0);
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'User ID required']);
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

            // Get features
            $featStmt = $pdo->prepare("
                SELECT feature_name, feature_value, feature_category
                FROM property_features
                WHERE property_id = ?
                ORDER BY feature_category, feature_name
            ");
            $featStmt->execute([$id]);
            $property['features'] = $featStmt->fetchAll(\PDO::FETCH_ASSOC);

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
                'data' => [
                    'query' => $query,
                    'properties' => $properties,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => (int) ceil($total / $perPage),
                    ],
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

            $where = "lp.assigned_to = ?";
            $params = [$userId];

            if ($status && $status !== 'all') {
                $where .= " AND lp.status = ?";
                $params[] = $status;
            }

            $stmt = $pdo->prepare(
                "SELECT lp.id, lp.lead_number, lp.lead_name, lp.status, lp.priority,
                        lp.follow_up_date as next_followup_date, lp.score as lead_score, lp.lead_source as source, lp.created_at
                 FROM lead_pipeline lp
                 WHERE {$where}
                 ORDER BY
                    CASE lp.priority WHEN 'hot' THEN 0 WHEN 'warm' THEN 1 WHEN 'cold' THEN 2 ELSE 3 END,
                    COALESCE(lp.follow_up_date, '9999-12-31') ASC
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

            $favoriteModel = new \App\Models\Property\Favorite();
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
    public function removeFavorite()
    {
        $this->setCorsHeaders();
        $this->authenticateAndRateLimit();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $propertyId = (int) ($_GET['id'] ?? 0);

            if ($propertyId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID required']);
                return;
            }

            $favoriteModel = new \App\Models\Property\Favorite();
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
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID required']);
                return;
            }

            $favoriteModel = new \App\Models\Property\Favorite();
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

            $favoriteModel = new \App\Models\Property\Favorite();
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
            $favoriteModel = new \App\Models\Property\Favorite();
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
     * POST /api/attendance/punch-in
     * Body: { "latitude": 26.84, "longitude": 83.30 }
     */
}

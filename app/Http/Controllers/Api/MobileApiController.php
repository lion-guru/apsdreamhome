<?php

/**
 * Mobile API Controller
 * Provides REST API endpoints for mobile applications
 * Standalone controller (doesn't extend BaseController for API use)
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;

use EmailNotification;
use Exception;
use PDO;

class MobileApiController extends BaseController
{

    protected $apiAuthService;
    protected $syncService;

    public function __construct()
    {
        parent::__construct();
        $this->apiAuthService = new \App\Services\Auth\ApiAuthService();
        $this->syncService = new \App\Services\SyncService();

        if (!$this->db) {
            error_log('MobileApiController: Failed to initialize database connection');
        }
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
                $this->removeFavorite($user_id, $property_id);
                $is_favorited = false;
                $message = 'Removed from favorites';
            } else {
                $this->addFavorite($user_id, $property_id);
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
                    pt.name as property_type,
                    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'available'
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
                    pt.name as property_type_name,
                    pt.icon as property_type_icon
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.id = :id AND p.status = 'available'
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
                    pt.name as property_type_name,
                    pt.icon as property_type_icon
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.updated_at > :last_sync AND p.status = 'available'
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

            $sql = "SELECT COUNT(*) as count FROM properties WHERE updated_at > :last_sync AND status = 'available'";
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
     * Add property to favorites
     */
    private function addFavorite($user_id, $property_id)
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
     * Remove property from favorites
     */
    private function removeFavorite($user_id, $property_id)
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

            $stmt = $this->db->prepare("SELECT id FROM properties WHERE id = :propertyId AND status = 'available'");
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
                    pt.name as property_type,
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

            $stmt = $this->db->query("SELECT DISTINCT city FROM properties WHERE status = 'available' AND city IS NOT NULL ORDER BY city");
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

            $sql = "SELECT COUNT(*) as count FROM properties WHERE status = 'available'";
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
                SELECT p.id, p.title as property_name, pt.name as property_type, p.status, p.price, p.city as location, p.area_sqft, p.updated_at
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                ORDER BY p.updated_at DESC
            ");
            $stmt->execute();
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->successResponse($properties, 'Properties fetched for sync');
        } catch (Exception $e) {
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
            return $this->successResponse(['synced_count' => count($leads)], 'Leads batch synced successfully');
        } catch (Exception $e) {
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
                       mp.current_level as rank, mp.referral_code, mp.status as mlm_status,
                       (SELECT target_amount FROM mlm_rank_rates WHERE rank = mp.current_level LIMIT 1) as target
                FROM users u
                LEFT JOIN mlm_profiles mp ON u.id = mp.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            // Ensure target is numeric
            $user['target'] = (double)($user['target'] ?? 0);
            $user['avatar'] = null; // Placeholder for now

            return $this->successResponse($user, 'User profile fetched');
        } catch (Exception $e) {
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
        $uploadDir = __DIR__ . '/../../../../public/uploads/documents/' . $userId . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $documentType . '_' . time() . '.' . $extension;
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
     * Start a site visit session
     */
    public function startSiteVisit()
    {
        $this->setCorsHeaders();
        $agentId = $GLOBALS['api_user_id'] ?? \App\Core\Security::sanitize($_POST['user_id']) ?? null;
        $leadId = \App\Core\Security::sanitize($_POST['lead_id']) ?? null;
        $propertyId = \App\Core\Security::sanitize($_POST['property_id']) ?? null;
        $destLat = \App\Core\Security::sanitize($_POST['dest_lat']) ?? null;
        $destLng = \App\Core\Security::sanitize($_POST['dest_lng']) ?? null;

        if (!$agentId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $visitService = new \App\Services\SiteVisitService();
            $result = $visitService->startVisit($agentId, $leadId, $propertyId, $destLat, $destLng);
            echo json_encode($result);
        } catch (Exception $e) {
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
        $visitId = \App\Core\Security::sanitize($_POST['visit_id']) ?? null;
        $lat = \App\Core\Security::sanitize($_POST['lat']) ?? null;
        $lng = \App\Core\Security::sanitize($_POST['lng']) ?? null;

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
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
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
            $customerService = new \App\Services\CustomerService();
            $data = $customerService->getEmiSchedule($bookingId);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
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
            $customerService = new \App\Services\CustomerService();
            $result = $customerService->recordEmiPayment($emiId, $amount, $method);
            echo json_encode($result);
        } catch (Exception $e) {
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
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

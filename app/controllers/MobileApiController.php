<?php
/**
 * Mobile API Controller
 * Provides REST API endpoints for mobile applications
 * Standalone controller (doesn't extend BaseController for API use)
 */

namespace App\Controllers;

class MobileApiController {
    public function __construct() {
        // Mobile API controller - no view rendering needed
    }

    /**
     * API endpoint for mobile app - Get properties with pagination and filters
     */
    public function properties() {
        $this->setCorsHeaders();

        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = min((int)($_GET['limit'] ?? 10), 50); // Max 50 per page
            $offset = ($page - 1) * $limit;

            // Build filters
            $filters = [];
            if (isset($_GET['property_type']) && !empty($_GET['property_type'])) {
                $filters['property_type'] = $_GET['property_type'];
            }
            if (isset($_GET['city']) && !empty($_GET['city'])) {
                $filters['city'] = $_GET['city'];
            }
            if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                $filters['min_price'] = $_GET['min_price'];
            }
            if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                $filters['max_price'] = $_GET['max_price'];
            }
            if (isset($_GET['featured']) && $_GET['featured'] === 'true') {
                $filters['featured'] = true;
            }

            // Get properties
            $properties = $this->getPropertiesWithFilters($filters, $limit, $offset);

            // Get total count for pagination
            $total_count = $this->getPropertiesCount($filters);

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
                    'filters' => $filters
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
    private function setCorsHeaders() {
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
    private function handleApiError($exception, $context = 'API Error') {
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
    public function property($id) {
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
    public function submitInquiry() {
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
                $emailNotification = new \App\Core\EmailNotification();
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
    public function toggleFavorite() {
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
    public function userFavorites() {
        $this->setCorsHeaders();

        try {
            $user_id = (int)($_GET['user_id'] ?? 0);

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
    public function propertyTypes() {
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
    public function cities() {
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
    public function getPropertiesWithFilters($filters, $limit, $offset) {
        try {
            global $pdo;
            if (!$pdo) {
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
                $sql .= " AND p.property_type_id = ?";
                $params[] = $filters['property_type'];
            }

            if (isset($filters['city'])) {
                $sql .= " AND p.city LIKE ?";
                $params[] = '%' . $filters['city'] . '%';
            }

            if (isset($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $filters['max_price'];
            }

            if (isset($filters['featured']) && $filters['featured']) {
                $sql .= " AND p.featured = 1";
            }

            $sql .= " ORDER BY p.featured DESC, p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get properties with filters error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property by ID
     */
    private function getPropertyById($id) {
        try {
            global $pdo;
            if (!$pdo) {
                return null;
            }

            $sql = "
                SELECT
                    p.*,
                    pt.name as property_type_name,
                    pt.icon as property_type_icon
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.id = ? AND p.status = 'available'
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get property by ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get property images
     */
    private function getPropertyImages($property_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC");
            $stmt->execute([$property_id]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get property images error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property features
     */
    private function getPropertyFeatures($property_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->prepare("SELECT * FROM property_features WHERE property_id = ? ORDER BY feature_category, feature_name");
            $stmt->execute([$property_id]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get property features error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create inquiry
     */
    private function createInquiry($data) {
        try {
            global $pdo;
            if (!$pdo) {
                return false;
            }

            $sql = "
                INSERT INTO property_inquiries (
                    property_id, guest_name, guest_email, guest_phone,
                    subject, message, inquiry_type, status, priority, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";

            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $data['property_id'],
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['subject'] ?? 'Property Inquiry',
                $data['message'],
                $data['inquiry_type'] ?? 'general',
                'new',
                $data['priority'] ?? 'medium'
            ]);

            if ($result) {
                return $pdo->lastInsertId();
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
    private function isFavorited($user_id, $property_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return false;
            }

            $stmt = $pdo->prepare("SELECT id FROM property_favorites WHERE user_id = ? AND property_id = ?");
            $stmt->execute([$user_id, $property_id]);
            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            error_log('Check favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add property to favorites
     */
    private function addFavorite($user_id, $property_id) {
        try {
            global $pdo;
            if (!$pdo) {
                throw new Exception('Database connection not available');
            }

            $stmt = $pdo->prepare("INSERT INTO property_favorites (user_id, property_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $property_id]);

        } catch (Exception $e) {
            error_log('Add favorite error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove property from favorites
     */
    private function removeFavorite($user_id, $property_id) {
        try {
            global $pdo;
            if (!$pdo) {
                throw new Exception('Database connection not available');
            }

            $stmt = $pdo->prepare("DELETE FROM property_favorites WHERE user_id = ? AND property_id = ?");
            $stmt->execute([$user_id, $property_id]);

        } catch (Exception $e) {
            error_log('Remove favorite error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if property exists
     */
    private function propertyExists($property_id) {
        try {
            global $pdo;
            if (!$pdo) {
                return false;
            }

            $stmt = $pdo->prepare("SELECT id FROM properties WHERE id = ? AND status = 'available'");
            $stmt->execute([$property_id]);
            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            error_log('Property exists check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's favorite properties
     */
    private function getUserFavorites($user_id) {
        try {
            global $pdo;
            if (!$pdo) {
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
                WHERE pf.user_id = ?
                ORDER BY pf.created_at DESC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get user favorites error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property types for mobile filters
     */
    public function getPropertyTypes() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->query("SELECT id, name, icon FROM property_types WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get property types error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available cities for mobile filters
     */
    private function getAvailableCities() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->query("SELECT DISTINCT city FROM properties WHERE status = 'available' AND city IS NOT NULL ORDER BY city");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);

        } catch (Exception $e) {
            error_log('Get available cities error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get properties count for pagination
     */
    private function getPropertiesCount($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return 0;
            }

            $sql = "SELECT COUNT(*) as count FROM properties WHERE status = 'available'";
            $params = [];

            // Apply filters
            if (isset($filters['property_type'])) {
                $sql .= " AND property_type_id = ?";
                $params[] = $filters['property_type'];
            }

            if (isset($filters['city'])) {
                $sql .= " AND city LIKE ?";
                $params[] = '%' . $filters['city'] . '%';
            }

            if (isset($filters['min_price'])) {
                $sql .= " AND price >= ?";
                $params[] = $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $sql .= " AND price <= ?";
                $params[] = $filters['max_price'];
            }

            if (isset($filters['featured']) && $filters['featured']) {
                $sql .= " AND featured = 1";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0);

        } catch (Exception $e) {
            error_log('Get properties count error: ' . $e->getMessage());
            return 0;
        }
    }
}

?>

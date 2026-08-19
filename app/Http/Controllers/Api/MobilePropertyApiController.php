<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use App\Services\CacheService;
use App\Services\SyncService;
use PDO;
use App\Traits\TenantAwareTrait;

class MobilePropertyApiController extends BaseController
{
    use TenantAwareTrait;
    protected $syncService;

    public function __construct()
    {
        parent::__construct();
        $this->syncService = new \App\Services\SyncService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function properties()
    {
        $this->setCorsHeaders();

        try {
            $page = (int)(\App\Core\Security::sanitize($_GET['page'] ?? 1) ?? 1);
            $limit = min((int)(\App\Core\Security::sanitize($_GET['limit'] ?? 10) ?? 10), 50);
            $offset = ($page - 1) * $limit;

            $sync_mode = \App\Core\Security::sanitize($_GET['sync_mode'] ?? 'normal') ?? 'normal';
            $last_sync = \App\Core\Security::sanitize($_GET['last_sync'] ?? null) ?? null;
            $user_id = $GLOBALS['api_user_id'] ?? null;

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
            $bedrooms = \App\Core\Security::sanitize($_GET['bedrooms'] ?? null);
            if ($bedrooms !== null && $bedrooms !== '') {
                $filters['bedrooms'] = (int) $bedrooms;
            }
            $sort_by = \App\Core\Security::sanitize($_GET['sort_by'] ?? null);
            if ($sort_by !== null && $sort_by !== '') {
                $filters['sort_by'] = $sort_by;
            }
            $location = \App\Core\Security::sanitize($_GET['location'] ?? null);
            if ($location !== null && $location !== '') {
                $filters['location'] = $location;
            }
            $colony_id = \App\Core\Security::sanitize($_GET['colony_id'] ?? null);
            if ($colony_id !== null && $colony_id !== '') {
                $filters['colony_id'] = (int) $colony_id;
            }

            if ($sync_mode === 'sync' && $last_sync) {
                $properties = $this->syncService->getDeltaUpdates('properties', $last_sync, ['limit' => $limit, 'offset' => $offset]);
                $total_count = $this->getUpdatedPropertiesCount($last_sync, $filters);
            } else {
                $properties = $this->getPropertiesWithFilters($filters, $limit, $offset);
                $total_count = $this->getPropertiesCount($filters);
            }

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
            ]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Properties API error');
        }
    }

    public function property($id)
    {
        $this->setCorsHeaders();

        try {
            $property = $this->getPropertyById($id);

            if (!$property) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Property not found']);
                return;
            }

            $property['images'] = $this->getPropertyImages($id);
            $property['features'] = $this->getPropertyFeatures($id);

            echo json_encode(['success' => true, 'data' => $property]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Single Property API error');
        }
    }

    public function toggleFavorite()
    {
        $this->setCorsHeaders();

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $property_id = (int)($input['property_id'] ?? 0);
            $user_id = (int)($input['user_id'] ?? 0);

            if (!$property_id || !$user_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Property ID and User ID are required']);
                return;
            }

            if (!$this->propertyExists($property_id)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Property not found']);
                return;
            }

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

    public function userFavorites()
    {
        $this->setCorsHeaders();

        try {
            $user_id = (int)($GLOBALS['api_user_id'] ?? 0);

            if (!$user_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                return;
            }

            $favorites = $this->getUserFavorites($user_id);

            echo json_encode(['success' => true, 'data' => $favorites]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'User Favorites API error');
        }
    }

    public function propertyTypes()
    {
        $this->setCorsHeaders();

        try {
            $property_types = CacheService::cache('mobile_property_types', 3600, function() {
                return $this->getPropertyTypes();
            });

            echo json_encode(['success' => true, 'data' => $property_types]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Property Types API error');
        }
    }

    public function cities()
    {
        $this->setCorsHeaders();

        try {
            $cities = CacheService::cache('mobile_cities', 3600, function() {
                return $this->getAvailableCities();
            });

            echo json_encode(['success' => true, 'data' => $cities]);
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Cities API error');
        }
    }

    public function syncProperties()
    {
        $this->setCorsHeaders();
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(200, max(1, (int)($_GET['limit'] ?? 50)));
            $offset = ($page - 1) * $limit;

            list($tSql, $tParams) = $this->tenantWhere();
            $countParams = $tParams;
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM properties p WHERE 1=1 {$tSql}");
            $countStmt->execute($countParams);
            $total = (int)$countStmt->fetchColumn();

            $stmt = $this->db->prepare("
                SELECT p.id, p.title as property_name, pt.type as property_type, p.status, p.price, p.city as location, p.area_sqft, p.updated_at
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE 1=1 {$tSql}
                ORDER BY p.updated_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ");
            $stmt->execute($tParams);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->successResponse([
                'data' => $properties,
                'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
            ], 'Properties fetched for sync');
        } catch (\Exception $e) {
            error_log("[MobilePropertyApiController] exception: " . $e->getMessage());
            return $this->errorResponse('Internal server error');
        }
    }

    private function getPropertiesWithFilters($filters, $limit, $offset)
    {
        try {
            if (!$this->db) return [];
            $sql = "SELECT p.id, p.title, p.price, p.city, p.state, p.bedrooms, p.bathrooms, p.area_sqft, p.featured, p.created_at, pt.type as property_type, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id WHERE p.status IN ('active', '')";
            $params = [];
            $tid = $this->tenantId();
            if ($tid > 1) { $sql .= " AND p.tenant_id = :tenant_id"; $params['tenant_id'] = $tid; }
            if (isset($filters['property_type'])) { $sql .= " AND p.property_type_id = :propertyType"; $params['propertyType'] = $filters['property_type']; }
            if (isset($filters['city'])) { $sql .= " AND p.city LIKE :city"; $params['city'] = '%' . $filters['city'] . '%'; }
            if (isset($filters['min_price'])) { $sql .= " AND p.price >= :minPrice"; $params['minPrice'] = $filters['min_price']; }
            if (isset($filters['max_price'])) { $sql .= " AND p.price <= :maxPrice"; $params['maxPrice'] = $filters['max_price']; }
            if (isset($filters['featured']) && $filters['featured']) { $sql .= " AND p.featured = 1"; }
            if (isset($filters['bedrooms']) && $filters['bedrooms'] > 0) { $sql .= " AND p.bedrooms >= :bedrooms"; $params['bedrooms'] = (int) $filters['bedrooms']; }
            if (isset($filters['location'])) { $sql .= " AND (p.location LIKE :location OR p.city LIKE :location2)"; $params['location'] = '%' . $filters['location'] . '%'; $params['location2'] = '%' . $filters['location'] . '%'; }
            if (isset($filters['colony_id']) && $filters['colony_id'] > 0) { $sql .= " AND p.site_id = :colony_id"; $params['colony_id'] = (int) $filters['colony_id']; }
            $sortMap = ['newest' => 'p.created_at DESC', 'price_low' => 'p.price ASC', 'price_high' => 'p.price DESC', 'popular' => 'p.featured DESC, p.created_at DESC'];
            $orderBy = $sortMap[$filters['sort_by'] ?? 'newest'] ?? 'p.created_at DESC';
            $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
            $params['limit'] = (int)$limit; $params['offset'] = (int)$offset;
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            foreach ($params as $key => $value) { if ($key !== 'limit' && $key !== 'offset') $stmt->bindValue(':' . $key, $value); }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get properties with filters error: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertyById($id)
    {
        try {
            if (!$this->db) return null;
            $sql = "SELECT p.*, pt.type as property_type_name, NULL as property_type_icon FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id WHERE p.id = :id AND p.status IN ('active', '')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get property by ID error: ' . $e->getMessage());
            return null;
        }
    }

    private function getPropertyImages($property_id)
    {
        try {
            if (!$this->db) return [];
            $stmt = $this->db->prepare("SELECT * FROM property_images WHERE property_id = :propertyId ORDER BY is_primary DESC, sort_order ASC");
            $stmt->execute(['propertyId' => $property_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get property images error: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertyFeatures($property_id)
    {
        try {
            if (!$this->db) return [];
            $stmt = $this->db->prepare("SELECT * FROM property_features WHERE property_id = :propertyId ORDER BY feature_category, feature_name");
            $stmt->execute(['propertyId' => $property_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Get property features error: ' . $e->getMessage());
            return [];
        }
    }

    private function getPropertiesCount($filters)
    {
        try {
            $sql = "SELECT COUNT(*) FROM properties p WHERE p.status IN ('active', '')";
            $params = [];
            $tid = $this->tenantId();
            if ($tid > 1) { $sql .= " AND p.tenant_id = :tenant_id"; $params['tenant_id'] = $tid; }
            if (isset($filters['property_type'])) { $sql .= " AND p.property_type_id = :propertyType"; $params['propertyType'] = $filters['property_type']; }
            if (isset($filters['city'])) { $sql .= " AND p.city LIKE :city"; $params['city'] = '%' . $filters['city'] . '%'; }
            if (isset($filters['min_price'])) { $sql .= " AND p.price >= :minPrice"; $params['minPrice'] = $filters['min_price']; }
            if (isset($filters['max_price'])) { $sql .= " AND p.price <= :maxPrice"; $params['maxPrice'] = $filters['max_price']; }
            if (isset($filters['featured']) && $filters['featured']) { $sql .= " AND p.featured = 1"; }
            if (isset($filters['bedrooms']) && $filters['bedrooms'] > 0) { $sql .= " AND p.bedrooms >= :bedrooms"; $params['bedrooms'] = (int) $filters['bedrooms']; }
            if (isset($filters['location'])) { $sql .= " AND (p.location LIKE :location OR p.city LIKE :location2)"; $params['location'] = '%' . $filters['location'] . '%'; $params['location2'] = '%' . $filters['location'] . '%'; }
            if (isset($filters['colony_id']) && $filters['colony_id'] > 0) { $sql .= " AND p.site_id = :colony_id"; $params['colony_id'] = (int) $filters['colony_id']; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getUpdatedPropertiesCount($last_sync, $filters)
    {
        try {
            $sql = "SELECT COUNT(*) FROM properties p WHERE p.updated_at > :last_sync AND p.status IN ('active', '')";
            $params = ['last_sync' => $last_sync];
            $tid = $this->tenantId();
            if ($tid > 1) { $sql .= " AND p.tenant_id = :tenant_id"; $params['tenant_id'] = $tid; }
            if (!empty($filters)) {
                foreach ($filters as $key => $value) {
                    if ($key === 'property_type') { $sql .= " AND p.property_type = :property_type"; $params['property_type'] = $value; }
                    elseif ($key === 'city') { $sql .= " AND p.city = :city"; $params['city'] = $value; }
                    elseif ($key === 'min_price') { $sql .= " AND p.price >= :min_price"; $params['min_price'] = $value; }
                    elseif ($key === 'max_price') { $sql .= " AND p.price <= :max_price"; $params['max_price'] = $value; }
                    elseif ($key === 'featured') { $sql .= " AND p.featured = :featured"; $params['featured'] = 1; }
                }
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getUpdatedPropertiesCountSimple($last_sync)
    {
        try {
            $sql = "SELECT COUNT(*) FROM properties WHERE updated_at > :last_sync AND status IN ('active', '')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['last_sync' => $last_sync]);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getSyncQueueSize($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM sync_queue WHERE user_id = ?");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function propertyExists($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM properties WHERE id = ?");
            $stmt->execute([$id]);
            return (bool)$stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isFavorited($userId, $propertyId)
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM property_favorites WHERE user_id = ? AND property_id = ?");
            $stmt->execute([$userId, $propertyId]);
            return (bool)$stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function addFavoriteInternal($userId, $propertyId)
    {
        try {
            $this->db->execute("INSERT INTO property_favorites (user_id, property_id, created_at) VALUES (?, ?, NOW())", [$userId, $propertyId]);
        } catch (\Exception $e) {
            error_log("Add favorite error: " . $e->getMessage());
        }
    }

    private function removeFavoriteInternal($userId, $propertyId)
    {
        try {
            $this->db->execute("DELETE FROM property_favorites WHERE user_id = ? AND property_id = ?", [$userId, $propertyId]);
        } catch (\Exception $e) {
            error_log("Remove favorite error: " . $e->getMessage());
        }
    }

    private function getPropertyTypes()
    {
        try {
            $stmt = $this->db->query("SELECT id, name, icon FROM property_types WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAvailableCities()
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT city FROM properties WHERE status = 'active' ORDER BY city");
            $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(fn($c) => $c['city'], $cities);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserFavorites($user_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

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
            ";

            $tid = $this->tenantId();
            $params = ['userId' => $user_id];
            if ($tid > 1) {
                $sql .= " AND p.tenant_id = :tenant_id";
                $params['tenant_id'] = $tid;
            }

            $countSql = "SELECT COUNT(*) FROM property_favorites pf JOIN properties p ON pf.property_id = p.id WHERE pf.user_id = :userId";
            if ($tid > 1) {
                $countSql .= " AND p.tenant_id = :tenant_id";
            }
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $sql .= " ORDER BY pf.created_at DESC LIMIT {$limit} OFFSET {$offset}";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $results,
                'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
            ];
        } catch (\Exception $e) {
            error_log('Get user favorites error: ' . $e->getMessage());
            return [];
        }
    }

    private function createInquiry($data)
    {
        try {
            if (!$this->db) {
                return false;
            }
            $tid = (int)$this->tenantId();

            $sql = "
                INSERT INTO property_inquiries (
                    property_id, guest_name, guest_email, guest_phone,
                    subject, message, inquiry_type, status, priority, tenant_id, created_at
                ) VALUES (:propertyId, :name, :email, :phone, :subject, :message, :inquiryType, :status, :priority, :tenantId, NOW())
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
                'priority' => $data['priority'] ?? 'medium',
                'tenantId' => $tid
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

    public function browseProperties()
    {
        $this->setCorsHeaders();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(max(1, (int) ($_GET['per_page'] ?? 20)), 50);
        $offset = ($page - 1) * $perPage;

        $type = $_GET['type'] ?? null;
        $location = $_GET['location'] ?? null;
        $minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;
        $bedrooms = isset($_GET['bedrooms']) ? (int) $_GET['bedrooms'] : null;
        $colonyId = isset($_GET['colony_id']) ? (int) $_GET['colony_id'] : null;
        $sort = $_GET['sort'] ?? 'newest';

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();

            $where = "WHERE p.status IN ('active', '')";
            $params = [];

            $tid = $this->tenantId();
            if ($tid > 1) {
                $where .= " AND p.tenant_id = ?";
                $params[] = $tid;
            }

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
            if ($colonyId !== null) {
                $where .= " AND p.site_id = ?";
                $params[] = $colonyId;
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
                       p.bedrooms, p.bathrooms, p.area_sqft, p.site_id, p.featured, p.created_at,
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
            error_log('MobilePropertyApiController::browseProperties() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch properties', 'code' => 500]);
        }
    }

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

            $tid = $this->tenantId();
            if ($tid > 1) {
                $where[] = 'up.tenant_id = ?';
                $params[] = $tid;
            }

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

            $sql = "
                SELECT up.id, up.user_id, up.property_type, up.listing_type, up.name as title,
                       up.description, up.price, up.address, up.city_name as city, up.location,
                       up.area_sqft, up.bedrooms, up.bathrooms, up.status,
                       up.is_featured, up.is_urgent, up.is_premium, up.expires_at,
                       up.created_at, up.updated_at,
                       u.name as owner_name, u.phone as owner_phone,
                       COALESCE(up.image, (
                           SELECT pi.image_path FROM property_images pi 
                           WHERE pi.property_id = up.id AND pi.is_primary = 1 
                           LIMIT 1
                       ), (
                           SELECT pi.image_path FROM property_images pi 
                           WHERE pi.property_id = up.id 
                           ORDER BY pi.sort_order ASC LIMIT 1
                       )) as main_image,
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
                $p['image_url'] = $p['main_image'] ? (str_starts_with((string)$p['main_image'], 'http') ? $p['main_image'] : BASE_URL . '/' . ltrim($p['main_image'], '/')) : null;
            }

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
            error_log("[MobilePropertyApiController] getMarketplace() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch marketplace']);
        }
    }

    public function getPremiumProperties()
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));

            list($tSql, $tParams) = $this->tenantWhere();
            $tCond = !empty($tSql) ? " AND up.tenant_id = ?" : "";
            $stmt = $pdo->prepare("
                SELECT up.id, up.user_id, up.property_type, up.listing_type, up.name as title,
                       up.description, up.price, up.address, up.city_name as city, up.location,
                       up.area_sqft, up.bedrooms, up.bathrooms,
                       up.is_featured, up.is_urgent, up.is_premium,
                       u.name as owner_name,
                       up.image as main_image
                FROM user_properties up
                LEFT JOIN users u ON u.id = up.user_id
                WHERE up.status = 'approved' AND (up.is_premium = 1 OR up.is_featured = 1 OR up.is_urgent = 1) {$tCond}
                ORDER BY up.is_premium DESC, up.is_featured DESC, up.created_at DESC
                LIMIT ?
            ");
            $stmt->execute(array_merge($tParams, [$limit]));
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($properties as &$p) {
                $p['price'] = (float)($p['price'] ?? 0);
                $p['is_premium'] = (bool)$p['is_premium'];
                $p['is_featured'] = (bool)$p['is_featured'];
                $p['is_urgent'] = (bool)$p['is_urgent'];
                $p['image_url'] = $p['main_image'] ? (str_starts_with((string)$p['main_image'], 'http') ? $p['main_image'] : BASE_URL . '/' . ltrim($p['main_image'], '/')) : null;
            }

            echo json_encode([
                'success' => true,
                'data' => $properties,
                'count' => count($properties)
            ]);
        } catch (\Exception $e) {
            error_log("[MobilePropertyApiController] getPremiumProperties() exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch premium listings']);
        }
    }

    public function getFeaturedProperties()
    {
        $this->setCorsHeaders();
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            list($tSql, $tParams) = $this->tenantWhere();
            $tCond = !empty($tSql) ? " AND p.tenant_id = ?" : "";
            $stmt = $pdo->prepare("
                SELECT p.*, pt.type as property_type_name,
                       (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC LIMIT 1) as main_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.featured = 1 AND p.status = 'active' {$tCond}
                ORDER BY p.created_at DESC
                LIMIT 20
            ");
            $stmt->execute($tParams);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $properties]);
        } catch (\Throwable $e) {
            error_log('[MobilePropertyApiController] getFeaturedProperties: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch featured properties']);
        }
    }

    public function propertyDetail($id)
    {
        $this->setCorsHeaders();

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $id = (int) $id;

            list($tSql, $tParams) = $this->tenantWhere();
            $tCond = !empty($tSql) ? " AND p.tenant_id = ?" : "";
            $stmt = $pdo->prepare("
                SELECT p.*, pt.type as property_type_name
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.id = ? AND p.status = 'active' {$tCond}
            ");
            $stmt->execute(array_merge([$id], $tParams));
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$property) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Property not found', 'code' => 404]);
                return;
            }

            $imgStmt = $pdo->prepare("
                SELECT id, image_path, is_primary, sort_order
                FROM property_images
                WHERE property_id = ?
                ORDER BY is_primary DESC, sort_order ASC
            ");
            $imgStmt->execute([$id]);
            $property['images'] = $imgStmt->fetchAll(\PDO::FETCH_ASSOC);

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
                error_log('propertyDetail features skipped: ' . $fe->getMessage());
            }

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
            error_log('MobilePropertyApiController::propertyDetail() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch property details', 'code' => 500]);
        }
    }

    public function searchProperties()
    {
        $this->setCorsHeaders();

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

            list($tSql, $tParams) = $this->tenantWhere();
            $tCond = !empty($tSql) ? " {$tSql}" : "";

            $countStmt = $pdo->prepare("
                SELECT COUNT(*) FROM properties p
                WHERE p.status = 'active'
                  AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.city LIKE ? OR p.type LIKE ?){$tCond}
            ");
            $countStmt->execute(array_merge([$like, $like, $like, $like, $like], $tParams));
            $total = (int) $countStmt->fetchColumn();

            $stmt = $pdo->prepare("
                SELECT p.id, p.title, p.price, p.type, p.location, p.city, p.state,
                       p.bedrooms, p.bathrooms, p.area_sqft, p.featured, p.created_at,
                       (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM properties p
                WHERE p.status = 'active'
                  AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.city LIKE ? OR p.type LIKE ?){$tCond}
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}
            ");
            $stmt->execute(array_merge([$like, $like, $like, $like, $like], $tParams));
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
            error_log('MobilePropertyApiController::searchProperties() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Search failed', 'code' => 500]);
        }
    }

    public function getFavorites()
    {
        $this->setCorsHeaders();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $limit = (int) ($_GET['limit'] ?? 50);

            $favoriteModel = new \App\Models\PropertyFavorite();
            $favorites = $favoriteModel->getUserFavorites($userId, $limit);

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
            error_log('MobilePropertyApiController::getFavorites() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch favorites']);
        }
    }

    public function removeFavorite($id = null)
    {
        $this->setCorsHeaders();

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
            error_log('MobilePropertyApiController::removeFavorite() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to remove favorite']);
        }
    }

    public function addFavorite()
    {
        $this->setCorsHeaders();

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
            error_log('MobilePropertyApiController::addFavorite() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to add favorite']);
        }
    }

    public function checkFavorite()
    {
        $this->setCorsHeaders();

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
            error_log('MobilePropertyApiController::checkFavorite() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to check favorite']);
        }
    }

    public function getFavoritesStats()
    {
        $this->setCorsHeaders();

        try {
            $userId = (int) $GLOBALS['api_user_id'];
            $favoriteModel = new \App\Models\PropertyFavorite();
            $stats = $favoriteModel->getStats();

            $userFavorites = $favoriteModel->getUserFavorites($userId, 1000);
            $stats['user_total'] = count($userFavorites);

            echo json_encode([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            error_log('MobilePropertyApiController::getFavoritesStats() exception: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch stats']);
        }
    }

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
                       c.starting_price, c.image_path, c.layout_image, c.is_featured, c.is_active,
                       c.latitude, c.longitude, c.map_link,
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
                $colony['image_url'] = $colony['image_path'] ? (str_starts_with((string)$colony['image_path'], 'http') ? $colony['image_path'] : BASE_URL . '/' . ltrim($colony['image_path'], '/')) : null;
                $colony['layout_image_url'] = $colony['layout_image'] ? (str_starts_with((string)$colony['layout_image'], 'http') ? $colony['layout_image'] : BASE_URL . '/' . ltrim($colony['layout_image'], '/')) : null;
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
            $colony['image_url'] = $colony['image_path'] ? (str_starts_with((string)$colony['image_path'], 'http') ? $colony['image_path'] : BASE_URL . '/' . ltrim($colony['image_path'], '/')) : null;
            $colony['layout_image_url'] = $colony['layout_image'] ? (str_starts_with((string)$colony['layout_image'], 'http') ? $colony['layout_image'] : BASE_URL . '/' . ltrim($colony['layout_image'], '/')) : null;
            $colony['latitude'] = $colony['latitude'] ? (float)$colony['latitude'] : null;
            $colony['longitude'] = $colony['longitude'] ? (float)$colony['longitude'] : null;
            echo json_encode(['success' => true, 'data' => $colony]);
        } catch (\Throwable $e) {
            error_log('getColonyDetail error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch colony']);
        }
    }

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
            error_log('MobilePropertyApiController::getPlotDetail error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }

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
            error_log('MobilePropertyApiController::getAllPlots error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }

    public function holdPlot($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->db->prepare("SELECT status FROM plots WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tid]);
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
            $stmt = $this->db->prepare("UPDATE plots SET status = 'hold', held_by = ?, held_at = NOW() WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$userId, $id, $tid]);
            echo json_encode(['success' => true, 'message' => 'Plot held successfully']);
        } catch (\Exception $e) {
            error_log('MobilePropertyApiController::holdPlot error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }

    public function releasePlot($id) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->db->prepare("UPDATE plots SET status = 'available', held_by = NULL, held_at = NULL WHERE id = ? AND held_by = ? AND tenant_id = ?");
            $stmt->execute([$id, $userId, $tid]);
            echo json_encode(['success' => true, 'message' => 'Plot released successfully']);
        } catch (\Exception $e) {
            error_log('MobilePropertyApiController::releasePlot error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }

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

            $pricePerSqft = $areaSqft > 0 ? $estimatedPrice / $areaSqft : 0;
            $minPrice = $estimatedPrice * 0.85;
            $maxPrice = $estimatedPrice * 1.15;

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

    public function getMyListings() {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $stmt = $this->db->query(
            "SELECT * FROM user_properties WHERE user_id = ? OR posted_by = ? ORDER BY is_featured DESC, created_at DESC",
            [$userId, $userId]
        );

        echo json_encode(['success' => true, 'listings' => $stmt->fetchAll()]);
    }

    public function getListingPackages() {
        $this->setCorsHeaders();
        $stmt = $this->db->query(
            "SELECT * FROM listing_packages WHERE status = 'active' ORDER BY price ASC"
        );
        echo json_encode(['success' => true, 'packages' => $stmt->fetchAll()]);
    }

    public function submitPropertyInquiry() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $propertyId = (int)($input['property_id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $message = trim($input['message'] ?? '');

        if (!$propertyId || !$name || !$phone) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            return;
        }

        $property = $this->db->query(
            "SELECT up.*, u.name as owner_name FROM user_properties up 
             LEFT JOIN users u ON up.user_id = u.id 
             WHERE up.id = ? AND up.tenant_id = 1",
            [$propertyId]
        )->fetch();

        if (!$property) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Property not found']);
            return;
        }

        $this->db->query(
            "INSERT INTO property_inquiries (property_id, name, phone, message, tenant_id) VALUES (?, ?, ?, ?, 1)",
            [$propertyId, $name, $phone, $message]
        );

        $inquiryId = $this->db->query("SELECT LAST_INSERT_ID() as id")->fetch()['id'];

        if (!empty($property['user_id'])) {
            try {
                $pushService = new \App\Services\Communication\PushNotificationService();
                $pushService->sendToUser(
                    (int)$property['user_id'],
                    [
                        'title' => 'New Property Inquiry',
                        'body' => "{$name} inquired about {$property['name']}",
                        'data' => [
                            'type' => 'property_inquiry',
                            'inquiry_id' => (string)$inquiryId,
                            'property_id' => (string)$propertyId,
                        ],
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                    ]
                );
            } catch (\Throwable $e) {
                error_log('Push notification failed: ' . $e->getMessage());
            }
        }

        echo json_encode(['success' => true, 'message' => 'Inquiry sent successfully', 'inquiry_id' => $inquiryId]);
    }

    public function boostProperty() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        $propertyId = (int)($input['property_id'] ?? 0);
        $packageId = (int)($input['package_id'] ?? 0);

        if (!$userId || !$propertyId || !$packageId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing fields']);
            return;
        }

        $pkg = $this->db->fetch("SELECT * FROM listing_packages WHERE id = ? AND status = 'active'", [$packageId]);
        if (!$pkg) {
            echo json_encode(['success' => false, 'error' => 'Package not found']);
            return;
        }

        $updates = [];
        $params = [];
        if ($pkg['is_featured']) { $updates[] = 'is_featured = 1'; }
        if ($pkg['is_premium']) { $updates[] = 'is_premium = 1'; }
        if ($pkg['is_urgent']) { $updates[] = 'is_urgent = 1'; }

        if ($updates) {
            $params[] = $propertyId;
            $this->db->query("UPDATE user_properties SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        }

        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$pkg['duration_days']} days"));
        $this->db->query(
            "INSERT INTO property_boost_orders (user_id, property_id, package_id, amount, status, starts_at, expires_at, tenant_id) VALUES (?, ?, ?, ?, 'active', NOW(), ?, 1)",
            [$userId, $propertyId, $packageId, $pkg['price'], $expiresAt]
        );

        echo json_encode(['success' => true, 'message' => "Property boosted with {$pkg['name']}"]);
    }

    public function sendPropertyMessage() {
        $this->setCorsHeaders();
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);

        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $propertyId = (int)($input['property_id'] ?? 0);
        $receiverId = (int)($input['receiver_id'] ?? 0);
        $message = trim($input['message'] ?? '');

        if (!$propertyId || !$receiverId || !$message) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing fields']);
            return;
        }

        $sender = $this->db->query(
            "SELECT name FROM users WHERE id = ?",
            [$userId]
        )->fetch();
        $senderName = $sender['name'] ?? 'Someone';

        $this->db->query(
            "INSERT INTO property_messages (property_id, sender_id, receiver_id, message, tenant_id) VALUES (?, ?, ?, ?, 1)",
            [$propertyId, $userId, $receiverId, $message]
        );

        $messageId = $this->db->query("SELECT LAST_INSERT_ID() as id")->fetch()['id'];

        try {
            $pushService = new \App\Services\Communication\PushNotificationService();
            $pushService->sendToUser(
                $receiverId,
                [
                    'title' => 'New Message',
                    'body' => "{$senderName}: " . substr($message, 0, 50) . (strlen($message) > 50 ? '...' : ''),
                    'data' => [
                        'type' => 'property_message',
                        'message_id' => (string)$messageId,
                        'property_id' => (string)$propertyId,
                        'sender_id' => (string)$userId,
                    ],
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]
            );
        } catch (\Throwable $e) {
            error_log('Push notification failed: ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message_id' => $messageId]);
    }

    public function getPropertyMessages($propertyId) {
        $this->setCorsHeaders();
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        $propertyId = (int)$propertyId;

        $stmt = $this->db->query(
            "SELECT pm.*, u.name as sender_name FROM property_messages pm 
             LEFT JOIN users u ON pm.sender_id = u.id 
             WHERE pm.property_id = ? AND (pm.sender_id = ? OR pm.receiver_id = ?) 
             ORDER BY pm.created_at ASC LIMIT 100",
            [$propertyId, $userId, $userId]
        );

        echo json_encode(['success' => true, 'messages' => $stmt->fetchAll()]);
    }
}

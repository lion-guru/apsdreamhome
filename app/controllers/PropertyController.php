<?php
/**
 * Property Controller
 * Handles property listings, search, and property details
 */

namespace App\Controllers;

class PropertyController extends BaseController {
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display property listings with search and filters
     */
    public function index() {
        // Set page data
        $this->data['page_title'] = 'Properties - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Properties', 'url' => BASE_URL . 'properties']
        ];

        // Get search filters from URL
        $filters = $this->getFilters();

        // Get properties based on filters
        $this->data['properties'] = $this->getProperties($filters);
        $this->data['filters'] = $filters;
        $this->data['property_types'] = $this->getPropertyTypes();
        $this->data['locations'] = $this->getLocations();

        // Pagination data
        $this->data['total_properties'] = $this->getTotalProperties($filters);
        $this->data['current_page'] = $filters['page'];
        $this->data['per_page'] = $filters['per_page'];
        $this->data['total_pages'] = ceil($this->data['total_properties'] / $filters['per_page']);

        // Render the properties page
        $this->render('pages/properties');
    }

    /**
     * Display individual property details
     */
    public function show() {
        $property_id = $_GET['id'] ?? null;

        if (!$property_id) {
            $this->show404();
            return;
        }

        $property = $this->getPropertyById($property_id);

        if (!$property) {
            $this->show404();
            return;
        }

        // Set page data
        $this->data['page_title'] = htmlspecialchars($property['title']) . ' - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Properties', 'url' => BASE_URL . 'properties'],
            ['title' => htmlspecialchars($property['title']), 'url' => BASE_URL . 'property?id=' . $property_id]
        ];

        $this->data['property'] = $property;
        $this->data['property_images'] = $this->getPropertyImages($property_id);
        $this->data['related_properties'] = $this->getRelatedProperties($property);

        // Render the property detail page
        $this->render('pages/property_detail');
    }

    /**
     * Get search filters from URL parameters
     */
    private function getFilters() {
        return [
            'type' => $_GET['type'] ?? '',
            'location' => $_GET['location'] ?? '',
            'city' => $_GET['city'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'bedrooms' => $_GET['bedrooms'] ?? '',
            'bathrooms' => $_GET['bathrooms'] ?? '',
            'min_area' => $_GET['min_area'] ?? '',
            'max_area' => $_GET['max_area'] ?? '',
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC',
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'per_page' => min(50, max(6, (int)($_GET['per_page'] ?? 12))),
            'featured' => isset($_GET['featured']) ? 1 : 0
        ];
    }

    /**
     * Get properties based on filters
     */
    private function getProperties($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            // Build WHERE clause
            $where_conditions = ["p.status = 'available'"];
            $params = [];

            if (!empty($filters['type'])) {
                $where_conditions[] = "p.property_type_id = (SELECT id FROM property_types WHERE name = ?)";
                $params[] = $filters['type'];
            }

            if (!empty($filters['location'])) {
                $where_conditions[] = "p.city LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }

            if (!empty($filters['city'])) {
                $where_conditions[] = "p.city = ?";
                $params[] = $filters['city'];
            }

            if (!empty($filters['min_price'])) {
                $where_conditions[] = "p.price >= ?";
                $params[] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $where_conditions[] = "p.price <= ?";
                $params[] = $filters['max_price'];
            }

            if (!empty($filters['bedrooms'])) {
                $where_conditions[] = "p.bedrooms >= ?";
                $params[] = $filters['bedrooms'];
            }

            if (!empty($filters['bathrooms'])) {
                $where_conditions[] = "p.bathrooms >= ?";
                $params[] = $filters['bathrooms'];
            }

            if (!empty($filters['min_area'])) {
                $where_conditions[] = "p.area_sqft >= ?";
                $params[] = $filters['min_area'];
            }

            if (!empty($filters['max_area'])) {
                $where_conditions[] = "p.area_sqft <= ?";
                $params[] = $filters['max_area'];
            }

            if ($filters['featured']) {
                $where_conditions[] = "p.featured = 1";
            }

            $where_clause = implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['price', 'created_at', 'title', 'area_sqft', 'bedrooms'];
            $sort = in_array($filters['sort'], $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY p.{$sort} {$order}";

            // Calculate offset for pagination
            $offset = ($filters['page'] - 1) * $filters['per_page'];

            // Build the query
            $sql = "
                SELECT
                    p.id,
                    p.title,
                    p.address,
                    p.price,
                    p.bedrooms,
                    p.bathrooms,
                    p.area_sqft,
                    p.status,
                    p.description,
                    p.created_at,
                    p.city,
                    p.state,
                    p.latitude,
                    p.longitude,
                    p.featured,
                    u.name as agent_name,
                    u.phone as agent_phone,
                    u.email as agent_email,
                    pt.name as property_type,
                    (SELECT pi.image_path
                     FROM property_images pi
                     WHERE pi.property_id = p.id
                     ORDER BY pi.is_primary DESC, pi.sort_order ASC
                     LIMIT 1) as main_image,
                    (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images
                FROM properties p
                LEFT JOIN users u ON p.created_by = u.id AND u.status = 'active'
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE {$where_clause}
                {$order_clause}
                LIMIT {$filters['per_page']} OFFSET {$offset}
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Properties query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of properties for pagination
     */
    private function getTotalProperties($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return 0;
            }

            // Build WHERE clause (same as getProperties)
            $where_conditions = ["p.status = 'available'"];
            $params = [];

            if (!empty($filters['type'])) {
                $where_conditions[] = "p.property_type_id = (SELECT id FROM property_types WHERE name = ?)";
                $params[] = $filters['type'];
            }

            if (!empty($filters['location'])) {
                $where_conditions[] = "p.city LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }

            if (!empty($filters['city'])) {
                $where_conditions[] = "p.city = ?";
                $params[] = $filters['city'];
            }

            if (!empty($filters['min_price'])) {
                $where_conditions[] = "p.price >= ?";
                $params[] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $where_conditions[] = "p.price <= ?";
                $params[] = $filters['max_price'];
            }

            if (!empty($filters['bedrooms'])) {
                $where_conditions[] = "p.bedrooms >= ?";
                $params[] = $filters['bedrooms'];
            }

            if (!empty($filters['bathrooms'])) {
                $where_conditions[] = "p.bathrooms >= ?";
                $params[] = $filters['bathrooms'];
            }

            if (!empty($filters['min_area'])) {
                $where_conditions[] = "p.area_sqft >= ?";
                $params[] = $filters['min_area'];
            }

            if (!empty($filters['max_area'])) {
                $where_conditions[] = "p.area_sqft <= ?";
                $params[] = $filters['max_area'];
            }

            if ($filters['featured']) {
                $where_conditions[] = "p.featured = 1";
            }

            $where_clause = implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total FROM properties p WHERE {$where_clause}";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);

        } catch (Exception $e) {
            error_log('Total properties query error: ' . $e->getMessage());
            return 0;
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

            $stmt = $pdo->prepare("
                SELECT
                    p.id,
                    p.title,
                    p.address,
                    p.price,
                    p.bedrooms,
                    p.bathrooms,
                    p.area_sqft,
                    p.status,
                    p.description,
                    p.created_at,
                    p.city,
                    p.state,
                    p.latitude,
                    p.longitude,
                    p.featured,
                    u.name as agent_name,
                    u.phone as agent_phone,
                    u.email as agent_email,
                    pt.name as property_type
                FROM properties p
                LEFT JOIN users u ON p.created_by = u.id AND u.status = 'active'
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.id = ? AND p.status = 'available'
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Property query error: ' . $e->getMessage());
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

            $stmt = $pdo->prepare("
                SELECT image_path, is_primary, sort_order
                FROM property_images
                WHERE property_id = ?
                ORDER BY is_primary DESC, sort_order ASC
            ");
            $stmt->execute([$property_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Property images query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get related properties
     */
    private function getRelatedProperties($property) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->prepare("
                SELECT
                    p.id,
                    p.title,
                    p.price,
                    p.city,
                    p.bedrooms,
                    p.bathrooms,
                    p.area_sqft,
                    (SELECT pi.image_path
                     FROM property_images pi
                     WHERE pi.property_id = p.id
                     ORDER BY pi.is_primary DESC, pi.sort_order ASC
                     LIMIT 1) as main_image
                FROM properties p
                WHERE p.status = 'available'
                  AND p.id != ?
                  AND (p.city = ? OR p.property_type_id = (
                      SELECT property_type_id FROM properties WHERE id = ?
                  ))
                ORDER BY p.created_at DESC
                LIMIT 4
            ");
            $stmt->execute([$property['id'], $property['city'], $property['id']]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Related properties query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property types for filter dropdown
     */
    private function getPropertyTypes() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->query("SELECT id, name FROM property_types ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Property types query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get locations for filter dropdown
     */
    private function getLocations() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->query("
                SELECT DISTINCT city, state
                FROM properties
                WHERE city IS NOT NULL AND city != '' AND status = 'available'
                ORDER BY state, city
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Locations query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Show 404 page
     */
    private function show404() {
        header("HTTP/1.0 404 Not Found");
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>404 - Property Not Found</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <h1 class="display-1 text-danger">404</h1>
                        <h2>Property Not Found</h2>
                        <p class="lead">The property you are looking for might have been sold or removed.</p>
                        <a href="' . BASE_URL . 'properties" class="btn btn-primary">Browse Properties</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        exit();
    }
}

?>

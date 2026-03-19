<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Property Controller - Custom MVC Implementation
 * Handles admin CRUD operations for properties
 */
class PropertyController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * List all properties for admin
     */
    public function index()
    {
        try {
            // Get query parameters
            $page = (int)($_GET['page'] ?? 1);
            $search = trim($_GET['search'] ?? '');
            $filters = [
                'type' => $_GET['type'] ?? '',
                'status' => $_GET['status'] ?? '',
                'category_id' => $_GET['category_id'] ?? '',
                'min_price' => $_GET['min_price'] ?? '',
                'max_price' => $_GET['max_price'] ?? '',
                'location' => $_GET['location'] ?? ''
            ];
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT p.*, 
                           c.name as category_name,
                           pr.project_name,
                           pl.plot_number,
                           l.land_title,
                           s.site_name,
                           b.booking_number,
                           u.name as customer_name
                    FROM properties p
                    LEFT JOIN property_categories c ON p.category_id = c.id
                    LEFT JOIN projects pr ON p.project_id = pr.id
                    LEFT JOIN plots pl ON p.plot_id = pl.id
                    LEFT JOIN land_records l ON p.land_id = l.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN bookings b ON p.id = b.property_id
                    LEFT JOIN users u ON b.customer_id = u.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($filters['type'])) {
                $sql .= " AND p.property_type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND p.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['category_id'])) {
                $sql .= " AND p.category_id = ?";
                $params[] = (int)$filters['category_id'];
            }

            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = (float)$filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = (float)$filters['max_price'];
            }

            if (!empty($filters['location'])) {
                $sql .= " AND p.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }

            $sql .= " ORDER BY p.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT p.*, c.name as category_name, pr.project_name, pl.plot_number, l.land_title, s.site_name, b.booking_number, u.name as customer_name", "SELECT COUNT(DISTINCT p.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll();

            // Get property categories for filter dropdown
            $categories = $this->db->fetchAll("SELECT * FROM property_categories ORDER BY name");

            $data = [
                'page_title' => 'Property Management - APS Dream Home',
                'active_page' => 'properties',
                'properties' => $properties,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => $filters,
                'categories' => $categories
            ];

            return $this->render('admin/properties/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load properties');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new property
     */
    public function create()
    {
        try {
            // Get dropdown options
            $categories = $this->db->fetchAll("SELECT * FROM property_categories ORDER BY name");
            $projects = $this->db->fetchAll("SELECT * FROM projects WHERE status IN ('planning', 'in_progress') ORDER BY project_name");
            $plots = $this->db->fetchAll("SELECT p.*, l.land_title FROM plots p LEFT JOIN land_records l ON p.land_id = l.id WHERE p.status = 'available' ORDER BY p.plot_number");
            $sites = $this->db->fetchAll("SELECT * FROM sites ORDER BY site_name");

            $data = [
                'page_title' => 'Create Property - APS Dream Home',
                'active_page' => 'properties',
                'categories' => $categories,
                'projects' => $projects,
                'plots' => $plots,
                'sites' => $sites
            ];

            return $this->render('admin/properties/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property form');
            return $this->redirect('admin/properties');
        }
    }

    /**
     * Store a newly created property
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['title', 'property_type', 'price', 'total_area'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate numeric fields
            $price = (float)$data['price'];
            $totalArea = (float)$data['total_area'];

            if ($price <= 0 || $totalArea <= 0) {
                return $this->jsonError('Price and total area must be greater than 0', 400);
            }

            // Handle image uploads
            $images = [];
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $name,
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];

                        $imageValidation = $this->validateImage($file);
                        if ($imageValidation['valid']) {
                            $imagePath = $this->uploadImage($file);
                            if ($imagePath) {
                                $images[] = $imagePath;
                            }
                        }
                    }
                }
            }

            $this->db->beginTransaction();

            try {
                // Insert property
                $sql = "INSERT INTO properties 
                        (title, description, property_type, category_id, price, total_area, 
                         bedrooms, bathrooms, location, address, city, state, pincode,
                         project_id, plot_id, land_id, site_id, amenities, features, 
                         status, featured, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    CoreFunctionsServiceCustom::validateInput($data['title'], 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['description'] ?? '', 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['property_type'], 'string'),
                    (int)($data['category_id'] ?? 0),
                    $price,
                    $totalArea,
                    (int)($data['bedrooms'] ?? 0),
                    (int)($data['bathrooms'] ?? 0),
                    CoreFunctionsServiceCustom::validateInput($data['location'] ?? '', 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['address'] ?? '', 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['city'] ?? '', 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['state'] ?? '', 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['pincode'] ?? '', 'string'),
                    (int)($data['project_id'] ?? 0),
                    (int)($data['plot_id'] ?? 0),
                    (int)($data['land_id'] ?? 0),
                    (int)($data['site_id'] ?? 0),
                    CoreFunctionsServiceCustom::validateInput($data['amenities'] ?? '', 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['features'] ?? '', 'string'),
                    CoreFunctionsServiceCustom::validateInput($data['status'] ?? 'available', 'string'),
                    (int)($data['featured'] ?? 0)
                ]);

                $propertyId = $this->db->lastInsertId();

                // Insert property images
                if (!empty($images)) {
                    $sql = "INSERT INTO property_images (property_id, image_path, created_at) VALUES (?, ?, NOW())";
                    $stmt = $this->db->prepare($sql);
                    foreach ($images as $image) {
                        $stmt->execute([$propertyId, $image]);
                    }
                }

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'property_created', [
                    'property_id' => $propertyId,
                    'title' => $data['title']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Property created successfully',
                    'property_id' => $propertyId
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Property Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create property', 500);
        }
    }

    /**
     * Display the specified property
     */
    public function show($id)
    {
        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                $this->setFlash('error', 'Invalid property ID');
                return $this->redirect('admin/properties');
            }

            // Get property details
            $sql = "SELECT p.*, 
                           c.name as category_name,
                           pr.project_name,
                           pl.plot_number,
                           l.land_title,
                           s.site_name,
                           b.booking_number,
                           u.name as customer_name,
                           u.email as customer_email
                    FROM properties p
                    LEFT JOIN property_categories c ON p.category_id = c.id
                    LEFT JOIN projects pr ON p.project_id = pr.id
                    LEFT JOIN plots pl ON p.plot_id = pl.id
                    LEFT JOIN land_records l ON p.land_id = l.id
                    LEFT JOIN sites s ON p.site_id = s.id
                    LEFT JOIN bookings b ON p.id = b.property_id
                    LEFT JOIN users u ON b.customer_id = u.id
                    WHERE p.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propertyId]);
            $property = $stmt->fetch();

            if (!$property) {
                $this->setFlash('error', 'Property not found');
                return $this->redirect('admin/properties');
            }

            // Get property images
            $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propertyId]);
            $images = $stmt->fetchAll();

            $data = [
                'page_title' => 'Property Details - APS Dream Home',
                'active_page' => 'properties',
                'property' => $property,
                'images' => $images
            ];

            return $this->render('admin/properties/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property details');
            return $this->redirect('admin/properties');
        }
    }

    /**
     * Show the form for editing the specified property
     */
    public function edit($id)
    {
        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                $this->setFlash('error', 'Invalid property ID');
                return $this->redirect('admin/properties');
            }

            // Get property details
            $sql = "SELECT * FROM properties WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propertyId]);
            $property = $stmt->fetch();

            if (!$property) {
                $this->setFlash('error', 'Property not found');
                return $this->redirect('admin/properties');
            }

            // Get dropdown options
            $categories = $this->db->fetchAll("SELECT * FROM property_categories ORDER BY name");
            $projects = $this->db->fetchAll("SELECT * FROM projects WHERE status IN ('planning', 'in_progress') ORDER BY project_name");
            $plots = $this->db->fetchAll("SELECT p.*, l.land_title FROM plots p LEFT JOIN land_records l ON p.land_id = l.id WHERE p.status = 'available' ORDER BY p.plot_number");
            $sites = $this->db->fetchAll("SELECT * FROM sites ORDER BY site_name");

            // Get property images
            $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propertyId]);
            $images = $stmt->fetchAll();

            $data = [
                'page_title' => 'Edit Property - APS Dream Home',
                'active_page' => 'properties',
                'property' => $property,
                'categories' => $categories,
                'projects' => $projects,
                'plots' => $plots,
                'sites' => $sites,
                'images' => $images
            ];

            return $this->render('admin/properties/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property form');
            return $this->redirect('admin/properties');
        }
    }

    /**
     * Update the specified property
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                return $this->jsonError('Invalid property ID', 400);
            }

            $data = $_POST;

            // Check if property exists
            $sql = "SELECT * FROM properties WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propertyId]);
            $property = $stmt->fetch();

            if (!$property) {
                return $this->jsonError('Property not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['title'])) {
                $updateFields[] = "title = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['title'], 'string');
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            if (!empty($data['property_type'])) {
                $updateFields[] = "property_type = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['property_type'], 'string');
            }

            if (isset($data['category_id'])) {
                $updateFields[] = "category_id = ?";
                $updateValues[] = (int)$data['category_id'];
            }

            if (isset($data['price'])) {
                $price = (float)$data['price'];
                if ($price <= 0) {
                    return $this->jsonError('Price must be greater than 0', 400);
                }
                $updateFields[] = "price = ?";
                $updateValues[] = $price;
            }

            if (isset($data['total_area'])) {
                $totalArea = (float)$data['total_area'];
                if ($totalArea <= 0) {
                    return $this->jsonError('Total area must be greater than 0', 400);
                }
                $updateFields[] = "total_area = ?";
                $updateValues[] = $totalArea;
            }

            if (isset($data['bedrooms'])) {
                $updateFields[] = "bedrooms = ?";
                $updateValues[] = (int)$data['bedrooms'];
            }

            if (isset($data['bathrooms'])) {
                $updateFields[] = "bathrooms = ?";
                $updateValues[] = (int)$data['bathrooms'];
            }

            if (isset($data['location'])) {
                $updateFields[] = "location = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['location'], 'string');
            }

            if (isset($data['address'])) {
                $updateFields[] = "address = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['address'], 'string');
            }

            if (isset($data['city'])) {
                $updateFields[] = "city = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['city'], 'string');
            }

            if (isset($data['state'])) {
                $updateFields[] = "state = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['state'], 'string');
            }

            if (isset($data['pincode'])) {
                $updateFields[] = "pincode = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['pincode'], 'string');
            }

            if (isset($data['project_id'])) {
                $updateFields[] = "project_id = ?";
                $updateValues[] = (int)$data['project_id'];
            }

            if (isset($data['plot_id'])) {
                $updateFields[] = "plot_id = ?";
                $updateValues[] = (int)$data['plot_id'];
            }

            if (isset($data['land_id'])) {
                $updateFields[] = "land_id = ?";
                $updateValues[] = (int)$data['land_id'];
            }

            if (isset($data['site_id'])) {
                $updateFields[] = "site_id = ?";
                $updateValues[] = (int)$data['site_id'];
            }

            if (isset($data['amenities'])) {
                $updateFields[] = "amenities = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['amenities'], 'string');
            }

            if (isset($data['features'])) {
                $updateFields[] = "features = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['features'], 'string');
            }

            if (isset($data['status'])) {
                $validStatuses = ['available', 'sold', 'reserved', 'under_maintenance'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (isset($data['featured'])) {
                $updateFields[] = "featured = ?";
                $updateValues[] = (int)$data['featured'];
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $propertyId;

            $sql = "UPDATE properties SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'property_updated', [
                    'property_id' => $propertyId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Property updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update property', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Property Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update property', 500);
        }
    }

    /**
     * Remove the specified property
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                return $this->jsonError('Invalid property ID', 400);
            }

            // Check if property exists
            $sql = "SELECT * FROM properties WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propertyId]);
            $property = $stmt->fetch();

            if (!$property) {
                return $this->jsonError('Property not found', 404);
            }

            // Check if property has bookings
            $sql = "SELECT COUNT(*) as booking_count FROM bookings WHERE property_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$propertyId]);
            $bookingCount = $stmt->fetch()['booking_count'];

            if ($bookingCount > 0) {
                return $this->jsonError('Cannot delete property with existing bookings', 400);
            }

            $this->db->beginTransaction();

            try {
                // Delete property images
                $sql = "SELECT image_path FROM property_images WHERE property_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$propertyId]);
                $images = $stmt->fetchAll();

                foreach ($images as $image) {
                    if (file_exists($image['image_path'])) {
                        unlink($image['image_path']);
                    }
                }

                $sql = "DELETE FROM property_images WHERE property_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$propertyId]);

                // Delete property
                $sql = "DELETE FROM properties WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$propertyId]);

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'property_deleted', [
                    'property_id' => $propertyId,
                    'title' => $property['title']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Property deleted successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Property Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete property', 500);
        }
    }

    /**
     * Validate uploaded image
     */
    private function validateImage(array $file): array
    {
        // Check file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Image size too large. Maximum 5MB allowed.'];
        }

        // Check image type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid image type. Allowed types: JPG, PNG, GIF, WebP'];
        }

        return ['valid' => true];
    }

    /**
     * Upload image
     */
    private function uploadImage(array $file): ?string
    {
        try {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('property_') . '.' . $extension;

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/properties/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                return $filePath;
            }

            return null;
        } catch (Exception $e) {
            $this->loggingService->error("Upload Image error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get property statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total properties
            $sql = "SELECT COUNT(*) as total FROM properties";
            $result = $this->db->fetchOne($sql);
            $stats['total_properties'] = (int)($result['total'] ?? 0);

            // Available properties
            $sql = "SELECT COUNT(*) as total FROM properties WHERE status = 'available'";
            $result = $this->db->fetchOne($sql);
            $stats['available_properties'] = (int)($result['total'] ?? 0);

            // Sold properties
            $sql = "SELECT COUNT(*) as total FROM properties WHERE status = 'sold'";
            $result = $this->db->fetchOne($sql);
            $stats['sold_properties'] = (int)($result['total'] ?? 0);

            // Total value
            $sql = "SELECT COALESCE(SUM(price), 0) as total FROM properties";
            $result = $this->db->fetchOne($sql);
            $stats['total_value'] = (float)($result['total'] ?? 0);

            // Featured properties
            $sql = "SELECT COUNT(*) as total FROM properties WHERE featured = 1";
            $result = $this->db->fetchOne($sql);
            $stats['featured_properties'] = (int)($result['total'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Property Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch property stats'
            ], 500);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
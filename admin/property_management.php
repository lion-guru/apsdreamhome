<?php
/**
 * Property Management System
 * 
 * Comprehensive CRUD operations for managing properties,
 * including listings, images, pricing, and status management.
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Security.php';

class PropertyManagement {
    private $db;
    private $security;
    private $uploadPath;
    
    public function __construct() {
        $this->db = new Database();
        $this->security = new Security();
        $this->uploadPath = __DIR__ . '/../uploads/properties/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Create a new property
     */
    public function createProperty($propertyData) {
        try {
            // Validate required fields
            $required = ['title', 'description', 'price', 'location', 'property_type'];
            foreach ($required as $field) {
                if (empty($propertyData[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Sanitize input
            $propertyData = $this->sanitizePropertyData($propertyData);
            
            // Handle image uploads
            $imagePaths = $this->handleImageUploads($_FILES['images'] ?? []);
            
            $sql = "INSERT INTO properties (
                title, description, price, location, property_type, 
                bedrooms, bathrooms, area, status, featured, 
                images, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $propertyData['title'],
                $propertyData['description'],
                $propertyData['price'],
                $propertyData['location'],
                $propertyData['property_type'],
                $propertyData['bedrooms'] ?? 0,
                $propertyData['bathrooms'] ?? 0,
                $propertyData['area'] ?? 0,
                $propertyData['status'] ?? 'available',
                $propertyData['featured'] ?? 0,
                json_encode($imagePaths)
            ]);
            
            if ($result) {
                $propertyId = $this->db->lastInsertId();
                return ['success' => true, 'message' => 'Property created successfully', 'property_id' => $propertyId];
            }
            
            return ['success' => false, 'message' => 'Failed to create property'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get all properties with pagination and filtering
     */
    public function getProperties($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];
            
            // Build WHERE clause from filters
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['property_type'])) {
                $where[] = "property_type = ?";
                $params[] = $filters['property_type'];
            }
            
            if (!empty($filters['location'])) {
                $where[] = "location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }
            
            if (!empty($filters['price_min'])) {
                $where[] = "price >= ?";
                $params[] = $filters['price_min'];
            }
            
            if (!empty($filters['price_max'])) {
                $where[] = "price <= ?";
                $params[] = $filters['price_max'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM properties $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Get properties
            $sql = "SELECT * FROM properties $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode images JSON
            foreach ($properties as &$property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return [
                'properties' => $properties,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            return ['properties' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get a specific property
     */
    public function getProperty($id) {
        try {
            $sql = "SELECT * FROM properties WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return $property;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update a property
     */
    public function updateProperty($id, $propertyData) {
        try {
            // Validate required fields
            $required = ['title', 'description', 'price', 'location', 'property_type'];
            foreach ($required as $field) {
                if (empty($propertyData[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Sanitize input
            $propertyData = $this->sanitizePropertyData($propertyData);
            
            // Handle new image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $newImages = $this->handleImageUploads($_FILES['images']);
                $existingImages = json_decode($propertyData['existing_images'] ?? '[]', true);
                $allImages = array_merge($existingImages, $newImages);
                $propertyData['images'] = json_encode($allImages);
            } else {
                $propertyData['images'] = $propertyData['existing_images'] ?? '[]';
            }
            
            $sql = "UPDATE properties SET 
                title = ?, description = ?, price = ?, location = ?, property_type = ?,
                bedrooms = ?, bathrooms = ?, area = ?, status = ?, featured = ?,
                images = ?, updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $propertyData['title'],
                $propertyData['description'],
                $propertyData['price'],
                $propertyData['location'],
                $propertyData['property_type'],
                $propertyData['bedrooms'] ?? 0,
                $propertyData['bathrooms'] ?? 0,
                $propertyData['area'] ?? 0,
                $propertyData['status'] ?? 'available',
                $propertyData['featured'] ?? 0,
                $propertyData['images'],
                $id
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Property updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update property'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete a property
     */
    public function deleteProperty($id) {
        try {
            // Get property to delete images
            $property = $this->getProperty($id);
            if ($property && !empty($property['images'])) {
                foreach ($property['images'] as $image) {
                    $imagePath = $this->uploadPath . basename($image);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
            
            $sql = "DELETE FROM properties WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Property deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to delete property'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete property image
     */
    public function deletePropertyImage($propertyId, $imageName) {
        try {
            $property = $this->getProperty($propertyId);
            if (!$property) {
                return ['success' => false, 'message' => 'Property not found'];
            }
            
            // Remove image from array
            $images = $property['images'];
            $key = array_search($imageName, $images);
            if ($key !== false) {
                unset($images[$key]);
                $images = array_values($images);
                
                // Delete physical file
                $imagePath = $this->uploadPath . basename($imageName);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
                // Update database
                $sql = "UPDATE properties SET images = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([json_encode($images), $propertyId]);
                
                return ['success' => true, 'message' => 'Image deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'Image not found'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle image uploads
     */
    private function handleImageUploads($files) {
        $imagePaths = [];
        
        if (empty($files['name'][0])) {
            return $imagePaths;
        }
        
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $files['tmp_name'][$key];
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                // Validate file type
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    continue;
                }
                
                // Generate unique filename
                $filename = uniqid('property_', true) . '.' . $extension;
                $uploadPath = $this->uploadPath . $filename;
                
                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $imagePaths[] = 'uploads/properties/' . $filename;
                }
            }
        }
        
        return $imagePaths;
    }
    
    /**
     * Sanitize property data
     */
    private function sanitizePropertyData($data) {
        $sanitized = [];
        
        $sanitized['title'] = $this->security->sanitizeInput($data['title']);
        $sanitized['description'] = $this->security->sanitizeInput($data['description']);
        $sanitized['price'] = filter_var($data['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $sanitized['location'] = $this->security->sanitizeInput($data['location']);
        $sanitized['property_type'] = $this->security->sanitizeInput($data['property_type']);
        $sanitized['bedrooms'] = filter_var($data['bedrooms'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        $sanitized['bathrooms'] = filter_var($data['bathrooms'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $sanitized['area'] = filter_var($data['area'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $sanitized['status'] = $this->security->sanitizeInput($data['status'] ?? 'available');
        $sanitized['featured'] = filter_var($data['featured'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        
        return $sanitized;
    }
    
    /**
     * Get property statistics
     */
    public function getPropertyStats() {
        $stats = [];
        
        try {
            // Total properties
            $sql = "SELECT COUNT(*) as total FROM properties";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetchColumn();
            
            // Properties by status
            $sql = "SELECT status, COUNT(*) as count FROM properties GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Properties by type
            $sql = "SELECT property_type, COUNT(*) as count FROM properties GROUP BY property_type";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Average price
            $sql = "SELECT AVG(price) as avg_price FROM properties WHERE status = 'available'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['avg_price'] = round($stmt->fetchColumn(), 2);
            
        } catch (Exception $e) {
            error_log("Property stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
}

// Handle HTTP requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyManager = new PropertyManagement();
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $response = $propertyManager->createProperty($_POST);
            break;
            
        case 'update':
            $response = $propertyManager->updateProperty($_POST['id'], $_POST);
            break;
            
        case 'delete':
            $response = $propertyManager->deleteProperty($_POST['id']);
            break;
            
        case 'delete_image':
            $response = $propertyManager->deletePropertyImage($_POST['property_id'], $_POST['image_name']);
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle GET requests for API
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $propertyManager = new PropertyManagement();
    
    switch ($_GET['action']) {
        case 'get_properties':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $filters = $_GET;
            unset($filters['action'], $filters['page'], $filters['limit']);
            $response = $propertyManager->getProperties($page, $limit, $filters);
            break;
            
        case 'get_property':
            $response = $propertyManager->getProperty($_GET['id']);
            break;
            
        case 'get_stats':
            $response = $propertyManager->getPropertyStats();
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Initialize property manager
$propertyManager = new PropertyManagement();
$currentPage = $_GET['page'] ?? 1;
$properties = $propertyManager->getProperties($currentPage, 10);
$stats = $propertyManager->getPropertyStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .property-card {
            transition: transform 0.2s;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .property-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .property-image {
            height: 200px;
            object-fit: cover;
        }
        .price-tag {
            font-size: 1.2rem;
            font-weight: bold;
            color: #28a745;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-home"></i> Property Management
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="unified_key_management.php">
                    <i class="fas fa-key"></i> Key Management
                </a>
                <a class="nav-link" href="user_management.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4><?php echo $stats['total'] ?? 0; ?></h4>
                        <p>Total Properties</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h4><?php echo $stats['by_status']['available'] ?? 0; ?></h4>
                        <p>Available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h4><?php echo $stats['by_status']['sold'] ?? 0; ?></h4>
                        <p>Sold</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h4>$<?php echo number_format($stats['avg_price'] ?? 0); ?></h4>
                        <p>Average Price</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form id="filterForm" class="row g-3">
                                    <div class="col-md-3">
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="available">Available</option>
                                            <option value="sold">Sold</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="property_type">
                                            <option value="">All Types</option>
                                            <option value="house">House</option>
                                            <option value="apartment">Apartment</option>
                                            <option value="condo">Condo</option>
                                            <option value="land">Land</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="location" placeholder="Location...">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#propertyModal">
                                    <i class="fas fa-plus"></i> Add Property
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties Grid -->
        <div class="row" id="propertiesContainer">
            <?php if (!empty($properties['properties'])): ?>
                <?php foreach ($properties['properties'] as $property): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card property-card">
                            <div class="position-relative">
                                <?php if (!empty($property['images'])): ?>
                                    <img src="<?php echo $property['images'][0]; ?>" class="card-img-top property-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top property-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-home fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="badge status-badge bg-<?php echo $property['status'] === 'available' ? 'success' : ($property['status'] === 'sold' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($property['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?>
                                    </small>
                                </p>
                                <p class="price-tag">$<?php echo number_format($property['price']); ?></p>
                                <p class="card-text">
                                    <?php if ($property['bedrooms'] > 0): ?>
                                        <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> beds
                                    <?php endif; ?>
                                    <?php if ($property['bathrooms'] > 0): ?>
                                        <i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> baths
                                    <?php endif; ?>
                                    <?php if ($property['area'] > 0): ?>
                                        <i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> sqft
                                    <?php endif; ?>
                                </p>
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-primary btn-sm" onclick="editProperty(<?php echo $property['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-info btn-sm" onclick="viewProperty(<?php echo $property['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteProperty(<?php echo $property['id']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No properties found.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($properties['total_pages'] > 1): ?>
            <nav aria-label="Property pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $properties['total_pages']; $i++): ?>
                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Property Modal -->
    <div class="modal fade" id="propertyModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="propertyForm" enctype="multipart/form-data">
                        <input type="hidden" id="propertyId" name="id">
                        <input type="hidden" id="action" name="action" value="create">
                        <input type="hidden" id="existingImages" name="existing_images">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Property Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="property_type" class="form-label">Property Type *</label>
                                    <select class="form-select" id="property_type" name="property_type" required>
                                        <option value="">Select Type</option>
                                        <option value="house">House</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="condo">Condo</option>
                                        <option value="land">Land</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price *</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location *</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="available">Available</option>
                                        <option value="sold">Sold</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="bedrooms" class="form-label">Bedrooms</label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="bathrooms" class="form-label">Bathrooms</label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" step="0.5">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="area" class="form-label">Area (sqft)</label>
                                    <input type="number" class="form-control" id="area" name="area" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="featured" class="form-label">Featured</label>
                                    <select class="form-select" id="featured" name="featured">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="images" class="form-label">Property Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                            <div class="form-text">Upload multiple images (JPG, PNG, GIF, WebP)</div>
                        </div>
                        
                        <div id="imagePreview" class="mb-3"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveProperty">Save Property</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let propertyModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            propertyModal = new bootstrap.Modal(document.getElementById('propertyModal'));
            
            // Handle filter form
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const params = new URLSearchParams(formData);
                window.location.href = '?' + params.toString();
            });
            
            // Handle image preview
            document.getElementById('images').addEventListener('change', function(e) {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = '';
                
                Array.from(e.target.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'image-preview';
                            preview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });
            
            // Handle save property
            document.getElementById('saveProperty').addEventListener('click', function() {
                const form = document.getElementById('propertyForm');
                const formData = new FormData(form);
                
                fetch('property_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        propertyModal.hide();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            });
        });
        
        function editProperty(id) {
            fetch(`property_management.php?action=get_property&id=${id}`)
                .then(response => response.json())
                .then(property => {
                    document.getElementById('propertyId').value = property.id;
                    document.getElementById('action').value = 'update';
                    document.getElementById('modalTitle').textContent = 'Edit Property';
                    document.getElementById('title').value = property.title;
                    document.getElementById('property_type').value = property.property_type;
                    document.getElementById('description').value = property.description;
                    document.getElementById('price').value = property.price;
                    document.getElementById('location').value = property.location;
                    document.getElementById('status').value = property.status;
                    document.getElementById('bedrooms').value = property.bedrooms;
                    document.getElementById('bathrooms').value = property.bathrooms;
                    document.getElementById('area').value = property.area;
                    document.getElementById('featured').value = property.featured;
                    document.getElementById('existingImages').value = JSON.stringify(property.images);
                    
                    // Show existing images
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = '';
                    property.images.forEach(image => {
                        const img = document.createElement('img');
                        img.src = image;
                        img.className = 'image-preview';
                        preview.appendChild(img);
                    });
                    
                    propertyModal.show();
                });
        }
        
        function viewProperty(id) {
            window.open(`../property.php?id=${id}`, '_blank');
        }
        
        function deleteProperty(id) {
            if (confirm('Are you sure you want to delete this property?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                fetch('property_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        // Reset modal when hidden
        document.getElementById('propertyModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('propertyForm').reset();
            document.getElementById('propertyId').value = '';
            document.getElementById('action').value = 'create';
            document.getElementById('modalTitle').textContent = 'Add New Property';
            document.getElementById('imagePreview').innerHTML = '';
        });
    </script>
</body>
</html>

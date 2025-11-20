<?php
/**
 * Property Management System
 * Complete CRUD operations for properties
 */

class PropertyManager {
    private $conn;
    private $logger;
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    private $uploadPath = '/uploads/properties/';

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;

        // Create uploads directory if it doesn't exist
        $this->ensureUploadDirectory();
    }

    /**
     * Ensure upload directories exist
     */
    private function ensureUploadDirectory() {
        $basePath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadPath;
        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }
    }

    /**
     * Create new property
     */
    public function createProperty($data, $images = []) {
        $sql = "INSERT INTO properties (
            title, description, property_type_id, price, location, city, state,
            bedrooms, bathrooms, area, area_unit, features, amenities, agent_id, status, featured
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssdsssddsssissi",
            $data['title'],
            $data['description'],
            $data['property_type_id'],
            $data['price'],
            $data['location'],
            $data['city'],
            $data['state'],
            $data['bedrooms'],
            $data['bathrooms'],
            $data['area'],
            $data['area_unit'],
            json_encode($data['features'] ?? []),
            json_encode($data['amenities'] ?? []),
            $data['agent_id'] ?? null,
            $data['status'] ?? 'available',
            $data['featured'] ?? 0
        );

        $result = $stmt->execute();
        $propertyId = $stmt->insert_id;
        $stmt->close();

        if ($result && !empty($images)) {
            $this->handlePropertyImages($propertyId, $images);
        }

        if ($result && $this->logger) {
            $this->logger->log("Property created: {$data['title']} (ID: $propertyId)", 'info', 'property');
        }

        return $result ? $propertyId : false;
    }

    /**
     * Get all properties with filtering and pagination
     */
    public function getProperties($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, pt.name as property_type_name,
                       u.full_name as agent_name,
                       (SELECT image FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                       (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE 1=1";

        $params = [];
        $types = "";

        // Apply filters
        if (!empty($filters['property_type_id'])) {
            $sql .= " AND p.property_type_id = ?";
            $params[] = $filters['property_type_id'];
            $types .= "i";
        }

        if (!empty($filters['location'])) {
            $sql .= " AND (p.location LIKE ? OR p.city LIKE ?)";
            $params[] = "%" . $filters['location'] . "%";
            $params[] = "%" . $filters['location'] . "%";
            $types .= "ss";
        }

        if (!empty($filters['price_min'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['price_min'];
            $types .= "d";
        }

        if (!empty($filters['price_max'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['price_max'];
            $types .= "d";
        }

        if (!empty($filters['bedrooms'])) {
            $sql .= " AND p.bedrooms >= ?";
            $params[] = $filters['bedrooms'];
            $types .= "i";
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (!empty($filters['featured'])) {
            $sql .= " AND p.featured = ?";
            $params[] = $filters['featured'];
            $types .= "i";
        }

        $sql .= " ORDER BY p.featured DESC, p.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
        }

        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = $offset;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
        $stmt->close();

        return $properties;
    }

    /**
     * Get single property by ID
     */
    public function getProperty($id) {
        $sql = "SELECT p.*, pt.name as property_type_name, u.full_name as agent_name, u.phone as agent_phone, u.email as agent_email
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $property = $result->fetch_assoc();
        $stmt->close();

        if ($property) {
            // Get property images
            $property['images'] = $this->getPropertyImages($id);
            // Get property features
            $property['features'] = json_decode($property['features'] ?? '[]', true);
            $property['amenities'] = json_decode($property['amenities'] ?? '[]', true);
        }

        return $property;
    }

    /**
     * Update property
     */
    public function updateProperty($id, $data, $images = []) {
        $sql = "UPDATE properties SET
            title = ?, description = ?, property_type_id = ?, price = ?, location = ?,
            city = ?, state = ?, bedrooms = ?, bathrooms = ?, area = ?, area_unit = ?,
            features = ?, amenities = ?, agent_id = ?, status = ?, featured = ?,
            updated_at = NOW()
            WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssdsssiidsssisi",
            $data['title'],
            $data['description'],
            $data['property_type_id'],
            $data['price'],
            $data['location'],
            $data['city'],
            $data['state'],
            $data['bedrooms'],
            $data['bathrooms'],
            $data['area'],
            $data['area_unit'],
            json_encode($data['features'] ?? []),
            json_encode($data['amenities'] ?? []),
            $data['agent_id'] ?? null,
            $data['status'] ?? 'available',
            $data['featured'] ?? 0,
            $id
        );

        $result = $stmt->execute();
        $stmt->close();

        if ($result && !empty($images)) {
            $this->handlePropertyImages($id, $images);
        }

        if ($result && $this->logger) {
            $this->logger->log("Property updated: {$data['title']} (ID: $id)", 'info', 'property');
        }

        return $result;
    }

    /**
     * Delete property
     */
    public function deleteProperty($id) {
        // First delete property images
        $this->deletePropertyImages($id);

        // Then delete the property
        $sql = "DELETE FROM properties WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Property deleted: ID $id", 'warning', 'property');
        }

        return $result;
    }

    /**
     * Handle property image uploads
     */
    private function handlePropertyImages($propertyId, $images) {
        foreach ($images as $index => $imageFile) {
            if ($imageFile['error'] === UPLOAD_ERR_OK) {
                $this->uploadPropertyImage($propertyId, $imageFile, $index === 0);
            }
        }
    }

    /**
     * Upload property image
     */
    private function uploadPropertyImage($propertyId, $file, $isPrimary = false) {
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmpName = $file['tmp_name'];
        $fileError = $file['error'];

        // Validate file
        if ($fileError !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($fileSize > $this->maxFileSize) {
            return false;
        }

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExt, $this->allowedExtensions)) {
            return false;
        }

        // Generate unique filename
        $newFileName = uniqid('property_', true) . '.' . $fileExt;
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadPath . $newFileName;

        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            // Get next sort order
            $sortOrder = $this->getNextImageSortOrder($propertyId);

            // Insert into database
            $sql = "INSERT INTO property_images (property_id, image, is_primary, sort_order)
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isii", $propertyId, $newFileName, $isPrimary, $sortOrder);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        }

        return false;
    }

    /**
     * Get property images
     */
    public function getPropertyImages($propertyId) {
        $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        $result = $stmt->get_result();

        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        $stmt->close();

        return $images;
    }

    /**
     * Delete property images
     */
    private function deletePropertyImages($propertyId) {
        // Get image files to delete
        $images = $this->getPropertyImages($propertyId);

        // Delete physical files
        foreach ($images as $image) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadPath . $image['image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Note: Do NOT delete property_images rows here.
        // They will be removed automatically via FK ON DELETE CASCADE
        // when the parent property is deleted. This avoids manual DB cleanup
        // and ensures referential integrity policies are respected.
    }

    /**
     * Get next image sort order
     */
    private function getNextImageSortOrder($propertyId) {
        $sql = "SELECT MAX(sort_order) as max_order FROM property_images WHERE property_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return ($row['max_order'] ?? 0) + 1;
    }

    /**
     * Get property types
     */
    public function getPropertyTypes() {
        $sql = "SELECT * FROM property_types WHERE status = 'active' ORDER BY name";
        $result = $this->conn->query($sql);

        $types = [];
        while ($row = $result->fetch_assoc()) {
            $types[] = $row;
        }

        return $types;
    }

    /**
     * Search properties
     */
    public function searchProperties($query, $limit = 20) {
        $searchTerm = "%$query%";

        $sql = "SELECT p.*, pt.name as property_type_name,
                       (SELECT image FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'available'
                AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.city LIKE ?)
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
        $stmt->close();

        return $properties;
    }

    /**
     * Get featured properties
     */
    public function getFeaturedProperties($limit = 6) {
        $sql = "SELECT p.*, pt.name as property_type_name,
                       (SELECT image FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'available' AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
        $stmt->close();

        return $properties;
    }
}
?>

<?php
/**
 * Enhanced Property Management Class
 * Provides comprehensive property management functionality with security features
 */

require_once __DIR__ . '/AdminInputValidator.php';

class EnhancedPropertyManager {
    private $db;
    private $validator;

    public function __construct() {
        $this->db = \App\Core\App::database();
        $this->validator = new AdminInputValidator();
    }

    /**
     * Add new property with comprehensive validation
     */
    public function addProperty($propertyData, $files = []) {
        // Validate CSRF token
        if (!isset($propertyData['csrf_token']) || !AdminInputValidator::validateCSRFToken($propertyData['csrf_token'])['valid']) {
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }

        // Define validation rules
        $validationRules = [
            'title' => ['type' => 'text', 'min' => 5, 'max' => 200],
            'description' => ['type' => 'html', 'max' => 5000],
            'price' => ['type' => 'price'],
            'area' => ['type' => 'area'],
            'bedrooms' => ['type' => 'number', 'min' => 0, 'max' => 20],
            'bathrooms' => ['type' => 'number', 'min' => 0, 'max' => 20],
            'property_type' => ['type' => 'property_type'],
            'status' => ['type' => 'property_status'],
            'address' => ['type' => 'text', 'min' => 10, 'max' => 500],
            'city' => ['type' => 'text', 'min' => 2, 'max' => 100],
            'state' => ['type' => 'text', 'min' => 2, 'max' => 100],
            'zipcode' => ['type' => 'text', 'min' => 5, 'max' => 10],
            'featured' => ['type' => 'text', 'optional' => true]
        ];

        // Validate form data
        $validation = AdminInputValidator::validateForm($propertyData, $validationRules);

        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $validatedData = $validation['data'];

        // Handle file uploads
        $imageUrls = [];
        if (!empty($files['images']['name'][0])) {
            $uploadResult = $this->handleMultipleFileUploads($files['images']);
            if (!$uploadResult['success']) {
                return ['success' => false, 'error' => $uploadResult['error']];
            }
            $imageUrls = $uploadResult['urls'];
        }

        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Insert property data
            $sql = "
                INSERT INTO properties (
                    title, description, price, area, bedrooms, bathrooms,
                    property_type, status, address, city, state, zipcode,
                    featured, images, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";

            $imagesJson = json_encode($imageUrls);
            $featured = isset($validatedData['featured']) ? 1 : 0;

            $params = [
                $validatedData['title'],
                $validatedData['description'],
                $validatedData['price'],
                $validatedData['area'],
                $validatedData['bedrooms'],
                $validatedData['bathrooms'],
                $validatedData['property_type'],
                $validatedData['status'],
                $validatedData['address'],
                $validatedData['city'],
                $validatedData['state'],
                $validatedData['zipcode'],
                $featured,
                $imagesJson
            ];

            if (!$this->db->execute($sql, $params)) {
                throw new Exception("Failed to insert property");
            }

            $propertyId = $this->db->getLastInsertId();

            // Log the action
            $this->logAction('property_added', $propertyId, $_SESSION['auser']['id'] ?? 0);

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'property_id' => $propertyId,
                'message' => 'Property added successfully'
            ];

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update existing property
     */
    public function updateProperty($propertyId, $propertyData, $files = []) {
        // Validate CSRF token
        if (!isset($propertyData['csrf_token']) || !AdminInputValidator::validateCSRFToken($propertyData['csrf_token'])['valid']) {
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }

        // Validate property ID
        $idValidation = AdminInputValidator::validateNumber($propertyId, 1);
        if (!$idValidation['valid']) {
            return ['success' => false, 'error' => 'Invalid property ID'];
        }

        $propertyId = $idValidation['value'];

        // Check if property exists
        if (!$this->propertyExists($propertyId)) {
            return ['success' => false, 'error' => 'Property not found'];
        }

        // Define validation rules (similar to addProperty)
        $validationRules = [
            'title' => ['type' => 'text', 'min' => 5, 'max' => 200],
            'description' => ['type' => 'html', 'max' => 5000],
            'price' => ['type' => 'price'],
            'area' => ['type' => 'area'],
            'bedrooms' => ['type' => 'number', 'min' => 0, 'max' => 20],
            'bathrooms' => ['type' => 'number', 'min' => 0, 'max' => 20],
            'property_type' => ['type' => 'property_type'],
            'status' => ['type' => 'property_status'],
            'address' => ['type' => 'text', 'min' => 10, 'max' => 500],
            'city' => ['type' => 'text', 'min' => 2, 'max' => 100],
            'state' => ['type' => 'text', 'min' => 2, 'max' => 100],
            'zipcode' => ['type' => 'text', 'min' => 5, 'max' => 10],
            'featured' => ['type' => 'text', 'optional' => true]
        ];

        // Validate form data
        $validation = AdminInputValidator::validateForm($propertyData, $validationRules);

        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $validatedData = $validation['data'];

        try {
            // Begin transaction
            $this->conn->begin_transaction();

            // Handle new file uploads if provided
            if (!empty($files['images']['name'][0])) {
                $uploadResult = $this->handleMultipleFileUploads($files['images']);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['error']);
                }

                // Merge new images with existing ones
                $existingImages = $this->getPropertyImages($propertyId);
                $validatedData['images'] = json_encode(array_merge($existingImages, $uploadResult['urls']));
            } else {
                // Keep existing images
                $existingImages = $this->getPropertyImages($propertyId);
                $validatedData['images'] = json_encode($existingImages);
            }

            // Update property data
            $stmt = $this->conn->prepare("
                UPDATE properties SET
                    title = ?, description = ?, price = ?, area = ?,
                    bedrooms = ?, bathrooms = ?, property_type = ?,
                    status = ?, address = ?, city = ?, state = ?,
                    zipcode = ?, featured = ?, images = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $featured = isset($validatedData['featured']) ? 1 : 0;

            $stmt->bind_param(
                "ssddiisssssissi",
                $validatedData['title'],
                $validatedData['description'],
                $validatedData['price'],
                $validatedData['area'],
                $validatedData['bedrooms'],
                $validatedData['bathrooms'],
                $validatedData['property_type'],
                $validatedData['status'],
                $validatedData['address'],
                $validatedData['city'],
                $validatedData['state'],
                $validatedData['zipcode'],
                $featured,
                $validatedData['images'],
                $propertyId
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to update property: " . $stmt->error);
            }

            // Log the action
            $this->logAction('property_updated', $propertyId, $_SESSION['auser']['id'] ?? 0);

            // Commit transaction
            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Property updated successfully'
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete property (soft delete)
     */
    public function deleteProperty($propertyId) {
        // Validate property ID
        $idValidation = AdminInputValidator::validateNumber($propertyId, 1);
        if (!$idValidation['valid']) {
            return ['success' => false, 'error' => 'Invalid property ID'];
        }

        $propertyId = $idValidation['value'];

        // Check if property exists
        if (!$this->propertyExists($propertyId)) {
            return ['success' => false, 'error' => 'Property not found'];
        }

        try {
            // Begin transaction
            $this->conn->begin_transaction();

            // Soft delete the property
            $stmt = $this->conn->prepare("
                UPDATE properties
                SET status = 'deleted', updated_at = NOW()
                WHERE id = ?
            ");

            $stmt->bind_param("i", $propertyId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete property: " . $stmt->error);
            }

            // Log the action
            $this->logAction('property_deleted', $propertyId, $_SESSION['auser']['id'] ?? 0);

            // Commit transaction
            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Property deleted successfully'
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get property by ID
     */
    public function getProperty($propertyId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM properties
            WHERE id = ? AND status != 'deleted'
        ");

        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $property = $result->fetch_assoc();
        $property['images'] = json_decode($property['images'], true) ?? [];

        $stmt->close();
        return $property;
    }

    /**
     * Get all properties with pagination and filtering
     */
    public function getProperties($filters = [], $page = 1, $perPage = 20) {
        $whereConditions = ["status != 'deleted'"];
        $params = [];
        $types = "";

        // Build filter conditions
        if (!empty($filters['property_type'])) {
            $whereConditions[] = "property_type = ?";
            $params[] = $filters['property_type'];
            $types .= "s";
        }

        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (!empty($filters['min_price'])) {
            $whereConditions[] = "price >= ?";
            $params[] = $filters['min_price'];
            $types .= "d";
        }

        if (!empty($filters['max_price'])) {
            $whereConditions[] = "price <= ?";
            $params[] = $filters['max_price'];
            $types .= "d";
        }

        if (!empty($filters['city'])) {
            $whereConditions[] = "city LIKE ?";
            $params[] = "%{$filters['city']}%";
            $types .= "s";
        }

        $whereClause = implode(" AND ", $whereConditions);

        // Get total count
        $countStmt = $this->conn->prepare("SELECT COUNT(*) as total FROM properties WHERE $whereClause");
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalResult = $countStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
        $countStmt->close();

        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $totalPages = ceil($total / $perPage);

        // Get properties
        $query = "SELECT * FROM properties WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $row['images'] = json_decode($row['images'], true) ?? [];
            $properties[] = $row;
        }

        $stmt->close();

        return [
            'properties' => $properties,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Handle multiple file uploads
     */
    private function handleMultipleFileUploads($files) {
        $uploadedUrls = [];

        // Use secure file upload class for consistency
        $secureUploader = new SecureFileUpload([
            'upload_dir' => '../uploads/properties/',
            'allowed_types' => 'images',
            'max_size' => 10485760 // 10MB
        ]);

        // Process each file
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Create temporary file array for single file upload
            $tempFile = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            // Temporarily set $_FILES for the secure uploader
            $originalFiles = $_FILES;
            $_FILES['property_image'] = $tempFile;

            // Use secure file upload
            $uploadResult = $secureUploader->uploadFile('property_image');

            // Restore original $_FILES
            $_FILES = $originalFiles;

            if ($uploadResult['success']) {
                $uploadedUrls[] = 'uploads/properties/' . $uploadResult['filename'];
            } else {
                return ['success' => false, 'error' => $uploadResult['error']];
            }
        }

        return ['success' => true, 'urls' => $uploadedUrls];
    }

    /**
     * Get property images
     */
    private function getPropertyImages($propertyId) {
        $stmt = $this->conn->prepare("SELECT images FROM properties WHERE id = ?");
        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return [];
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        return json_decode($row['images'], true) ?? [];
    }

    /**
     * Check if property exists
     */
    private function propertyExists($propertyId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM properties WHERE id = ?");
        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();

        return $count > 0;
    }

    /**
     * Log admin actions
     */
    private function logAction($action, $propertyId, $adminId) {
        $stmt = $this->conn->prepare("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, created_at)
            VALUES (?, ?, 'properties', ?, NOW())
        ");

        $stmt->bind_param("isi", $adminId, $action, $propertyId);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Get property statistics
     */
    public function getPropertyStats() {
        $stats = [];

        // Total properties
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM properties WHERE status != 'deleted'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_properties'] = $result->fetch_assoc()['total'];
        $stmt->close();

        // Properties by status
        $stmt = $this->conn->prepare("
            SELECT status, COUNT(*) as count
            FROM properties
            WHERE status != 'deleted'
            GROUP BY status
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $stats['by_status'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_status'][$row['status']] = $row['count'];
        }
        $stmt->close();

        // Properties by type
        $stmt = $this->conn->prepare("
            SELECT property_type, COUNT(*) as count
            FROM properties
            WHERE status != 'deleted'
            GROUP BY property_type
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $stats['by_type'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_type'][$row['property_type']] = $row['count'];
        }
        $stmt->close();

        // Average price
        $stmt = $this->conn->prepare("
            SELECT AVG(price) as avg_price
            FROM properties
            WHERE status != 'deleted'
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['average_price'] = $result->fetch_assoc()['avg_price'];
        $stmt->close();

        return $stats;
    }

    /**
     * Get all property types
     */
    public function getPropertyTypes() {
        $query = "SELECT id, type_name FROM property_types ORDER BY type_name ASC";
        $result = $this->conn->query($query);

        if (!$result) {
            return [];
        }

        $types = [];
        while ($row = $result->fetch_assoc()) {
            $types[] = $row;
        }

        return $types;
    }
}

<?php

namespace App\Services\Legacy;
/**
 * Property Management System
 * Complete CRUD operations for properties
 */

class PropertyManager {
    private $db;
    private $logger;
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    private $uploadPath = '/uploads/properties/';

    public function __construct($db = null, $logger = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;

        // Create uploads directory if it doesn't exist
        $this->ensureUploadDirectory();
    }

    /**
     * Get full property details by ID
     */
    public function getPropertyDetails($pid) {
        $sql = "SELECT p.*, pt.type as ptype_name, c.cname as cityname,
                       u.name as aname, u.phone as aphone, u.email as aemail, u.profile_image as aimage
                FROM property p
                LEFT JOIN property_type pt ON p.type = pt.id
                LEFT JOIN city c ON p.city = c.cid
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.pid = ?";

        $property = $this->db->fetch($sql, [$pid]);

        if ($property) {
            $property['images'] = $this->getPropertyImages($pid);
        }

        return $property;
    }

    /**
     * Get property images
     */
    public function getPropertyImages($pid) {
        // Handle both $pid as property ID (integer) or potentially other identifiers
        // Use property_id or pid based on table structure
        $sql = "SELECT * FROM property_images WHERE pid = ? OR property_id = ? ORDER BY is_primary DESC, sort_order ASC";
        try {
            return $this->db->fetchAll($sql, [$pid, $pid]);
        } catch (\Exception $e) {
            // Fallback to simple query if one column doesn't exist
            try {
                $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC";
                return $this->db->fetchAll($sql, [$pid]);
            } catch (\Exception $e2) {
                $sql = "SELECT * FROM property_images WHERE pid = ? ORDER BY id ASC";
                return $this->db->fetchAll($sql, [$pid]);
            }
        }
    }

    /**
     * Get similar properties
     */
    public function getSimilarProperties($pid, $limit = 3) {
        // First get current property details to match criteria
        $prop = $this->getPropertyDetails($pid);
        if (!$prop) return [];

        $sql = "SELECT p.*, c.cname as cityname
                FROM property p
                LEFT JOIN city c ON p.city = c.cid
                WHERE p.pid != ? AND (p.type = ? OR p.city = ?)
                AND p.status_active = 1
                ORDER BY p.created_at DESC LIMIT ?";

        return $this->db->fetchAll($sql, [$pid, $prop['type'], $prop['city'], $limit]);
    }

    /**
     * Get properties with advanced filters
     */
    public function getProperties($filters = [], $limit = 20, $offset = 0) {
        // This is a merged version of the two getProperties methods
        // It handles both table structures (property vs properties)

        $table = "properties";
        try {
            // Check if 'properties' table exists, if not use 'property'
            $this->db->query("SELECT 1 FROM properties LIMIT 1");
        } catch (\Exception $e) {
            $table = "property";
        }

        if ($table === "property") {
            $sql = "SELECT p.*, pt.type as ptype_name, c.cname as cityname
                    FROM property p
                    LEFT JOIN property_type pt ON p.type = pt.id
                    LEFT JOIN city c ON p.city = c.cid
                    WHERE p.status_active = 1";

            $params = [];

            if (!empty($filters['type'])) {
                $sql .= " AND p.type = ?";
                $params[] = $filters['type'];
            }
            if (!empty($filters['location'])) {
                $sql .= " AND (c.cname LIKE ? OR p.location LIKE ?)";
                $loc = "%{$filters['location']}%";
                $params[] = $loc;
                $params[] = $loc;
            }
            if (!empty($filters['bedrooms'])) {
                $sql .= " AND p.bedroom >= ?";
                $params[] = $filters['bedrooms'];
            }
            if (!empty($filters['purpose'])) {
                $sql .= " AND p.stype = ?";
                $params[] = $filters['purpose'];
            }

            $sql .= " ORDER BY p.created_at DESC";
        } else {
            $sql = "SELECT p.*, pt.type as property_type_name,
                           u.uname as agent_name,
                           (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                           (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    LEFT JOIN user u ON p.agent_id = u.uid
                    WHERE 1=1";

            $params = [];

            // Apply filters
            if (!empty($filters['property_type_id'])) {
                $sql .= " AND p.property_type_id = ?";
                $params[] = $filters['property_type_id'];
            }

            if (!empty($filters['location'])) {
                $sql .= " AND (p.location LIKE ? OR p.city LIKE ?)";
                $params[] = "%" . $filters['location'] . "%";
                $params[] = "%" . $filters['location'] . "%";
            }

            if (isset($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $filters['max_price'];
            }

            if (!empty($filters['bedrooms'])) {
                $sql .= " AND p.bedrooms >= ?";
                $params[] = $filters['bedrooms'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND p.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['featured'])) {
                $sql .= " AND p.featured = 1";
            }

            $sql .= " ORDER BY p.created_at DESC";
        }

        if ($limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
        }

        if ($offset > 0) {
            $sql .= " OFFSET ?";
            $params[] = (int)$offset;
        }

        return $this->db->fetchAll($sql, $params);
    }

    private function ensureUploadDirectory($path = null) {
        $path = $path ?: dirname(__DIR__) . $this->uploadPath;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    public function createProperty($data, $images = []) {
        $sql = "INSERT INTO properties (
            title, description, property_type_id, price, location, city, state,
            bedrooms, bathrooms, area, area_unit, features, amenities, agent_id, status, featured
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
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
        ];

        $this->db->execute($sql, $params);
        $propertyId = $this->db->lastInsertId();

        if ($propertyId && !empty($images)) {
            $this->handlePropertyImages($propertyId, $images);
        }

        if ($propertyId && $this->logger) {
            $this->logger->log("Property created: {$data['title']} (ID: $propertyId)", 'info', 'property');
        }

        return $propertyId;
    }

    /**
     * Get single property by ID
     */
    public function getProperty($id) {
        $sql = "SELECT p.*, pt.type as property_type_name, u.uname as agent_name, u.uphone as agent_phone, u.uemail as agent_email
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN user u ON p.agent_id = u.uid
                WHERE p.id = ?";

        $property = $this->db->fetch($sql, [$id]);

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

        $params = [
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
        ];

        try {
            $this->db->execute($sql, $params);
            $success = true;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error updating property: " . $e->getMessage(), 'error', 'property');
            }
            $success = false;
        }

        if ($success && !empty($images)) {
            $this->handlePropertyImages($id, $images);
        }

        if ($success && $this->logger) {
            $this->logger->log("Property updated: {$data['title']} (ID: $id)", 'info', 'property');
        }

        return $success;
    }

    /**
     * Delete property
     */
    public function deleteProperty($id) {
        try {
            // First delete property images
            $this->deletePropertyImages($id);

            // Then delete the property
            $sql = "DELETE FROM properties WHERE id = ?";
            $this->db->execute($sql, [$id]);

            if ($this->logger) {
                $this->logger->log("Property deleted: ID $id", 'warning', 'property');
            }

            return true;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error deleting property: " . $e->getMessage(), 'error', 'property');
            }
            return false;
        }
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
        $newFileName = 'property_' . \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $fileExt;
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadPath . $newFileName;

        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            // Get next sort order
            $sortOrder = $this->getNextImageSortOrder($propertyId);

            // Insert into database
            $sql = "INSERT INTO property_images (property_id, image_path, is_primary, sort_order)
                    VALUES (?, ?, ?, ?)";

            try {
                $this->db->execute($sql, [$propertyId, $newFileName, (int)$isPrimary, $sortOrder]);
                return true;
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->log("Error saving property image to DB: " . $e->getMessage(), 'error', 'property');
                }
                return false;
            }
        }

        return false;
    }

    /**
     * Delete property images
     */
    private function deletePropertyImages($propertyId) {
        // Get image files to delete
        $images = $this->getPropertyImages($propertyId);

        // Delete physical files
        foreach ($images as $image) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadPath . $image['image_path'];
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
        try {
            $row = $this->db->fetch($sql, [$propertyId]);
            return ($row['max_order'] ?? 0) + 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Get property types
     */
    public function getPropertyTypes() {
        $sql = "SELECT * FROM property_types WHERE status = 'active' ORDER BY name";
        try {
            return $this->db->fetchAll($sql);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error getting property types: " . $e->getMessage(), 'error', 'property');
            }
            return [];
        }
    }

    /**
     * Search properties
     */
    public function searchProperties($query, $limit = 20) {
        $searchTerm = "%$query%";

        $sql = "SELECT p.*, pt.type as property_type_name,
                       (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'available'
                AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.city LIKE ?)
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT ?";

        try {
            return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, (int)$limit]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error searching properties: " . $e->getMessage(), 'error', 'property');
            }
            return [];
        }
    }

    /**
     * Get featured properties
     */
    public function getFeaturedProperties($limit = 6) {
        $sql = "SELECT p.*, pt.type as property_type_name,
                       (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'available' AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT ?";

        try {
            return $this->db->fetchAll($sql, [(int)$limit]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Error getting featured properties: " . $e->getMessage(), 'error', 'property');
            }
            return [];
        }
    }

    /**
     * List a new property (from public form)
     */
    public function listProperty($data, $files = null) {
        $owner_name = trim($data['owner_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $property_type = trim($data['property_type'] ?? '');
        $property_title = trim($data['property_title'] ?? '');
        $location = trim($data['location'] ?? '');
        $bedrooms = (int)($data['bedrooms'] ?? 0);
        $bathrooms = (int)($data['bathrooms'] ?? 0);
        $area = (float)($data['area'] ?? 0);
        $price = (float)($data['price'] ?? 0);
        $description = trim($data['description'] ?? '');
        $amenities = isset($data['amenities']) ? (is_array($data['amenities']) ? implode(', ', $data['amenities']) : $data['amenities']) : '';
        $availability = trim($data['availability'] ?? '');

        // Validation
        $errors = [];
        if (empty($owner_name)) $errors[] = 'Owner name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($property_title)) $errors[] = 'Property title is required';

        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        // Handle image uploads
        $image_urls = [];
        if ($files && isset($files['property_images']) && is_array($files['property_images']['name'])) {
            $upload_dir = dirname(__DIR__) . '/uploads/properties/';
            $this->ensureUploadDirectory($upload_dir);

            foreach ($files['property_images']['name'] as $key => $name) {
                if ($files['property_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (in_array($file_extension, $this->allowedExtensions)) {
                        $file_name = 'property_' . \App\Helpers\SecurityHelper::generateRandomString(16, false) . '_' . $key . '.' . $file_extension;
                        $file_path = $upload_dir . $file_name;

                        if (move_uploaded_file($files['property_images']['tmp_name'][$key], $file_path)) {
                            $image_urls[] = $file_name;
                        }
                    }
                }
            }
        }

        $images_json = !empty($image_urls) ? json_encode($image_urls) : null;

        // Insert property listing request
        $sql = "INSERT INTO property_listings
                (owner_name, email, phone, property_type, property_title, location, bedrooms,
                 bathrooms, area, price, description, amenities, availability, images, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

        $params = [
            $owner_name, $email, $phone, $property_type, $property_title, $location,
            $bedrooms, $bathrooms, $area, $price, $description, $amenities,
            $availability, $images_json
        ];

        $this->db->execute($sql, $params);

        return true;
    }
}
?>

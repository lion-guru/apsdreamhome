<?php

/**
 * Property Service
 * Handles all property-related business logic
 */

namespace App\Services\Business;

use App\Models\Property;
use App\Core\Database\Database;
use App\Services\SystemLogger;
use App\Core\Security;

class PropertyService
{
    private $db;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new SystemLogger();
    }

    /**
     * Get all properties with pagination
     */
    public function getAllProperties($page = 1, $limit = 10, $search = '', $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;

            $where = ["p.status != 'deleted'"];
            $params = [];

            // Apply search filter
            if (!empty($search)) {
                $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.address LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            // Apply filters
            if (!empty($filters['type'])) {
                $where[] = "p.type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $where[] = "p.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['price_min'])) {
                $where[] = "p.price >= ?";
                $params[] = $filters['price_min'];
            }

            if (!empty($filters['price_max'])) {
                $where[] = "p.price <= ?";
                $params[] = $filters['price_max'];
            }

            if (!empty($filters['area_min'])) {
                $where[] = "p.area >= ?";
                $params[] = $filters['area_min'];
            }

            if (!empty($filters['area_max'])) {
                $where[] = "p.area <= ?";
                $params[] = $filters['area_max'];
            }

            $whereClause = implode(' AND ', $where);

            // Get properties
            $sql = "SELECT p.*, a.name as associate_name, a.email as associate_email
                    FROM properties p
                    LEFT JOIN associates a ON p.associate_id = a.id
                    WHERE $whereClause
                    ORDER BY p.created_at DESC
                    LIMIT ? OFFSET ?";

            $properties = $this->db->fetchAll($sql, array_merge($params, [$limit, $offset]));

            // Get total count
            $countSql = "SELECT COUNT(*) as count FROM properties p WHERE $whereClause";
            $total = $this->db->fetchOne($countSql, $params)['count'];

            return [
                'data' => $properties,
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit)
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::getAllProperties - Error: " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => 1
            ];
        }
    }

    /**
     * Get property by ID
     */
    public function getPropertyById($id)
    {
        try {
            $sql = "SELECT p.*, a.name as associate_name, a.email as associate_email
                    FROM properties p
                    LEFT JOIN associates a ON p.associate_id = a.id
                    WHERE p.id = ? AND p.status != 'deleted'";

            $property = $this->db->fetchOne($sql, [$id]);

            if ($property) {
                // Get property images
                $imagesSql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order ASC";
                $property['images'] = $this->db->fetchAll($imagesSql, [$id]);
            }

            return $property;
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::getPropertyById - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new property
     */
    public function createProperty($data, $images = [])
    {
        try {
            // Simple validation
            if (empty($data['title']) || empty($data['description']) || empty($data['type']) || empty($data['price']) || empty($data['area']) || empty($data['location']) || empty($data['address']) || empty($data['city']) || empty($data['state']) || empty($data['pincode']) || empty($data['category_id']) || empty($data['associate_id'])) {
                return [
                    'success' => false,
                    'message' => 'All fields are required'
                ];
            }

            // Generate property ID
            $propertyId = 'PROP' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Handle image uploads
            $featuredImage = '';
            if (!empty($images)) {
                $featuredImage = $images[0] ?? '';
                $this->uploadPropertyImages($propertyId, $images);
            }

            // Insert property
            $sql = "INSERT INTO properties (id, title, description, type, price, area, bedrooms, bathrooms, location, address, city, state, pincode, featured_image, status, category_id, associate_id, created_by, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $params = [
                $propertyId,
                Security::sanitize($data['title']),
                Security::sanitize($data['description']),
                Security::sanitize($data['type']),
                (float)$data['price'],
                (float)$data['area'],
                (int)($data['bedrooms'] ?? 0),
                (int)($data['bathrooms'] ?? 0),
                Security::sanitize($data['location']),
                Security::sanitize($data['address']),
                Security::sanitize($data['city']),
                Security::sanitize($data['state']),
                Security::sanitize($data['pincode']),
                $featuredImage,
                'available',
                (int)$data['category_id'],
                Security::sanitize($data['associate_id']),
                $_SESSION['admin_id'] ?? null
            ];

            $this->db->execute($sql, $params);

            // Log activity
            $this->logActivity('property_created', $propertyId, 'Property created: ' . $data['title']);

            return [
                'success' => true,
                'message' => 'Property created successfully',
                'property_id' => $propertyId
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::createProperty - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create property: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update property
     */
    public function updateProperty($id, $data, $images = [])
    {
        try {
            // Simple validation
            if (empty($data['title']) || empty($data['description']) || empty($data['type']) || empty($data['price']) || empty($data['area']) || empty($data['location']) || empty($data['address']) || empty($data['city']) || empty($data['state']) || empty($data['pincode']) || empty($data['category_id']) || empty($data['associate_id'])) {
                return [
                    'success' => false,
                    'message' => 'All fields are required'
                ];
            }

            // Handle image updates
            $featuredImage = $data['featured_image'] ?? '';
            if (!empty($images)) {
                $featuredImage = $images[0] ?? '';
                $this->updatePropertyImages($id, $images);
            }

            // Update property
            $sql = "UPDATE properties SET title = ?, description = ?, type = ?, price = ?, area = ?, bedrooms = ?, bathrooms = ?, location = ?, address = ?, city = ?, state = ?, pincode = ?, featured_image = ?, category_id = ?, associate_id = ?, updated_by = ?, updated_at = NOW() 
                    WHERE id = ? AND status != 'deleted'";

            $params = [
                Security::sanitize($data['title']),
                Security::sanitize($data['description']),
                Security::sanitize($data['type']),
                (float)$data['price'],
                (float)$data['area'],
                (int)($data['bedrooms'] ?? 0),
                (int)($data['bathrooms'] ?? 0),
                Security::sanitize($data['location']),
                Security::sanitize($data['address']),
                Security::sanitize($data['city']),
                Security::sanitize($data['state']),
                Security::sanitize($data['pincode']),
                $featuredImage,
                (int)$data['category_id'],
                Security::sanitize($data['associate_id']),
                $_SESSION['admin_id'] ?? null,
                $id
            ];

            $this->db->execute($sql, $params);

            // Log activity
            $this->logActivity('property_updated', $id, 'Property updated: ' . $data['title']);

            return [
                'success' => true,
                'message' => 'Property updated successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::updateProperty - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update property: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete property
     */
    public function deleteProperty($id)
    {
        try {
            // Check if property exists
            $property = $this->getPropertyById($id);
            if (!$property) {
                return [
                    'success' => false,
                    'message' => 'Property not found'
                ];
            }

            // Soft delete
            $sql = "UPDATE properties SET status = 'deleted', updated_by = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$_SESSION['admin_id'] ?? null, $id]);

            // Log activity
            $this->logActivity('property_deleted', $id, 'Property deleted: ' . $property['title']);

            return [
                'success' => true,
                'message' => 'Property deleted successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::deleteProperty - Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete property: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get property statistics
     */
    public function getPropertyStats()
    {
        try {
            $stats = [];

            // Total properties
            $stats['total'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE status != 'deleted'")['count'];

            // Available properties
            $stats['available'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE status = 'available'")['count'];

            // Sold properties
            $stats['sold'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE status = 'sold'")['count'];

            // Properties by type
            $stats['by_type'] = $this->db->fetchAll("SELECT type, COUNT(*) as count FROM properties WHERE status != 'deleted' GROUP BY type");

            // Average price
            $stats['avg_price'] = $this->db->fetchOne("SELECT AVG(price) as avg FROM properties WHERE status != 'deleted' AND price > 0")['avg'];

            // Recent properties
            $stats['recent'] = $this->db->fetchAll("SELECT * FROM properties WHERE status != 'deleted' ORDER BY created_at DESC LIMIT 5");

            return $stats;
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::getPropertyStats - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search properties
     */
    public function searchProperties($query, $filters = [])
    {
        try {
            $where = ["p.status != 'deleted'"];
            $params = [];

            // Add search query
            if (!empty($query)) {
                $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ? OR p.address LIKE ?)";
                $searchParam = "%{$query}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            // Add filters
            if (!empty($filters['type'])) {
                $where[] = "p.type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['price_min'])) {
                $where[] = "p.price >= ?";
                $params[] = $filters['price_min'];
            }

            if (!empty($filters['price_max'])) {
                $where[] = "p.price <= ?";
                $params[] = $filters['price_max'];
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT p.*, a.name as associate_name, a.email as associate_email
                    FROM properties p
                    LEFT JOIN associates a ON p.associate_id = a.id
                    WHERE $whereClause
                    ORDER BY p.created_at DESC
                    LIMIT 50";

            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::searchProperties - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Upload property images
     */
    private function uploadPropertyImages($propertyId, $images)
    {
        try {
            $uploadDir = 'uploads/properties/' . $propertyId . '/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($images as $index => $image) {
                $filename = 'image_' . ($index + 1) . '_' . time() . '.jpg';
                $filepath = $uploadDir . $filename;

                // Move uploaded file
                if (move_uploaded_file($image['tmp_name'], $filepath)) {
                    // Insert image record
                    $sql = "INSERT INTO property_images (property_id, image_path, sort_order, created_at) VALUES (?, ?, ?, NOW())";
                    $this->db->execute($sql, [$propertyId, $filepath, $index + 1]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::uploadPropertyImages - Error: " . $e->getMessage());
        }
    }

    /**
     * Update property images
     */
    private function updatePropertyImages($propertyId, $images)
    {
        try {
            // Delete existing images
            $this->db->execute("DELETE FROM property_images WHERE property_id = ?", [$propertyId]);

            // Upload new images
            $this->uploadPropertyImages($propertyId, $images);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::updatePropertyImages - Error: " . $e->getMessage());
        }
    }

    /**
     * Log activity
     */
    private function logActivity($action, $propertyId, $details = '')
    {
        try {
            $sql = "INSERT INTO activity_log (user_id, action, details, ip_address, user_agent, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";

            $params = [
                $_SESSION['admin_id'] ?? null,
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];

            $this->db->execute($sql, $params);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::logActivity - Error: " . $e->getMessage());
        }
    }
}

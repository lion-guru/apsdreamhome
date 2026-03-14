<?php

/**
 * Property Service
 * Handles all property-related business logic
 */

namespace App\Services\Business;

use App\Core\Database;
use App\Core\Security;
use App\Core\SessionManager;
use App\Core\Logger;

class PropertyService
{
    private $db;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Get all properties with pagination
     */
    public function getAllProperties($page = 1, $limit = 10, $search = '', $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;

            $sql = "SELECT 
                p.id, p.title, p.type, p.status, p.price, p.area,
                p.bedrooms, p.bathrooms, p.location, p.description,
                p.featured_image, p.created_at, p.updated_at,
                a.name as associate_name, a.email as associate_email,
                a.phone as associate_phone,
                c.name as category_name
                FROM properties p
                LEFT JOIN associates a ON p.associate_id = a.id
                LEFT JOIN property_categories c ON p.category_id = c.id
                WHERE 1=1";

            $params = [];

            // Search functionality
            if (!empty($search)) {
                $sql .= " AND (p.title LIKE ? OR p.location LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // Apply filters
            if (!empty($filters['type'])) {
                $sql .= " AND p.type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND p.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['category_id'])) {
                $sql .= " AND p.category_id = ?";
                $params[] = $filters['category_id'];
            }

            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $filters['max_price'];
            }

            if (!empty($filters['location'])) {
                $sql .= " AND p.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }

            $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $properties = $this->db->fetchAll($sql, $params);

            // Get total count for pagination
            $countSql = "SELECT COUNT(p.id) as total
                         FROM properties p
                         LEFT JOIN associates a ON p.associate_id = a.id
                         LEFT JOIN property_categories c ON p.category_id = c.id
                         WHERE 1=1";

            $countParams = [];

            if (!empty($search)) {
                $countSql .= " AND (p.title LIKE ? OR p.location LIKE ? OR p.description LIKE ?)";
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }

            if (!empty($filters['type'])) {
                $countSql .= " AND p.type = ?";
                $countParams[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $countSql .= " AND p.status = ?";
                $countParams[] = $filters['status'];
            }

            if (!empty($filters['category_id'])) {
                $countSql .= " AND p.category_id = ?";
                $countParams[] = $filters['category_id'];
            }

            if (!empty($filters['min_price'])) {
                $countSql .= " AND p.price >= ?";
                $countParams[] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $countSql .= " AND p.price <= ?";
                $countParams[] = $filters['max_price'];
            }

            if (!empty($filters['location'])) {
                $countSql .= " AND p.location LIKE ?";
                $countParams[] = '%' . $filters['location'] . '%';
            }

            $totalResult = $this->db->fetch($countSql, $countParams);
            $total = $totalResult['total'] ?? 0;

            return [
                'properties' => $properties,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::getAllProperties - Error: " . $e->getMessage());
            return [
                'properties' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => 0
            ];
        }
    }

    /**
     * Get property by ID
     */
    public function getPropertyById($id)
    {
        try {
            $sql = "SELECT 
                p.*, a.name as associate_name, a.email as associate_email,
                a.phone as associate_phone, c.name as category_name
                FROM properties p
                LEFT JOIN associates a ON p.associate_id = a.id
                LEFT JOIN property_categories c ON p.category_id = c.id
                WHERE p.id = ?";

            return $this->db->fetch($sql, [$id]);
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
            // Validate input data
            $validationRules = [
                'title' => ['required' => true, 'type' => 'string', 'min' => 3, 'max' => 200],
                'description' => ['required' => true, 'type' => 'string', 'min' => 10, 'max' => 2000],
                'type' => ['required' => true, 'type' => 'string', 'in' => ['residential', 'commercial', 'land']],
                'price' => ['required' => true, 'type' => 'numeric', 'min' => 0],
                'area' => ['required' => true, 'type' => 'numeric', 'min' => 1],
                'bedrooms' => ['type' => 'numeric', 'min' => 0],
                'bathrooms' => ['type' => 'numeric', 'min' => 0],
                'location' => ['required' => true, 'type' => 'string', 'min' => 3, 'max' => 255],
                'address' => ['required' => true, 'type' => 'string', 'min' => 5, 'max' => 500],
                'city' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 100],
                'state' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 100],
                'pincode' => ['required' => true, 'type' => 'string', 'min' => 6, 'max' => 6],
                'category_id' => ['required' => true, 'type' => 'numeric'],
                'associate_id' => ['required' => true, 'type' => 'string']
            ];

            $validation = $this->inputValidation->validate($data, $validationRules);

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            // Generate property ID
            $propertyId = 'PROP' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Handle image uploads
            $featuredImage = '';
            if (!empty($images)) {
                $featuredImage = $images[0] ?? '';
            }

            // Insert property
            $sql = "INSERT INTO properties (
                id, title, description, type, price, area, bedrooms, bathrooms,
                location, address, city, state, pincode, category_id,
                associate_id, featured_image, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())";

            $params = [
                $propertyId,
                $data['title'],
                $data['description'],
                $data['type'],
                $data['price'],
                $data['area'],
                $data['bedrooms'] ?? 0,
                $data['bathrooms'] ?? 0,
                $data['location'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['pincode'],
                $data['category_id'],
                $data['associate_id'],
                $featuredImage
            ];

            $this->db->execute($sql, $params);

            // Save additional images
            if (!empty($images)) {
                $this->savePropertyImages($propertyId, $images);
            }

            // Log activity
            $this->logActivity('property_created', $propertyId, 'New property created: ' . $data['title']);

            return [
                'success' => true,
                'property_id' => $propertyId,
                'message' => 'Property created successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::createProperty - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to create property']
            ];
        }
    }

    /**
     * Update property
     */
    public function updateProperty($id, $data, $images = [])
    {
        try {
            // Validate input data
            $validationRules = [
                'title' => ['required' => true, 'type' => 'string', 'min' => 3, 'max' => 200],
                'description' => ['required' => true, 'type' => 'string', 'min' => 10, 'max' => 2000],
                'type' => ['required' => true, 'type' => 'string', 'in' => ['residential', 'commercial', 'land']],
                'price' => ['required' => true, 'type' => 'numeric', 'min' => 0],
                'area' => ['required' => true, 'type' => 'numeric', 'min' => 1],
                'bedrooms' => ['type' => 'numeric', 'min' => 0],
                'bathrooms' => ['type' => 'numeric', 'min' => 0],
                'location' => ['required' => true, 'type' => 'string', 'min' => 3, 'max' => 255],
                'address' => ['required' => true, 'type' => 'string', 'min' => 5, 'max' => 500],
                'city' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 100],
                'state' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 100],
                'pincode' => ['required' => true, 'type' => 'string', 'min' => 6, 'max' => 6],
                'category_id' => ['required' => true, 'type' => 'numeric'],
                'associate_id' => ['required' => true, 'type' => 'string']
            ];

            $validation = $this->inputValidation->validate($data, $validationRules);

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            // Handle image updates
            $featuredImage = $data['featured_image'] ?? '';
            if (!empty($images)) {
                $featuredImage = $images[0] ?? '';
                $this->updatePropertyImages($id, $images);
            }

            // Update property
            $sql = "UPDATE properties SET 
                title = ?, description = ?, type = ?, price = ?, area = ?,
                bedrooms = ?, bathrooms = ?, location = ?, address = ?,
                city = ?, state = ?, pincode = ?, category_id = ?,
                associate_id = ?, featured_image = ?, updated_at = NOW()
                WHERE id = ?";

            $params = [
                $data['title'],
                $data['description'],
                $data['type'],
                $data['price'],
                $data['area'],
                $data['bedrooms'] ?? 0,
                $data['bathrooms'] ?? 0,
                $data['location'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['pincode'],
                $data['category_id'],
                $data['associate_id'],
                $featuredImage,
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
                'errors' => ['general' => 'Failed to update property']
            ];
        }
    }

    /**
     * Delete property
     */
    public function deleteProperty($id)
    {
        try {
            // Delete property images first
            $this->deletePropertyImages($id);

            // Delete property
            $this->db->execute("DELETE FROM properties WHERE id = ?", [$id]);

            // Log activity
            $this->logActivity('property_deleted', $id, 'Property deleted');

            return [
                'success' => true,
                'message' => 'Property deleted successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::deleteProperty - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to delete property']
            ];
        }
    }

    /**
     * Update property status
     */
    public function updatePropertyStatus($id, $status)
    {
        try {
            $validStatuses = ['active', 'inactive', 'sold', 'pending'];

            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'errors' => ['status' => 'Invalid status']
                ];
            }

            $updateData = ['status' => $status];

            if ($status === 'sold') {
                $updateData['sold_date'] = date('Y-m-d H:i:s');
            }

            $setClause = [];
            $params = [];

            foreach ($updateData as $key => $value) {
                $setClause[] = "$key = ?";
                $params[] = $value;
            }

            $params[] = $id;

            $sql = "UPDATE properties SET " . implode(', ', $setClause) . ", updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, $params);

            // Log activity
            $this->logActivity('property_status_updated', $id, 'Property status updated to: ' . $status);

            return [
                'success' => true,
                'message' => 'Property status updated successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::updatePropertyStatus - Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to update property status']
            ];
        }
    }

    /**
     * Get property statistics
     */
    public function getPropertyStatistics()
    {
        try {
            $sql = "SELECT 
                COUNT(*) as total_properties,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_properties,
                SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_properties,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_properties,
                SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured_properties,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price,
                SUM(price) as total_value
                FROM properties";

            return $this->db->fetch($sql);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::getPropertyStatistics - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get featured properties
     */
    public function getFeaturedProperties($limit = 10)
    {
        try {
            $sql = "SELECT 
                p.*, a.name as associate_name, c.name as category_name
                FROM properties p
                LEFT JOIN associates a ON p.associate_id = a.id
                LEFT JOIN property_categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT ?";

            return $this->db->fetchAll($sql, [$limit]);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::getFeaturedProperties - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search properties
     */
    public function searchProperties($criteria)
    {
        try {
            $sql = "SELECT 
                p.*, a.name as associate_name, c.name as category_name
                FROM properties p
                LEFT JOIN associates a ON p.associate_id = a.id
                LEFT JOIN property_categories c ON p.category_id = c.id
                WHERE 1=1";

            $params = [];

            // Title search
            if (!empty($criteria['title'])) {
                $sql .= " AND p.title LIKE ?";
                $params[] = '%' . $criteria['title'] . '%';
            }

            // Location search
            if (!empty($criteria['location'])) {
                $sql .= " AND p.location LIKE ?";
                $params[] = '%' . $criteria['location'] . '%';
            }

            // Type filter
            if (!empty($criteria['type'])) {
                $sql .= " AND p.type = ?";
                $params[] = $criteria['type'];
            }

            // Price range
            if (!empty($criteria['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $criteria['min_price'];
            }

            if (!empty($criteria['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $criteria['max_price'];
            }

            // Area range
            if (!empty($criteria['min_area'])) {
                $sql .= " AND p.area >= ?";
                $params[] = $criteria['min_area'];
            }

            if (!empty($criteria['max_area'])) {
                $sql .= " AND p.area <= ?";
                $params[] = $criteria['max_area'];
            }

            // Bedrooms
            if (!empty($criteria['bedrooms'])) {
                $sql .= " AND p.bedrooms = ?";
                $params[] = $criteria['bedrooms'];
            }

            $sql .= " ORDER BY p.created_at DESC";

            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::searchProperties - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save property images
     */
    private function savePropertyImages($propertyId, $images)
    {
        try {
            foreach ($images as $index => $image) {
                $sql = "INSERT INTO property_images (property_id, image_url, is_featured, sort_order, created_at) 
                         VALUES (?, ?, ?, ?, NOW())";

                $params = [
                    $propertyId,
                    $image,
                    $index === 0 ? 1 : 0,
                    $index
                ];

                $this->db->execute($sql, $params);
            }
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::savePropertyImages - Error: " . $e->getMessage());
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

            // Save new images
            $this->savePropertyImages($propertyId, $images);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::updatePropertyImages - Error: " . $e->getMessage());
        }
    }

    /**
     * Delete property images
     */
    private function deletePropertyImages($propertyId)
    {
        try {
            $this->db->execute("DELETE FROM property_images WHERE property_id = ?", [$propertyId]);
        } catch (\Exception $e) {
            $this->logger->error("PropertyService::deletePropertyImages - Error: " . $e->getMessage());
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
                SessionManager::get('admin_id'),
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

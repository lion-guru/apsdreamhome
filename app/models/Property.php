<?php
/**
 * Property Model
 * 
 * Handles all property-related database operations
 * including CRUD operations, searching, and filtering.
 */

namespace App\Models;

use App\Core\Database\Database;
use App\Core\Security;
use Exception;
use PDO;

class Property {
    private $db;
    private $security;
    private $table = 'properties';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->security = new Security();
    }
    
    /**
     * Create a new property
     */
    public function create($data) {
        try {
            // Validate required fields
            $required = ['title', 'description', 'price', 'location', 'property_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Sanitize input
            $data = $this->sanitizePropertyData($data);
            
            $sql = "INSERT INTO {$this->table} (
                title, description, price, location, property_type, 
                bedrooms, bathrooms, area, status, featured, 
                images, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['title'],
                $data['description'],
                $data['price'],
                $data['location'],
                $data['property_type'],
                $data['bedrooms'] ?? 0,
                $data['bathrooms'] ?? 0,
                $data['area'] ?? 0,
                $data['status'] ?? 'available',
                $data['featured'] ?? 0,
                json_encode($data['images'] ?? [])
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Property create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get property by ID
     */
    public function find($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return $property;
        } catch (Exception $e) {
            error_log("Property find error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all properties with pagination and filtering
     */
    public function getAll($page = 1, $limit = 10, $filters = []) {
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
            
            if (!empty($filters['featured'])) {
                $where[] = "featured = ?";
                $params[] = $filters['featured'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(title LIKE ? OR description LIKE ? OR location LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM {$this->table} $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Get properties
            $sql = "SELECT * FROM {$this->table} $whereClause 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
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
            error_log("Property getAll error: " . $e->getMessage());
            return ['properties' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update property
     */
    public function update($id, $data) {
        try {
            // Validate required fields
            $required = ['title', 'description', 'price', 'location', 'property_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Sanitize input
            $data = $this->sanitizePropertyData($data);
            
            $sql = "UPDATE {$this->table} SET 
                title = ?, description = ?, price = ?, location = ?, property_type = ?,
                bedrooms = ?, bathrooms = ?, area = ?, status = ?, featured = ?,
                images = ?, updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['title'],
                $data['description'],
                $data['price'],
                $data['location'],
                $data['property_type'],
                $data['bedrooms'] ?? 0,
                $data['bathrooms'] ?? 0,
                $data['area'] ?? 0,
                $data['status'] ?? 'available',
                $data['featured'] ?? 0,
                json_encode($data['images'] ?? []),
                $id
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Property update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete property
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Property delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get featured properties
     */
    public function getFeatured($limit = 6) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE featured = 1 AND status = 'available' 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode images JSON
            foreach ($properties as &$property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return $properties;
        } catch (Exception $e) {
            error_log("Property getFeatured error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get available properties
     */
    public function getAvailable($limit = 10) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE status = 'available' 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode images JSON
            foreach ($properties as &$property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return $properties;
        } catch (Exception $e) {
            error_log("Property getAvailable error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search properties
     */
    public function search($query, $limit = 10) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE (title LIKE ? OR description LIKE ? OR location LIKE ?)
                    AND status = 'available' 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode images JSON
            foreach ($properties as &$property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return $properties;
        } catch (Exception $e) {
            error_log("Property search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get properties by type
     */
    public function getByType($type, $limit = 10) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE property_type = ? AND status = 'available' 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$type, $limit]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode images JSON
            foreach ($properties as &$property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return $properties;
        } catch (Exception $e) {
            error_log("Property getByType error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get properties by location
     */
    public function getByLocation($location, $limit = 10) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE location LIKE ? AND status = 'available' 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $locationTerm = '%' . $location . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$locationTerm, $limit]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode images JSON
            foreach ($properties as &$property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return $properties;
        } catch (Exception $e) {
            error_log("Property getByLocation error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get property statistics
     */
    public function getStats() {
        $stats = [];
        
        try {
            // Total properties
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetchColumn();
            
            // Properties by status
            $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Properties by type
            $sql = "SELECT property_type, COUNT(*) as count FROM {$this->table} GROUP BY property_type";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Average price
            $sql = "SELECT AVG(price) as avg_price FROM {$this->table} WHERE status = 'available'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['avg_price'] = round($stmt->fetchColumn(), 2);
            
            // Price range
            $sql = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM {$this->table} WHERE status = 'available'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $priceRange = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['price_range'] = [
                'min' => $priceRange['min_price'],
                'max' => $priceRange['max_price']
            ];
            
            // Featured properties count
            $sql = "SELECT COUNT(*) as featured FROM {$this->table} WHERE featured = 1 AND status = 'available'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['featured'] = $stmt->fetchColumn();
            
        } catch (Exception $e) {
            error_log("Property stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Toggle property status
     */
    public function toggleStatus($id) {
        try {
            $sql = "UPDATE {$this->table} SET status = CASE 
                    WHEN status = 'available' THEN 'sold' 
                    WHEN status = 'sold' THEN 'available' 
                    ELSE 'available' 
                    END, updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Property toggleStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle featured status
     */
    public function toggleFeatured($id) {
        try {
            $sql = "UPDATE {$this->table} SET featured = CASE 
                    WHEN featured = 1 THEN 0 
                    ELSE 1 
                    END, updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Property toggleFeatured error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get property types
     */
    public function getPropertyTypes() {
        try {
            $sql = "SELECT DISTINCT property_type FROM {$this->table} ORDER BY property_type";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Property getPropertyTypes error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get property locations
     */
    public function getPropertyLocations() {
        try {
            $sql = "SELECT DISTINCT location FROM {$this->table} ORDER BY location";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Property getPropertyLocations error: " . $e->getMessage());
            return [];
        }
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
     * Validate property data
     */
    public function validate($data) {
        $errors = [];
        
        // Title validation
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($data['title']) < 3) {
            $errors['title'] = 'Title must be at least 3 characters';
        }
        
        // Description validation
        if (empty($data['description'])) {
            $errors['description'] = 'Description is required';
        } elseif (strlen($data['description']) < 10) {
            $errors['description'] = 'Description must be at least 10 characters';
        }
        
        // Price validation
        if (empty($data['price'])) {
            $errors['price'] = 'Price is required';
        } elseif (!is_numeric($data['price']) || $data['price'] <= 0) {
            $errors['price'] = 'Price must be a positive number';
        }
        
        // Location validation
        if (empty($data['location'])) {
            $errors['location'] = 'Location is required';
        }
        
        // Property type validation
        if (empty($data['property_type'])) {
            $errors['property_type'] = 'Property type is required';
        }
        
        // Bedrooms validation
        if (isset($data['bedrooms']) && (!is_numeric($data['bedrooms']) || $data['bedrooms'] < 0)) {
            $errors['bedrooms'] = 'Bedrooms must be a non-negative number';
        }
        
        // Bathrooms validation
        if (isset($data['bathrooms']) && (!is_numeric($data['bathrooms']) || $data['bathrooms'] < 0)) {
            $errors['bathrooms'] = 'Bathrooms must be a non-negative number';
        }
        
        // Area validation
        if (isset($data['area']) && (!is_numeric($data['area']) || $data['area'] < 0)) {
            $errors['area'] = 'Area must be a non-negative number';
        }
        
        return $errors;
    }
}

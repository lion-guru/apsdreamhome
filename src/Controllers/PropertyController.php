<?php
namespace Controllers;

class PropertyController {
    private $db;

    public function __construct() {
        $this->db = \Database::getInstance();
    }

    public function getAllProperties($filters = []) {
        $sql = "SELECT * FROM properties WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND property_type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['city'])) {
            $sql .= " AND city = ?";
            $params[] = $filters['city'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND price <= ?";
            $params[] = $filters['max_price'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function getPropertyById($id) {
        $sql = "SELECT * FROM properties WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function createProperty($data) {
        // Validate required fields
        $required = ['title', 'description', 'price', 'property_type', 'city'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }

        // Sanitize inputs
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_input($value);
        }

        try {
            $this->db->insert('properties', $data);
            return ['success' => true, 'message' => 'Property created successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to create property'];
        }
    }

    public function updateProperty($id, $data) {
        // Sanitize inputs
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_input($value);
        }

        try {
            $this->db->update('properties', $data, "id = {$id}");
            return ['success' => true, 'message' => 'Property updated successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to update property'];
        }
    }

    public function deleteProperty($id) {
        try {
            $this->db->delete('properties', "id = {$id}");
            return ['success' => true, 'message' => 'Property deleted successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete property'];
        }
    }

    public function getPropertiesByType($type) {
        $sql = "SELECT * FROM properties WHERE property_type = ?";
        return $this->db->fetchAll($sql, [$type]);
    }

    public function getPropertiesByCity($city) {
        $sql = "SELECT * FROM properties WHERE city = ?";
        return $this->db->fetchAll($sql, [$city]);
    }

    public function getFeaturedProperties($limit = 6) {
        $sql = "SELECT * FROM properties WHERE is_featured = 1 LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function searchProperties($keyword) {
        $keyword = "%{$keyword}%";
        $sql = "SELECT * FROM properties WHERE title LIKE ? OR description LIKE ? OR city LIKE ?";
        return $this->db->fetchAll($sql, [$keyword, $keyword, $keyword]);
    }
}
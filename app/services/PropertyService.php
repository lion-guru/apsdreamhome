<?php

namespace App\Services;

use App\Models\Property;

class PropertyService {
    private $propertyModel;

    public function __construct() {
        $this->propertyModel = new Property();
    }

    /**
     * Get all properties with optional filters
     * 
     * @param array $filters
     * @return array
     */
    public function getProperties(array $filters = []) {
        $query = "SELECT * FROM properties WHERE status = 'active'";
        $params = [];
        
        // Apply filters
        if (!empty($filters['type'])) {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['location'])) {
            $query .= " AND location LIKE ?";
            $params[] = "%{$filters['location']}%";
        }
        
        // Add sorting
        $sort = $filters['sort'] ?? 'created_at';
        $order = isset($filters['order']) && strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY $sort $order";
        
        // Add pagination
        $page = max(1, $filters['page'] ?? 1);
        $perPage = min(20, max(1, $filters['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        // Execute query
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query($query, $params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get property by ID
     * 
     * @param int $id
     * @return Property|null
     */
    public function getPropertyById(int $id) {
        return Property::find($id);
    }
    
    /**
     * Create a new property
     * 
     * @param array $data
     * @return int|bool
     */
    public function createProperty(array $data) {
        $requiredFields = ['title', 'description', 'price', 'location', 'type'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }
        
        $property = new Property();
        $property->title = $data['title'];
        $property->description = $data['description'];
        $property->price = (float)$data['price'];
        $property->location = $data['location'];
        $property->property_type = $data['type'];
        $property->status = 'active';
        $property->bedrooms = $data['bedrooms'] ?? null;
        $property->bathrooms = $data['bathrooms'] ?? null;
        $property->area = $data['area'] ?? null;

        if ($property->save()) {
            return $property->id;
        }

        return false;
    }
    
    /**
     * Update a property
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProperty(int $id, array $data) {
        $property = Property::find($id);
        
        if (!$property) {
            return false;
        }
        
        $updatableFields = [
            'title', 'description', 'price', 'location', 'property_type',
            'bedrooms', 'bathrooms', 'area', 'status'
        ];
        
        foreach ($updatableFields as $field) {
            if (array_key_exists($field, $data)) {
                $property->$field = $data[$field];
            }
        }
        
        return $property->save();
    }
    
    /**
     * Delete a property
     * 
     * @param int $id
     * @return bool
     */
    public function deleteProperty(int $id) {
        $property = Property::find($id);
        
        if (!$property) {
            return false;
        }
        
        // Soft delete by updating status
        $property->status = 'deleted';
        return $property->save();
    }
    
    /**
     * Get featured properties
     * 
     * @param int $limit
     * @return array
     */
    public function getFeaturedProperties(int $limit = 6) {
        $query = "SELECT * FROM properties WHERE status = 'active' AND is_featured = 1 ORDER BY created_at DESC LIMIT ?";
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query($query, [$limit]);
        
        return $stmt->fetchAll();
    }
}

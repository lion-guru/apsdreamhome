<?php

class PropertiesEndpoint {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function list($params = []) {
        try {
            // Get query parameters with defaults
            $status = $params['status'] ?? 'available';
            $limit = min((int)($params['limit'] ?? 10), 100);
            $offset = max((int)($params['offset'] ?? 0), 0);
            $minPrice = isset($params['min_price']) ? (float)$params['min_price'] : null;
            $maxPrice = isset($params['max_price']) ? (float)$params['max_price'] : null;
            $bedrooms = isset($params['bedrooms']) ? (int)$params['bedrooms'] : null;
            $bathrooms = isset($params['bathrooms']) ? (int)$params['bathrooms'] : null;
            $type = $params['type'] ?? null;
            
            // Build WHERE clause
            $where = ["p.status = ?"];
            $params = [$status];
            $types = 's';
            
            if ($minPrice !== null) {
                $where[] = "p.price >= ?";
                $params[] = $minPrice;
                $types .= 'd';
            }
            
            if ($maxPrice !== null) {
                $where[] = "p.price <= ?";
                $params[] = $maxPrice;
                $types .= 'd';
            }
            
            if ($bedrooms !== null) {
                $where[] = "p.bedrooms = ?";
                $params[] = $bedrooms;
                $types .= 'i';
            }
            
            if ($bathrooms !== null) {
                $where[] = "p.bathrooms = ?";
                $params[] = $bathrooms;
                $types .= 'i';
            }
            
            if ($type) {
                $where[] = "p.type = ?";
                $params[] = $type;
                $types .= 's';
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM properties p WHERE $whereClause";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $total = $countStmt->get_result()->fetch_assoc()['total'];
            
            // Get properties with pagination
            $query = "SELECT p.*, u.first_name, u.last_name 
                      FROM properties p 
                      LEFT JOIN users u ON p.owner_id = u.id 
                      WHERE $whereClause 
                      ORDER BY p.created_at DESC 
                      LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($query);
            
            // Add pagination parameters
            $types .= 'ii';
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $properties = [];
            while ($row = $result->fetch_assoc()) {
                $properties[] = $this->formatProperty($row);
            }
            
            return [
                'success' => true,
                'data' => $properties,
                'pagination' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'pages' => ceil($total / $limit)
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception('Failed to fetch properties: ' . $e->getMessage(), 500);
        }
    }
    
    public function get($id) {
        try {
            $query = "SELECT p.*, u.first_name, u.last_name 
                      FROM properties p 
                      LEFT JOIN users u ON p.owner_id = u.id 
                      WHERE p.id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Property not found', 404);
            }
            
            return [
                'success' => true,
                'data' => $this->formatProperty($result->fetch_assoc())
            ];
            
        } catch (Exception $e) {
            throw new Exception('Failed to fetch property: ' . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function create($data) {
        try {
            // Validate required fields
            $required = ['title', 'price', 'bedrooms', 'bathrooms', 'area', 'address'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Missing required field: $field", 400);
                }
            }
            
            // Start transaction
            $this->conn->begin_transaction();
            
            // Insert property
            $query = "INSERT INTO properties (
                title, description, address, price, bedrooms, bathrooms, 
                area, type, status, features, images, owner_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Default values
            $status = $data['status'] ?? 'available';
            $features = json_encode($data['features'] ?? []);
            $images = json_encode($data['images'] ?? []);
            $ownerId = $data['owner_id'] ?? 1; // Default to admin if not specified
            
            $stmt->bind_param(
                'sssddidssssi',
                $data['title'],
                $data['description'] ?? '',
                $data['address'],
                $data['price'],
                $data['bedrooms'],
                $data['bathrooms'],
                $data['area'],
                $data['type'] ?? 'residential',
                $status,
                $features,
                $images,
                $ownerId
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create property: ' . $stmt->error, 500);
            }
            
            $propertyId = $this->conn->insert_id;
            
            // Commit transaction
            $this->conn->commit();
            
            // Return the created property
            return $this->get($propertyId);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception('Failed to create property: ' . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function update($id, $data) {
        try {
            // Check if property exists
            $this->get($id);
            
            // Build update query
            $updates = [];
            $params = [];
            $types = '';
            
            $fields = [
                'title' => 's',
                'description' => 's',
                'address' => 's',
                'price' => 'd',
                'bedrooms' => 'i',
                'bathrooms' => 'i',
                'area' => 'd',
                'type' => 's',
                'status' => 's',
                'features' => 's',
                'images' => 's',
                'owner_id' => 'i'
            ];
            
            foreach ($fields as $field => $type) {
                if (array_key_exists($field, $data)) {
                    $updates[] = "$field = ?";
                    $params[] = $field === 'features' || $field === 'images' 
                        ? json_encode($data[$field])
                        : $data[$field];
                    $types .= $type;
                }
            }
            
            if (empty($updates)) {
                throw new Exception('No fields to update', 400);
            }
            
            // Add ID to params
            $params[] = $id;
            $types .= 'i';
            
            // Start transaction
            $this->conn->begin_transaction();
            
            // Update property
            $query = "UPDATE properties SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update property: ' . $stmt->error, 500);
            }
            
            // Commit transaction
            $this->conn->commit();
            
            // Return the updated property
            return $this->get($id);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception('Failed to update property: ' . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function delete($id) {
        try {
            // Check if property exists
            $property = $this->get($id);
            
            // Soft delete (update status to 'deleted')
            $query = "UPDATE properties SET status = 'deleted', updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to delete property', 500);
            }
            
            return [
                'success' => true,
                'message' => 'Property deleted successfully'
            ];
            
        } catch (Exception $e) {
            throw new Exception('Failed to delete property: ' . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    private function formatProperty($row) {
        return [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'address' => $row['address'],
            'price' => (float)$row['price'],
            'bedrooms' => (int)$row['bedrooms'],
            'bathrooms' => (int)$row['bathrooms'],
            'area' => (float)$row['area'],
            'type' => $row['type'],
            'status' => $row['status'],
            'features' => json_decode($row['features'] ?? '[]', true),
            'images' => json_decode($row['images'] ?? '[]', true),
            'owner' => [
                'id' => (int)$row['owner_id'],
                'name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
            ],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
}

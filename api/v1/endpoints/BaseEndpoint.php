<?php

/**
 * BaseEndpoint
 * 
 * Provides common functionality for all API endpoints
 */
abstract class BaseEndpoint {
    protected $conn;
    protected $userId;
    protected $userRole;
    
    public function __construct($conn, $userId = null, $userRole = null) {
        $this->conn = $conn;
        $this->userId = $userId;
        $this->userRole = $userRole;
    }
    
    /**
     * Validate required fields in the input data
     * 
     * @param array $data Input data to validate
     * @param array $requiredFields Array of required field names
     * @throws Exception If any required fields are missing
     */
    protected function validateRequiredFields($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === '') {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing), 400);
        }
    }
    
    /**
     * Check if the current user has the required permission
     * 
     * @param string|array $requiredPermission Required permission(s)
     * @throws Exception If user doesn't have the required permission
     */
    protected function checkPermission($requiredPermission) {
        if ($this->userRole === 'admin') {
            return; // Admin has all permissions
        }
        
        // If no specific permissions required
        if (empty($requiredPermission)) {
            return;
        }
        
        // Convert to array if it's a string
        $requiredPermissions = is_array($requiredPermission) 
            ? $requiredPermission 
            : [$requiredPermission];
        
        // Check if user has any of the required permissions
        // This is a simplified example - you would typically check against the user's actual permissions
        if (!in_array('*', $requiredPermissions)) {
            throw new Exception('Insufficient permissions', 403);
        }
    }
    
    /**
     * Format a success response
     */
    protected function success($data = null, $message = null, $statusCode = 200) {
        $response = [
            'success' => true,
            'status' => $statusCode,
            'timestamp' => date('c')
        ];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Format an error response
     */
    protected function error($message, $statusCode = 400, $errors = null) {
        $response = [
            'success' => false,
            'status' => $statusCode,
            'message' => $message,
            'timestamp' => date('c')
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return $response;
    }
    
    /**
     * Paginate query results
     */
    protected function paginate($query, $params = [], $countQuery = null, $countParams = []) {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;
        
        // Add pagination to the query
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        // Execute the main query
        $stmt = $this->conn->prepare($query);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Get total count
        if ($countQuery === null) {
            $countQuery = "SELECT COUNT(*) as total FROM (" . str_replace("\n", " ", $query) . ") as count_table";
            $countParams = $params;
        }
        
        $countStmt = $this->conn->prepare($countQuery);
        $countTypes = str_repeat('s', count($countParams));
        $countStmt->bind_param($countTypes, ...$countParams);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        
        // Format the paginated response
        return [
            'data' => $result->fetch_all(MYSQLI_ASSOC),
            'pagination' => [
                'total' => (int)$total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => max(1, ceil($total / $perPage)),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
    
    /**
     * Get the authenticated user ID
     */
    protected function getUserId() {
        if (!$this->userId) {
            throw new Exception('User not authenticated', 401);
        }
        return $this->userId;
    }
}

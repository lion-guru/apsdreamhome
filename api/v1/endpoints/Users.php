<?php

class UsersEndpoint extends BaseEndpoint {
    
    public function list($params = []) {
        try {
            $this->checkPermission('view_users');
            
            $query = "SELECT id, first_name, last_name, email, phone, role, status, created_at 
                      FROM users 
                      WHERE status != 'deleted'";
            
            // Add filters
            $filters = [];
            $filterParams = [];
            $types = '';
            
            if (!empty($params['role'])) {
                $filters[] = "role = ?";
                $filterParams[] = $params['role'];
                $types .= 's';
            }
            
            if (!empty($params['status'])) {
                $filters[] = "status = ?";
                $filterParams[] = $params['status'];
                $types .= 's';
            }
            
            if (!empty($filters)) {
                $query .= " AND " . implode(" AND ", $filters);
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $paginated = $this->paginate($query, $filterParams);
            
            return $this->success([
                'users' => $paginated['data'],
                'pagination' => $paginated['pagination']
            ]);
            
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function get($id) {
        try {
            $this->checkPermission('view_users');
            
            $query = "SELECT id, first_name, last_name, email, phone, role, status, created_at, updated_at 
                      FROM users 
                      WHERE id = ? AND status != 'deleted'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return $this->error('User not found', 404);
            }
            
            return $this->success($result->fetch_assoc());
            
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function create($data) {
        try {
            $this->checkPermission('create_users');
            
            // Validate required fields
            $this->validateRequiredFields($data, [
                'first_name', 'last_name', 'email', 'password', 'role'
            ]);
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->error('Invalid email format', 400);
            }
            
            // Check if email already exists
            $checkStmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param('s', $data['email']);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows > 0) {
                return $this->error('Email already in use', 409);
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Start transaction
            $this->conn->begin_transaction();
            
            // Insert user
            $query = "INSERT INTO users (
                first_name, last_name, email, password, phone, role, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                'ssssss',
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $hashedPassword,
                $data['phone'] ?? null,
                $data['role']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create user: ' . $stmt->error);
            }
            
            $userId = $this->conn->insert_id;
            
            // Commit transaction
            $this->conn->commit();
            
            // Return the created user (without password)
            return $this->get($userId);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function update($id, $data) {
        try {
            $this->checkPermission('edit_users');
            
            // Check if user exists and is not deleted
            $user = $this->get($id);
            if (!$user['success']) {
                return $user; // Return the error
            }
            
            // Build update query
            $updates = [];
            $params = [];
            $types = '';
            
            $fields = [
                'first_name' => 's',
                'last_name' => 's',
                'email' => 's',
                'phone' => 's',
                'role' => 's',
                'status' => 's'
            ];
            
            foreach ($fields as $field => $type) {
                if (array_key_exists($field, $data)) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= $type;
                }
            }
            
            // Handle password update separately
            if (!empty($data['password'])) {
                $updates[] = "password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
                $types .= 's';
            }
            
            if (empty($updates)) {
                return $this->error('No fields to update', 400);
            }
            
            // Add ID to params
            $params[] = $id;
            $types .= 'i';
            
            // Start transaction
            $this->conn->begin_transaction();
            
            // Update user
            $query = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update user: ' . $stmt->error);
            }
            
            // Commit transaction
            $this->conn->commit();
            
            // Return the updated user
            return $this->get($id);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function delete($id) {
        try {
            $this->checkPermission('delete_users');
            
            // Check if user exists and is not already deleted
            $user = $this->get($id);
            if (!$user['success']) {
                return $user; // Return the error
            }
            
            // Soft delete (update status to 'deleted')
            $query = "UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to delete user');
            }
            
            return $this->success(null, 'User deleted successfully');
            
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function getProfile() {
        try {
            $userId = $this->getUserId();
            return $this->get($userId);
            
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function updateProfile($data) {
        try {
            $userId = $this->getUserId();
            
            // Only allow updating specific fields for profile
            $allowedFields = ['first_name', 'last_name', 'phone', 'password'];
            $filteredData = array_intersect_key($data, array_flip($allowedFields));
            
            if (empty($filteredData)) {
                return $this->error('No valid fields to update', 400);
            }
            
            return $this->update($userId, $filteredData);
            
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}

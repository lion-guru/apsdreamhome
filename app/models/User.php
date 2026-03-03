<?php
/**
 * User Model
 * 
 * Handles all user-related database operations
 * including authentication, CRUD operations, and user management.
 */

namespace App\Models;

use App\Core\Database\Database;
use App\Core\Security;
use Exception;
use PDO;

class User {
    private $db;
    private $security;
    private $table = 'users';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->security = new Security();
    }
    
    /**
     * Create a new user
     */
    public function create($data) {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'full_name'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            // Check if username or email already exists
            if ($this->userExists($data['username'], $data['email'])) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Sanitize input
            $data = $this->sanitizeUserData($data);
            
            $sql = "INSERT INTO {$this->table} (
                username, email, password, full_name, phone, 
                role, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['full_name'],
                $data['phone'] ?? null,
                $data['role'] ?? 'user',
                $data['status'] ?? 'active'
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("User create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by ID
     */
    public function find($id) {
        try {
            $sql = "SELECT id, username, email, full_name, phone, role, status, 
                    created_at, updated_at, last_login 
                    FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User find error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by username
     */
    public function findByUsername($username) {
        try {
            $sql = "SELECT id, username, email, password, full_name, phone, role, status 
                    FROM {$this->table} WHERE username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User findByUsername error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by email
     */
    public function findByEmail($email) {
        try {
            $sql = "SELECT id, username, email, password, full_name, phone, role, status 
                    FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User findByEmail error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all users with pagination and filtering
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
            
            if (!empty($filters['role'])) {
                $where[] = "role = ?";
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
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
            
            // Get users
            $sql = "SELECT id, username, email, full_name, phone, role, status, 
                    created_at, updated_at, last_login 
                    FROM {$this->table} $whereClause 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            error_log("User getAll error: " . $e->getMessage());
            return ['users' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        try {
            // Validate required fields
            if (empty($data['username']) || empty($data['email']) || empty($data['full_name'])) {
                throw new Exception("Username, email, and full name are required");
            }
            
            // Check if username or email already exists (excluding current user)
            $sql = "SELECT id FROM {$this->table} WHERE (username = ? OR email = ?) AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['username'], $data['email'], $id]);
            
            if ($stmt->fetch()) {
                throw new Exception("Username or email already exists");
            }
            
            // Sanitize input
            $data = $this->sanitizeUserData($data);
            
            $sql = "UPDATE {$this->table} SET 
                username = ?, email = ?, full_name = ?, phone = ?, 
                role = ?, status = ?, updated_at = NOW()";
            $params = [
                $data['username'],
                $data['email'],
                $data['full_name'],
                $data['phone'] ?? null,
                $data['role'] ?? 'user',
                $data['status'] ?? 'active'
            ];
            
            // Update password if provided
            if (!empty($data['password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        try {
            // Prevent deletion of admin users
            $sql = "SELECT role FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['role'] === 'admin') {
                throw new Exception("Cannot delete admin users");
            }
            
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            error_log("User delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        try {
            $sql = "SELECT id, username, email, password, full_name, phone, role, status 
                    FROM {$this->table} WHERE (username = ? OR email = ?) AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $this->updateLastLogin($user['id']);
                
                // Remove password from returned data
                unset($user['password']);
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("User authenticate error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user password
     */
    public function updatePassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hashedPassword, $id]);
        } catch (Exception $e) {
            error_log("User updatePassword error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle user status
     */
    public function toggleStatus($id) {
        try {
            $sql = "UPDATE {$this->table} SET status = CASE 
                    WHEN status = 'active' THEN 'inactive' 
                    ELSE 'active' 
                    END, updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("User toggleStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user statistics
     */
    public function getStats() {
        $stats = [];
        
        try {
            // Total users
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetchColumn();
            
            // Users by status
            $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Users by role
            $sql = "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Active users (last 30 days)
            $sql = "SELECT COUNT(*) as active FROM {$this->table} WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['active'] = $stmt->fetchColumn();
            
            // New users (last 7 days)
            $sql = "SELECT COUNT(*) as new_users FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['new_users'] = $stmt->fetchColumn();
            
        } catch (Exception $e) {
            error_log("User stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get recent users
     */
    public function getRecent($limit = 5) {
        try {
            $sql = "SELECT id, username, email, full_name, role, status, created_at 
                    FROM {$this->table} 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User getRecent error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active users
     */
    public function getActive($limit = 10) {
        try {
            $sql = "SELECT id, username, email, full_name, role, last_login 
                    FROM {$this->table} 
                    WHERE status = 'active' 
                    ORDER BY last_login DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User getActive error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search users
     */
    public function search($query, $limit = 10) {
        try {
            $sql = "SELECT id, username, email, full_name, phone, role, status 
                    FROM {$this->table} 
                    WHERE (username LIKE ? OR email LIKE ? OR full_name LIKE ?)
                    ORDER BY username ASC 
                    LIMIT ?";
            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user exists
     */
    private function userExists($username, $email) {
        try {
            $sql = "SELECT id FROM {$this->table} WHERE username = ? OR email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username, $email]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("User userExists error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($id) {
        try {
            $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("User updateLastLogin error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sanitize user data
     */
    private function sanitizeUserData($data) {
        $sanitized = [];
        
        $sanitized['username'] = $this->security->sanitizeInput($data['username']);
        $sanitized['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $sanitized['full_name'] = $this->security->sanitizeInput($data['full_name']);
        $sanitized['phone'] = $this->security->sanitizeInput($data['phone'] ?? '');
        $sanitized['role'] = $this->security->sanitizeInput($data['role'] ?? 'user');
        $sanitized['status'] = $this->security->sanitizeInput($data['status'] ?? 'active');
        
        return $sanitized;
    }
    
    /**
     * Validate user data
     */
    public function validate($data, $isUpdate = false) {
        $errors = [];
        
        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Full name validation
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Full name is required';
        } elseif (strlen($data['full_name']) < 2) {
            $errors['full_name'] = 'Full name must be at least 2 characters';
        }
        
        // Password validation (only for create, not update)
        if (!$isUpdate) {
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
            }
        } elseif (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        // Phone validation (optional)
        if (!empty($data['phone']) && !preg_match('/^[\d\s\-\+\(\)]+$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number format';
        }
        
        // Role validation
        if (!empty($data['role']) && !in_array($data['role'], ['user', 'agent', 'admin'])) {
            $errors['role'] = 'Invalid role';
        }
        
        // Status validation
        if (!empty($data['status']) && !in_array($data['status'], ['active', 'inactive', 'suspended'])) {
            $errors['status'] = 'Invalid status';
        }
        
        return $errors;
    }
    
    /**
     * Get user roles
     */
    public function getRoles() {
        return [
            'user' => 'User',
            'agent' => 'Agent',
            'admin' => 'Admin'
        ];
    }
    
    /**
     * Get user statuses
     */
    public function getStatuses() {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended'
        ];
    }
}

<?php
/**
 * User Management System
 * 
 * Comprehensive CRUD operations for managing users,
 * including authentication, roles, permissions, and user profiles.
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Security.php';

class UserManagement {
    private $db;
    private $security;
    
    public function __construct() {
        $this->db = new Database();
        $this->security = new Security();
    }
    
    /**
     * Create a new user
     */
    public function createUser($userData) {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'full_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Validate email format
            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            // Check if username or email already exists
            if ($this->userExists($userData['username'], $userData['email'])) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Sanitize input
            $userData = $this->sanitizeUserData($userData);
            
            $sql = "INSERT INTO users (
                username, email, password, full_name, phone, 
                role, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['full_name'],
                $userData['phone'] ?? null,
                $userData['role'] ?? 'user',
                $userData['status'] ?? 'active'
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                return ['success' => true, 'message' => 'User created successfully', 'user_id' => $userId];
            }
            
            return ['success' => false, 'message' => 'Failed to create user'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get all users with pagination and filtering
     */
    public function getUsers($page = 1, $limit = 10, $filters = []) {
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
            $countSql = "SELECT COUNT(*) FROM users $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Get users
            $sql = "SELECT id, username, email, full_name, phone, role, status, 
                    created_at, updated_at, last_login 
                    FROM users $whereClause 
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
            return ['users' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get a specific user
     */
    public function getUser($id) {
        try {
            $sql = "SELECT id, username, email, full_name, phone, role, status, 
                    created_at, updated_at, last_login 
                    FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update a user
     */
    public function updateUser($id, $userData) {
        try {
            // Validate required fields
            if (empty($userData['username']) || empty($userData['email']) || empty($userData['full_name'])) {
                throw new Exception("Username, email, and full name are required");
            }
            
            // Check if username or email already exists (excluding current user)
            $sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userData['username'], $userData['email'], $id]);
            
            if ($stmt->fetch()) {
                throw new Exception("Username or email already exists");
            }
            
            // Sanitize input
            $userData = $this->sanitizeUserData($userData);
            
            $sql = "UPDATE users SET 
                username = ?, email = ?, full_name = ?, phone = ?, 
                role = ?, status = ?, updated_at = NOW()";
            $params = [
                $userData['username'],
                $userData['email'],
                $userData['full_name'],
                $userData['phone'] ?? null,
                $userData['role'] ?? 'user',
                $userData['status'] ?? 'active'
            ];
            
            // Update password if provided
            if (!empty($userData['password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                return ['success' => true, 'message' => 'User updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update user'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete a user
     */
    public function deleteUser($id) {
        try {
            // Prevent deletion of admin users
            $sql = "SELECT role FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['role'] === 'admin') {
                throw new Exception("Cannot delete admin users");
            }
            
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'User deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to delete user'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Toggle user status
     */
    public function toggleUserStatus($id) {
        try {
            $sql = "UPDATE users SET status = CASE 
                    WHEN status = 'active' THEN 'inactive' 
                    ELSE 'active' 
                    END, updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'User status updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update user status'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats() {
        $stats = [];
        
        try {
            // Total users
            $sql = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetchColumn();
            
            // Users by status
            $sql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Users by role
            $sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Active users (last 30 days)
            $sql = "SELECT COUNT(*) as active FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['active'] = $stmt->fetchColumn();
            
            // New users (last 7 days)
            $sql = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['new_users'] = $stmt->fetchColumn();
            
        } catch (Exception $e) {
            error_log("User stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Check if user exists
     */
    private function userExists($username, $email) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetch() !== false;
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
}

// Handle HTTP requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userManager = new UserManagement();
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $response = $userManager->createUser($_POST);
            break;
            
        case 'update':
            $response = $userManager->updateUser($_POST['id'], $_POST);
            break;
            
        case 'delete':
            $response = $userManager->deleteUser($_POST['id']);
            break;
            
        case 'toggle_status':
            $response = $userManager->toggleUserStatus($_POST['id']);
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle GET requests for API
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $userManager = new UserManagement();
    
    switch ($_GET['action']) {
        case 'get_users':
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $filters = $_GET;
            unset($filters['action'], $filters['page'], $filters['limit']);
            $response = $userManager->getUsers($page, $limit, $filters);
            break;
            
        case 'get_user':
            $response = $userManager->getUser($_GET['id']);
            break;
            
        case 'get_stats':
            $response = $userManager->getUserStats();
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Initialize user manager
$userManager = new UserManagement();
$currentPage = $_GET['page'] ?? 1;
$users = $userManager->getUsers($currentPage, 10);
$stats = $userManager->getUserStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-card {
            transition: transform 0.2s;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .role-badge {
            font-size: 0.8rem;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #6f42c1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-users"></i> User Management
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="unified_key_management.php">
                    <i class="fas fa-key"></i> Key Management
                </a>
                <a class="nav-link" href="property_management.php">
                    <i class="fas fa-home"></i> Properties
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4><?php echo $stats['total'] ?? 0; ?></h4>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h4><?php echo $stats['by_status']['active'] ?? 0; ?></h4>
                        <p>Active Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h4><?php echo $stats['active'] ?? 0; ?></h4>
                        <p>Recent Active</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h4><?php echo $stats['new_users'] ?? 0; ?></h4>
                        <p>New Users</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form id="filterForm" class="row g-3">
                                    <div class="col-md-3">
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="role">
                                            <option value="">All Roles</option>
                                            <option value="admin">Admin</option>
                                            <option value="user">User</option>
                                            <option value="agent">Agent</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="search" placeholder="Search users...">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal">
                                    <i class="fas fa-plus"></i> Add User
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users['users'])): ?>
                                        <?php foreach ($users['users'] as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-2">
                                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                            <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '-'; ?></td>
                                                <td>
                                                    <span class="badge role-badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'agent' ? 'warning' : 'primary'); ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'inactive' ? 'secondary' : 'danger'); ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-primary btn-sm" onclick="editUser(<?php echo $user['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-<?php echo $user['status'] === 'active' ? 'warning' : 'success'; ?> btn-sm" 
                                                                onclick="toggleStatus(<?php echo $user['id']; ?>)">
                                                            <i class="fas fa-<?php echo $user['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                        </button>
                                                        <?php if ($user['role'] !== 'admin'): ?>
                                                            <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> No users found.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($users['total_pages'] > 1): ?>
                            <nav aria-label="User pagination">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $users['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="userId" name="id">
                        <input type="hidden" id="action" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">Leave blank to keep current password when editing</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="user">User</option>
                                        <option value="agent">Agent</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveUser">Save User</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let userModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            userModal = new bootstrap.Modal(document.getElementById('userModal'));
            
            // Handle filter form
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const params = new URLSearchParams(formData);
                window.location.href = '?' + params.toString();
            });
            
            // Handle save user
            document.getElementById('saveUser').addEventListener('click', function() {
                const form = document.getElementById('userForm');
                const formData = new FormData(form);
                
                fetch('user_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        userModal.hide();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            });
        });
        
        function editUser(id) {
            fetch(`user_management.php?action=get_user&id=${id}`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('userId').value = user.id;
                    document.getElementById('action').value = 'update';
                    document.getElementById('modalTitle').textContent = 'Edit User';
                    document.getElementById('username').value = user.username;
                    document.getElementById('email').value = user.email;
                    document.getElementById('full_name').value = user.full_name;
                    document.getElementById('phone').value = user.phone || '';
                    document.getElementById('role').value = user.role;
                    document.getElementById('status').value = user.status;
                    document.getElementById('password').value = '';
                    document.getElementById('password').placeholder = 'Leave blank to keep current password';
                    
                    userModal.show();
                });
        }
        
        function toggleStatus(id) {
            if (confirm('Are you sure you want to toggle this user\'s status?')) {
                const formData = new FormData();
                formData.append('action', 'toggle_status');
                formData.append('id', id);
                
                fetch('user_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                fetch('user_management.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        // Reset modal when hidden
        document.getElementById('userModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('action').value = 'create';
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('password').placeholder = '';
        });
    </script>
</body>
</html>

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * User Controller - Custom MVC Implementation
 * Handles user management operations in Admin panel
 */
class UserController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of users
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT u.*,
                           COUNT(p.id) as property_count,
                           (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as booking_count
                    FROM users u
                    LEFT JOIN properties p ON u.id = p.created_by
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($role)) {
                $sql .= " AND u.role = ?";
                $params[] = $role;
            }

            if (!empty($status)) {
                $sql .= " AND u.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY u.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT u.*, COUNT(p.id) as property_count, (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as booking_count", "SELECT COUNT(DISTINCT u.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'User Management - APS Dream Home',
                'active_page' => 'users',
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'role' => $role,
                    'status' => $status
                ]
            ];

            return $this->render('admin/users/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("User Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load users');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Create User - APS Dream Home',
                'active_page' => 'users',
                'roles' => ['admin', 'manager', 'associate', 'customer', 'user']
            ];

            return $this->render('admin/users/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("User Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load user form');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Store a newly created user
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['name', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->jsonError('Invalid email address', 400);
            }

            // Validate role
            $validRoles = ['admin', 'manager', 'associate', 'customer', 'user'];
            if (!in_array($data['role'], $validRoles)) {
                return $this->jsonError('Invalid role', 400);
            }

            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                return $this->jsonError('Email already exists', 400);
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users 
                    (name, email, password, phone, address, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['name'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['email'], 'string'),
                $hashedPassword,
                CoreFunctionsServiceCustom::validateInput($data['phone'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['address'] ?? '', 'string'),
                $data['role'],
                $data['status'] ?? 'active'
            ]);

            if ($result) {
                $userId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'user_created', [
                    'user_id' => $userId,
                    'email' => $data['email'],
                    'role' => $data['role']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User created successfully',
                    'user_id' => $userId
                ]);
            }

            return $this->jsonError('Failed to create user', 500);
        } catch (Exception $e) {
            $this->loggingService->error("User Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create user', 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        try {
            $userId = intval($id);
            if ($userId <= 0) {
                $this->setFlash('error', 'Invalid user ID');
                return $this->redirect('admin/users');
            }

            // Get user details
            $sql = "SELECT u.*,
                           COUNT(p.id) as property_count,
                           (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as booking_count
                    FROM users u
                    LEFT JOIN properties p ON u.id = p.created_by
                    WHERE u.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $this->setFlash('error', 'User not found');
                return $this->redirect('admin/users');
            }

            $data = [
                'page_title' => 'User Details - APS Dream Home',
                'active_page' => 'users',
                'user' => $user
            ];

            return $this->render('admin/users/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("User Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load user details');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        try {
            $userId = intval($id);
            if ($userId <= 0) {
                $this->setFlash('error', 'Invalid user ID');
                return $this->redirect('admin/users');
            }

            // Get user details
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $this->setFlash('error', 'User not found');
                return $this->redirect('admin/users');
            }

            $data = [
                'page_title' => 'Edit User - APS Dream Home',
                'active_page' => 'users',
                'user' => $user,
                'roles' => ['admin', 'manager', 'associate', 'customer', 'user']
            ];

            return $this->render('admin/users/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("User Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load user form');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Update the specified user
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $userId = intval($id);
            if ($userId <= 0) {
                return $this->jsonError('Invalid user ID', 400);
            }

            $data = $_POST;

            // Check if user exists
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['name'])) {
                $updateFields[] = "name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['name'], 'string');
            }

            if (isset($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    return $this->jsonError('Invalid email address', 400);
                }

                // Check if email already exists (excluding current user)
                $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$data['email'], $userId]);
                if ($stmt->fetch()) {
                    return $this->jsonError('Email already exists', 400);
                }

                $updateFields[] = "email = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['email'], 'string');
            }

            if (isset($data['phone'])) {
                $updateFields[] = "phone = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['phone'], 'string');
            }

            if (isset($data['address'])) {
                $updateFields[] = "address = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['address'], 'string');
            }

            if (isset($data['role'])) {
                $validRoles = ['admin', 'manager', 'associate', 'customer', 'user'];
                if (in_array($data['role'], $validRoles)) {
                    $updateFields[] = "role = ?";
                    $updateValues[] = $data['role'];
                }
            }

            if (isset($data['status'])) {
                $validStatuses = ['active', 'inactive', 'suspended'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $updateFields[] = "password = ?";
                $updateValues[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $userId;

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'user_updated', [
                    'user_id' => $userId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update user', 500);
        } catch (Exception $e) {
            $this->loggingService->error("User Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update user', 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $userId = intval($id);
            if ($userId <= 0) {
                return $this->jsonError('Invalid user ID', 400);
            }

            // Check if user exists
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            // Prevent deletion of admin users
            if ($user['role'] === 'admin') {
                return $this->jsonError('Cannot delete admin users', 400);
            }

            // Delete user
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'user_deleted', [
                    'user_id' => $userId,
                    'user_name' => $user['name'],
                    'user_email' => $user['email']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete user', 500);
        } catch (Exception $e) {
            $this->loggingService->error("User Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete user', 500);
        }
    }

    /**
     * Get user statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total users
            $sql = "SELECT COUNT(*) as total FROM users";
            $result = $this->db->fetchOne($sql);
            $stats['total_users'] = (int)($result['total'] ?? 0);

            // Users by role
            $sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
            $result = $this->db->fetchAll($sql);
            $stats['by_role'] = $result ?: [];

            // Users by status
            $sql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
            $result = $this->db->fetchAll($sql);
            $stats['by_status'] = $result ?: [];

            // New users this month
            $sql = "SELECT COUNT(*) as new_this_month FROM users 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['new_this_month'] = (int)($result['new_this_month'] ?? 0);

            // Active users (logged in within last 7 days)
            $sql = "SELECT COUNT(*) as active_users FROM users 
                    WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['active_users'] = (int)($result['active_users'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get User Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * JSON response helper
     */
    public function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    protected function jsonError($message, $status = 400)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

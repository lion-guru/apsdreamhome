<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Services\UserRegistrationService;
use App\Core\Database;
use Exception;

/**
 * User Controller - Custom MVC Implementation
 * Handles user management operations in Admin panel
 */
class UserController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

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
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $sortBy = $_GET['sort_by'] ?? 'created_at';
            $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC');
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            // Validate sort parameters
            $allowedSort = ['name', 'email', 'role', 'status', 'registration_status', 'created_at', 'last_login_at'];
            if (!in_array($sortBy, $allowedSort)) $sortBy = 'created_at';
            if (!in_array($sortOrder, ['ASC', 'DESC'])) $sortOrder = 'DESC';

            $offset = ($page - 1) * $perPage;

            // Build query
            $select = "SELECT u.*, COUNT(p.id) as property_count, (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as booking_count";
            $from = "FROM users u LEFT JOIN properties p ON u.id = p.created_by WHERE 1=1";

            $sql = $select . "\n" . $from;
            $params = [];

            list($tSql, $tParams) = $this->tenantWhere();
            $sql .= $tSql;
            $params = array_merge($params, $tParams);

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

            if (!empty($dateFrom)) {
                $sql .= " AND u.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if (!empty($dateTo)) {
                $sql .= " AND u.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $sql .= " GROUP BY u.id ORDER BY u.$sortBy $sortOrder";

            // Count total - use separate clean count query
            $countSql = "SELECT COUNT(DISTINCT u.id) as total " . $from;
            $countParams = [];
            if (!empty($tSql)) {
                $countSql .= $tSql;
                $countParams = $tParams;
            }
            if (!empty($search)) {
                $countSql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchParam = '%' . $search . '%';
                $countParams[] = $searchParam;
                $countParams[] = $searchParam;
                $countParams[] = $searchParam;
            }
            if (!empty($role)) {
                $countSql .= " AND u.role = ?";
                $countParams[] = $role;
            }
            if (!empty($status)) {
                $countSql .= " AND u.status = ?";
                $countParams[] = $status;
            }
            if (!empty($dateFrom)) {
                $countSql .= " AND u.created_at >= ?";
                $countParams[] = $dateFrom . ' 00:00:00';
            }
            if (!empty($dateTo)) {
                $countSql .= " AND u.created_at <= ?";
                $countParams[] = $dateTo . ' 23:59:59';
            }
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get stats for cards
            $stats = $this->getUserStats($tParams);

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
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ],
                'stats' => $stats,
                'per_page_options' => [10, 20, 50, 100]
            ];

            return $this->render('admin/users/index', $data);
        } catch (\Exception $e) {
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
                'roles' => ['admin', 'manager', 'associate', 'agent', 'customer', 'user']
            ];

            return $this->render('admin/users/create', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("User Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load user form');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Store a newly created user — uses UserRegistrationService for complete record creation
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
            $validRoles = ['admin', 'manager', 'associate', 'agent', 'customer', 'user', 'employee', 'telecaller'];
            if (!in_array($data['role'], $validRoles)) {
                return $this->jsonError('Invalid role', 400);
            }

            // Use UserRegistrationService for complete record creation (wallet, MLM, tree, etc.)
            $regService = new UserRegistrationService();
            $user = null;
            $result = $regService->createUser($data['role'], [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? '',
                'password' => $data['password'],
                'city' => $data['city'] ?? '',
                'occupation' => $data['occupation'] ?? '',
                'registration_method' => 'admin',
            ], $user);

            if (!$result['success']) {
                return $this->jsonError($result['message'], 400);
            }

            $userId = $result['user_id'];

            // If employee role, also create employees table row
            if ($data['role'] === 'employee' || $data['role'] === 'telecaller') {
                $this->db->execute(
                    "INSERT INTO employees (user_id, name, email, phone, role, department, designation, salary, joining_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())",
                    [
                        $userId,
                        $data['name'],
                        $data['email'],
                        $data['phone'] ?? '',
                        $data['role'],
                        $data['department'] ?? 'General',
                        $data['designation'] ?? $data['role'],
                        $data['salary'] ?? 0,
                        $data['join_date'] ?? date('Y-m-d'),
                    ]
                );
            }

            // Log activity
            $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'user_created', [
                'user_id' => $userId,
                'email' => $data['email'],
                'role' => $data['role']
            ]);

            $this->setFlash('success', ucfirst($data['role']) . ' created successfully. Customer ID: ' . ($user['customer_id'] ?? $userId));
            return $this->redirect('admin/users');

        } catch (\Exception $e) {
            $this->loggingService->error("User Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create user: ' . $e->getMessage(), 500);
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

            // Get user details with sponsor/referred_by names
            $sql = "SELECT u.*,
                           COUNT(p.id) as property_count,
                           (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as booking_count,
                           s.name as sponsor_name,
                           r.name as referred_by_name
                    FROM users u
                    LEFT JOIN properties p ON u.id = p.created_by
                    LEFT JOIN users s ON u.sponsor_id = s.id
                    LEFT JOIN users r ON u.referred_by = r.id
                    WHERE u.id = ?";
            $params = [$userId];
            list($tSql, $tParams) = $this->tenantWhere();
            $sql .= $tSql;
            $params = array_merge($params, $tParams);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $this->setFlash('error', 'User not found');
                return $this->redirect('admin/users');
            }

            // Get wallet balance from wallet_points (not legacy users.wallet_balance)
            $wallet = $this->db->fetchOne("SELECT COALESCE(SUM(points_balance), 0) as balance FROM wallet_points WHERE user_id = ?", [$userId]);
            $user['wallet_balance'] = $wallet['balance'] ?? 0;

            // Get commission totals
            $commissionTotals = $this->db->fetchOne(
                "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ?",
                [$userId]
            );
            $user['commission_count'] = $commissionTotals['count'] ?? 0;
            $user['commission_total'] = $commissionTotals['total'] ?? 0;

            // Get direct referrals count
            $directCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM mlm_network_tree WHERE parent_id = ?",
                [$userId]
            );
            $user['direct_referrals'] = $directCount['count'] ?? 0;

            $data = [
                'page_title' => 'User Details - APS Dream Home',
                'active_page' => 'users',
                'user' => $user
            ];

            return $this->render('admin/users/show', $data);
        } catch (\Exception $e) {
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
            list($tSql, $tParams) = $this->tenantWhere();
            $sql .= $tSql;
            $params = array_merge([$userId], $tParams);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $this->setFlash('error', 'User not found');
                return $this->redirect('admin/users');
            }

            $data = [
                'page_title' => 'Edit User - APS Dream Home',
                'active_page' => 'users',
                'user' => $user,
                'roles' => ['admin', 'super_admin', 'manager', 'employee', 'telecaller', 'associate', 'agent', 'customer', 'user']
            ];

            return $this->render('admin/users/edit', $data);
        } catch (\Exception $e) {
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
            list($tSql, $tParams) = $this->tenantWhere();
            $sql .= $tSql;
            $params = array_merge([$userId], $tParams);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
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
                list($tSql, $tParams) = $this->tenantWhere();
                $sql .= $tSql;
                $params = array_merge([$data['email'], $userId], $tParams);
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
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
                $validRoles = ['admin', 'super_admin', 'manager', 'employee', 'telecaller', 'associate', 'agent', 'customer', 'user'];
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
            if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $updateValues[] = $tid; }
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
        } catch (\Exception $e) {
            $this->loggingService->error("User Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update user', 500);
        }
    }

    /**
     * Inline update single field (for inline editing)
     */
    public function inlineUpdate($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $field = $_POST['field'] ?? '';
            $value = $_POST['value'] ?? '';
            
            $allowedFields = ['name', 'phone', 'status', 'role'];
            if (!in_array($field, $allowedFields)) {
                return $this->jsonError('Field not allowed for inline edit', 400);
            }

            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);

            // Validate based on field
            if ($field === 'status') {
                if (!in_array($value, ['active', 'inactive', 'suspended'])) {
                    return $this->jsonError('Invalid status', 400);
                }
            }
            if ($field === 'role') {
                $validRoles = ['admin', 'super_admin', 'manager', 'employee', 'telecaller', 'associate', 'agent', 'customer', 'user'];
                if (!in_array($value, $validRoles)) {
                    return $this->jsonError('Invalid role', 400);
                }
            }
            if ($field === 'phone') {
                $value = trim($value);
            }
            if ($field === 'name') {
                $value = trim($value);
                if (empty($value)) return $this->jsonError('Name cannot be empty', 400);
            }

            $this->db->execute("UPDATE users SET $field = ?, updated_at = NOW() WHERE id = ?" . $tSql, array_merge([$value, $userId], $tParams));

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $this->loggingService->logUserActivity($adminId, 'user_inline_update', [
                'user_id' => $userId,
                'field' => $field,
                'old_value' => $user[$field],
                'new_value' => $value
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => ucfirst($field) . ' updated',
                'value' => $value
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
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
            list($tSql, $tParams) = $this->tenantWhere();
            $sql .= $tSql;
            $params = array_merge([$userId], $tParams);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            // Prevent deletion of admin users
            if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
                return $this->jsonError('Cannot delete admin users', 400);
            }

            // Soft delete instead of hard delete (preserves data integrity)
            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $sql = "UPDATE users SET status = 'inactive', deleted_at = NOW(), updated_at = NOW() WHERE id = ?";
            $delParams = [$userId];
            if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $delParams[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($delParams);

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
        } catch (\Exception $e) {
            $this->loggingService->error("User Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete user', 500);
        }
    }

    /**
     * Display pending registrations awaiting approval
     */
    public function pending()
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);
            $offset = ($page - 1) * $perPage;

            $select = "SELECT u.*, COUNT(p.id) as property_count, (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as booking_count";
            $from = "FROM users u LEFT JOIN properties p ON u.id = p.created_by WHERE u.registration_status = 'pending'";

            $sql = $select . "\n" . $from;
            list($tSql, $tParams) = $this->tenantWhere();
            $sql .= $tSql;
            $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

            $countSql = "SELECT COUNT(*) as total " . $from;
            $countResult = $this->db->fetchOne($countSql, $tParams);
            $total = (int)($countResult['total'] ?? 0);

            $sql .= " LIMIT ?, ?";
            $params = array_merge($tParams, [$offset, $perPage]);

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Pending Registrations - APS Dream Home',
                'active_page' => 'users',
                'users' => $users ?? [],
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];

            return $this->render('admin/users/pending', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Pending Users error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load pending registrations');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Approve a pending user registration
     */
    public function approve($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $userId = intval($id);
            if ($userId <= 0) {
                return $this->jsonError('Invalid user ID', 400);
            }

            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            if ($user['registration_status'] !== 'pending') {
                return $this->jsonError('User is not pending approval', 400);
            }

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $sql = "UPDATE users SET registration_status = 'approved', status = 'active', approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?";
            $upParams = [$adminId, $userId];
            if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
            $this->db->query($sql, $upParams);

            $this->loggingService->logUserActivity($adminId, 'user_approved', [
                'user_id' => $userId,
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'user_role' => $user['role']
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'User approved successfully'
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Approve User error: " . $e->getMessage());
            return $this->jsonError('Failed to approve user', 500);
        }
    }

    /**
     * Reject a pending user registration
     */
    public function reject($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $userId = intval($id);
            if ($userId <= 0) {
                return $this->jsonError('Invalid user ID', 400);
            }

            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            if ($user['registration_status'] !== 'pending') {
                return $this->jsonError('User is not pending approval', 400);
            }

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $reason = trim($_POST['reason'] ?? '');

            $sql = "UPDATE users SET registration_status = 'rejected', status = 'inactive', rejection_reason = ?, approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?";
            $upParams = [$reason, $adminId, $userId];
            if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
            $this->db->query($sql, $upParams);

            $this->loggingService->logUserActivity($adminId, 'user_rejected', [
                'user_id' => $userId,
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'reason' => $reason
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'User rejected'
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Reject User error: " . $e->getMessage());
            return $this->jsonError('Failed to reject user', 500);
        }
    }

    /**
     * Bulk approve pending users
     */
    public function bulkApprove()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $userIds = $_POST['user_ids'] ?? [];
            if (empty($userIds)) {
                return $this->jsonError('No users selected', 400);
            }

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));

            $sql = "UPDATE users SET registration_status = 'approved', status = 'active', approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id IN ($placeholders) AND registration_status = 'pending'";
            $upParams = array_merge([$adminId], $userIds);
            if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
            $this->db->query($sql, $upParams);

            $this->loggingService->logUserActivity($adminId, 'bulk_user_approved', [
                'user_ids' => $userIds,
                'count' => count($userIds)
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => count($userIds) . ' users approved'
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Bulk Approve error: " . $e->getMessage());
            return $this->jsonError('Failed to bulk approve', 500);
        }
    }

    /**
     * Get user statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            list($tSql, $tParams) = $this->tenantWhere();

            // Total users
            $sql = "SELECT COUNT(*) as total FROM users WHERE 1=1" . $tSql;
            $result = $this->db->fetchOne($sql, $tParams);
            $stats['total_users'] = (int)($result['total'] ?? 0);

            // Users by role
            $sql = "SELECT role, COUNT(*) as count FROM users WHERE 1=1" . $tSql . " GROUP BY role";
            $result = $this->db->fetchAll($sql, $tParams);
            $stats['by_role'] = $result ?: [];

            // Users by status
            $sql = "SELECT status, COUNT(*) as count FROM users WHERE 1=1" . $tSql . " GROUP BY status";
            $result = $this->db->fetchAll($sql, $tParams);
            $stats['by_status'] = $result ?: [];

            // New users this month
            $sql = "SELECT COUNT(*) as new_this_month FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $tSql;
            $result = $this->db->fetchOne($sql, $tParams);
            $stats['new_this_month'] = (int)($result['new_this_month'] ?? 0);

            // Active users (logged in within last 7 days)
            $sql = "SELECT COUNT(*) as active_users FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)" . $tSql;
            $result = $this->db->fetchOne($sql, $tParams);
            $stats['active_users'] = (int)($result['active_users'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Get User Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * Get user stats for index page cards
     */
    protected function getUserStats(array $tParams = []): array
    {
        try {
            list($tSql) = $this->tenantWhere();

            // Total users
            $total = $this->db->fetchOne("SELECT COUNT(*) as c FROM users WHERE 1=1" . $tSql, $tParams);
            $totalUsers = (int)($total['c'] ?? 0);

            // Active users
            $active = $this->db->fetchOne("SELECT COUNT(*) as c FROM users WHERE status = 'active'" . $tSql, $tParams);
            $activeUsers = (int)($active['c'] ?? 0);

            // New this month
            $newMonth = $this->db->fetchOne("SELECT COUNT(*) as c FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $tSql, $tParams);
            $newThisMonth = (int)($newMonth['c'] ?? 0);

            // Pending approval
            $pending = $this->db->fetchOne("SELECT COUNT(*) as c FROM users WHERE registration_status = 'pending'" . $tSql, $tParams);
            $pendingCount = (int)($pending['c'] ?? 0);

            // By role (for chart)
            $byRole = $this->db->fetchAll("SELECT role, COUNT(*) as count FROM users WHERE 1=1" . $tSql . " GROUP BY role", $tParams);

            // By status
            $byStatus = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM users WHERE 1=1" . $tSql . " GROUP BY status", $tParams);

            return [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'new_this_month' => $newThisMonth,
                'pending' => $pendingCount,
                'by_role' => $byRole ?: [],
                'by_status' => $byStatus ?: []
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0, 'active' => 0, 'new_this_month' => 0, 'pending' => 0,
                'by_role' => [], 'by_status' => []
            ];
        }
    }

    // ============================================================
    // ENHANCED USER MANAGEMENT — Wallet, Commissions, Team, Sponsor
    // ============================================================

    /**
     * View user wallet — balance, transactions, commissions
     */
    public function viewWallet($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, phone, role, customer_id FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }

            // Wallet balance from wallet_points
            $wallet = $this->db->fetchOne("SELECT COALESCE(SUM(points_balance), 0) as balance, COALESCE(SUM(total_credited), 0) as total_credited FROM wallet_points WHERE user_id = ?", [$userId]);

            // Recent wallet transactions
            $transactions = $this->db->fetchAll(
                "SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
                [$userId]
            );

            // Commission ledger
            $commissions = $this->db->fetchAll(
                "SELECT * FROM mlm_commission_ledger WHERE beneficiary_user_id = ? ORDER BY created_at DESC LIMIT 50",
                [$userId]
            );

            $data = [
                'page_title' => "Wallet: {$user['name']} - APS Dream Home",
                'active_page' => 'users',
                'user' => $user,
                'wallet' => $wallet ?? ['balance' => 0, 'total_credited' => 0],
                'transactions' => $transactions ?? [],
                'commissions' => $commissions ?? [],
            ];

            return $this->render('admin/users/wallet', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("View Wallet error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load wallet');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Credit user wallet (AJAX)
     */
    public function creditWallet($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $amount = (float)($_POST['amount'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            if ($amount <= 0) return $this->jsonError('Amount must be positive', 400);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $tid = (int)$this->tenantId();

            // Ensure wallet exists
            $this->db->execute(
                "INSERT IGNORE INTO wallet_points (user_id, tenant_id) VALUES (?, ?)",
                [$userId, $tid]
            );

            $wallet = $this->db->fetchOne("SELECT points_balance FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);
            $balanceBefore = $wallet ? (float)$wallet['points_balance'] : 0.0;

            $this->db->execute(
                "UPDATE wallet_points SET points_balance = points_balance + ?, total_earned = total_earned + ? WHERE user_id = ?",
                [$amount, $amount, $userId]
            );

            $this->db->execute(
                "INSERT INTO wallet_transactions (tenant_id, user_id, transaction_type, transaction_category, amount, balance_before, balance_after, description, reference_id, reference_type)
                 VALUES (?, ?, 'credit', 'adjustment', ?, ?, ?, ?, ?, 'admin_credit')",
                [$tid, $userId, $amount, $balanceBefore, $balanceBefore + $amount, "Admin credit: {$reason}", $adminId]
            );

            $this->loggingService->logUserActivity($adminId, 'wallet_credit', ['user_id' => $userId, 'amount' => $amount, 'reason' => $reason]);
            return $this->jsonResponse(['success' => true, 'message' => "₹" . number_format($amount) . " credited"]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Debit user wallet (AJAX)
     */
    public function debitWallet($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $amount = (float)($_POST['amount'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            if ($amount <= 0) return $this->jsonError('Amount must be positive', 400);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $tid = (int)$this->tenantId();

            $wallet = $this->db->fetchOne("SELECT points_balance FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);
            if (!$wallet || (float)$wallet['points_balance'] < $amount) {
                return $this->jsonError('Insufficient balance', 400);
            }
            $balanceBefore = (float)$wallet['points_balance'];

            $this->db->execute(
                "UPDATE wallet_points SET points_balance = points_balance - ?, total_used = total_used + ? WHERE user_id = ?",
                [$amount, $amount, $userId]
            );

            $this->db->execute(
                "INSERT INTO wallet_transactions (tenant_id, user_id, transaction_type, transaction_category, amount, balance_before, balance_after, description, reference_id, reference_type)
                 VALUES (?, ?, 'debit', 'withdrawal', ?, ?, ?, ?, ?, 'admin_debit')",
                [$tid, $userId, $amount, $balanceBefore, $balanceBefore - $amount, "Admin debit: {$reason}", $adminId]
            );

            $this->loggingService->logUserActivity($adminId, 'wallet_debit', ['user_id' => $userId, 'amount' => $amount, 'reason' => $reason]);
            return $this->jsonResponse(['success' => true, 'message' => "₹" . number_format($amount) . " debited"]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Change user's sponsor/referrer — updates ALL related tables
     */
    public function changeSponsor($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $newSponsorId = (int)($_POST['new_sponsor_id'] ?? 0);
            if ($newSponsorId <= 0) return $this->jsonError('Invalid sponsor', 400);
            if ($newSponsorId === $userId) return $this->jsonError('Cannot be own sponsor', 400);

            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, role FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);

            $newSponsor = $this->db->fetchOne("SELECT id, name FROM users WHERE id = ?" . $tSql, array_merge([$newSponsorId], $tParams));
            if (!$newSponsor) return $this->jsonError('Sponsor not found', 404);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $this->db->beginTransaction();
            try {
                // 1. Update users table
                $sql = "UPDATE users SET referred_by = ?, sponsor_id = ?, updated_at = NOW() WHERE id = ?";
                $upParams = [$newSponsorId, $newSponsorId, $userId];
                if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
                $this->db->execute($sql, $upParams);

                // 2. Update mlm_profiles
                $tid = (int)$this->tenantId();
                $this->db->execute("UPDATE mlm_profiles SET sponsor_user_id = ?, updated_at = NOW() WHERE user_id = ? AND tenant_id = ?", [$newSponsorId, $userId, $tid]);

                // 3. Update associates
                $this->db->execute("UPDATE associates SET sponsor_id = ?, updated_at = NOW() WHERE user_id = ? AND tenant_id = ?", [$newSponsorId, $userId, $tid]);

                // 4. Update mlm_network_tree
                $this->db->execute("UPDATE mlm_network_tree SET sponsor_id = ?, parent_id = ? WHERE associate_id = ? AND tenant_id = ?", [$newSponsorId, $newSponsorId, $userId, $tid]);

                // 5. Update network_tree parent
                $this->db->execute("UPDATE network_tree SET parent_id = ? WHERE associate_id = ? AND tenant_id = ?", [$newSponsorId, $userId, $tid]);

                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

            $this->loggingService->logUserActivity($adminId, 'sponsor_changed', ['user_id' => $userId, 'new_sponsor_id' => $newSponsorId]);
            return $this->jsonResponse(['success' => true, 'message' => "Sponsor changed to {$newSponsor['name']}"]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Change user's referral code
     */
    public function changeReferralCode($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $newCode = strtoupper(trim($_POST['new_referral_code'] ?? ''));
            if (empty($newCode) || strlen($newCode) < 3) return $this->jsonError('Referral code too short (min 3 chars)', 400);

            // Check uniqueness
            list($tSql, $tParams) = $this->tenantWhere();
            $exists = $this->db->fetchOne("SELECT id FROM users WHERE referral_code = ? AND id != ?" . $tSql, array_merge([$newCode, $userId], $tParams));
            if ($exists) return $this->jsonError('Referral code already in use', 400);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $sql = "UPDATE users SET referral_code = ?, updated_at = NOW() WHERE id = ?";
            $upParams = [$newCode, $userId];
            if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
            $this->db->execute($sql, $upParams);
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE mlm_profiles SET referral_code = ?, updated_at = NOW() WHERE user_id = ? AND tenant_id = ?", [$newCode, $userId, $tid]);

            $this->loggingService->logUserActivity($adminId, 'referral_code_changed', ['user_id' => $userId, 'new_code' => $newCode]);
            return $this->jsonResponse(['success' => true, 'message' => "Referral code changed to {$newCode}"]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * View user's team / MLM downline
     */
    public function viewTeam($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, phone, role, referral_code FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }

            // Direct referrals from mlm_network_tree
            $directReferrals = $this->db->fetchAll(
                "SELECT mnt.associate_id, u.name, u.email, u.phone, u.role, u.status, u.created_at, mnt.level
                 FROM mlm_network_tree mnt
                 JOIN users u ON u.id = mnt.associate_id
                 WHERE mnt.parent_id = ?
                 ORDER BY mnt.level ASC, u.name ASC",
                [$userId]
            );

            // Full team (up to 3 levels deep via recursive CTE or iterative)
            $team = [];
            $queue = [$userId];
            $visited = [$userId];
            for ($depth = 0; $depth < 3 && !empty($queue); $depth++) {
                $placeholders = implode(',', array_fill(0, count($queue), '?'));
                $members = $this->db->fetchAll(
                    "SELECT mnt.associate_id, mnt.level, mnt.sponsor_id, u.name, u.email, u.role, u.status, u.created_at
                     FROM mlm_network_tree mnt
                     JOIN users u ON u.id = mnt.associate_id
                     WHERE mnt.parent_id IN ($placeholders)
                     ORDER BY u.name ASC",
                    $queue
                );
                $nextQueue = [];
                foreach ($members as $m) {
                    if (!in_array($m['associate_id'], $visited)) {
                        $visited[] = $m['associate_id'];
                        $m['depth'] = $depth + 1;
                        $team[] = $m;
                        $nextQueue[] = $m['associate_id'];
                    }
                }
                $queue = $nextQueue;
            }

            // MLM profile
            $mlmProfile = $this->db->fetchOne("SELECT * FROM mlm_profiles WHERE user_id = ?", [$userId]);

            $data = [
                'page_title' => "Team: {$user['name']} - APS Dream Home",
                'active_page' => 'users',
                'user' => $user,
                'directReferrals' => $directReferrals ?? [],
                'team' => $team ?? [],
                'mlmProfile' => $mlmProfile,
            ];

            return $this->render('admin/users/team', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("View Team error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load team');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Soft delete user (sets status='deleted' + deleted_at timestamp)
     */
    public function softDelete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, role FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);
            if ($user['role'] === 'admin' || $user['role'] === 'super_admin') return $this->jsonError('Cannot delete admin users', 400);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $sql = "UPDATE users SET status = 'inactive', deleted_at = NOW(), updated_at = NOW() WHERE id = ?";
            $delParams = [$userId];
            if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $delParams[] = $tid; }
            $this->db->execute($sql, $delParams);

            $this->loggingService->logUserActivity($adminId, 'user_soft_deleted', ['user_id' => $userId, 'name' => $user['name'], 'email' => $user['email']]);
            return $this->jsonResponse(['success' => true, 'message' => 'User deactivated (soft deleted)']);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk operations: activate, deactivate, change role
     */
    public function bulkOperation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userIds = $_POST['user_ids'] ?? [];
            $action = $_POST['bulk_action'] ?? '';
            if (empty($userIds)) return $this->jsonError('No users selected', 400);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));

            switch ($action) {
                case 'activate':
                    $sql = "UPDATE users SET status = 'active', updated_at = NOW() WHERE id IN ($placeholders)";
                    $upParams = $userIds;
                    if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
                    $this->db->execute($sql, $upParams);
                    $msg = count($userIds) . ' users activated';
                    break;
                case 'deactivate':
                    $sql = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id IN ($placeholders)";
                    $upParams = $userIds;
                    if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
                    $this->db->execute($sql, $upParams);
                    $msg = count($userIds) . ' users deactivated';
                    break;
                case 'suspend':
                    $sql = "UPDATE users SET status = 'suspended', updated_at = NOW() WHERE id IN ($placeholders)";
                    $upParams = $userIds;
                    if ($tid = $this->tenantId()) { $sql .= " AND tenant_id = ?"; $upParams[] = $tid; }
                    $this->db->execute($sql, $upParams);
                    $msg = count($userIds) . ' users suspended';
                    break;
                default:
                    return $this->jsonError('Invalid action', 400);
            }

            $this->loggingService->logUserActivity($adminId, 'bulk_' . $action, ['user_ids' => $userIds, 'count' => count($userIds)]);
            return $this->jsonResponse(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * View admin activity log for a user — who did what, when
     */
    public function viewActivityLog($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, phone, role FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }

            $page = (int)($_GET['page'] ?? 1);
            $perPage = 30;
            $offset = ($page - 1) * $perPage;

            $logs = $this->db->fetchAll(
                "SELECT l.*, a.name as admin_name
                 FROM user_activity_logs_unified l
                 LEFT JOIN users a ON a.id = l.user_id
                 WHERE JSON_EXTRACT(l.context, '$.user_id') = ? OR l.user_id = ?
                 ORDER BY l.created_at DESC
                 LIMIT ? OFFSET ?",
                [$userId, $userId, $perPage, $offset]
            );

            $countResult = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM user_activity_logs_unified
                 WHERE JSON_EXTRACT(context, '$.user_id') = ? OR user_id = ?",
                [$userId, $userId]
            );
            $total = (int)($countResult['cnt'] ?? 0);
            $totalPages = ceil($total / $perPage);

            $data = [
                'page_title' => "Activity Log: {$user['name']} - APS Dream Home",
                'active_page' => 'users',
                'user' => $user,
                'logs' => $logs ?? [],
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ];

            return $this->render('admin/users/activity_log', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("View Activity Log error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load activity log');
            return $this->redirect('admin/users');
        }
    }

    /**
     * View all commission history for a user
     */
    public function viewCommissions($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, phone, role, customer_id FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }

            $commissions = $this->db->fetchAll(
                "SELECT ml.*, u.name as source_name
                 FROM mlm_commission_ledger ml
                 LEFT JOIN users u ON u.id = ml.source_user_id
                 WHERE ml.beneficiary_user_id = ?
                 ORDER BY ml.created_at DESC",
                [$userId]
            );

            $totals = $this->db->fetchOne(
                "SELECT commission_type, COUNT(*) as cnt, SUM(amount) as total
                 FROM mlm_commission_ledger
                 WHERE beneficiary_user_id = ?
                 GROUP BY commission_type
                 ORDER BY total DESC",
                [$userId]
            );

            $data = [
                'page_title' => "Commissions: {$user['name']} - APS Dream Home",
                'active_page' => 'users',
                'user' => $user,
                'commissions' => $commissions ?? [],
                'totals' => $totals ?? [],
            ];

            return $this->render('admin/users/commissions', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("View Commissions error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load commissions');
            return $this->redirect('admin/users');
        }
    }
    
    public function export()
    {
        $this->requireAdmin();
        try {
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';
            $format = strtolower($_GET['format'] ?? 'csv');
            
            // Validate format
            if (!in_array($format, ['csv', 'xlsx', 'excel'])) $format = 'csv';

            $sql = "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at,
                           (SELECT COUNT(*) FROM properties WHERE created_by = u.id) as property_count,
                           (SELECT COUNT(*) FROM plot_bookings WHERE customer_id = u.id) as booking_count
                    FROM users u
                    WHERE 1=1";
            $params = [];

            [$tSql, $tParams] = $this->tenantWhere();
            $sql .= $tSql;
            $params = array_merge($params, $tParams);

            if (!empty($search)) {
                $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
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

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $filename = 'users-' . date('Y-m-d');

            if ($format === 'xlsx' || $format === 'excel') {
                // Excel export using simple HTML table approach (works without external library)
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename . '.xls');
                
                echo '<table border="1">';
                echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Properties</th><th>Bookings</th><th>Registered</th></tr>';
                foreach ($users as $u) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($u['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['phone']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['role']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['status']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['property_count']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['booking_count']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['created_at']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                exit;
            }

            // Default CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename . '.csv');
            $output = fopen('php://output', 'w');
            // Add BOM for UTF-8 Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Properties', 'Bookings', 'Registered']);
            foreach ($users as $u) {
                fputcsv($output, [$u['id'], $u['name'], $u['email'], $u['phone'], $u['role'], $u['status'], $u['property_count'], $u['booking_count'], $u['created_at']]);
            }
            fclose($output);
            exit;
        } catch (\Exception $e) {
            error_log('UserController::export error: ' . $e->getMessage());
            $this->setFlash('error', 'Export failed');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Export selected users
     */
    public function exportSelected()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->requireAdmin();
            
            $format = strtolower($_POST['format'] ?? 'csv');
            $userIds = $_POST['user_ids'] ?? [];
            
            if (!in_array($format, ['csv', 'xlsx', 'excel'])) $format = 'csv';
            if (empty($userIds)) return $this->jsonError('No users selected', 400);

            list($tSql, $tParams) = $this->tenantWhere();
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            
            $sql = "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at,
                           (SELECT COUNT(*) FROM properties WHERE created_by = u.id) as property_count,
                           (SELECT COUNT(*) FROM plot_bookings WHERE customer_id = u.id) as booking_count
                    FROM users u
                    WHERE u.id IN ($placeholders)" . $tSql;
            
            $params = array_merge($userIds, $tParams);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $filename = 'users-selected-' . date('Y-m-d');

            if ($format === 'xlsx' || $format === 'excel') {
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename . '.xls');
                
                echo '<table border="1">';
                echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Properties</th><th>Bookings</th><th>Registered</th></tr>';
                foreach ($users as $u) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($u['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['phone']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['role']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['status']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['property_count']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['booking_count']) . '</td>';
                    echo '<td>' . htmlspecialchars($u['created_at']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                exit;
            }

            // Default CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename . '.csv');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Properties', 'Bookings', 'Registered']);
            foreach ($users as $u) {
                fputcsv($output, [$u['id'], $u['name'], $u['email'], $u['phone'], $u['role'], $u['status'], $u['property_count'], $u['booking_count'], $u['created_at']]);
            }
            fclose($output);
            exit;
        } catch (\Exception $e) {
            error_log('UserController::exportSelected error: ' . $e->getMessage());
            $this->setFlash('error', 'Export failed');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Impersonate user (Login as user)
     */
    public function impersonate($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }
            if ($user['role'] === 'admin' || $user['role'] === 'super_admin') { $this->setFlash('error', 'Cannot impersonate admin users'); return $this->redirect('admin/users'); }

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $adminRole = $_SESSION['role'] ?? 'admin';

            // Store admin session for return
            $_SESSION['impersonated_from'] = [
                'admin_id' => $adminId,
                'admin_role' => $adminRole,
                'admin_name' => $_SESSION['name'] ?? 'Admin',
                'impersonated_at' => date('Y-m-d H:i:s')
            ];

            // Set user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['phone'] = $user['phone'] ?? '';
            $_SESSION['customer_id'] = $user['customer_id'] ?? $user['id'];

            // Role-specific session vars
            if ($user['role'] === 'associate') {
                $assoc = $this->db->fetchOne("SELECT id FROM associates WHERE user_id = ?", [$userId]);
                if ($assoc) $_SESSION['associate_id'] = $assoc['id'];
            } elseif ($user['role'] === 'agent') {
                $_SESSION['agent_id'] = $userId;
            } elseif ($user['role'] === 'employee' || $user['role'] === 'telecaller') {
                $_SESSION['employee_id'] = $userId;
            } elseif ($user['role'] === 'customer') {
                $_SESSION['customer_id'] = $user['customer_id'] ?? $userId;
            }

            $this->loggingService->logUserActivity($adminId, 'user_impersonated', [
                'impersonated_user_id' => $userId,
                'impersonated_user_email' => $user['email'],
                'impersonated_user_role' => $user['role']
            ]);

            // Redirect to user's dashboard based on role
            $dashboards = [
                'customer' => 'user/dashboard',
                'associate' => 'associate/dashboard',
                'agent' => 'agent/dashboard',
                'employee' => 'employee/dashboard',
                'telecaller' => 'employee/dashboard',
                'user' => 'user/dashboard'
            ];
            $redirect = $dashboards[$user['role']] ?? 'user/dashboard';

            return $this->redirect($redirect);
        } catch (\Exception $e) {
            $this->loggingService->error("Impersonate error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to impersonate user');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Stop impersonation and return to admin
     */
    public function stopImpersonation()
    {
        if (empty($_SESSION['impersonated_from'])) {
            return $this->redirect('admin/users');
        }

        $admin = $_SESSION['impersonated_from'];
        $adminId = $admin['admin_id'];

        // Restore admin session
        $_SESSION['user_id'] = $adminId;
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['role'] = $admin['admin_role'];
        $_SESSION['name'] = $admin['admin_name'];

        unset($_SESSION['impersonated_from']);
        unset($_SESSION['associate_id']);
        unset($_SESSION['agent_id']);
        unset($_SESSION['employee_id']);
        unset($_SESSION['customer_id']);

        $this->loggingService->logUserActivity($adminId, 'impersonation_stopped', []);

        return $this->redirect('admin/users');
    }

    /**
     * Force password reset for user
     */
    public function forcePasswordReset($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, email, name FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);

            // Generate temporary password
            $tempPass = 'Temp@' . bin2hex(random_bytes(4));
            $hashed = password_hash($tempPass, PASSWORD_DEFAULT);

            $this->db->execute("UPDATE users SET password = ?, password_reset_required = 1, updated_at = NOW() WHERE id = ?", [$hashed, $userId]);

            // Log activity
            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $this->loggingService->logUserActivity($adminId, 'password_force_reset', [
                'user_id' => $userId,
                'user_email' => $user['email']
            ]);

            // TODO: Send email with temp password (integrate with notification service)

            return $this->jsonResponse([
                'success' => true,
                'message' => "Password reset. Temporary password: {$tempPass} (user must change on next login)"
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Import users page
     */
    public function import()
    {
        try {
            $data = [
                'page_title' => 'Import Users - APS Dream Home',
                'active_page' => 'users',
                'roles' => ['customer', 'associate', 'agent', 'employee', 'telecaller', 'user']
            ];
            return $this->render('admin/users/import', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("Import page error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load import page');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Process CSV import
     */
    public function importProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonError('No file uploaded or upload error', 400);
            }

            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');
            if (!$handle) return $this->jsonError('Cannot read CSV file', 400);

            $header = fgetcsv($handle);
            if (!$header) return $this->jsonError('Empty CSV file', 400);

            // Normalize header
            $header = array_map('strtolower', array_map('trim', $header));
            $required = ['name', 'email', 'password', 'role'];
            foreach ($required as $req) {
                if (!in_array($req, $header)) {
                    fclose($handle);
                    return $this->jsonError("Missing required column: {$req}", 400);
                }
            }

            $regService = new \App\Services\UserRegistrationService();
            $imported = 0;
            $errors = [];
            $rowNum = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $data = array_combine($header, $row);

                // Validate
                if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
                    $errors[] = "Row {$rowNum}: Missing required fields";
                    continue;
                }
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowNum}: Invalid email";
                    continue;
                }
                $validRoles = ['customer', 'associate', 'agent', 'employee', 'telecaller', 'user'];
                if (!in_array(strtolower($data['role']), $validRoles)) {
                    $errors[] = "Row {$rowNum}: Invalid role";
                    continue;
                }

                $result = $regService->createUser(strtolower($data['role']), [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? '',
                    'password' => $data['password'],
                    'city' => $data['city'] ?? '',
                    'occupation' => $data['occupation'] ?? '',
                    'registration_method' => 'csv_import',
                ], $user);

                if ($result['success']) {
                    $imported++;
                } else {
                    $errors[] = "Row {$rowNum}: " . $result['message'];
                }
            }
            fclose($handle);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $this->loggingService->logUserActivity($adminId, 'users_imported', [
                'imported' => $imported,
                'errors' => count($errors)
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => "Imported {$imported} users" . ($errors ? " ({$errors['count']} errors)" : ''),
                'imported' => $imported,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Import failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * View user sessions
     */
    public function viewSessions($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, phone, role FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }

            $sessions = $this->db->fetchAll(
                "SELECT * FROM user_sessions WHERE user_id = ? ORDER BY created_at DESC",
                [$userId]
            );

            $data = [
                'page_title' => "Sessions: {$user['name']} - APS Dream Home",
                'active_page' => 'users',
                'user' => $user,
                'sessions' => $sessions ?? []
            ];
            return $this->render('admin/users/sessions', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("View Sessions error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sessions');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Revoke user session
     */
    public function revokeSession($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $sessionId = intval($id);
            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $session = $this->db->fetchOne("SELECT * FROM user_sessions WHERE id = ?", [$sessionId]);
            if (!$session) return $this->jsonError('Session not found', 404);

            $this->db->execute("DELETE FROM user_sessions WHERE id = ?", [$sessionId]);

            $this->loggingService->logUserActivity($adminId, 'session_revoked', [
                'session_id' => $sessionId,
                'user_id' => $session['user_id']
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'Session revoked']);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Revoke all user sessions except current
     */
    public function revokeAllSessions($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $this->db->execute("DELETE FROM user_sessions WHERE user_id = ?", [$userId]);

            $this->loggingService->logUserActivity($adminId, 'all_sessions_revoked', [
                'user_id' => $userId
            ]);

            return $this->jsonResponse(['success' => true, 'message' => 'All sessions revoked']);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * View/Manage 2FA for user
     */
    public function viewTwoFactor($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, phone, role, two_factor_enabled, two_factor_secret FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }

            $data = [
                'page_title' => "2FA: {$user['name']} - APS Dream Home",
                'active_page' => 'users',
                'user' => $user
            ];
            return $this->render('admin/users/two_factor', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("View 2FA error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load 2FA');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Enable/Disable 2FA
     */
    public function toggleTwoFactor($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $action = $_POST['action'] ?? '';
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            if ($action === 'enable') {
                // Generate new secret (RFC 3548 base32)
                $secret = $this->base32Encode(random_bytes(20));
                $this->db->execute("UPDATE users SET two_factor_enabled = 1, two_factor_secret = ?, updated_at = NOW() WHERE id = ?", [$secret, $userId]);
                
                $this->loggingService->logUserActivity($adminId, '2fa_enabled', ['user_id' => $userId]);
                return $this->jsonResponse(['success' => true, 'message' => '2FA enabled', 'secret' => $secret, 'qr' => 'otpauth://totp/APS%20Dream%20Home:' . urlencode($user['email']) . '?secret=' . $secret . '&issuer=APS%20Dream%20Home']);
            } elseif ($action === 'disable') {
                $this->db->execute("UPDATE users SET two_factor_enabled = 0, two_factor_secret = NULL, updated_at = NOW() WHERE id = ?", [$userId]);
                
                $this->loggingService->logUserActivity($adminId, '2fa_disabled', ['user_id' => $userId]);
                return $this->jsonResponse(['success' => true, 'message' => '2FA disabled']);
            } elseif ($action === 'regenerate_backup') {
                $codes = [];
                for ($i = 0; $i < 8; $i++) {
                    $codes[] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                }
                $this->db->execute("UPDATE users SET two_factor_backup_codes = ?, updated_at = NOW() WHERE id = ?", [json_encode($codes), $userId]);
                return $this->jsonResponse(['success' => true, 'message' => 'Backup codes regenerated', 'codes' => $codes]);
            }

            return $this->jsonError('Invalid action', 400);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * View user notes/tags
     */
    public function viewNotes($id)
    {
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, phone, role FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) { $this->setFlash('error', 'User not found'); return $this->redirect('admin/users'); }

            // Ensure notes table exists
            $this->db->execute("CREATE TABLE IF NOT EXISTS user_admin_notes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                admin_id BIGINT UNSIGNED NOT NULL,
                note TEXT NOT NULL,
                tags JSON DEFAULT NULL,
                is_important TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_admin_id (admin_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $notes = $this->db->fetchAll(
                "SELECT n.*, a.name as admin_name FROM user_admin_notes n LEFT JOIN users a ON a.id = n.admin_id WHERE n.user_id = ? ORDER BY n.created_at DESC",
                [$userId]
            );

            $data = [
                'page_title' => "Notes: {$user['name']} - APS Dream Home",
                'active_page' => 'users',
                'user' => $user,
                'notes' => $notes ?? []
            ];
            return $this->render('admin/users/notes', $data);
        } catch (\Exception $e) {
            $this->loggingService->error("View Notes error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load notes');
            return $this->redirect('admin/users');
        }
    }

    /**
     * Add note for user
     */
    public function addNote($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            $note = trim($_POST['note'] ?? '');
            $tags = $_POST['tags'] ?? [];
            $isImportant = isset($_POST['is_important']) ? 1 : 0;

            if (empty($note)) return $this->jsonError('Note cannot be empty', 400);

            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $tagsJson = json_encode($tags);

            $this->db->execute(
                "INSERT INTO user_admin_notes (user_id, admin_id, note, tags, is_important, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$userId, $adminId, $note, $tagsJson, $isImportant]
            );

            $noteId = $this->db->getConnection()->lastInsertId();
            $newNote = $this->db->fetchOne(
                "SELECT n.*, a.name as admin_name FROM user_admin_notes n LEFT JOIN users a ON a.id = n.admin_id WHERE n.id = ?",
                [$noteId]
            );

            $this->loggingService->logUserActivity($adminId, 'user_note_added', ['user_id' => $userId, 'note_id' => $noteId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Note added', 'note' => $newNote]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update note
     */
    public function updateNote($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $noteId = intval($id);
            $note = trim($_POST['note'] ?? '');
            $tags = $_POST['tags'] ?? [];
            $isImportant = isset($_POST['is_important']) ? 1 : 0;

            if (empty($note)) return $this->jsonError('Note cannot be empty', 400);

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $tagsJson = json_encode($tags);

            $this->db->execute(
                "UPDATE user_admin_notes SET note = ?, tags = ?, is_important = ?, updated_at = NOW() WHERE id = ?",
                [$note, $tagsJson, $isImportant, $noteId]
            );

            $updatedNote = $this->db->fetchOne(
                "SELECT n.*, a.name as admin_name FROM user_admin_notes n LEFT JOIN users a ON a.id = n.admin_id WHERE n.id = ?",
                [$noteId]
            );

            $this->loggingService->logUserActivity($adminId, 'user_note_updated', ['note_id' => $noteId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Note updated', 'note' => $updatedNote]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete note
     */
    public function deleteNote($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $noteId = intval($id);
            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

            $this->db->execute("DELETE FROM user_admin_notes WHERE id = ?", [$noteId]);

            $this->loggingService->logUserActivity($adminId, 'user_note_deleted', ['note_id' => $noteId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Note deleted']);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get activity feed for dashboard widget
     */
    public function getActivityFeed()
    {
        try {
            $limit = min((int)($_GET['limit'] ?? 10), 50);
            list($tSql, $tParams) = $this->tenantWhere();
            
            $activities = $this->db->fetchAll(
                "SELECT l.*, u.name as admin_name, u2.name as user_name
                 FROM user_activity_logs_unified l
                 LEFT JOIN users u ON u.id = l.user_id
                 LEFT JOIN users u2 ON JSON_EXTRACT(l.context, '$.user_id') = u2.id OR l.user_id = u2.id
                 WHERE 1=1" . $tSql . "
                 ORDER BY l.created_at DESC
                 LIMIT ?",
                array_merge($tParams, [$limit])
            );

            return $this->jsonResponse([
                'success' => true,
                'activities' => $activities ?? []
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Get Activity Feed error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch activity feed',
                'activities' => []
            ], 500);
        }
    }

    /**
     * Base32 encode (RFC 3548) for TOTP secrets
     */
    public function uploadAvatar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, name, email, profile_image FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);

            if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonError('No file uploaded or upload error', 400);
            }

            $file = $_FILES['avatar'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes)) {
                return $this->jsonError('Invalid file type. Allowed: JPG, PNG, WebP, GIF', 400);
            }
            if ($file['size'] > $maxSize) {
                return $this->jsonError('File too large. Maximum 5MB', 400);
            }

            // Create upload directory
            $uploadDir = 'public/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return $this->jsonError('Failed to move uploaded file', 500);
            }

            // Delete old avatar if exists
            if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                @unlink($user['profile_image']);
            }

            // Update user record
            $relativePath = '/' . $filepath;
            $this->db->execute("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?" . $tSql, array_merge([$relativePath, $userId], $tParams));

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $this->loggingService->logUserActivity($adminId, 'avatar_uploaded', ['user_id' => $userId, 'file' => $filename]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => BASE_URL . $relativePath
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $userId = intval($id);
            list($tSql, $tParams) = $this->tenantWhere();
            $user = $this->db->fetchOne("SELECT id, profile_image FROM users WHERE id = ?" . $tSql, array_merge([$userId], $tParams));
            if (!$user) return $this->jsonError('User not found', 404);

            if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                @unlink($user['profile_image']);
            }

            $this->db->execute("UPDATE users SET profile_image = NULL, updated_at = NOW() WHERE id = ?" . $tSql, array_merge([$userId], $tParams));

            $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
            $this->loggingService->logUserActivity($adminId, 'avatar_deleted', ['user_id' => $userId]);

            return $this->jsonResponse(['success' => true, 'message' => 'Avatar deleted']);
        } catch (\Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
}

    /**
     * Get user analytics for dashboard charts
     */
    public function getUserAnalytics()
    {
        try {
            list($tSql, $tParams) = $this->tenantWhere();
            
            // Users by role
            $byRole = $this->db->fetchAll(
                "SELECT role, COUNT(*) as count FROM users WHERE 1=1" . $tSql . " GROUP BY role ORDER BY count DESC",
                $tParams
            );
            
            // Users by status
            $byStatus = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM users WHERE 1=1" . $tSql . " GROUP BY status",
                $tParams
            );
            
            // Registrations trend (last 30 days)
            $trend = $this->db->fetchAll(
                "SELECT DATE(created_at) as date, COUNT(*) as count 
                 FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $tSql . "
                 GROUP BY DATE(created_at) ORDER BY date ASC",
                $tParams
            );
            
            return $this->jsonResponse([
                'success' => true,
                'by_role' => $byRole ?: [],
                'by_status' => $byStatus ?: [],
                'trend' => $trend ?: []
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error("Get User Analytics error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch analytics',
                'by_role' => [],
                'by_status' => [],
                'trend' => []
            ], 500);
        }
    }

}

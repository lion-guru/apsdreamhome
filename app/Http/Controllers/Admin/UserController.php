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

            $sql = "SELECT u.*,
                           COUNT(p.id) as property_count,
                           (SELECT COUNT(*) FROM bookings WHERE customer_id = u.id) as booking_count
                    FROM users u
                    LEFT JOIN properties p ON u.id = p.created_by
                    WHERE u.registration_status = 'pending'";
            list($tSql, $tParams) = $this->tenantWhere();
            $sql .= $tSql;
            $sql .= " ORDER BY u.created_at DESC";

            $countSql = "SELECT COUNT(*) as total FROM users WHERE registration_status = 'pending'" . $tSql;
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

            // Ensure wallet exists
            $this->db->execute(
                "INSERT IGNORE INTO wallet_points (user_id, user_type, points_balance, total_credited, is_active) VALUES (?, 'associate', 0, 0, 1)",
                [$userId]
            );

            $this->db->execute(
                "UPDATE wallet_points SET points_balance = points_balance + ?, total_credited = total_credited + ? WHERE user_id = ?",
                [$amount, $amount, $userId]
            );

            $this->db->execute(
                "INSERT INTO wallet_transactions (user_id, type, amount, description, reference_id, created_at) VALUES (?, 'credit', ?, ?, ?, NOW())",
                [$userId, $amount, "Admin credit: {$reason}", $adminId]
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

            $wallet = $this->db->fetchOne("SELECT points_balance FROM wallet_points WHERE user_id = ?", [$userId]);
            if (!$wallet || (float)$wallet['points_balance'] < $amount) {
                return $this->jsonError('Insufficient balance', 400);
            }

            $this->db->execute(
                "UPDATE wallet_points SET points_balance = points_balance - ? WHERE user_id = ?",
                [$amount, $userId]
            );

            $this->db->execute(
                "INSERT INTO wallet_transactions (user_id, type, amount, description, reference_id, created_at) VALUES (?, 'debit', ?, ?, ?, NOW())",
                [$userId, $amount, "Admin debit: {$reason}", $adminId]
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
                $this->db->execute("UPDATE mlm_profiles SET sponsor_user_id = ?, updated_at = NOW() WHERE user_id = ?", [$newSponsorId, $userId]);

                // 3. Update associates
                $this->db->execute("UPDATE associates SET sponsor_id = ?, updated_at = NOW() WHERE user_id = ?", [$newSponsorId, $userId]);

                // 4. Update mlm_network_tree
                $this->db->execute("UPDATE mlm_network_tree SET sponsor_id = ?, parent_id = ? WHERE associate_id = ?", [$newSponsorId, $newSponsorId, $userId]);

                // 5. Update network_tree parent
                $this->db->execute("UPDATE network_tree SET parent_id = ? WHERE associate_id = ?", [$newSponsorId, $userId]);

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
            $this->db->execute("UPDATE mlm_profiles SET referral_code = ?, updated_at = NOW() WHERE user_id = ?", [$newCode, $userId]);

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
}

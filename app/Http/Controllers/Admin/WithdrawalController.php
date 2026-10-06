<?php
namespace App\Http\Controllers\Admin;


class WithdrawalController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $filters = [];
        $params = [];
        
        // Build filters
        if (!empty($_GET['status'])) {
            $filters[] = "wr.status = ?";
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['user_type'])) {
            $filters[] = "u.role = ?";
            $params[] = $_GET['user_type'];
        }
        if (!empty($_GET['from_date'])) {
            $filters[] = "DATE(wr.created_at) >= ?";
            $params[] = $_GET['from_date'];
        }
        if (!empty($_GET['to_date'])) {
            $filters[] = "DATE(wr.created_at) <= ?";
            $params[] = $_GET['to_date'];
        }
        if (!empty($_GET['search'])) {
            $filters[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR wr.id LIKE ?)";
            $searchTerm = "%{$_GET['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereSql = $filters ? "WHERE " . implode(" AND ", $filters) : "";
        $tid = \App\Core\Middleware\TenantContext::getId();
        if ($tid > 1) {
            $whereSql .= ($whereSql ? " AND " : "WHERE ") . "wr.tenant_id = ?";
            $params[] = $tid;
        }
        
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        
        // Total count
        $countSql = "SELECT COUNT(*) FROM withdrawal_requests wr JOIN users u ON u.id = wr.user_id $whereSql";
        $total = (int)$db->fetchOne($countSql, $params)['COUNT(*)'];
        $totalPages = ceil($total / $perPage);
        
        // Fetch requests with user info and wallet type detection
        $listSql = "
            SELECT 
                wr.*, 
                u.name, u.email, u.phone, u.role,
                CASE 
                    WHEN u.role = 'customer' THEN 'wallet_points'
                    ELSE 'user_wallets'
                END as wallet_system,
                bk.account_holder_name, bk.bank_name, bk.account_number, bk.ifsc_code
            FROM withdrawal_requests wr
            JOIN users u ON u.id = wr.user_id
            LEFT JOIN user_bank_accounts bk ON bk.id = wr.bank_account_id
            $whereSql
            ORDER BY wr.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $params[] = $perPage;
        $params[] = $offset;
        $requests = $db->fetchAll($listSql, $params) ?: [];
        
        // Stats
        $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'processing' => 0];
        try {
            $counts = $this->db->fetchAll("SELECT status, COUNT(*) as cnt FROM withdrawal_requests GROUP BY status") ?: [];
            foreach ($counts as $row) {
                $key = strtolower($row['status']);
                if (isset($stats[$key])) $stats[$key] = intval($row['cnt']);
                $stats['total'] += intval($row['cnt']);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        
        $csrf_token = $_SESSION['csrf_token'] ?? '';

        return $this->render('admin/withdrawal/index', [
            'page_title' => 'Withdrawal Requests',
            'requests' => $requests,
            'stats' => $stats,
            'csrf_token' => $csrf_token,
        ]);
    }
    
    public function updateStatus($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/withdrawals');
        }
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            $this->redirect('/admin/withdrawals');
            return;
        }
        
        $status = $_POST['status'] ?? '';
        $adminNotes = trim((string)($_POST['admin_notes'] ?? ''));
        
        if (!in_array($status, ['approved', 'rejected', 'completed', 'processing'])) {
            $_SESSION['error'] = 'Invalid status.';
            $this->redirect('/admin/withdrawals');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $request = $this->db->fetchOne("SELECT * FROM withdrawal_requests WHERE id = ? FOR UPDATE", [$id]);
            if (!$request) {
                $this->db->rollBack();
                $_SESSION['error'] = 'Request not found.';
                $this->redirect('/admin/withdrawals');
                return;
            }
            
            if (in_array($request['status'], ['rejected', 'completed'])) {
                $this->db->rollBack();
                $_SESSION['error'] = 'Cannot change status of a finalized request.';
                $this->redirect('/admin/withdrawals');
                return;
            }
            
            $update = ['status' => $status];
            $tid = (int)$this->tenantId();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            
            if ($status === 'rejected') {
                $update['rejection_reason'] = $adminNotes;
                $reqRole = '';
                try {
                    $roleRow = $this->db->fetchOne("SELECT role FROM users WHERE id = ? LIMIT 1", [$request['user_id']]);
                    $reqRole = $roleRow['role'] ?? '';
                } catch (\Throwable $e) { error_log('Withdrawal refund role lookup: ' . $e->getMessage()); }
                
                if ($reqRole === 'customer') {
                    $this->db->query(
                        "UPDATE wallet_points SET points_balance = points_balance + ?, total_used = GREATEST(total_used - ?, 0) WHERE user_id = ?" . $tenantSql,
                        array_merge([$request['amount'], $request['amount'], $request['user_id']], $tenantParams)
                    );
                } else {
                    $this->db->query(
                        "UPDATE user_wallets SET balance = balance + ?, updated_at = NOW() WHERE user_id = ? AND user_type = 'associate'" . $tenantSql,
                        array_merge([$request['amount'], $request['user_id']], $tenantParams)
                    );
                }
            } else {
                $update['remarks'] = $adminNotes;
            }
            
            if ($status === 'completed' || $status === 'approved') {
                $update['processed_at'] = date('Y-m-d H:i:s');
                if ($status === 'approved') {
                    $update['approved_at'] = date('Y-m-d H:i:s');
                    $update['approved_by'] = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
                }
            }
            
            $where = ['id' => intval($id)];
            if ($tid > 1) $where['tenant_id'] = $tid;
            $this->db->update('withdrawal_requests', $update, $where);
            $this->db->commit();
            
            $_SESSION['success'] = "Withdrawal request #{$id} marked as {$status}.";
            
            // Send notification to user
            try {
                $notifService = new \App\Services\Communication\NotificationService();
                $notifService->sendToUser($request['user_id'], [
                    'title' => 'Withdrawal Request Update',
                    'body' => "Your withdrawal request #{$id} has been marked as {$status}.",
                    'data' => ['type' => 'withdrawal', 'withdrawal_id' => $id, 'status' => $status],
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]);
            } catch (\Throwable $e) {
                error_log("Withdrawal notification failed: " . $e->getMessage());
            }
            
        } catch (\Throwable $e) {
            if ($this->db && method_exists($this->db, 'inTransaction') && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Admin withdrawal update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update request: ' . $e->getMessage();
        }
        $this->redirect('/admin/withdrawals');
    }
    
    public function export()
    {
        $this->requireAdmin();
        
        $filters = [];
        $params = [];
        
        if (!empty($_GET['status'])) {
            $filters[] = "wr.status = ?";
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['user_type'])) {
            $filters[] = "u.role = ?";
            $params[] = $_GET['user_type'];
        }
        if (!empty($_GET['from_date'])) {
            $filters[] = "DATE(wr.created_at) >= ?";
            $params[] = $_GET['from_date'];
        }
        if (!empty($_GET['to_date'])) {
            $filters[] = "DATE(wr.created_at) <= ?";
            $params[] = $_GET['to_date'];
        }
        if (!empty($_GET['search'])) {
            $filters[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR wr.id LIKE ?)";
            $searchTerm = "%{$_GET['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereSql = $filters ? "WHERE " . implode(" AND ", $filters) : "";
        $tid = \App\Core\Middleware\TenantContext::getId();
        if ($tid > 1) {
            $whereSql .= ($whereSql ? " AND " : "WHERE ") . "wr.tenant_id = ?";
            $params[] = \App\Core\Middleware\TenantContext::getId();
        }
        
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $requests = $db->fetchAll("
            SELECT 
                wr.*, 
                u.name, u.email, u.phone, u.role,
                bk.account_holder_name, bk.bank_name, bk.account_number, bk.ifsc_code
            FROM withdrawal_requests wr
            JOIN users u ON u.id = wr.user_id
            LEFT JOIN user_bank_accounts bk ON bk.id = wr.bank_account_id
            " . $whereSql . "
            ORDER BY wr.created_at DESC
        ", $params) ?: [];
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="withdrawal_requests_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'ID', 'User ID', 'User Name', 'User Email', 'User Role', 'User Phone',
            'Amount', 'Status', 'Bank Account Holder', 'Bank Name', 'Account Number (Last 4)', 'IFSC',
            'Created At', 'Processed At', 'Approved At', 'Rejection Reason', 'Remarks'
        ]);
        
        foreach ($requests as $r) {
            fputcsv($output, [
                $r['id'],
                $r['user_id'],
                $r['name'] ?? '',
                $r['email'] ?? '',
                $r['role'] ?? '',
                $r['phone'] ?? '',
                number_format((float)$r['amount'], 2, '.', ''),
                ucfirst($r['status'] ?? ''),
                $r['account_holder_name'] ?? '',
                $r['bank_name'] ?? '',
                $r['account_number'] ? substr($r['account_number'], -4) : '',
                $r['ifsc_code'] ?? '',
                $r['created_at'] ?? '',
                $r['processed_at'] ?? '',
                $r['approved_at'] ?? '',
                $r['rejection_reason'] ?? '',
                $r['remarks'] ?? '',
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function show($id)
    {
        $this->requireAdmin();
        
        $tid = \App\Core\Middleware\TenantContext::getId();
        $tenantWhere = $tid > 1 ? " AND wr.tenant_id = ?" : "";
        $params = array_merge([$id], $tid > 1 ? [$tid] : []);
        
        $request = $this->db->fetchOne("
            SELECT 
                wr.*, 
                u.name, u.email, u.phone, u.role, u.referral_code,
                bk.account_holder_name, bk.bank_name, bk.branch_name, bk.account_number, bk.ifsc_code,
                bk.micr_code, bk.is_primary
            FROM withdrawal_requests wr
            JOIN users u ON u.id = wr.user_id
            LEFT JOIN user_bank_accounts bk ON bk.id = wr.bank_account_id
            WHERE wr.id = ? $tenantWhere
        ", $params);
        
        if (!$request) {
            $_SESSION['error'] = 'Withdrawal request not found.';
            $this->redirect('/admin/withdrawals');
            return;
        }
        
        // Get user's wallet balance
        $tid = \App\Core\Middleware\TenantContext::getId();
        $tenantWhere2 = $tid > 1 ? " AND tenant_id = ?" : "";
        $params2 = array_merge([$request['user_id']], $tid > 1 ? [$tid] : []);
        
        $wallet = null;
        if ($request['role'] === 'customer') {
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$request['user_id']]);
        } else {
            $wallet = $this->db->fetchOne("SELECT * FROM user_wallets WHERE user_id = ? AND user_type = 'associate'" . $tenantWhere2 . " LIMIT 1", $params2);
        }
        
        // Get recent transactions
        $transactions = [];
        if ($request['role'] === 'customer') {
            $transactions = $this->db->fetchAll("
                SELECT * FROM wallet_transactions WHERE user_id = ? AND transaction_category = 'withdrawal' ORDER BY created_at DESC LIMIT 10
            ", [$request['user_id']]);
        } else {
            $params3 = array_merge([$request['user_id']], $tid > 1 ? [$tid] : []);
            $transactions = $this->db->fetchAll("
                SELECT * FROM wallet_transactions WHERE user_id = ? AND transaction_category = 'withdrawal' ORDER BY created_at DESC LIMIT 10
            ", $params3);
        }
        
        // Get user's other withdrawal requests
        $history = $this->db->fetchAll("
            SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 10
        ", [$request['user_id']]);
        
        $csrf_token = $_SESSION['csrf_token'] ?? '';

        return $this->render('admin/withdrawal/view', [
            'page_title' => 'Withdrawal Request #' . $id,
            'request' => $request,
            'wallet' => $wallet,
            'transactions' => $transactions,
            'history' => $history,
            'csrf_token' => $csrf_token,
        ]);
    }
}
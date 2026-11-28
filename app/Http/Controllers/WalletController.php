<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;

#[\AllowDynamicProperties]
class WalletController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Wallet Dashboard - Main wallet page
     */
    public function index()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Get wallet information
        $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);

        if (!$wallet) {
            // Create wallet if not exists
            $tid = (int)$this->tenantId();
            $this->db->insert('wallet_points', [
                'user_id' => $userId,
                'points_balance' => 0.00,
                'total_earned' => 0.00,
                'total_used' => 0.00,
                'total_transferred_to_emi' => 0.00,
                'referral_earnings' => 0.00,
                'commission_earnings' => 0.00,
                'bonus_earnings' => 0.00,
                'status' => 'active',
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);
        }

        // Get recent transactions
        $recentTransactions = $this->db->fetchAll(
            "SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$userId]
        );

        try {
            // Get referral earnings summary
            $referralStats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_referrals,
                    SUM(reward_amount) as total_referral_earnings
                FROM referral_rewards 
                WHERE referrer_id = ? AND status = 'credited'",
                [$userId]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        // Get wallet configuration
        $config = $this->db->fetchAll("SELECT * FROM wallet_configuration");
        $walletConfig = [];
        foreach ($config as $item) {
            $walletConfig[$item['setting_key']] = $item['setting_value'];
        }

        $userReferralCode = '';
        try {
            $userData = $this->db->fetchOne("SELECT referral_code FROM users WHERE id = ?", [$userId]);
            $userReferralCode = $userData['referral_code'] ?? '';
        } catch (\Throwable $e) { error_log('WalletController exception: ' . $e->getMessage()); }

        // Fetch user's pending installments from booking_payment_schedules
        $upcomingEmis = [];
        try {
            $upcomingEmis = $this->db->fetchAll("
                SELECT bps.*, bps.installment_no as installment_number, pb.booking_number, p.plot_number, c.name as colony_name
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON bps.booking_id = pb.id
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON pb.colony_id = c.id
                WHERE (pb.customer_id = ? OR pb.user_id = ?)
                  AND bps.status IN ('pending', 'overdue')
                ORDER BY bps.due_date ASC
                LIMIT 5
            ", [$userId, $userId]);
        } catch (\Throwable $e) {
            error_log("WalletController::index upcoming EMIs error: " . $e->getMessage());
        }

        $data = [
            'wallet' => $wallet,
            'recentTransactions' => $recentTransactions,
            'referralStats' => $referralStats,
            'config' => $walletConfig,
            'upcomingEmis' => $upcomingEmis,
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'user_email' => $_SESSION['user_email'] ?? '',
            'user_referral_code' => $userReferralCode,
            'referral_code' => $userReferralCode,
            'page_title' => 'Wallet Dashboard'
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/dashboard', $data);
    }

    /**
     * Top-up Wallet Balance via UPI / Deposit
     */
    public function topUp()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Please log in to continue']);
                exit;
            }
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // S809: this controller skips the router's global CSRF gate (several
        // sibling endpoints are Bearer/fetch driven), but topUp() is a plain
        // form POST that CREDITS WALLET MONEY. Without its own check any
        // third-party page could POST with a logged-in victim's cookie and
        // create a fake balance. Validate per-method, keep the class skip.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
        }

        $userId = (int)$_SESSION['user_id'];
        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $method = trim($_POST['payment_method'] ?? 'upi');
        $utr = trim($_POST['utr_number'] ?? '');

        if ($amount <= 0) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid top-up amount']);
                exit;
            }
            $_SESSION['flash_error'] = 'Invalid top-up amount.';
            header('Location: ' . BASE_URL . '/wallet/dashboard');
            exit;
        }

        try {
            $walletService = new \App\Services\WalletService();
            $desc = "Wallet deposit via " . strtoupper($method) . ($utr ? " (Ref: {$utr})" : "");
            $credited = $walletService->credit($userId, $amount, 'adjustment', $desc, $userId, 'user');

            if ($credited) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => '₹' . number_format($amount, 2) . ' credited to your wallet successfully!',
                        'new_balance' => $walletService->getBalance($userId)
                    ]);
                    exit;
                }
                $_SESSION['flash_success'] = '₹' . number_format($amount, 2) . ' added to your wallet successfully!';
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to credit wallet']);
                    exit;
                }
                $_SESSION['flash_error'] = 'Failed to top up wallet.';
            }
        } catch (\Throwable $e) {
            error_log("WalletController::topUp error: " . $e->getMessage());
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Server error during top-up']);
                exit;
            }
            $_SESSION['flash_error'] = 'Server error during top-up.';
        }

        $isAssociate = (($_SESSION['role'] ?? '') === 'associate');
        header('Location: ' . BASE_URL . ($isAssociate ? '/associate/wallet' : '/wallet'));
        exit;
    }

    /**
     * Transaction History Page
     */
    public function transactions()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $filter = $_GET['filter'] ?? 'all';
        $type = $_GET['type'] ?? '';

        $where = "WHERE user_id = ?";
        $params = [$userId];

        if ($filter !== 'all') {
            $where .= " AND transaction_category = ?";
            $params[] = $filter;
        }

        if ($type) {
            $where .= " AND transaction_type = ?";
            $params[] = $type;
        }

        // Get transactions with pagination
        $transactions = $this->db->fetchAll(
            "SELECT * FROM wallet_transactions $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset",
            $params
        );

        // Get total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM wallet_transactions $where",
            $params
        )['count'];

        $totalPages = ceil($total / $limit);

        $data = [
            'transactions' => $transactions,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filter' => $filter,
            'type' => $type,
            'user_name' => $_SESSION['user_name'] ?? 'User'
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/transactions', $data);
    }

    /**
     * Transfer to EMI Page
     */
    public function transferToEmi()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        // Get wallet balance (auto-create if missing)
        $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);
        if (!$wallet) {
            $tid = (int)$this->tenantId();
            $this->db->insert('wallet_points', [
                'user_id' => $userId,
                'points_balance' => 0.00,
                'total_earned' => 0.00,
                'total_used' => 0.00,
                'total_transferred_to_emi' => 0.00,
                'referral_earnings' => 0.00,
                'commission_earnings' => 0.00,
                'bonus_earnings' => 0.00,
                'status' => 'active',
                'tenant_id' => $tid > 0 ? $tid : 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);
        }

        // Get user's pending / overdue EMIs with rich booking & colony details
        $emis = [];
        try {
            $emis = $this->db->fetchAll("
                SELECT 
                    es.id,
                    es.booking_id,
                    es.emi_number,
                    es.amount,
                    (es.amount + COALESCE(es.late_fee, 0) - COALESCE(es.paid_amount, 0)) AS due_amount,
                    es.due_date,
                    es.status,
                    COALESCE(es.late_fee, 0) AS late_fee,
                    COALESCE(es.paid_amount, 0) AS paid_amount,
                    pb.booking_number,
                    pb.customer_name,
                    c.name AS colony_name,
                    p.plot_number,
                    CONCAT(COALESCE(c.name, 'APS Township'), ' - Plot #', COALESCE(p.plot_number, pb.plot_id, 'N/A')) AS property_name
                FROM emi_schedule es
                LEFT JOIN plot_bookings pb ON es.booking_id = pb.id
                LEFT JOIN colonies c ON pb.colony_id = c.id
                LEFT JOIN plots p ON pb.plot_id = p.id
                WHERE (es.customer_id = ? OR pb.user_id = ? OR pb.customer_id = ?)
                  AND es.status IN ('pending', 'overdue')
                ORDER BY es.due_date ASC
            ", [$userId, $userId, $userId]);
        } catch (\Throwable $e) {
            error_log('WalletController emi fetch: ' . $e->getMessage());
        }

        $data = [
            'wallet' => $wallet,
            'emis' => $emis,
            'user_name' => $_SESSION['user_name'] ?? 'User'
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/transfer_emi', $data, $this->layout);
    }

    /**
     * Process EMI Transfer
     */
    public function processEmiTransfer()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $emiId = (int)($_POST['emi_id'] ?? 0);
        $amount = round(floatval($_POST['amount'] ?? 0), 2);

        if ($emiId <= 0 || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid transfer details']);
            exit;
        }

        try {
            // Verify EMI existence and status
            $emi = $this->db->fetchOne("
                SELECT es.*, pb.user_id as booking_user_id, pb.customer_id as booking_customer_id
                FROM emi_schedule es
                LEFT JOIN plot_bookings pb ON es.booking_id = pb.id
                WHERE es.id = ? AND es.status IN ('pending', 'overdue')
                LIMIT 1
            ", [$emiId]);

            if (!$emi) {
                echo json_encode(['success' => false, 'message' => 'EMI record not found or already settled']);
                exit;
            }

            $userRole = $_SESSION['role'] ?? '';
            $isAuthorized = ($emi['customer_id'] == $userId || 
                             $emi['booking_user_id'] == $userId || 
                             $emi['booking_customer_id'] == $userId || 
                             $userRole === 'admin' || 
                             $userRole === 'associate');

            if (!$isAuthorized) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized to pay this EMI']);
                exit;
            }

            $dueAmount = (float)$emi['amount'] + (float)($emi['late_fee'] ?? 0) - (float)($emi['paid_amount'] ?? 0);
            if ($amount > ($dueAmount + 0.05)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Transfer amount cannot exceed remaining due: ₹' . number_format($dueAmount, 2)
                ]);
                exit;
            }

            $this->db->beginTransaction();

            // Lock wallet row to prevent concurrent overdraft / race conditions
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1 FOR UPDATE", [$userId]);

            if (!$wallet || (float)$wallet['points_balance'] < $amount) {
                $this->db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance']);
                exit;
            }

            // Deduct from wallet atomically
            $newBalance = (float)$wallet['points_balance'] - $amount;
            $newUsed = (float)$wallet['total_used'] + $amount;
            $newTransferred = (float)$wallet['total_transferred_to_emi'] + $amount;

            $tid = (int)$this->tenantId();
            $this->db->query(
                "UPDATE wallet_points SET points_balance = ?, total_used = ?, total_transferred_to_emi = ?, updated_at = NOW() WHERE user_id = ? AND points_balance >= ?",
                [$newBalance, $newUsed, $newTransferred, $userId, $amount]
            );

            // Create transaction record
            $transactionId = $this->db->insert('wallet_transactions', [
                'user_id' => $userId,
                'transaction_type' => 'debit',
                'transaction_category' => 'emi_transfer',
                'amount' => $amount,
                'balance_before' => $wallet['points_balance'],
                'balance_after' => $newBalance,
                'description' => "EMI Payment via Wallet - EMI #$emiId",
                'reference_id' => $emiId,
                'reference_type' => 'emi',
                'status' => 'completed',
                'tenant_id' => $tid > 0 ? $tid : 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Create EMI transfer record (wallet_emi_transfers has no tenant_id)
            $this->db->insert('wallet_emi_transfers', [
                'user_id' => $userId,
                'emi_id' => $emiId,
                'emi_amount' => (float)$emi['amount'],
                'wallet_amount_used' => $amount,
                'transaction_id' => $transactionId,
                'transfer_status' => 'completed',
                'transferred_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update emi_schedule row with payment
            $newPaidAmount = (float)($emi['paid_amount'] ?? 0) + $amount;
            $isFullyPaid = ($newPaidAmount >= (float)$emi['amount']);
            $newStatus = $isFullyPaid ? 'paid' : 'pending';
            $paidDate = $isFullyPaid ? date('Y-m-d') : null;

            $this->db->query("
                UPDATE emi_schedule 
                SET paid_amount = ?, 
                    status = ?, 
                    paid_date = COALESCE(paid_date, ?),
                    updated_at = NOW() 
                WHERE id = ?
            ", [$newPaidAmount, $newStatus, $paidDate, $emiId]);

            // Unified Audit Log
            try {
                $this->db->query("
                    INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, created_at)
                    VALUES (?, 'wallet_emi_transfer', ?, ?, NOW())
                ", [$userId, json_encode([
                    'emi_id' => $emiId,
                    'amount' => $amount,
                    'status' => $newStatus,
                    'remaining' => max(0, $dueAmount - $amount)
                ]), $_SERVER['REMOTE_ADDR'] ?? '']);
            } catch (\Throwable $e) {}

            $this->db->commit();
            echo json_encode([
                'success' => true, 
                'message' => '₹' . number_format($amount, 2) . ' transferred to EMI successfully' . ($isFullyPaid ? ' (EMI Fully Settled)' : '')
            ]);
            exit;
        } catch (\Throwable $e) {
            try { $this->db->rollBack(); } catch (\Throwable $ignored) {}
            echo json_encode(['success' => false, 'message' => 'Transfer failed: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Withdrawal Request Page
     */
    public function withdrawal()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Pagination parameters
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get wallet balance
        $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);

        // Get bank accounts
        $bankAccounts = [];
        try {
            $bankAccounts = $this->db->fetchAll(
                "SELECT * FROM user_bank_accounts WHERE user_id = ? AND status = 'active' ORDER BY is_primary DESC",
                [$userId]
            );
        } catch (\Throwable $e) {
            error_log('WalletController bank accounts: ' . $e->getMessage());
        }

        // Get withdrawal history with pagination
        $withdrawals = [];
        $totalWithdrawals = 0;
        try {
            $totalStmt = $this->db->fetchOne("SELECT COUNT(*) FROM withdrawal_requests WHERE user_id = ?", [$userId]);
            $totalWithdrawals = (int)($totalStmt['COUNT(*)'] ?? 0);

            $withdrawals = $this->db->fetchAll(
                "SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
                [$userId, $perPage, $offset]
            );
        } catch (\Throwable $e) {
            error_log('WalletController withdrawals: ' . $e->getMessage());
        }

        $totalPages = ceil($totalWithdrawals / $perPage);

        $data = [
            'wallet' => $wallet,
            'bankAccounts' => $bankAccounts,
            'withdrawals' => $withdrawals,
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'page_title' => 'Withdrawal Request',
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalWithdrawals,
                'total_pages' => $totalPages,
                'offset' => $offset,
            ],
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/withdrawal', $data);
    }

    /**
     * Process Withdrawal Request
     */
    public function processWithdrawal()
    {
        @session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $bankAccountId = $_POST['bank_account_id'] ?? 0;
        $amount = floatval($_POST['amount'] ?? 0);

        // S264: CSRF + atomicity. The form field exists but the fetch() builds
        // FormData by hand — the view now appends the token (withdrawal.php).
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please reload and try again.']);
            exit;
        }

        try {
            $this->db->beginTransaction();
            // Lock the wallet row: concurrent double-clicks serialize here.
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1 FOR UPDATE", [$userId]);

            if (!$wallet || $wallet['points_balance'] < $amount) {
                $this->db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance']);
                exit;
            }

            // Get minimum withdrawal amount from config (schema: setting_key/setting_value)
            $minRow = $this->db->fetchOne("SELECT setting_value FROM wallet_configuration WHERE setting_key = 'minimum_withdrawal'");
            $minWithdrawal = isset($minRow['setting_value']) ? (float)$minRow['setting_value'] : 500;

            if ($amount < $minWithdrawal) {
                $this->db->rollBack();
                echo json_encode(['success' => false, 'message' => "Minimum withdrawal amount is ₹$minWithdrawal"]);
                exit;
            }

            // Verify bank account belongs to user
            $bankAccount = $this->db->fetchOne("SELECT * FROM user_bank_accounts WHERE id = ? AND user_id = ? AND status = 'active'", [$bankAccountId, $userId]);
            if (!$bankAccount) {
                $this->db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Invalid bank account']);
                exit;
            }

            // Deduct at request time (admin approve only flips status; reject refunds)
            $newBalance = (float)$wallet['points_balance'] - $amount;
            $this->db->query("UPDATE wallet_points SET points_balance = ?, total_used = total_used + ?, updated_at = NOW() WHERE user_id = ? AND points_balance >= ?", [$newBalance, $amount, $userId, $amount]);

            // Create withdrawal request
            $tid = (int)$this->tenantId();
            $withdrawalId = $this->db->insert('withdrawal_requests', [
                'user_id' => $userId,
                'bank_account_id' => $bankAccountId,
                'amount' => $amount,
                'status' => 'pending',
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Ledger row (real wallet_transactions schema)
            $this->db->insert('wallet_transactions', [
                'user_id' => $userId,
                'transaction_type' => 'debit',
                'transaction_category' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => (float)$wallet['points_balance'],
                'balance_after' => $newBalance,
                'description' => 'Withdrawal to bank: ' . ($bankAccount['account_holder_name'] ?? '') . ' - ' . substr($bankAccount['account_number'] ?? '', -4),
                'reference_id' => (int)$withdrawalId,
                'reference_type' => 'withdrawal_request',
                'tenant_id' => $tid,
            ]);

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Withdrawal request submitted successfully']);
            exit;
        } catch (\Throwable $e) {
            try { $this->db->rollBack(); } catch (\Throwable $ignored) {}
            echo json_encode(['success' => false, 'message' => 'Withdrawal failed: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Bank Account Management Page
     */
    public function bankAccounts()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            $isAssociateRoute = (strpos($_SERVER['REQUEST_URI'] ?? '', '/associate/') !== false);
            header('Location: ' . BASE_URL . ($isAssociateRoute ? '/associate/login' : '/login'));
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Get bank accounts
        $bankAccounts = [];
        try {
            $bankAccounts = $this->db->fetchAll(
                "SELECT * FROM user_bank_accounts WHERE user_id = ? ORDER BY is_primary DESC, created_at DESC",
                [$userId]
            );
        } catch (\Throwable $e) {
            error_log('WalletController bank accounts list: ' . $e->getMessage());
        }

        $data = [
            'bankAccounts' => $bankAccounts,
            'user_name' => $_SESSION['user_name'] ?? 'User'
        ];

        $isAssociate = in_array($_SESSION['role'] ?? '', ['associate', 'agent'], true) || (strpos($_SERVER['REQUEST_URI'] ?? '', '/associate/') !== false);
        $this->layout = $isAssociate ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/bank_accounts', $data);
    }

    /**
     * Add Bank Account
     */
    public function addBankAccount()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $bankName = trim($_POST['bank_name'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = strtoupper(trim($_POST['ifsc_code'] ?? ''));
        $accountHolder = trim($_POST['account_holder'] ?? '');
        $accountHolderName = trim($_POST['account_holder_name'] ?? $accountHolder);
        $branchName = trim($_POST['branch_name'] ?? '');
        $accountType = trim($_POST['account_type'] ?? 'savings');
        $isPrimary = !empty($_POST['is_primary']) ? 1 : 0;

        if (empty($bankName) || empty($accountNumber) || empty($ifscCode) || empty($accountHolderName)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
            exit;
        }

        if (!preg_match('/^[0-9]{9,18}$/', $accountNumber)) {
            echo json_encode(['success' => false, 'message' => 'Account number must be between 9 and 18 digits']);
            exit;
        }

        if (strlen($ifscCode) < 8 || strlen($ifscCode) > 15) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid IFSC code']);
            exit;
        }

        try {
            // Check for duplicate account number for this user
            $existing = $this->db->fetchOne(
                "SELECT id FROM user_bank_accounts WHERE user_id = ? AND account_number = ? LIMIT 1",
                [$userId, $accountNumber]
            );
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'This account number is already registered']);
                exit;
            }

            // If user has no existing bank account, make this the primary account
            $hasAccount = $this->db->fetchOne("SELECT id FROM user_bank_accounts WHERE user_id = ? LIMIT 1", [$userId]);
            if (!$hasAccount) {
                $isPrimary = 1;
            }

            // If setting as primary, unset other primary accounts
            $tid = (int)$this->tenantId();
            if ($isPrimary) {
                $this->db->query("UPDATE user_bank_accounts SET is_primary = 0 WHERE user_id = ?", [$userId]);
            }

            // Add bank account
            $this->db->insert('user_bank_accounts', [
                'user_id' => $userId,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'ifsc_code' => $ifscCode,
                'account_holder' => $accountHolderName,
                'account_holder_name' => $accountHolderName,
                'branch_name' => $branchName,
                'account_type' => in_array($accountType, ['savings', 'current', 'joint']) ? $accountType : 'savings',
                'is_primary' => $isPrimary ? 1 : 0,
                'status' => 'active',
                'tenant_id' => $tid > 0 ? $tid : 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Audit log
            try {
                $this->db->query("
                    INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, created_at)
                    VALUES (?, 'bank_account_added', ?, ?, NOW())
                ", [$userId, json_encode(['bank_name' => $bankName, 'account_last4' => substr($accountNumber, -4)]), $_SERVER['REMOTE_ADDR'] ?? '']);
            } catch (\Throwable $e) {}

            echo json_encode(['success' => true, 'message' => 'Bank account added successfully']);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add bank account: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Set bank account as primary (AJAX)
     */
    public function setPrimaryBank()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $accountId = (int)($_POST['account_id'] ?? 0);

        if ($accountId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid account ID']);
            exit;
        }

        try {
            $account = $this->db->fetchOne("SELECT id FROM user_bank_accounts WHERE id = ? AND user_id = ?", [$accountId, $userId]);
            if (!$account) {
                echo json_encode(['success' => false, 'message' => 'Bank account not found or access denied']);
                exit;
            }

            $this->db->query("UPDATE user_bank_accounts SET is_primary = 0 WHERE user_id = ?", [$userId]);
            $this->db->query("UPDATE user_bank_accounts SET is_primary = 1 WHERE id = ? AND user_id = ?", [$accountId, $userId]);

            // Audit log
            try {
                $this->db->query("
                    INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, created_at)
                    VALUES (?, 'bank_account_primary_set', ?, ?, NOW())
                ", [$userId, json_encode(['account_id' => $accountId]), $_SERVER['REMOTE_ADDR'] ?? '']);
            } catch (\Throwable $e) {}

            echo json_encode(['success' => true, 'message' => 'Primary account updated successfully']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Delete bank account (AJAX)
     */
    public function deleteBankAccount()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $accountId = (int)($_POST['account_id'] ?? 0);

        if ($accountId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid account ID']);
            exit;
        }

        try {
            // Guard: check if bank account belongs to user
            $target = $this->db->fetchOne("SELECT id, is_primary FROM user_bank_accounts WHERE id = ? AND user_id = ?", [$accountId, $userId]);
            if (!$target) {
                echo json_encode(['success' => false, 'message' => 'Bank account not found or access denied']);
                exit;
            }

            // Guard: prevent deleting bank account tied to pending withdrawals
            $pendingWithdrawal = $this->db->fetchOne(
                "SELECT id FROM withdrawal_requests WHERE bank_account_id = ? AND status = 'pending' LIMIT 1",
                [$accountId]
            );
            if ($pendingWithdrawal) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete a bank account with an active pending withdrawal request']);
                exit;
            }

            $wasPrimary = ((int)($target['is_primary'] ?? 0) === 1);

            $this->db->query("DELETE FROM user_bank_accounts WHERE id = ? AND user_id = ?", [$accountId, $userId]);

            // If deleted account was primary, auto-promote next available active account
            if ($wasPrimary) {
                $nextPrimary = $this->db->fetchOne(
                    "SELECT id FROM user_bank_accounts WHERE user_id = ? AND status = 'active' ORDER BY is_primary DESC, id DESC LIMIT 1",
                    [$userId]
                );
                if ($nextPrimary) {
                    $this->db->query("UPDATE user_bank_accounts SET is_primary = 1 WHERE id = ?", [$nextPrimary['id']]);
                }
            }

            // Audit log
            try {
                $this->db->query("
                    INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, created_at)
                    VALUES (?, 'bank_account_deleted', ?, ?, NOW())
                ", [$userId, json_encode(['account_id' => $accountId, 'was_primary' => $wasPrimary]), $_SERVER['REMOTE_ADDR'] ?? '']);
            } catch (\Throwable $e) {}

            echo json_encode(['success' => true, 'message' => 'Bank account deleted successfully']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Update bank account (AJAX)
     */
    public function updateBankAccount()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $bankId = (int)($_POST['bank_id'] ?? 0);
        $bankName = trim($_POST['bank_name'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = strtoupper(trim($_POST['ifsc_code'] ?? ''));
        $accountHolder = trim($_POST['account_holder'] ?? '');
        $accountHolderName = trim($_POST['account_holder_name'] ?? $accountHolder);
        $branchName = trim($_POST['branch_name'] ?? '');
        $accountType = trim($_POST['account_type'] ?? 'savings');

        if ($bankId <= 0 || empty($bankName) || empty($accountNumber) || empty($ifscCode) || empty($accountHolderName)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
            exit;
        }

        if (!preg_match('/^[0-9]{9,18}$/', $accountNumber)) {
            echo json_encode(['success' => false, 'message' => 'Account number must be between 9 and 18 digits']);
            exit;
        }

        if (strlen($ifscCode) < 8 || strlen($ifscCode) > 15) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid IFSC code']);
            exit;
        }

        $validAccountType = in_array($accountType, ['savings', 'current', 'joint']) ? $accountType : 'savings';

        try {
            // Verify ownership
            $existing = $this->db->fetchOne("SELECT id FROM user_bank_accounts WHERE id = ? AND user_id = ?", [$bankId, $userId]);
            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Bank account not found or access denied']);
                exit;
            }

            // Check if updated account number conflicts with another bank account of the same user
            $conflict = $this->db->fetchOne(
                "SELECT id FROM user_bank_accounts WHERE user_id = ? AND account_number = ? AND id != ? LIMIT 1",
                [$userId, $accountNumber, $bankId]
            );
            if ($conflict) {
                echo json_encode(['success' => false, 'message' => 'Another account with this account number already exists']);
                exit;
            }

            // Update bank details
            $this->db->query("
                UPDATE user_bank_accounts 
                SET bank_name = ?, account_number = ?, ifsc_code = ?, account_holder = ?, account_holder_name = ?, branch_name = ?, account_type = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ", [$bankName, $accountNumber, $ifscCode, $accountHolderName, $accountHolderName, $branchName, $validAccountType, $bankId, $userId]);

            // Log activity
            try {
                $this->db->query("
                    INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, created_at)
                    VALUES (?, 'bank_account_updated', ?, ?, NOW())
                ", [$userId, json_encode([
                    'bank_id' => $bankId,
                    'bank_name' => $bankName,
                    'account_last4' => substr($accountNumber, -4)
                ]), $_SERVER['REMOTE_ADDR'] ?? '']);
            } catch (\Throwable $e) {}

            echo json_encode(['success' => true, 'message' => 'Bank account updated successfully']);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Referral Network Page
     */
    public function referralNetwork()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        try {
            // Get direct referrals
            $directReferrals = $this->db->fetchAll(
                "SELECT u.*, rr.reward_amount, rr.created_at as referral_date 
                FROM users u 
                LEFT JOIN referral_rewards rr ON u.id = rr.referred_id AND rr.referrer_id = ?
                WHERE u.referred_by = ? 
                ORDER BY u.created_at DESC",
                [$userId, $userId]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        // Get referral earnings
        $referralEarnings = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_referrals,
                SUM(reward_amount) as total_earnings,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_referrals
            FROM referral_rewards 
            WHERE referrer_id = ? AND status = 'credited'",
            [$userId]
        );

        $data = [
            'directReferrals' => $directReferrals,
            'referralEarnings' => $referralEarnings,
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'user_referral_code' => $this->db->fetchOne("SELECT referral_code FROM users WHERE id = ?", [$userId])['referral_code'] ?? ''
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/referral_network', $data);
    }

    /**
     * Wallet Analytics Page
     */
    public function analytics()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Get wallet
        $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);

        // Get monthly earnings (last 6 months)
        $monthlyEarnings = $this->db->fetchAll(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as credits,
                SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END) as debits
            FROM wallet_transactions 
            WHERE user_id = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC",
            [$userId]
        );

        // Get earnings by category
        $earningsByCategory = $this->db->fetchAll(
            "SELECT 
                transaction_category,
                SUM(amount) as total
            FROM wallet_transactions 
            WHERE user_id = ? AND transaction_type = 'credit'
            GROUP BY transaction_category",
            [$userId]
        );

        $data = [
            'wallet' => $wallet,
            'monthlyEarnings' => $monthlyEarnings,
            'earningsByCategory' => $earningsByCategory,
            'user_name' => $_SESSION['user_name'] ?? 'User'
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/analytics', $data);
    }

    /**
     * Associate Wallet Dashboard
     */
    public function associateWallet()
    {
        @session_start();

        $associateId = $_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? null;
        if (!$associateId) {
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        }

        // Get wallet information (using associate_id as user_id)
        $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$associateId]);

        if (!$wallet) {
            // Create wallet if not exists
            $tid = (int)$this->tenantId();
            $this->db->insert('wallet_points', [
                'user_id' => $associateId,
                'points_balance' => 0.00,
                'total_earned' => 0.00,
                'total_used' => 0.00,
                'total_transferred_to_emi' => 0.00,
                'referral_earnings' => 0.00,
                'commission_earnings' => 0.00,
                'bonus_earnings' => 0.00,
                'status' => 'active',
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$associateId]);
        }

        // Get recent transactions
        $recentTransactions = $this->db->fetchAll(
            "SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$associateId]
        );

        // Get commission earnings from mlm_commission_ledger
        $commissionStats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_commissions,
                COALESCE(SUM(amount), 0) as total_earned
            FROM mlm_commission_ledger 
            WHERE beneficiary_user_id = ? AND status IN ('paid','approved')",
            [$associateId]
        );

        // Get network stats
        $networkStats = $this->db->fetchOne(
            "SELECT 
                (SELECT COUNT(*) FROM network_tree WHERE parent_id = ?) as direct_referrals,
                (SELECT COUNT(*) FROM network_tree WHERE associate_id = ? OR parent_id = ?) as network_size",
            [$associateId, $associateId, $associateId]
        );

        $config = [];
        try {
            // Get wallet configuration
            $config = $this->db->fetchAll("SELECT * FROM wallet_configuration");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $walletConfig = [];
        foreach ($config as $item) {
            $walletConfig[$item['setting_key']] = $item['setting_value'];
        }

        $userReferralCode = '';
        try {
            $userData = $this->db->fetchOne("SELECT referral_code FROM users WHERE id = ?", [$associateId]);
            $userReferralCode = $userData['referral_code'] ?? '';
        } catch (\Throwable $e) { error_log('WalletController exception: ' . $e->getMessage()); }

        $data = [
            'wallet' => $wallet,
            'recentTransactions' => $recentTransactions,
            'commissionStats' => $commissionStats,
            'networkStats' => $networkStats,
            'referralStats' => ['total_referrals' => $networkStats['direct_referrals'] ?? 0],
            'config' => $walletConfig,
            'user_name' => $_SESSION['associate_name'] ?? $_SESSION['user_name'] ?? 'Associate',
            'user_email' => $_SESSION['associate_email'] ?? $_SESSION['user_email'] ?? '',
            'user_referral_code' => $userReferralCode,
            'referral_code' => $userReferralCode,
            'is_associate' => true,
            'page_title' => 'Wallet Dashboard - Associate'
        ];

        $this->layout = 'layouts/associate';
        $this->render('wallet/dashboard', $data);
    }
}

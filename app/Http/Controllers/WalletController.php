<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;

#[\AllowDynamicProperties]
class WalletController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
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
            $walletConfig[$item['config_key']] = $item['config_value'];
        }

        $userReferralCode = '';
        try {
            $userData = $this->db->fetchOne("SELECT referral_code FROM users WHERE id = ?", [$userId]);
            $userReferralCode = $userData['referral_code'] ?? '';
        } catch (\Exception $e) { error_log('WalletController exception: ' . $e->getMessage()); }

        $data = [
            'wallet' => $wallet,
            'recentTransactions' => $recentTransactions,
            'referralStats' => $referralStats,
            'config' => $walletConfig,
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
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Get wallet balance
        $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);

        // Get user's EMIs (if EMI table exists)
        $emis = [];
        try {
            $emis = $this->db->fetchAll(
                "SELECT * FROM emi_schedule WHERE customer_id = ? AND status = 'pending' ORDER BY due_date ASC",
                [$userId]
            );
        } catch (\Exception $e) {
            error_log('WalletController emi fetch: ' . $e->getMessage());
        }

        $data = [
            'wallet' => $wallet,
            'emis' => $emis,
            'user_name' => $_SESSION['user_name'] ?? 'User'
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/transfer_emi', $data);
    }

    /**
     * Process EMI Transfer
     */
    public function processEmiTransfer()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $emiId = $_POST['emi_id'] ?? 0;
        $amount = floatval($_POST['amount'] ?? 0);

        try {
            // Get wallet
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);

            if (!$wallet || $wallet['points_balance'] < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance']);
                exit;
            }

            // Deduct from wallet
            $newBalance = $wallet['points_balance'] - $amount;
            $newUsed = $wallet['total_used'] + $amount;
            $newTransferred = $wallet['total_transferred_to_emi'] + $amount;

            $this->db->query(
                "UPDATE wallet_points SET points_balance = ?, total_used = ?, total_transferred_to_emi = ?, updated_at = ? WHERE user_id = ?",
                [$newBalance, $newUsed, $newTransferred, date('Y-m-d H:i:s'), $userId]
            );

            // Create transaction record
            $transactionId = $this->db->insert('wallet_transactions', [
                'user_id' => $userId,
                'transaction_type' => 'debit',
                'transaction_category' => 'emi_transfer',
                'amount' => $amount,
                'balance_before' => $wallet['points_balance'],
                'balance_after' => $newBalance,
                'description' => "EMI Transfer - EMI #$emiId",
                'reference_id' => $emiId,
                'reference_type' => 'emi',
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Create EMI transfer record
            $this->db->insert('wallet_emi_transfers', [
                'user_id' => $userId,
                'emi_id' => $emiId,
                'emi_amount' => $amount,
                'wallet_amount_used' => $amount,
                'transaction_id' => $transactionId,
                'transfer_status' => 'completed',
                'transferred_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            echo json_encode(['success' => true, 'message' => 'EMI transfer successful']);
            exit;
        } catch (\Exception $e) {
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

        // Get wallet balance
        $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);

        // Get bank accounts
        $bankAccounts = [];
        try {
            $bankAccounts = $this->db->fetchAll(
                "SELECT * FROM user_bank_accounts WHERE user_id = ? AND status = 'active' ORDER BY is_primary DESC",
                [$userId]
            );
        } catch (\Exception $e) {
            error_log('WalletController bank accounts: ' . $e->getMessage());
        }

        // Get withdrawal history
        $withdrawals = [];
        try {
            $withdrawals = $this->db->fetchAll(
                "SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
                [$userId]
            );
        } catch (\Exception $e) {
            error_log('WalletController withdrawals: ' . $e->getMessage());
        }

        $data = [
            'wallet' => $wallet,
            'bankAccounts' => $bankAccounts,
            'withdrawals' => $withdrawals,
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'page_title' => 'Withdrawal Request'
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

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $bankAccountId = $_POST['bank_account_id'] ?? 0;
        $amount = floatval($_POST['amount'] ?? 0);

        try {
            // Get wallet
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ? LIMIT 1", [$userId]);

            if (!$wallet || $wallet['points_balance'] < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance']);
                exit;
            }

            // Get minimum withdrawal amount from config
            $minWithdrawal = $this->db->fetchOne("SELECT config_value FROM wallet_configuration WHERE config_key = 'minimum_withdrawal'")['config_value'] ?? 500;

            if ($amount < $minWithdrawal) {
                echo json_encode(['success' => false, 'message' => "Minimum withdrawal amount is ₹$minWithdrawal"]);
                exit;
            }

            // Create withdrawal request
            $this->db->insert('withdrawal_requests', [
                'user_id' => $userId,
                'bank_account_id' => $bankAccountId,
                'amount' => $amount,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            echo json_encode(['success' => true, 'message' => 'Withdrawal request submitted successfully']);
            exit;
        } catch (\Exception $e) {
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
            header('Location: ' . BASE_URL . '/login');
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
        } catch (\Exception $e) {
            error_log('WalletController bank accounts list: ' . $e->getMessage());
        }

        $data = [
            'bankAccounts' => $bankAccounts,
            'user_name' => $_SESSION['user_name'] ?? 'User'
        ];

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/base';
        $this->render('wallet/bank_accounts', $data);
    }

    /**
     * Add Bank Account
     */
    public function addBankAccount()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $bankName = $_POST['bank_name'] ?? '';
        $accountNumber = $_POST['account_number'] ?? '';
        $ifscCode = $_POST['ifsc_code'] ?? '';
        $accountHolder = $_POST['account_holder'] ?? '';
        $isPrimary = $_POST['is_primary'] ?? '0';

        try {
            // Check if bank accounts table exists
            $tableExists = $this->db->fetchOne("SHOW TABLES LIKE 'user_bank_accounts'");

            if (!$tableExists) {
                // Create table
                $this->db->query("
                    CREATE TABLE user_bank_accounts (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        bank_name VARCHAR(100) NOT NULL,
                        account_number VARCHAR(50) NOT NULL,
                        ifsc_code VARCHAR(20) NOT NULL,
                        account_holder VARCHAR(100) NOT NULL,
                        is_primary TINYINT(1) DEFAULT 0,
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }

            // If setting as primary, unset other primary accounts
            if ($isPrimary) {
                $this->db->query("UPDATE user_bank_accounts SET is_primary = 0 WHERE user_id = ?", [$userId]);
            }

            // Add bank account
            $this->db->insert('user_bank_accounts', [
                'user_id' => $userId,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'ifsc_code' => $ifscCode,
                'account_holder' => $accountHolder,
                'is_primary' => $isPrimary,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            echo json_encode(['success' => true, 'message' => 'Bank account added successfully']);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add bank account: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Set bank account as primary (AJAX)
     */
    public function setPrimaryBank()
    {
        @session_start();
        header('Content-Type: application/json');

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
            $this->db->query("UPDATE user_bank_accounts SET is_primary = 0 WHERE user_id = ?", [$userId]);
            $this->db->query("UPDATE user_bank_accounts SET is_primary = 1 WHERE id = ? AND user_id = ?", [$accountId, $userId]);
            echo json_encode(['success' => true, 'message' => 'Primary account updated']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Delete bank account (AJAX)
     */
    public function deleteBankAccount()
    {
        @session_start();
        header('Content-Type: application/json');

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
            $this->db->query("DELETE FROM user_bank_accounts WHERE id = ? AND user_id = ?", [$accountId, $userId]);
            echo json_encode(['success' => true, 'message' => 'Bank account deleted']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Update bank account (AJAX)
     */
    public function updateBankAccount()
    {
        @session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $bankId = (int)($_POST['bank_id'] ?? 0);
        $bankName = trim($_POST['bank_name'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = trim($_POST['ifsc_code'] ?? '');
        $accountHolder = trim($_POST['account_holder'] ?? '');
        $branchName = trim($_POST['branch_name'] ?? '');
        $accountType = trim($_POST['account_type'] ?? 'savings');

        if ($bankId <= 0 || empty($bankName) || empty($accountNumber) || empty($ifscCode) || empty($accountHolder)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
            exit;
        }

        try {
            // Verify ownership
            $existing = $this->db->fetchOne("SELECT id FROM user_bank_accounts WHERE id = ? AND user_id = ?", [$bankId, $userId]);
            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Bank account not found or access denied']);
                exit;
            }

            // Log the change for audit
            $oldData = $this->db->fetchOne("SELECT * FROM user_bank_accounts WHERE id = ?", [$bankId]);
            
            $this->db->query("
                UPDATE user_bank_accounts 
                SET bank_name = ?, account_number = ?, ifsc_code = ?, account_holder = ?, branch_name = ?, account_type = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ", [$bankName, $accountNumber, $ifscCode, $accountHolder, $branchName, $accountType, $bankId, $userId]);

            // Log activity
            try {
                $this->db->query("
                    INSERT INTO user_activity_log (user_id, action, details, ip_address, created_at)
                    VALUES (?, 'bank_account_updated', ?, ?, NOW())
                ", [$userId, json_encode(['bank_id' => $bankId, 'changes' => 'Bank details updated']), $_SERVER['REMOTE_ADDR'] ?? '']);
            } catch (\Throwable $e) {
            // Activity log table might not exist, ignore
            error_log($e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Bank account updated successfully']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()]);
        }
        exit;
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
            $walletConfig[$item['config_key']] = $item['config_value'];
        }

        $userReferralCode = '';
        try {
            $userData = $this->db->fetchOne("SELECT referral_code FROM users WHERE id = ?", [$associateId]);
            $userReferralCode = $userData['referral_code'] ?? '';
        } catch (\Exception $e) { error_log('WalletController exception: ' . $e->getMessage()); }

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

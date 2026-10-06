<?php
namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Services\WalletActivationService;

class WalletActivationController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    private WalletActivationService $walletActivationService;

    public function __construct()
    {
        parent::__construct();
        $this->walletActivationService = new WalletActivationService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function requireAssociate(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        }
    }

    private function jsonInput(): array
    {
        static $parsed = null;
        if ($parsed !== null) return $parsed;
        $parsed = $_POST;
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) $parsed = array_merge($parsed, $json);
        }
        return $parsed;
    }

    public function showPackages()
    {
        $this->requireAssociate();
        $userId = (int)$_SESSION['user_id'];
        $packages = $this->walletActivationService->getActivePackages();
        $userPurchase = $this->walletActivationService->getUserPurchase($userId);
        $walletType = $this->walletActivationService->getWalletType($userId);
        $isActivated = $this->walletActivationService->isWalletActivated($userId);

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/associate/wallet_activation_packages.php';
    }

    public function purchase()
    {
        $this->requireAssociate();
        $in = $this->jsonInput();
        $packageId = (int)($in['package_id'] ?? 0);
        $paymentMode = $in['payment_mode'] ?? 'razorpay';

        if (!$packageId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid package']);
            exit;
        }

        $result = $this->walletActivationService->purchasePackage(
            (int)$_SESSION['user_id'],
            $packageId,
            $paymentMode
        );

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function verifyPayment()
    {
        $this->requireAssociate();
        $in = $this->jsonInput();
        $purchaseId = (int)($in['purchase_id'] ?? 0);
        $razorpayPaymentId = $in['razorpay_payment_id'] ?? '';
        $razorpayOrderId = $in['razorpay_order_id'] ?? '';
        $razorpaySignature = $in['razorpay_signature'] ?? '';

        if (!$purchaseId || !$razorpayPaymentId || !$razorpayOrderId || !$razorpaySignature) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid payment data']);
            exit;
        }

        try {
            $razorpay = new \App\Services\Gateway\RazorpayService();
            $verified = $razorpay->verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

            if (!$verified) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
                exit;
            }

            // Activate purchase for associate wallet (user_wallets)
            $result = $this->walletActivationService->activatePurchaseUserWallet($purchaseId, (int)$_SESSION['user_id']);

            header('Content-Type: application/json');
            echo json_encode($result);

        } catch (\Throwable $e) {
            error_log("Associate wallet activation verify error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Activation failed']);
        }
    }

    public function myWallet()
    {
        $this->requireAssociate();
        $userId = (int)$_SESSION['user_id'];
        $isActivated = $this->walletActivationService->isWalletActivated($userId);
        $userPurchase = $this->walletActivationService->getUserPurchase($userId);
        $walletType = $this->walletActivationService->getWalletType($userId);
        $packages = $this->walletActivationService->getActivePackages();

        // Get user_wallets balance
        $walletBalance = 0;
        try {
            $tid = \App\Core\Middleware\TenantContext::getId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = array_merge([$userId], $tid > 1 ? [$tid] : []);
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT balance FROM user_wallets WHERE user_id = ?" . $tenantWhere . " LIMIT 1");
            $stmt->execute($params);
            $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);
            $walletBalance = $wallet ? (float)$wallet['balance'] : 0;
        } catch (\Throwable $e) {
            error_log("Wallet balance fetch failed: " . $e->getMessage());
        }

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/associate/wallet_activation_my_wallet.php';
    }

    public function transactions()
    {
        $this->requireAssociate();
        $userId = (int)$_SESSION['user_id'];
        
        $tid = \App\Core\Middleware\TenantContext::getId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$userId], $tid > 1 ? [$tid] : []);
        
        // Build filters (filterParams holds ONLY filter values; user/tenant come from $params)
        $where = [];
        $filterParams = [];
        if (!empty($_GET['type']) && in_array($_GET['type'], ['credit', 'debit', 'transfer'], true)) {
            $where[] = "transaction_type = ?";
            $filterParams[] = $_GET['type'];
        }
        if (!empty($_GET['category'])) {
            $where[] = "transaction_category = ?";
            $filterParams[] = $_GET['category'];
        }
        if (!empty($_GET['from_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $filterParams[] = $_GET['from_date'];
        }
        if (!empty($_GET['to_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $filterParams[] = $_GET['to_date'];
        }
        if (!empty($_GET['search'])) {
            $where[] = "(description LIKE ? OR reference_id LIKE ?)";
            $like = '%' . $_GET['search'] . '%';
            $filterParams[] = $like;
            $filterParams[] = $like;
        }
        $whereSql = $where ? ' AND ' . implode(' AND ', $where) : '';
        
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        
        // Get transactions
        $stmt = $db->prepare(
            "SELECT * FROM wallet_transactions WHERE user_id = ?" . $tenantWhere . $whereSql . " ORDER BY created_at DESC LIMIT 100"
        );
        $stmt->execute(array_merge($params, $filterParams));
        $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        
        // Get wallet balance
        $stmt = $db->prepare("SELECT balance FROM user_wallets WHERE user_id = ?" . $tenantWhere . " LIMIT 1");
        $stmt->execute($params);
        $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);
        $walletBalance = $wallet ? (float)$wallet['balance'] : 0;
        
        // Get summary
        $stmt = $db->prepare(
            "SELECT
                COUNT(*) as total_transactions,
                SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END) as total_credited,
                SUM(CASE WHEN transaction_type IN ('debit','transfer') THEN amount ELSE 0 END) as total_debited
             FROM wallet_transactions WHERE user_id = ?" . $tenantWhere
        );
        $stmt->execute($params);
        $summary = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        
        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/associate/wallet_transactions.php';
    }

    public function exportTransactions()
    {
        $this->requireAssociate();
        $userId = (int)$_SESSION['user_id'];

        $tid = \App\Core\Middleware\TenantContext::getId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$userId], $tid > 1 ? [$tid] : []);

        // Build filters (filterParams holds ONLY filter values; user/tenant come from $params)
        $where = [];
        $filterParams = [];
        if (!empty($_GET['type']) && in_array($_GET['type'], ['credit', 'debit', 'transfer'], true)) {
            $where[] = "transaction_type = ?";
            $filterParams[] = $_GET['type'];
        }
        if (!empty($_GET['category'])) {
            $where[] = "transaction_category = ?";
            $filterParams[] = $_GET['category'];
        }
        if (!empty($_GET['from_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $filterParams[] = $_GET['from_date'];
        }
        if (!empty($_GET['to_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $filterParams[] = $_GET['to_date'];
        }
        if (!empty($_GET['search'])) {
            $where[] = "(description LIKE ? OR reference_id LIKE ?)";
            $like = '%' . $_GET['search'] . '%';
            $filterParams[] = $like;
            $filterParams[] = $like;
        }
        $whereSql = $where ? ' AND ' . implode(' AND ', $where) : '';

        $db = \App\Core\Database\Database::getInstance()->getConnection();

        // Get transactions
        $stmt = $db->prepare(
            "SELECT * FROM wallet_transactions WHERE user_id = ?" . $tenantWhere . $whereSql . " ORDER BY created_at DESC"
        );
        $stmt->execute(array_merge($params, $filterParams));
        $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Export as CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="wallet_transactions_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Type', 'Category', 'Amount', 'Description', 'Balance After', 'Reference ID', 'Reference Type']);
        
        foreach ($transactions as $tx) {
            $type = $tx['transaction_type'] ?? 'credit';
            $isCredit = ($type === 'credit');
            fputcsv($output, [
                date('d M Y H:i', strtotime($tx['created_at'] ?? 'now')),
                ucfirst($type),
                ucfirst($tx['transaction_category'] ?? ''),
                ($isCredit ? '+' : '-') . '₹' . number_format((float)($tx['amount'] ?? 0), 2),
                $tx['description'] ?? '-',
                '₹' . number_format((float)($tx['balance_after'] ?? 0), 2),
                $tx['reference_id'] ?? '-',
                $tx['reference_type'] ?? '-',
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function withdrawal()
    {
        $this->requireAssociate();
        $userId = (int)$_SESSION['user_id'];

        $tid = \App\Core\Middleware\TenantContext::getId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$userId], $tid > 1 ? [$tid] : []);

        $db = \App\Core\Database\Database::getInstance()->getConnection();

        // Get wallet balance
        $stmt = $db->prepare("SELECT * FROM user_wallets WHERE user_id = ?" . $tenantWhere . " LIMIT 1");
        $stmt->execute($params);
        $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);
        $walletBalance = $wallet ? (float)$wallet['balance'] : 0;

        // Get bank accounts
        $bankAccounts = [];
        try {
            $stmt = $db->prepare("SELECT * FROM user_bank_accounts WHERE user_id = ? AND status = 'active' ORDER BY is_primary DESC");
            $stmt->execute([$userId]);
            $bankAccounts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Bank accounts fetch failed: " . $e->getMessage());
        }

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/associate/wallet_withdrawal.php';
    }

    public function processWithdrawal()
    {
        $this->requireAssociate();
        $userId = (int)$_SESSION['user_id'];
        $bankAccountId = (int)($_POST['bank_account_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);

        if ($amount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid amount']);
            exit;
        }

        $tid = \App\Core\Middleware\TenantContext::getId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = array_merge([$userId], $tid > 1 ? [$tid] : []);

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Get wallet
            $stmt = $db->prepare("SELECT * FROM user_wallets WHERE user_id = ?" . $tenantWhere . " LIMIT 1");
            $stmt->execute($params);
            $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$wallet || (float)$wallet['balance'] < $amount) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance']);
                exit;
            }

            // Verify bank account belongs to user
            $stmt = $db->prepare("SELECT * FROM user_bank_accounts WHERE id = ? AND user_id = ? AND status = 'active'");
            $stmt->execute([$bankAccountId, $userId]);
            $bankAccount = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$bankAccount) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Invalid bank account']);
                exit;
            }

            // Check min withdrawal
            if ($amount < 1000) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Minimum withdrawal is ₹1,000']);
                exit;
            }

            // Deduct from wallet
            $newBalance = (float)$wallet['balance'] - $amount;
            $stmt = $db->prepare("UPDATE user_wallets SET balance = ?, total_debited = total_debited + ?, updated_at = NOW() WHERE user_id = ?" . $tenantWhere);
            $stmt->execute([$newBalance, $amount, $userId]);

            // Create withdrawal request record
            $withdrawalId = $db->insert('withdrawal_requests', array_merge([
                'user_id' => $userId,
                'bank_account_id' => $bankAccountId,
                'amount' => $amount,
                'status' => 'pending',
                'remarks' => 'Associate wallet withdrawal',
            ], $this->tenantInsertData()));

            // Record transaction (real schema: transaction_type enum + category enum + INT reference)
            $db->insert('wallet_transactions', array_merge([
                'user_id' => $userId,
                'transaction_type' => 'debit',
                'transaction_category' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => (float)$wallet['balance'],
                'balance_after' => $newBalance,
                'description' => 'Withdrawal to bank: ' . $bankAccount['account_holder_name'] . ' - ' . substr($bankAccount['account_number'], -4),
                'reference_id' => (int)$withdrawalId,
                'reference_type' => 'withdrawal_request',
            ], $this->tenantInsertData()));

            $db->commit();

            echo json_encode(['success' => true, 'message' => 'Withdrawal request submitted successfully']);

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Associate wallet withdrawal error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Withdrawal failed']);
        }
    }
}
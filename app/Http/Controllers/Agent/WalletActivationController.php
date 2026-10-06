<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;
use App\Services\WalletActivationService;

class WalletActivationController extends BaseController
{
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

    private function requireAgent(): void
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'agent') {
            header('Location: ' . BASE_URL . '/agent/login');
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
        $this->requireAgent();
        $userId = (int)$_SESSION['user_id'];
        $packages = $this->walletActivationService->getActivePackages();
        $userPurchase = $this->walletActivationService->getUserPurchase($userId);
        $walletType = $this->walletActivationService->getWalletType($userId);
        $isActivated = $this->walletActivationService->isWalletActivated($userId);

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/agent/wallet_activation_packages.php';
    }

    public function purchase()
    {
        $this->requireAgent();
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
        $this->requireAgent();
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

            // Activate purchase for agent wallet (user_wallets)
            $result = $this->walletActivationService->activatePurchaseUserWallet($purchaseId, (int)$_SESSION['user_id']);

            header('Content-Type: application/json');
            echo json_encode($result);

        } catch (\Throwable $e) {
            error_log("Agent wallet activation verify error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Activation failed']);
        }
    }

    public function myWallet()
    {
        $this->requireAgent();
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

        include __DIR__ . '/../../../views/agent/wallet_activation_my_wallet.php';
    }
}
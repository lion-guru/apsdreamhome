<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\WalletActivationService;
use App\Core\Middleware\TenantContext;

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

    public function showPackages()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $packages = $this->walletActivationService->getActivePackages();
        $userPurchase = $this->walletActivationService->getUserPurchase((int)$_SESSION['user_id']);

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/auth/wallet_activation_packages.php';
    }

    /**
     * Read request input: JSON body first (fetch callers), $_POST fallback.
     * Named jsonInput() — BaseController already owns input().
     */
    private function jsonInput(): array
    {
        static $parsed = null;
        if ($parsed !== null) {
            return $parsed;
        }
        $parsed = $_POST;
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $parsed = array_merge($parsed, $json);
            }
        }
        return $parsed;
    }

    public function purchase()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

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
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

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

            // Activate purchase
            $result = $this->walletActivationService->activatePurchase($purchaseId, (int)$_SESSION['user_id']);

            header('Content-Type: application/json');
            echo json_encode($result);

        } catch (\Throwable $e) {
            error_log("Wallet activation verify error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Activation failed']);
        }
    }

    public function myWallet()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $isActivated = $this->walletActivationService->isWalletActivated($userId);
        $userPurchase = $this->walletActivationService->getUserPurchase($userId);
        $packages = $this->walletActivationService->getActivePackages();

        $base = BASE_URL;
        $csrf_token = $this->getCsrfToken();

        include __DIR__ . '/../../../views/auth/wallet_activation_my_wallet.php';
    }
}
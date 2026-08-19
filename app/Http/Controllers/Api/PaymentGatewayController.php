<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class PaymentGatewayController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function initiatePhonePe()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? uniqid('PH_');
        $userId = $GLOBALS['api_user_id'] ?? $data['user_id'] ?? null;

        echo json_encode([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'gateway' => 'phonepe',
                'redirect_url' => "https://mercury-t2.phonepe.com/transact?orderId=$orderId",
                'merchant_id' => 'APS_DREAM_HOME'
            ]
        ]);
    }

    public function verifyPhonePe($transactionId = null)
    {
        header('Content-Type: application/json');
        $transactionId = $transactionId ?? $_GET['transactionId'] ?? '';

        echo json_encode([
            'success' => true,
            'data' => [
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'verified_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function phonePeWebhook()
    {
        header('Content-Type: application/json');
        $payload = json_decode(file_get_contents('php://input'), true);
        error_log('PhonePe webhook received: ' . json_encode($payload));
        echo json_encode(['success' => true]);
    }

    public function initiateGPay()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? uniqid('GP_');

        echo json_encode([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'gateway' => 'gpay',
                'deep_link' => "tez://upi/pay?pa=apsdreamhome@upi&pn=APS%20Dream%20Home&am=$amount&tr=$orderId"
            ]
        ]);
    }

    public function generateQRCode()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $amount = $data['amount'] ?? 0;
        $orderId = $data['order_id'] ?? uniqid('UPI_');

        $upiString = "upi://pay?pa=apsdreamhome@upi&pn=APS%20Dream%20Home&am=$amount&tr=$orderId&cu=INR";

        echo json_encode([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'upi_string' => $upiString,
                'qr_data' => base64_encode($upiString)
            ]
        ]);
    }

    public function upiCallback()
    {
        header('Content-Type: application/json');
        $payload = json_decode(file_get_contents('php://input'), true);
        error_log('UPI callback received: ' . json_encode($payload));
        echo json_encode(['success' => true, 'status' => 'received']);
    }

    public function getStatus($orderId = null)
    {
        header('Content-Type: application/json');
        $orderId = $orderId ?? $_GET['orderId'] ?? '';

        echo json_encode([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'status' => 'pending',
                'amount' => 0,
                'gateway' => 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function getPaymentMethods()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                ['id' => 'phonepe', 'name' => 'PhonePe', 'enabled' => true],
                ['id' => 'gpay', 'name' => 'Google Pay', 'enabled' => true],
                ['id' => 'paytm', 'name' => 'Paytm', 'enabled' => true],
                ['id' => 'razorpay', 'name' => 'Razorpay', 'enabled' => true],
                ['id' => 'upi', 'name' => 'UPI', 'enabled' => true]
            ]
        ]);
    }
}

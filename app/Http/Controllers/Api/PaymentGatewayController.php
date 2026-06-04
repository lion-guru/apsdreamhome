<?php

namespace App\Http\Controllers\Api;

use App\Services\Payment\PhonePeGatewayService;
use App\Services\Payment\GooglePayService;
use App\Core\Database\Database;

/**
 * Payment Gateway API Controller
 * PhonePe and Google Pay integration endpoints
 */
class PaymentGatewayController extends BaseApiController
{
    private $database;
    
    public function __construct()
    {
        parent::__construct();
        $this->database = Database::getInstance();
    }
    
    /**
     * Initiate PhonePe Payment
     * POST /api/payment/phonepe/initiate
     */
    public function initiatePhonePe(): void
    {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            // Validate required fields
            if (empty($data['amount']) || empty($data['order_id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Missing required fields: amount, order_id'
                ]);
                return;
            }
            
            $phonePe = new PhonePeGatewayService();
            $result = $phonePe->initiatePayment([
                'order_id' => $data['order_id'],
                'amount' => floatval($data['amount']),
                'customer_id' => $data['customer_id'] ?? null,
                'redirect_url' => $data['redirect_url'] ?? '/payment/phonepe/callback',
                'callback_url' => $data['callback_url'] ?? '/api/payment/phonepe/webhook'
            ]);
            
            // Log transaction
            $this->logTransaction('phonepe', $data['order_id'], $data['amount'], 'initiated');
            
            echo json_encode($result);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Verify PhonePe Payment
     * GET /api/payment/phonepe/verify/{transactionId}
     */
    public function verifyPhonePe(string $transactionId): void
    {
        header('Content-Type: application/json');
        
        try {
            $phonePe = new PhonePeGatewayService();
            $result = $phonePe->verifyPayment($transactionId);
            
            // Update transaction status in database
            if ($result['success']) {
                $this->updateTransactionStatus($transactionId, 'completed', $result);
            }
            
            echo json_encode($result);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * PhonePe Webhook Handler
     * POST /api/payment/phonepe/webhook
     */
    public function phonePeWebhook(): void
    {
        header('Content-Type: application/json');
        
        try {
            $headers = getallheaders();
            $payload = file_get_contents('php://input');
            $data = json_decode($payload, true);
            
            $phonePe = new PhonePeGatewayService();
            
            // Validate webhook signature
            if (!$phonePe->validateWebhook($headers, $payload)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid signature']);
                return;
            }
            
            // Process webhook data
            if (isset($data['merchantTransactionId'])) {
                $this->updateTransactionStatus(
                    $data['merchantTransactionId'],
                    $data['status'] ?? 'unknown',
                    $data
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Webhook processed']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Initiate Google Pay UPI Intent
     * POST /api/payment/gpay/initiate
     */
    public function initiateGPay(): void
    {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (empty($data['amount']) || empty($data['order_id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Missing required fields: amount, order_id'
                ]);
                return;
            }
            
            $gPay = new GooglePayService();
            
            // Generate UPI intent based on platform
            if (!empty($data['platform']) && $data['platform'] === 'ios') {
                $result = $gPay->generateiOSDeepLink([
                    'order_id' => $data['order_id'],
                    'amount' => floatval($data['amount']),
                    'note' => $data['note'] ?? 'Payment to APS Dream Home'
                ]);
            } else {
                $result = $gPay->generateUniversalUpiIntent([
                    'order_id' => $data['order_id'],
                    'amount' => floatval($data['amount']),
                    'note' => $data['note'] ?? 'Payment to APS Dream Home'
                ]);
            }
            
            // Log transaction
            $this->logTransaction('gpay', $data['order_id'], $data['amount'], 'initiated');
            
            echo json_encode($result);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate UPI QR Code
     * POST /api/payment/upi/qrcode
     */
    public function generateQRCode(): void
    {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (empty($data['amount'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Missing required field: amount'
                ]);
                return;
            }
            
            $gPay = new GooglePayService();
            $qrData = $gPay->generateQRCodeData([
                'order_id' => $data['order_id'] ?? 'QR' . time(),
                'amount' => floatval($data['amount']),
                'note' => $data['note'] ?? 'Payment to APS Dream Home'
            ]);
            
            echo json_encode([
                'success' => true,
                'qr_data' => $qrData,
                'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrData)
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle UPI Callback
     * POST /api/payment/upi/callback
     */
    public function upiCallback(): void
    {
        header('Content-Type: application/json');
        
        try {
            $data = $_POST;
            
            $gPay = new GooglePayService();
            $result = $gPay->handleCallback($data);
            
            if ($result['success']) {
                $this->updateTransactionStatus(
                    $result['transaction_id'],
                    $result['status'],
                    $result
                );
            }
            
            echo json_encode($result);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get Payment Status
     * GET /api/payment/status/{orderId}
     */
    public function getStatus(string $orderId): void
    {
        header('Content-Type: application/json');
        
        try {
            $db = $this->database->getConnection();
            $stmt = $db->prepare("SELECT * FROM payment_transactions WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$orderId]);
            $transaction = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($transaction) {
                echo json_encode([
                    'success' => true,
                    'transaction' => $transaction
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Transaction not found'
                ]);
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get Supported Payment Methods
     * GET /api/payment/methods
     */
    public function getPaymentMethods(): void
    {
        header('Content-Type: application/json');
        
        $phonePe = new PhonePeGatewayService();
        $gPay = new GooglePayService();
        
        echo json_encode([
            'success' => true,
            'methods' => [
                'phonepe' => [
                    'name' => 'PhonePe',
                    'type' => 'upi',
                    'supported' => $phonePe->getSupportedMethods()
                ],
                'gpay' => [
                    'name' => 'Google Pay',
                    'type' => 'upi_intent',
                    'merchant' => $gPay->getMerchantDetails()
                ]
            ]
        ]);
    }
    
    /**
     * Log transaction to database
     */
    private function logTransaction(string $gateway, string $orderId, float $amount, string $status): void
    {
        try {
            $db = $this->database->getConnection();
            $stmt = $db->prepare("
                INSERT INTO payment_transactions 
                (gateway, order_id, amount, status, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$gateway, $orderId, $amount, $status]);
        } catch (\Exception $e) {
            // Silently log error
            error_log("Failed to log transaction: " . $e->getMessage());
        }
    }
    
    /**
     * Update transaction status
     */
    private function updateTransactionStatus(string $orderId, string $status, array $data): void
    {
        try {
            $db = $this->database->getConnection();
            $stmt = $db->prepare("
                UPDATE payment_transactions 
                SET status = ?, response_data = ?, updated_at = NOW() 
                WHERE order_id = ?
            ");
            $stmt->execute([$status, json_encode($data), $orderId]);
        } catch (\Exception $e) {
            error_log("Failed to update transaction: " . $e->getMessage());
        }
    }
}

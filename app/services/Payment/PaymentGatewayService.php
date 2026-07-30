<?php

namespace App\Services\Payment;

use App\Core\Database\Database;
use App\Services\Gateway\RazorpayService;

/**
 * Payment Gateway Service
 * Multi-gateway integration: Razorpay, PayU, Stripe
 *
 * Razorpay logic is delegated to App\Services\Gateway\RazorpayService
 * (the unified client). This class remains as a thin router / facade so
 * legacy call-sites (createOrder, verifyPayment, processRefund, etc.)
 * continue to work.
 */
class PaymentGatewayService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    private $gateway;
    private $config;
    
    public function __construct(string $gateway = 'razorpay')
    {
        $this->database = Database::getInstance();
        $this->gateway = $gateway;
        $this->config = $this->getGatewayConfig($gateway);
        $this->ensureTablesExist();
    }
    
    /**
     * Get gateway configuration
     */
    private function getGatewayConfig(string $gateway): array
    {
        $configs = [
            'razorpay' => [
                'key_id' => $_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_key',
                'key_secret' => $_ENV['RAZORPAY_KEY_SECRET'] ?? 'secret',
                'api_url' => 'https://api.razorpay.com/v1',
                'currency' => 'INR'
            ],
            'payu' => [
                'merchant_key' => $_ENV['PAYU_MERCHANT_KEY'] ?? 'test_key',
                'salt' => $_ENV['PAYU_SALT'] ?? 'test_salt',
                'api_url' => 'https://test.payu.in', // Change to secure.payu.in for prod
                'currency' => 'INR'
            ],
            'stripe' => [
                'public_key' => $_ENV['STRIPE_PUBLIC_KEY'] ?? 'pk_test_key',
                'secret_key' => $_ENV['STRIPE_SECRET_KEY'] ?? 'sk_test_key',
                'currency' => 'inr'
            ]
        ];
        
        return $configs[$gateway] ?? $configs['razorpay'];
    }
    
    /**
     * Ensure payment tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Payment transactions
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Payment methods (saved cards, UPI, etc)
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Payment schedules (EMIs, installments)
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Webhook logs
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Create order/payment intent
     */
    public function createOrder(float $amount, array $options = []): array
    {
        $orderId = 'APS' . time() . rand(1000, 9999);
        
        $orderData = [
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $this->config['currency'],
            'user_id' => $options['user_id'] ?? null,
            'entity_type' => $options['entity_type'] ?? 'misc',
            'entity_id' => $options['entity_id'] ?? null,
            'description' => $options['description'] ?? 'Payment for APS Dream Home',
            'metadata' => $options['metadata'] ?? []
        ];
        
        try {
            switch ($this->gateway) {
                case 'razorpay':
                    return $this->createRazorpayOrder($orderData);
                case 'payu':
                    return $this->createPayUOrder($orderData);
                case 'stripe':
                    return $this->createStripeIntent($orderData);
                default:
                    throw new \Exception("Unsupported gateway: {$this->gateway}");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create Razorpay order — delegates to unified Gateway\RazorpayService
     */
    private function createRazorpayOrder(array $data): array
    {
        $svc = new RazorpayService();
        $resp = $svc->createOrder(
            (float)$data['amount'],
            $data['currency'],
            $data['order_id'],
            [
                'user_id'        => $data['user_id'],
                'entity_type'    => $data['entity_type'],
                'entity_id'      => $data['entity_id'],
                'booking_id'     => $data['entity_id'],
                'customer_name'  => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'description'    => $data['description'] ?? null,
            ]
        );

        if (!$resp['success']) {
            return [
                'success'         => false,
                'order_id'        => $data['order_id'],
                'gateway_order_id'=> null,
                'amount'          => $data['amount'],
                'currency'        => $data['currency'],
                'key_id'          => $svc->getKeyId(),
                'gateway'         => 'razorpay',
                'error'           => $resp['error'] ?? 'Unknown error',
            ];
        }

        return [
            'success'         => true,
            'order_id'        => $data['order_id'],
            'gateway_order_id'=> $resp['data']['id'] ?? null,
            'amount'          => $resp['data']['amount'] / 100,
            'currency'        => $resp['data']['currency'] ?? 'INR',
            'key_id'          => $svc->getKeyId(),
            'gateway'         => 'razorpay',
            'data'            => $resp['data'],
        ];
    }

    /**
     * Verify Razorpay payment signature — delegates to unified service
     */
    public function verifyRazorpaySignature(string $orderId, string $paymentId, string $signature): bool
    {
        return (new RazorpayService())->verifyPaymentSignature($orderId, $paymentId, $signature);
    }

    /**
     * Verify Razorpay webhook signature — delegates to unified service
     */
    public function verifyRazorpayWebhook(string $payload, string $signature): bool
    {
        return (new RazorpayService())->verifyWebhookSignature($payload, $signature);
    }
    
    /**
     * Verify payment
     */
    public function verifyPayment(string $gatewayPaymentId, string $orderId, string $signature): array
    {
        try {
            // Verify signature
            $generatedSignature = hash_hmac('sha256', $orderId . '|' . $gatewayPaymentId, $this->config['key_secret']);
            
            if (!hash_equals($generatedSignature, $signature)) {
                return [
                    'success' => false,
                    'error' => 'Invalid signature'
                ];
            }
            
            // Record successful payment
            $this->recordPayment([
                'transaction_id' => $gatewayPaymentId,
                'order_id' => $orderId,
                'gateway' => $this->gateway,
                'status' => 'captured',
                'gateway_response' => ['signature_verified' => true]
            ]);
            
            return [
                'success' => true,
                'payment_id' => $gatewayPaymentId,
                'status' => 'captured'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process refund
     */
    public function processRefund(string $transactionId, float $amount = null, string $reason = ''): array
    {
        try {
            // Get transaction details
            $sql = "SELECT * FROM payment_transactions WHERE transaction_id = ?" . $this->tenantSql();
            $stmt = $this->database->prepare($sql);
            $params = [$transactionId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
            $transaction = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$transaction) {
                return ['success' => false, 'error' => 'Transaction not found'];
            }
            
            $refundAmount = $amount ?? $transaction['amount'];
            
            // Check if already fully refunded
            if ($transaction['refund_amount'] >= $transaction['amount']) {
                return ['success' => false, 'error' => 'Already fully refunded'];
            }
            
            // In production, make actual refund API call
            $refundId = 'refund_' . uniqid();
            
            // Update transaction
            $newStatus = ($refundAmount >= $transaction['amount']) ? 'refunded' : 'partially_refunded';
            $newRefundAmount = $transaction['refund_amount'] + $refundAmount;
            
            $updateSql = "UPDATE payment_transactions SET 
                status = ?,
                refund_amount = ?,
                refund_reason = ?,
                refunded_at = NOW()
                WHERE transaction_id = ?" . $this->tenantSql();
            
            $updateStmt = $this->database->prepare($updateSql);
            $updateParams = [$newStatus, $newRefundAmount, $reason, $transactionId];
            if ($this->tenantId() > 1) $updateParams[] = $this->tenantId();
            $updateStmt->execute($updateParams);
            
            return [
                'success' => true,
                'refund_id' => $refundId,
                'amount' => $refundAmount,
                'status' => $newStatus
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle webhook
     */
    public function handleWebhook(string $gateway, array $payload, string $signature = ''): array
    {
        try {
            // Log webhook
            $this->logWebhook($gateway, $payload, $signature);
            
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($gateway, $payload, $signature)) {
                return ['success' => false, 'error' => 'Invalid webhook signature'];
            }
            
            // Process based on event type
            $event = $payload['event'] ?? '';
            
            switch ($event) {
                case 'payment.captured':
                    return $this->processPaymentCaptured($payload);
                case 'payment.failed':
                    return $this->processPaymentFailed($payload);
                case 'refund.processed':
                    return $this->processRefundWebhook($payload);
                default:
                    return ['success' => true, 'message' => 'Event not handled'];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats(string $dateFrom = null, string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');
        
        $sql = "SELECT 
            COUNT(*) as total_transactions,
            SUM(CASE WHEN status = 'captured' THEN amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'refunded' THEN refund_amount ELSE 0 END) as total_refunds,
            COUNT(CASE WHEN status = 'captured' THEN 1 END) as successful_payments,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
            AVG(CASE WHEN status = 'captured' THEN amount END) as avg_transaction_value
            FROM payment_transactions 
            WHERE DATE(created_at) BETWEEN ? AND ?" . $this->tenantSql();
        
        $stmt = $this->database->prepare($sql);
        $params = [$dateFrom, $dateTo];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user payment history
     */
    public function getUserPaymentHistory(int $userId, string $userType, int $limit = 20): array
    {
        $sql = "SELECT * FROM payment_transactions 
            WHERE user_id = ? AND user_type = ?" . $this->tenantSql() . "
            ORDER BY created_at DESC 
            LIMIT ?";
        
        $stmt = $this->database->prepare($sql);
        $params = [$userId, $userType, $limit];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Save payment method
     */
    public function savePaymentMethod(int $userId, string $userType, array $methodData): array
    {
        try {
            $columns = "user_id, user_type, gateway, method_type, method_token, last_four, 
                 card_brand, expiry_month, expiry_year, upi_id, bank_name, wallet_name, is_default";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
            $values = [
                $userId,
                $userType,
                $this->gateway,
                $methodData['type'],
                $methodData['token'],
                $methodData['last_four'] ?? null,
                $methodData['card_brand'] ?? null,
                $methodData['expiry_month'] ?? null,
                $methodData['expiry_year'] ?? null,
                $methodData['upi_id'] ?? null,
                $methodData['bank_name'] ?? null,
                $methodData['wallet_name'] ?? null,
                $methodData['is_default'] ?? 0
            ];
            if ($this->tenantId() > 1) {
                $columns .= ", tenant_id";
                $placeholders .= ", ?";
                $values[] = $this->tenantId();
            }
            $sql = "INSERT INTO user_payment_methods 
                ({$columns})
                VALUES ({$placeholders})
                ON DUPLICATE KEY UPDATE
                last_four = VALUES(last_four),
                is_active = 1";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true, 'method_id' => $this->database->lastInsertId()];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Record payment in database
     */
    private function recordPayment(array $data): void
    {
        $columns = "transaction_id, gateway, order_id, user_id, user_type, entity_type, entity_id,
             amount, currency, status, payment_method, gateway_response, metadata, ip_address, user_agent";
        $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
        $values = [
            $data['transaction_id'],
            $data['gateway'],
            $data['order_id'] ?? null,
            $data['user_id'] ?? 0,
            $data['user_type'] ?? 'customer',
            $data['entity_type'] ?? 'misc',
            $data['entity_id'] ?? null,
            $data['amount'] ?? 0,
            $data['currency'] ?? 'INR',
            $data['status'],
            $data['payment_method'] ?? null,
            json_encode($data['gateway_response'] ?? []),
            json_encode($data['metadata'] ?? []),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        if ($this->tenantId() > 1) {
            $columns .= ", tenant_id";
            $placeholders .= ", ?";
            $values[] = $this->tenantId();
        }
        $sql = "INSERT INTO payment_transactions 
            ({$columns})
            VALUES ({$placeholders})
            ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            gateway_response = VALUES(gateway_response),
            updated_at = NOW()";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($values);
    }
    
    /**
     * Log webhook
     */
    private function logWebhook(string $gateway, array $payload, string $signature): void
    {
        $columns = "gateway, event_type, payload, signature";
        $placeholders = "?, ?, ?, ?";
        $values = [
            $gateway,
            $payload['event'] ?? 'unknown',
            json_encode($payload),
            $signature
        ];
        if ($this->tenantId() > 1) {
            $columns .= ", tenant_id";
            $placeholders .= ", ?";
            $values[] = $this->tenantId();
        }
        $sql = "INSERT INTO payment_webhook_logs 
            ({$columns})
            VALUES ({$placeholders})";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($values);
    }
    
    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(string $gateway, array $payload, string $signature): bool
    {
        if ($gateway === 'razorpay' && $signature) {
            $body = json_encode($payload);
            return (new RazorpayService())->verifyWebhookSignature($body, $signature);
        }
        // PayU / Stripe: full checksum implementation deferred.
        // Returning false here so we don't silently accept unverified webhooks.
        return false;
    }
    
    /**
     * Process payment captured webhook
     */
    private function processPaymentCaptured(array $payload): array
    {
        $payment = $payload['payload']['payment']['entity'] ?? [];
        
        $this->recordPayment([
            'transaction_id' => $payment['id'] ?? '',
            'gateway' => $this->gateway,
            'status' => 'captured',
            'amount' => ($payment['amount'] ?? 0) / 100,
            'currency' => $payment['currency'] ?? 'INR',
            'payment_method' => $payment['method'] ?? '',
            'gateway_response' => $payment
        ]);
        
        return ['success' => true];
    }
    
    private function processPaymentFailed(array $payload): array
    {
        return ['success' => true];
    }
    
    private function processRefundWebhook(array $payload): array
    {
        return ['success' => true];
    }
    
    private function createPayUOrder(array $data): array
    {
        // PayU implementation
        return ['success' => true, 'gateway' => 'payu'];
    }
    
    private function createStripeIntent(array $data): array
    {
        // Stripe implementation
        return ['success' => true, 'gateway' => 'stripe'];
    }
}

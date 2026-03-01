<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Core\Database;

/**
 * Unified Payment Service
 * Manages multiple payment gateways
 */
class PaymentService
{
    private $gateways = [];
    private $defaultGateway = 'razorpay';

    public function __construct()
    {
        $this->registerGateways();
    }

    /**
     * Register all available payment gateways
     */
    private function registerGateways(): void
    {
        $this->gateways = [
            'razorpay' => new RazorpayGateway(),
            'stripe' => new StripeGateway()
        ];
    }

    /**
     * Get configured gateway
     */
    public function getGateway(string $name = null): ?PaymentGatewayInterface
    {
        $name = $name ?? $this->defaultGateway;
        return $this->gateways[$name] ?? null;
    }

    /**
     * Get all configured gateways
     */
    public function getConfiguredGateways(): array
    {
        $configured = [];
        foreach ($this->gateways as $name => $gateway) {
            if ($gateway->isConfigured()) {
                $configured[$name] = $gateway;
            }
        }
        return $configured;
    }

    /**
     * Initiate payment with specified gateway
     */
    public function initiatePayment(array $paymentData, string $gateway = null): array
    {
        $gateway = $this->getGateway($gateway);
        
        if (!$gateway || !$gateway->isConfigured()) {
            return ['success' => false, 'error' => 'Payment gateway not configured'];
        }

        $result = $gateway->initiatePayment($paymentData);

        if ($result['success']) {
            // Store payment record in database
            $paymentModel = new Payment();
            $paymentModel->create([
                'transaction_id' => $result['order_id'] ?? $result['payment_intent_id'] ?? uniqid('txn_'),
                'user_id' => $paymentData['user_id'] ?? null,
                'property_id' => $paymentData['property_id'] ?? null,
                'booking_id' => $paymentData['booking_id'] ?? null,
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'INR',
                'gateway' => $gateway->getGatewayName(),
                'status' => 'pending',
                'metadata' => json_encode($result),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $result;
    }

    /**
     * Verify and complete payment
     */
    public function verifyAndComplete(string $gateway, string $paymentId, array $data): array
    {
        $gatewayInstance = $this->getGateway($gateway);
        
        if (!$gatewayInstance) {
            return ['success' => false, 'error' => 'Invalid gateway'];
        }

        $result = $gatewayInstance->verifyPayment($paymentId, $data);

        if ($result['success']) {
            // Update payment record
            $db = Database::getInstance();
            $db->query(
                "UPDATE payments SET status = 'completed', transaction_id = ?, 
                 gateway_response = ?, completed_at = NOW() 
                 WHERE transaction_id = ? OR gateway_transaction_id = ?",
                [
                    $paymentId,
                    json_encode($result),
                    $result['order_id'] ?? $paymentId,
                    $paymentId
                ]
            );

            // Trigger payment completed event
            $this->onPaymentCompleted($result);
        }

        return $result;
    }

    /**
     * Process refund
     */
    public function processRefund(string $paymentId, float $amount = null, string $reason = null): array
    {
        // Get payment details
        $payment = (new Payment())->where('transaction_id', $paymentId)
            ->orWhere('gateway_transaction_id', $paymentId)
            ->first();

        if (!$payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        $gateway = $this->getGateway($payment['gateway']);
        
        if (!$gateway) {
            return ['success' => false, 'error' => 'Gateway not available'];
        }

        $refundAmount = $amount ?? $payment['amount'];
        $result = $gateway->refund($paymentId, $refundAmount, $reason);

        if ($result['success']) {
            // Update payment record
            $db = Database::getInstance();
            $db->query(
                "UPDATE payments SET status = 'refunded', refund_id = ?, 
                 refund_amount = ?, refunded_at = NOW() 
                 WHERE id = ?",
                [$result['refund_id'], $refundAmount, $payment['id']]
            );
        }

        return $result;
    }

    /**
     * Handle webhook from payment gateway
     */
    public function handleWebhook(string $gateway, string $payload, string $signature): array
    {
        $gatewayInstance = $this->getGateway($gateway);
        
        if (!$gatewayInstance) {
            return ['success' => false, 'error' => 'Invalid gateway'];
        }

        // Verify webhook signature
        if (method_exists($gatewayInstance, 'verifyWebhook')) {
            if (!$gatewayInstance->verifyWebhook($payload, $signature)) {
                return ['success' => false, 'error' => 'Invalid webhook signature'];
            }
        }

        $data = json_decode($payload, true);
        $eventType = $data['event'] ?? '';

        // Handle different webhook events
        switch ($eventType) {
            case 'payment.captured':
            case 'payment_intent.succeeded':
                return $this->handlePaymentSuccess($gateway, $data);
            
            case 'payment.failed':
            case 'payment_intent.payment_failed':
                return $this->handlePaymentFailed($gateway, $data);
            
            case 'refund.created':
            case 'refund.updated':
                return $this->handleRefundEvent($gateway, $data);
            
            default:
                return ['success' => true, 'message' => 'Event processed'];
        }
    }

    /**
     * Handle successful payment webhook
     */
    private function handlePaymentSuccess(string $gateway, array $data): array
    {
        $paymentId = $data['payload']['payment']['entity']['id'] 
            ?? $data['data']['object']['id'] ?? null;

        if ($paymentId) {
            $this->verifyAndComplete($gateway, $paymentId, []);
        }

        return ['success' => true, 'message' => 'Payment recorded'];
    }

    /**
     * Handle failed payment webhook
     */
    private function handlePaymentFailed(string $gateway, array $data): array
    {
        $paymentId = $data['payload']['payment']['entity']['id'] 
            ?? $data['data']['object']['id'] ?? null;

        if ($paymentId) {
            $db = Database::getInstance();
            $db->query(
                "UPDATE payments SET status = 'failed', failed_at = NOW() 
                 WHERE transaction_id = ? OR gateway_transaction_id = ?",
                [$paymentId, $paymentId]
            );
        }

        return ['success' => true, 'message' => 'Failure recorded'];
    }

    /**
     * Handle refund webhook
     */
    private function handleRefundEvent(string $gateway, array $data): array
    {
        $refundId = $data['payload']['refund']['entity']['id'] 
            ?? $data['data']['object']['id'] ?? null;

        if ($refundId) {
            $db = Database::getInstance();
            $db->query(
                "UPDATE payments SET status = 'refunded', refund_id = ?, refunded_at = NOW() 
                 WHERE refund_id = ?",
                [$refundId, $refundId]
            );
        }

        return ['success' => true, 'message' => 'Refund recorded'];
    }

    /**
     * Trigger post-payment actions
     */
    private function onPaymentCompleted(array $paymentData): void
    {
        // Send confirmation email
        // Update booking status
        // Generate invoice
        // Notify relevant parties
    }
}

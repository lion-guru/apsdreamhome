<?php
/**
 * Razorpay Payment Gateway Integration
 * Handles payments, EMI, and auto-commission distribution
 */

namespace App\Services\Payment;

use App\Core\Database\Database;

class RazorpayService
{
    private $db;
    private $keyId;
    private $keySecret;
    private $baseUrl = 'https://api.razorpay.com/v1';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->keyId = $_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_default';
        $this->keySecret = $_ENV['RAZORPAY_KEY_SECRET'] ?? 'secret_default';
    }
    
    /**
     * Create a new order
     */
    public function createOrder($amount, $currency = 'INR', $receipt = null, $notes = [])
    {
        $receipt = $receipt ?? 'ORD_' . time();
        
        $data = [
            'amount' => $amount * 100, // Razorpay expects paise
            'currency' => $currency,
            'receipt' => $receipt,
            'payment_capture' => 1,
            'notes' => $notes
        ];
        
        return $this->apiCall('/orders', $data);
    }
    
    /**
     * Verify payment signature
     */
    public function verifyPayment($orderId, $paymentId, $signature)
    {
        $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($generatedSignature, $signature);
    }
    
    /**
     * Capture payment
     */
    public function capturePayment($paymentId, $amount)
    {
        return $this->apiCall("/payments/{$paymentId}/capture", [
            'amount' => $amount * 100
        ], 'POST');
    }
    
    /**
     * Process booking payment with auto-commission
     */
    public function processBookingPayment($bookingId, $userId, $amount, $paymentMethod = 'razorpay')
    {
        try {
            // Create order
            $order = $this->createOrder($amount, 'INR', 'BOOKING_' . $bookingId, [
                'booking_id' => $bookingId,
                'user_id' => $userId
            ]);
            
            if (!isset($order['id'])) {
                return ['success' => false, 'error' => 'Failed to create order'];
            }
            
            // Save order to database
            $this->db->insert('payment_orders', [
                'order_id' => $order['id'],
                'booking_id' => $bookingId,
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => 'INR',
                'status' => 'created',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $amount,
                'currency' => 'INR',
                'key_id' => $this->keyId
            ];
            
        } catch (\Exception $e) {
            error_log("Razorpay order creation failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Handle successful payment - distribute commissions
     */
    public function handlePaymentSuccess($paymentId, $orderId, $signature)
    {
        try {
            // Verify signature
            if (!$this->verifyPayment($orderId, $paymentId, $signature)) {
                return ['success' => false, 'error' => 'Invalid payment signature'];
            }
            
            // Get order details from DB
            $order = $this->db->fetchOne("SELECT * FROM payment_orders WHERE order_id = ?", [$orderId]);
            if (!$order) {
                return ['success' => false, 'error' => 'Order not found'];
            }
            
            // Update payment status
            $this->db->query(
                "UPDATE payment_orders SET payment_id = ?, status = 'paid', paid_at = NOW() WHERE order_id = ?",
                [$paymentId, $orderId]
            );
            
            // Record payment
            $this->db->insert('payments', [
                'booking_id' => $order['booking_id'],
                'user_id' => $order['user_id'],
                'amount' => $order['amount'],
                'payment_method' => 'razorpay',
                'transaction_id' => $paymentId,
                'order_id' => $orderId,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Auto-distribute commissions
            $this->distributeCommissions($order['booking_id'], $order['user_id'], $order['amount']);
            
            return ['success' => true, 'booking_id' => $order['booking_id']];
            
        } catch (\Exception $e) {
            error_log("Payment success handling failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Distribute commissions to referrer chain
     */
    private function distributeCommissions($bookingId, $userId, $amount)
    {
        try {
            // Get user's referrer chain
            $referrers = $this->getReferrerChain($userId);
            
            if (empty($referrers)) {
                return;
            }
            
            // Get commission rules
            $rules = $this->db->fetchAll("SELECT * FROM commission_rules WHERE is_active = 1 ORDER BY level ASC");
            
            foreach ($referrers as $index => $referrer) {
                $level = $index + 1;
                $rule = $this->getRuleForLevel($rules, $level);
                
                if ($rule && $rule['percentage'] > 0) {
                    $commissionAmount = ($amount * $rule['percentage']) / 100;
                    
                    // Record commission
                    $this->db->insert('commissions', [
                        'associate_id' => $referrer['id'],
                        'referred_user_id' => $userId,
                        'booking_id' => $bookingId,
                        'sale_amount' => $amount,
                        'commission_amount' => $commissionAmount,
                        'percentage' => $rule['percentage'],
                        'level' => $level,
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Credit to wallet
                    $this->creditWallet($referrer['id'], $commissionAmount, 'commission', $bookingId);
                }
            }
            
        } catch (\Exception $e) {
            error_log("Commission distribution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Credit amount to user's wallet
     */
    private function creditWallet($userId, $amount, $type, $referenceId)
    {
        try {
            // Get current wallet
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ?", [$userId]);
            
            if (!$wallet) {
                // Create wallet
                $this->db->insert('wallet_points', [
                    'user_id' => $userId,
                    'points_balance' => $amount,
                    'total_earned' => $amount,
                    'commission_earnings' => $amount,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Update wallet
                $newBalance = $wallet['points_balance'] + $amount;
                $newTotal = $wallet['total_earned'] + $amount;
                $newCommission = $wallet['commission_earnings'] + $amount;
                
                $this->db->query(
                    "UPDATE wallet_points SET points_balance = ?, total_earned = ?, commission_earnings = ?, updated_at = NOW() WHERE user_id = ?",
                    [$newBalance, $newTotal, $newCommission, $userId]
                );
            }
            
            // Record transaction
            $this->db->insert('wallet_transactions', [
                'user_id' => $userId,
                'transaction_type' => 'credit',
                'transaction_category' => $type,
                'amount' => $amount,
                'reference_id' => $referenceId,
                'reference_type' => 'booking',
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            error_log("Wallet credit failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get referrer chain up to 5 levels
     */
    private function getReferrerChain($userId, $maxLevel = 5)
    {
        $referrers = [];
        $currentId = $userId;
        $level = 0;
        
        while ($level < $maxLevel) {
            $user = $this->db->fetchOne("SELECT id, referred_by FROM users WHERE id = ?", [$currentId]);
            
            if (!$user || !$user['referred_by']) {
                break;
            }
            
            $referrer = $this->db->fetchOne("SELECT id, name, email FROM users WHERE id = ?", [$user['referred_by']]);
            
            if ($referrer) {
                $referrers[] = $referrer;
                $currentId = $referrer['id'];
            }
            
            $level++;
        }
        
        return $referrers;
    }
    
    /**
     * Get commission rule for level
     */
    private function getRuleForLevel($rules, $level)
    {
        foreach ($rules as $rule) {
            if ($rule['level'] == $level) {
                return $rule;
            }
        }
        return null;
    }
    
    /**
     * Make API call to Razorpay
     */
    private function apiCall($endpoint, $data = [], $method = 'POST')
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("Razorpay API Error: " . $error);
        }
        
        return json_decode($response, true);
    }
}

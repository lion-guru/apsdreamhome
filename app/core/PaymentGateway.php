<?php
/**
 * Razorpay Payment Gateway Integration
 * Handles payment processing for property bookings and services
 */

namespace App\Core;

class PaymentGateway {
    private $razorpay_key_id;
    private $razorpay_key_secret;
    private $settings;

    public function __construct() {
        $this->settings = $this->_getPaymentSettingsFromEnv();
        $this->razorpay_key_id = $this->settings['razorpay_key_id'] ?? '';
        $this->razorpay_key_secret = $this->settings['razorpay_key_secret'] ?? '';
    }

    /**
     * Get payment settings from environment
     */
    private function _getPaymentSettingsFromEnv() {
        return [
            'razorpay_key_id' => env('RAZORPAY_KEY_ID', ''),
            'razorpay_key_secret' => env('RAZORPAY_KEY_SECRET', ''),
            'currency' => env('PAYMENT_CURRENCY', 'INR'),
            'payment_methods' => ['card', 'netbanking', 'upi', 'wallet'],
            'sandbox_mode' => env('PAYMENT_SANDBOX', true)
        ];
    }

    /**
     * Create payment order
     */
    public function createOrder($amount, $currency = 'INR', $receipt = null, $notes = []) {
        try {
            // Validate amount (minimum ₹1)
            if ($amount < 1) {
                throw new \Exception('Minimum payment amount is ₹1');
            }

            // Convert to paisa (Razorpay works with smallest currency unit)
            $amount_in_paisa = $amount * 100;

            $order_data = [
                'amount' => $amount_in_paisa,
                'currency' => $currency,
                'receipt' => $receipt ?? 'rcpt_' . uniqid(),
                'notes' => array_merge($notes, [
                    'platform' => 'APS Dream Home',
                    'created_at' => date('Y-m-d H:i:s')
                ])
            ];

            // Create order using Razorpay API
            $order = $this->createRazorpayOrder($order_data);

            // Save order to database
            $order_id = $this->savePaymentOrder([
                'razorpay_order_id' => $order['id'],
                'amount' => $amount,
                'currency' => $currency,
                'receipt' => $order_data['receipt'],
                'status' => 'created',
                'notes' => json_encode($notes),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'order_id' => $order_id,
                'razorpay_order_id' => $order['id'],
                'amount' => $amount,
                'currency' => $currency,
                'key' => $this->razorpay_key_id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPayment($razorpay_payment_id, $razorpay_order_id, $razorpay_signature) {
        try {
            // Verify signature
            $generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $this->razorpay_key_secret);

            if (hash_equals($generated_signature, $razorpay_signature)) {
                // Get payment details from Razorpay
                $payment = $this->getRazorpayPayment($razorpay_payment_id);

                if ($payment) {
                    // Update order status in database
                    $this->updatePaymentOrder($razorpay_order_id, [
                        'status' => 'paid',
                        'razorpay_payment_id' => $razorpay_payment_id,
                        'payment_method' => $payment['method'] ?? 'unknown',
                        'payment_status' => 'completed',
                        'paid_at' => date('Y-m-d H:i:s')
                    ]);

                    return [
                        'success' => true,
                        'payment_id' => $razorpay_payment_id,
                        'order_id' => $razorpay_order_id,
                        'amount' => $payment['amount'] / 100, // Convert back from paisa
                        'method' => $payment['method'] ?? 'unknown',
                        'status' => 'completed'
                    ];
                } else {
                    throw new \Exception('Payment not found');
                }
            } else {
                throw new \Exception('Payment signature verification failed');
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment order details
     */
    public function getOrder($order_id) {
        try {
            global $pdo;

            $stmt = $pdo->prepare("
                SELECT * FROM payment_orders
                WHERE id = ? OR razorpay_order_id = ?
            ");
            $stmt->execute([$order_id, $order_id]);
            $order = $stmt->fetch();

            if ($order) {
                return [
                    'success' => true,
                    'order' => $order
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Order not found'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Razorpay order via API
     */
    private function createRazorpayOrder($order_data) {
        $url = 'https://api.razorpay.com/v1/orders';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
        curl_setopt($ch, CURLOPT_USERPWD, $this->razorpay_key_id . ':' . $this->razorpay_key_secret);

        $headers = [
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('Razorpay API Error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($result, true);
        } else {
            $error = json_decode($result, true);
            throw new \Exception('Razorpay API Error: ' . ($error['error']['description'] ?? 'Unknown error'));
        }
    }

    /**
     * Get payment details from Razorpay
     */
    private function getRazorpayPayment($payment_id) {
        $url = 'https://api.razorpay.com/v1/payments/' . $payment_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->razorpay_key_id . ':' . $this->razorpay_key_secret);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('Razorpay API Error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($result, true);
        } else {
            return false;
        }
    }

    /**
     * Save payment order to database
     */
    private function savePaymentOrder($order_data) {
        try {
            global $pdo;

            $stmt = $pdo->prepare("
                INSERT INTO payment_orders (
                    razorpay_order_id, amount, currency, receipt, status, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $order_data['razorpay_order_id'],
                $order_data['amount'],
                $order_data['currency'],
                $order_data['receipt'],
                $order_data['status'],
                $order_data['notes'],
                $order_data['created_at']
            ]);

            return $pdo->lastInsertId();

        } catch (\Exception $e) {
            throw new \Exception('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Update payment order in database
     */
    private function updatePaymentOrder($razorpay_order_id, $update_data) {
        try {
            global $pdo;

            $fields = [];
            $values = [];

            foreach ($update_data as $field => $value) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }

            $values[] = $razorpay_order_id;

            $sql = "UPDATE payment_orders SET " . implode(', ', $fields) . " WHERE razorpay_order_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

        } catch (\Exception $e) {
            throw new \Exception('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Get payment settings for frontend
     */
    public function getPaymentSettings() {
        return [
            'key' => $this->razorpay_key_id,
            'currency' => $this->settings['currency'],
            'sandbox' => $this->settings['sandbox_mode'],
            'methods' => $this->settings['payment_methods']
        ];
    }

    /**
     * Process refund
     */
    public function processRefund($payment_id, $amount = null, $notes = '') {
        try {
            $url = 'https://api.razorpay.com/v1/payments/' . $payment_id . '/refund';

            $refund_data = [];
            if ($amount) {
                $refund_data['amount'] = $amount * 100; // Convert to paisa
            }
            if ($notes) {
                $refund_data['notes'] = ['reason' => $notes];
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refund_data));
            curl_setopt($ch, CURLOPT_USERPWD, $this->razorpay_key_id . ':' . $this->razorpay_key_secret);

            $headers = [
                'Content-Type: application/json'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new \Exception('Razorpay API Error: ' . curl_error($ch));
            }

            curl_close($ch);

            if ($http_code === 200) {
                $refund = json_decode($result, true);

                // Update database with refund info
                $this->updatePaymentOrder($payment_id, [
                    'refund_id' => $refund['id'],
                    'refund_amount' => $refund['amount'] / 100,
                    'refund_status' => 'processed',
                    'refunded_at' => date('Y-m-d H:i:s')
                ]);

                return [
                    'success' => true,
                    'refund_id' => $refund['id'],
                    'amount' => $refund['amount'] / 100,
                    'status' => 'processed'
                ];
            } else {
                $error = json_decode($result, true);
                throw new \Exception('Refund failed: ' . ($error['error']['description'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

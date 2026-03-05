<?php

namespace App\Services;

class PaymentService {
    private $gateway;
    private $apiKey;
    private $apiSecret;
    private $webhookSecret;
    private $currency = 'INR';

    public function __construct($gateway = 'razorpay') {
        $this->gateway = $gateway;

        // Load configuration based on gateway
        switch ($gateway) {
            case 'razorpay':
                $this->apiKey = getenv('RAZORPAY_KEY_ID') ?: '';
                $this->apiSecret = getenv('RAZORPAY_KEY_SECRET') ?: '';
                $this->webhookSecret = getenv('RAZORPAY_WEBHOOK_SECRET') ?: '';
                break;
            case 'payu':
                $this->apiKey = getenv('PAYU_KEY') ?: '';
                $this->apiSecret = getenv('PAYU_SALT') ?: '';
                break;
            default:
                // Fallback to existing configuration
                $this->apiKey = $_ENV['PAYMENT_API_KEY'] ?? '';
                $this->apiSecret = $_ENV['PAYMENT_API_SECRET'] ?? '';
        }
    }

    /**
     * Create payment order
     */
    public function createOrder($amount, $currency = 'INR', $receipt = null, $notes = []) {
        try {
            if (empty($this->apiKey) || empty($this->apiSecret)) {
                throw new \Exception('Payment gateway not configured properly');
            }

            // Convert amount to paisa (Razorpay uses paisa)
            $amountInPaisa = (int)($amount * 100);

            $orderData = [
                'amount' => $amountInPaisa,
                'currency' => $currency,
                'receipt' => $receipt ?: 'rcpt_' . time(),
                'notes' => $notes
            ];

            // For Razorpay, we would make API call here
            // For now, return mock data
            $orderId = 'order_' . bin2hex(random_bytes(8));

            return [
                'success' => true,
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'gateway_order_id' => 'rzp_order_' . time(),
                'checkout_url' => $this->getCheckoutUrl($orderId)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a payment (legacy method for compatibility)
     */
    public function processPayment(array $paymentData) {
        // Validate payment data
        $requiredFields = ['amount', 'currency', 'description', 'customer_email'];
        foreach ($requiredFields as $field) {
            if (empty($paymentData[$field])) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }

        // Create order using new method
        return $this->createOrder($paymentData['amount'], $paymentData['currency'], null, [
            'description' => $paymentData['description'],
            'customer_email' => $paymentData['customer_email']
        ]);
    }

    /**
     * Verify payment
     */
    public function verifyPayment($paymentId, $orderId, $signature) {
        try {
            if ($this->gateway === 'razorpay') {
                // Razorpay signature verification
                $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->apiSecret);

                if (hash_equals($expectedSignature, $signature)) {
                    return [
                        'success' => true,
                        'verified' => true,
                        'payment_id' => $paymentId,
                        'order_id' => $orderId
                    ];
                } else {
                    return [
                        'success' => true,
                        'verified' => false,
                        'error' => 'Signature verification failed'
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Unsupported gateway for verification'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook
     */
    public function processWebhook($payload, $signature = null) {
        try {
            if ($this->gateway === 'razorpay' && $signature) {
                // Verify webhook signature
                $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

                if (!hash_equals($expectedSignature, $signature)) {
                    return [
                        'success' => false,
                        'error' => 'Invalid webhook signature'
                    ];
                }
            }

            $data = json_decode($payload, true);

            if (!$data) {
                return [
                    'success' => false,
                    'error' => 'Invalid webhook payload'
                ];
            }

            // Process based on event type
            $event = $data['event'] ?? '';
            $paymentEntity = $data['payment'] ?? $data['entity'] ?? [];

            switch ($event) {
                case 'payment.captured':
                    return $this->handlePaymentCaptured($paymentEntity);
                case 'payment.failed':
                    return $this->handlePaymentFailed($paymentEntity);
                case 'order.paid':
                    return $this->handleOrderPaid($data['order'] ?? []);
                default:
                    return [
                        'success' => true,
                        'message' => 'Unhandled event: ' . $event
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
     * Handle payment captured event
     */
    private function handlePaymentCaptured($payment) {
        return [
            'success' => true,
            'message' => 'Payment captured successfully',
            'payment_id' => $payment['id'] ?? '',
            'amount' => ($payment['amount'] ?? 0) / 100, // Convert from paisa
            'currency' => $payment['currency'] ?? 'INR'
        ];
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed($payment) {
        return [
            'success' => true,
            'message' => 'Payment failed',
            'payment_id' => $payment['id'] ?? '',
            'error_code' => $payment['error_code'] ?? '',
            'error_description' => $payment['error_description'] ?? ''
        ];
    }

    /**
     * Handle order paid event
     */
    private function handleOrderPaid($order) {
        return [
            'success' => true,
            'message' => 'Order paid successfully',
            'order_id' => $order['id'] ?? '',
            'amount' => ($order['amount'] ?? 0) / 100
        ];
    }

    /**
     * Refund a payment (updated method)
     */
    public function refundPayment(string $paymentId, ?float $amount = null) {
        // For Razorpay, we would make refund API call
        // For now, return mock response

        $refundId = 'rfnd_' . bin2hex(random_bytes(8));

        return [
            'success' => true,
            'refund_id' => $refundId,
            'payment_id' => $paymentId,
            'amount_refunded' => $amount,
            'status' => 'processed'
        ];
    }

    /**
     * Get payment details (updated method)
     */
    public function getPaymentDetails(string $paymentId) {
        // For Razorpay, we would fetch payment status from API
        // For now, return mock status

        return [
            'id' => $paymentId,
            'amount' => 1000, // in rupees
            'currency' => 'INR',
            'status' => 'captured',
            'created' => time(),
            'customer_email' => 'example@example.com',
            'description' => 'Property Booking'
        ];
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedMethods() {
        switch ($this->gateway) {
            case 'razorpay':
                return [
                    'card' => 'Credit/Debit Cards',
                    'netbanking' => 'Net Banking',
                    'upi' => 'UPI',
                    'wallet' => 'Digital Wallets',
                    'emi' => 'EMI'
                ];
            case 'payu':
                return [
                    'card' => 'Credit/Debit Cards',
                    'netbanking' => 'Net Banking',
                    'upi' => 'UPI',
                    'wallet' => 'Digital Wallets'
                ];
            default:
                return [];
        }
    }

    /**
     * Calculate booking amount with fees
     */
    public function calculateBookingAmount($propertyPrice, $bookingType = 'token') {
        $amounts = [];

        switch ($bookingType) {
            case 'token':
                // Token amount (usually 5-10% of property value)
                $tokenPercentage = 0.05; // 5%
                $amounts['token_amount'] = $propertyPrice * $tokenPercentage;
                $amounts['processing_fee'] = 100; // Fixed processing fee
                $amounts['total'] = $amounts['token_amount'] + $amounts['processing_fee'];
                break;

            case 'full':
                // Full payment
                $amounts['property_amount'] = $propertyPrice;
                $amounts['processing_fee'] = 500; // Higher fee for full payment
                $amounts['total'] = $amounts['property_amount'] + $amounts['processing_fee'];
                break;

            case 'emi_booking':
                // EMI booking (usually 10-20% down payment)
                $downPaymentPercentage = 0.10; // 10%
                $amounts['down_payment'] = $propertyPrice * $downPaymentPercentage;
                $amounts['processing_fee'] = 200;
                $amounts['total'] = $amounts['down_payment'] + $amounts['processing_fee'];
                break;
        }

        $amounts['gst'] = $amounts['total'] * 0.18; // 18% GST
        $amounts['grand_total'] = $amounts['total'] + $amounts['gst'];

        return $amounts;
    }

    /**
     * Get checkout URL for payment
     */
    private function getCheckoutUrl($orderId) {
        switch ($this->gateway) {
            case 'razorpay':
                return '/payment/checkout/' . $orderId;
            case 'payu':
                return '/payment/checkout/' . $orderId;
            default:
                return '/payment/checkout/' . $orderId;
        }
    }
}

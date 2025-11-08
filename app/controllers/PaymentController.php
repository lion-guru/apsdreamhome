<?php

namespace App\Controllers;

use App\Services\PaymentService;
use App\Services\PropertyService;

class PaymentController extends Controller {
    private $paymentService;
    private $propertyService;

    public function __construct() {
        parent::__construct();
        $this->paymentService = new PaymentService();
        $this->propertyService = new PropertyService();
        $this->requireLogin();
    }

    /**
     * Show payment form for a property
     */
    public function checkout($propertyId) {
        $property = $this->propertyService->getPropertyById($propertyId);
        
        if (!$property) {
            $this->notFound();
            return;
        }
        
        $this->view('payments/checkout', [
            'title' => 'Complete Your Purchase',
            'property' => $property
        ]);
    }

    /**
     * Process payment
     */
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
            return;
        }
        
        $propertyId = (int)($_POST['property_id'] ?? 0);
        $property = $this->propertyService->getPropertyById($propertyId);
        
        if (!$property) {
            $this->notFound();
            return;
        }
        
        try {
            $paymentData = [
                'amount' => $property['price'] * 100, // Convert to smallest currency unit (e.g., cents)
                'currency' => 'INR',
                'description' => 'Property Purchase: ' . $property['title'],
                'customer_email' => $_SESSION['user_email'] ?? '',
                'metadata' => [
                    'property_id' => $propertyId,
                    'user_id' => $_SESSION['user_id']
                ]
            ];
            
            $payment = $this->paymentService->processPayment($paymentData);
            
            if ($payment['success']) {
                // Save payment record to database
                $this->savePaymentRecord($payment, $propertyId);
                
                $_SESSION['success'] = 'Payment successful! Your purchase is complete.';
                $this->redirect('/payments/success?payment_id=' . $payment['payment_id']);
                return;
            }
            
            throw new \Exception('Payment processing failed');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Payment failed: ' . $e->getMessage();
            $this->redirect("/properties/$propertyId/payment");
        }
    }

    /**
     * Show payment success page
     */
    public function success() {
        $paymentId = $_GET['payment_id'] ?? '';
        
        if (empty($paymentId)) {
            $this->redirect('/');
            return;
        }
        
        try {
            $payment = $this->paymentService->getPaymentDetails($paymentId);
            
            $this->view('payments/success', [
                'title' => 'Payment Successful',
                'payment' => $payment
            ]);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Invalid payment reference';
            $this->redirect('/');
        }
    }

    /**
     * Save payment record to database
     */
    private function savePaymentRecord(array $payment, int $propertyId) {
        $db = \App\Core\Database::getInstance();
        
        $query = "
            INSERT INTO payments (
                payment_id, user_id, property_id, amount, 
                currency, status, payment_method, 
                transaction_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $params = [
            $payment['payment_id'],
            $_SESSION['user_id'],
            $propertyId,
            $payment['amount'] / 100, // Convert back to normal amount
            $payment['currency'],
            $payment['status'],
            $payment['payment_method'] ?? 'card',
            $payment['transaction_id'] ?? null
        ];
        
        $db->query($query, $params);
        
        // Update property status to sold
        $this->propertyService->updateProperty($propertyId, ['status' => 'sold']);
    }

    /**
     * Process payment webhook
     */
    public function webhook() {
        // In a real application, this would verify the webhook signature
        $payload = @file_get_contents('php://input');
        $event = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            exit('Invalid payload');
        }
        
        // Process different types of webhook events
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event['data']['object']);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event['data']['object']);
                break;
                
            // Add more event types as needed
        }
        
        http_response_code(200);
    }
    
    /**
     * Handle successful payment webhook
     */
    private function handlePaymentSucceeded(array $paymentIntent) {
        // Update payment status in database
        $db = \App\Core\Database::getInstance();
        
        $query = "
            UPDATE payments 
            SET status = 'succeeded',
                payment_method = ?,
                transaction_id = ?,
                updated_at = NOW()
            WHERE payment_id = ?
        ";
        
        $params = [
            $paymentIntent['payment_method_types'][0] ?? 'card',
            $paymentIntent['id'],
            $paymentIntent['id']
        ];
        
        $db->query($query, $params);
    }
    
    /**
     * Handle failed payment webhook
     */
    private function handlePaymentFailed(array $paymentIntent) {
        // Update payment status in database
        $db = \App\Core\Database::getInstance();
        
        $query = "
            UPDATE payments 
            SET status = 'failed',
                failure_message = ?,
                updated_at = NOW()
            WHERE payment_id = ?
        ";
        
        $params = [
            $paymentIntent['last_payment_error']['message'] ?? 'Payment failed',
            $paymentIntent['id']
        ];
        
        $db->query($query, $params);
    }
}

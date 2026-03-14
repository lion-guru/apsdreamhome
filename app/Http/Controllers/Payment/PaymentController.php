<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\BaseController;

class PaymentController extends BaseController
{
function success() {
        $paymentId = $_GET['payment_id'] ?? '';
        
        if (empty($paymentId)) {
            $this->redirect('/');
            return;
        }
}
function savePaymentRecord(array $payment, int $propertyId) {
        $query = "
            INSERT INTO payments (
                payment_id, user_id, property_id, amount, 
                currency, status, payment_method, 
                transaction_id, created_at
            ) VALUES (:payment_id, :user_id, :property_id, :amount, 
                :currency, :status, :payment_method, 
                :transaction_id, NOW())
        ";
        
        $params = [
            ':payment_id' => $payment['payment_id'],
            ':user_id' => $_SESSION['user_id'],
            ':property_id' => $propertyId,
            ':amount' => $payment['amount'] / 100, // Convert back to normal amount
            ':currency' => $payment['currency'],
            ':status' => $payment['status'],
            ':payment_method' => $payment['payment_method'] ?? 'card',
            ':transaction_id' => $payment['transaction_id'] ?? null
        ];
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        // Update property status to sold
        $this->propertyService->updateProperty($propertyId, ['status' => 'sold']);
    }
function handlePaymentSucceeded(array $paymentIntent) {
        $query = "
            UPDATE payments 
            SET status = 'succeeded',
                payment_method = :payment_method,
                transaction_id = :transaction_id,
                updated_at = NOW()
            WHERE payment_id = :payment_id
        ";
        
        $params = [
            ':payment_method' => $paymentIntent['payment_method_types'][0] ?? 'card',
            ':transaction_id' => $paymentIntent['id'],
            ':payment_id' => $paymentIntent['id']
        ];
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
    }
function handlePaymentFailed(array $paymentIntent) {
        $query = "
            UPDATE payments 
            SET status = 'failed',
                failure_message = :failure_message,
                updated_at = NOW()
            WHERE payment_id = :payment_id
        ";
        
        $params = [
            ':failure_message' => $paymentIntent['last_payment_error']['message'] ?? 'Payment failed',
            ':payment_id' => $paymentIntent['id']
        ];
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
    }
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 530 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//
}

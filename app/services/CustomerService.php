<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

/**
 * CustomerService
 * Handles customer-specific logic like booking tracking and EMI management.
 */
class CustomerService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all active and past bookings for a customer.
     */
    public function getCustomerBookings($customerId)
    {
        $sql = "SELECT b.*, p.title as property_name, p.main_image 
                FROM bookings b
                JOIN properties p ON b.property_id = p.id
                WHERE b.customer_id = ?
                ORDER BY b.created_at DESC";
        
        return $this->db->fetchAll($sql, [$customerId]) ?? [];
    }

    /**
     * Get the EMI schedule for a specific booking.
     */
    public function getEmiSchedule($bookingId)
    {
        $sql = "SELECT * FROM emi_schedule 
                WHERE booking_id = ? 
                ORDER BY emi_number ASC";
        
        return $this->db->fetchAll($sql, [$bookingId]) ?? [];
    }

    /**
     * Record a payment for an EMI installment.
     */
    public function recordEmiPayment($emiId, $amount, $paymentMethod = 'Simulated')
    {
        // Start transaction
        try {
            // 1. Update EMI Schedule
            $sql = "UPDATE emi_schedule 
                    SET status = 'paid', 
                        paid_date = CURRENT_TIMESTAMP, 
                        paid_amount = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?";
            $this->db->query($sql, [$amount, $emiId]);

            // 2. Log Payment Order (Simplified)
            $sql = "INSERT INTO payment_logs (emi_id, amount, payment_method, status) 
                    VALUES (?, ?, ?, 'success')";
            $this->db->query($sql, [$emiId, $amount, $paymentMethod]);

            return ['success' => true, 'message' => 'Payment recorded successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

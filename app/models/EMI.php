<?php

namespace App\Models;

use App\Core\Model;

/**
 * EMI Model
 * Handles EMI plans, payments and scheduling
 */
class EMI extends Model
{
    protected static $table = 'emi_plans';
    protected static $primaryKey = 'id';
    
    protected $fillable = [
        'customer_id',
        'booking_id',
        'total_amount',
        'down_payment',
        'emi_amount',
        'interest_rate',
        'tenure_months',
        'start_date',
        'end_date',
        'status',
        'created_by'
    ];

    /**
     * Get EMI plan by booking ID
     */
    public function getByBookingId($bookingId)
    {
        return $this->where('booking_id', $bookingId)->first();
    }

    /**
     * Get active EMI plans for a customer
     */
    public function getActiveByCustomerId($customerId)
    {
        return $this->where('customer_id', $customerId)
                    ->where('status', 'active')
                    ->get();
    }

    /**
     * Calculate monthly EMI amount (Standard formula)
     * P * r * (1+r)^n / ((1+r)^n - 1)
     */
    public function calculateEMIAmount($principal, $annualRate, $tenureMonths)
    {
        if ($annualRate == 0) {
            return $principal / $tenureMonths;
        }

        $monthlyRate = ($annualRate / 100) / 12;
        $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / (pow(1 + $monthlyRate, $tenureMonths) - 1);
        
        return round($emi, 2);
    }

    /**
     * Get EMI payment schedule
     */
    public function getSchedule($emiPlanId)
    {
        $sql = "SELECT * FROM emi_payments WHERE emi_plan_id = ? ORDER BY due_date ASC";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$emiPlanId]);
        return $stmt->fetchAll();
    }

    /**
     * Record an EMI payment
     */
    public function recordPayment($data)
    {
        $sql = "INSERT INTO emi_payments (emi_plan_id, amount, payment_date, transaction_id, status, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute([
            $data['emi_plan_id'],
            $data['amount'],
            $data['payment_date'] ?? date('Y-m-d'),
            $data['transaction_id'] ?? null,
            $data['status'] ?? 'completed',
            $data['notes'] ?? null
        ]);
    }

    /**
     * Update EMI plan status
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE emi_plans SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
}

<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Services\Payment\RazorpayService;

/**
 * Foreclosure & Early Payoff Engine
 * 
 * Calculates the remaining principal + applicable rebate,
 * generates dynamic UPI QR for instant payoff.
 */
class ForeclosureService
{
    protected $db;

    // Rebate tiers: remaining tenure -> rebate percentage
    const REBATE_TIERS = [
        0   => 5.0,   // < 6 months: 5%
        6   => 4.0,   // 6-12 months: 4%
        12  => 3.0,   // 1-2 years: 3%
        24  => 2.0,   // 2-3 years: 2%
        36  => 1.0,   // 3-4 years: 1%
        48  => 0.5,   // 4+ years: 0.5%
    ];

    public function __construct($db = null)
    {
        if ($db instanceof \PDO) {
            $this->db = $db;
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
    }

    /**
     * Calculate remaining tenure in months for a booking's payment schedule
     */
    public function getRemainingTenureMonths(int $bookingId): int
    {
        $sql = "SELECT COUNT(*) FROM booking_payment_schedules 
                WHERE booking_id = ? AND status IN ('pending', 'overdue')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookingId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get applicable rebate percentage based on remaining tenure
     */
    public function getRebatePercent(int $remainingMonths): float
    {
        foreach (self::REBATE_TIERS as $months => $rate) {
            if ($remainingMonths >= $months) {
                return $rate;
            }
        }
        return 0.5; // fallback
    }

    /**
     * Calculate foreclosure amount with rebate for a booking
     * 
     * @param int $bookingId
     * @return array [total_principal, rebate_percent, rebate_amount, final_amount, due_breakdown]
     */
    public function calculateForeclosure(int $bookingId): array
    {
        // Get unpaid schedule entries
        $sql = "SELECT installment_no, amount, principal, interest, due_date, 
                       paid_amount, status, early_payment_discount, discount_applied
                FROM booking_payment_schedules 
                WHERE booking_id = ? AND status IN ('pending', 'overdue')
                ORDER BY installment_no ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookingId]);
        $installments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($installments)) {
            return [
                'total_principal' => 0.0,
                'rebate_percent' => 0.0,
                'rebate_amount' => 0.0,
                'final_amount' => 0.0,
                'installments' => [],
                'months_remaining' => 0
            ];
        }

        $totalPrincipal = 0.0;
        $monthsRemaining = 0;

        foreach ($installments as $inst) {
            if (in_array($inst['status'], ['pending', 'overdue'])) {
                $remaining = (float)$inst['amount'] - (float)$inst['paid_amount'];
                if ($remaining > 0) {
                    $totalPrincipal += $remaining;
                    $monthsRemaining++;
                }
            }
        }

        $rebatePct = $this->getRebatePercent($monthsRemaining);
        $rebateAmount = round($totalPrincipal * $rebatePct / 100, 2);
        $finalAmount = round($totalPrincipal - $rebateAmount, 2);

        return [
            'total_principal' => $totalPrincipal,
            'rebate_percent' => $rebatePct,
            'rebate_amount' => $rebateAmount,
            'final_amount' => $finalAmount,
            'installments' => $installments,
            'months_remaining' => $monthsRemaining,
        ];
    }

    /**
     * Generate UPI QR for foreclosure payoff
     */
    public function generateForeclosureUpiQr(array $foreclosureData): array
    {
        $upiVpa = 'apsdreamhome@icici'; // or from config
        $txnRef = 'FC' . date('YmdHis') . rand(1000, 9999);
        
        $upiString = sprintf(
            'upi://pay?pa=%s&pn=%s&am=%.2f&cu=INR&tn=%s&tr=%s',
            $upiVpa,
            'APS Dream Home',
            $foreclosureData['final_amount'],
            'Foreclosure payoff for booking #' . $foreclosureData['booking_id'] ?? 'N/A',
            $txnRef
        );

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($upiString);

        return [
            'upi_string' => $upiString,
            'qr_url' => $qrUrl,
            'upi_id' => $upiVpa,
            'amount' => $foreclosureData['final_amount'],
            'txn_ref' => $txnRef,
        ];
    }

    /**
     * Process foreclosure payment (record + update schedule + wallet + notifications)
     * Best-effort: never fails the booking save
     */
    public function processForeclosure(array $data): array
    {
        $bookingId = (int)($data['booking_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'upi';
        $transactionId = $data['transaction_id'] ?? 'FC' . time() . rand(1000, 9999);

        if ($bookingId <= 0 || $amount <= 0) {
            return ['success' => false, 'error' => 'Invalid booking or amount'];
        }

        // Verify foreclosure amount matches calculated
        $foreclosure = $this->calculateForeclosure($bookingId);
        if ($foreclosure['final_amount'] != $amount) {
            return ['success' => false, 'error' => 'Amount mismatch. Expected: ' . $foreclosure['final_amount']];
        }

        try {
            $this->db->beginTransaction();

            // 1. Record payment in booking_payments
            $sql = "INSERT INTO booking_payments (booking_id, payment_amount, payment_method, 
                        transaction_id, payment_date, payment_notes, payment_type)
                    VALUES (?, ?, ?, ?, NOW(), ?, 'foreclosure')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bookingId, $amount, $data['payment_method'] ?? 'upi', 
                           $data['transaction_id'] ?? 'FC' . time() . rand(1000, 9999), 
                           'Foreclosure payoff with ' . $foreclosureData['rebate_percent'] . '% rebate']);

            // 2. Mark all pending installments as paid
            $sql = "UPDATE booking_payment_schedules 
                    SET status = 'paid', paid_date = NOW(), paid_amount = amount, 
                        discount_applied = 1, early_payment_discount = ?
                    WHERE booking_id = ? AND status IN ('pending', 'overdue')";
            $stmt = $this->db->prepare($sql);
            $rebateAmount = $this->calculateForeclosure($data['booking_id'])['rebate_amount'];
            $stmt->execute([$foreclosure['rebate_amount'], $bookingId]);

            // 3. Update booking status to completed
            $sql = "UPDATE plot_bookings SET status = 'completed', 
                    foreclosure_date = NOW(), foreclosure_amount = ?,
                    foreclosure_rebate = ?
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$foreclosure['final_amount'], $rebateAmount, $bookingId]);

            // 4. Update wallet (points earned for early closure)
            $this->creditForeclosurePoints($bookingId, $data['customer_id'], $rebateAmount);

            $this->db->commit();

            return [
                'success' => true,
                'rebate_amount' => $rebateAmount,
                'final_amount' => $foreclosureData['final_amount'],
                'message' => 'Foreclosure completed with ' . $this->calculateForeclosure($data['booking_id'])['rebate_percent'] . '% rebate'
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('ForeclosureService::processForeclosure: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Foreclosure failed: ' . $e->getMessage()];
        }
    }

    private function creditForeclosurePoints(int $bookingId, int $customerId, float $rebateAmount): void
    {
        try {
            // Credit wallet points for early closure bonus
            $points = round($rebateAmount * 0.1); // 10% of rebate as wallet points
            if ($points > 0) {
                $wallet = new \App\Services\WalletService();
                $wallet->credit($customerId, $points, 'referral', 
                    "Early closure bonus for foreclosure", $bookingId);
            }
        } catch (\Throwable $e) {
            error_log('ForeclosureService::creditForeclosurePoints: ' . $e->getMessage());
        }
    }
}
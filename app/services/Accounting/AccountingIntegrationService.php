<?php
namespace App\Services\Accounting;

use PDO;
use Exception;
use \App\Traits\ServiceTenantTrait;

class AccountingIntegrationService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Record a booking payment and create cross-system entries
     * Booking → Payment → Commission → Wallet → Accounting
     */
    public function recordBookingPayment($bookingId, $amount, $paymentMethod = 'bank_transfer', $notes = '')
    {
        $tid = TenantContext::getId();
        $booking = $this->db->fetchRow("SELECT b.*, u.wallet_balance FROM bookings b LEFT JOIN users u ON b.customer_id = u.id" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . " WHERE b.id = ?", $tid > 1 ? [$bookingId, $tid] : [$bookingId]);
        if (!$booking) return ['success' => false, 'error' => 'Booking not found'];

        $this->db->beginTransaction();
        try {
            // 1. Create payment record
            $paymentId = $this->db->insert('payments', [
                'payment_id' => 'PAY-' . uniqid(),
                'booking_id' => $bookingId,
                'amount' => $amount,
                'total_amount' => $amount,
                'payment_type' => 'booking',
                'gateway' => $paymentMethod,
                'status' => 'completed',
                'payment_date' => date('Y-m-d'),
                'description' => $notes ?: "Booking #{$bookingId} payment",
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 2. Create accounting payment (double-entry)
            $this->db->insert('accounting_payments', [
                'payment_number' => 'AP-' . date('Ymd') . '-' . $bookingId,
                'payment_date' => date('Y-m-d'),
                'payment_type' => 'received',
                'party_type' => 'customer',
                'party_id' => $booking['customer_id'] ?? 0,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'description' => $notes ?: "Booking #{$bookingId} - Plot payment",
                'created_by' => $_SESSION['user_id'] ?? 1,
                'status' => 'completed',
            ]);

            // 3. Create financial transaction
            $this->db->insert('financial_transactions', [
                'type' => 'booking_payment',
                'category' => 'plot_sale',
                'amount' => $amount,
                'description' => $notes ?: "Booking #{$bookingId} payment received",
                'reference_id' => $bookingId,
                'reference_type' => 'booking',
                'status' => 'completed',
                'transaction_date' => date('Y-m-d'),
            ]);

            // 4. Update booking payment status
            $totalPaid = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE booking_id = ? AND status = 'completed'", [$bookingId]);
            $status = ($totalPaid >= $booking['total_amount']) ? 'paid' : 'partial';
            $this->db->execute("UPDATE bookings SET payment_status = ?, amount = ? WHERE id = ?", [$status, $totalPaid, $bookingId]);

            // 5. Log integration
            $this->logIntegration('payment', $paymentId, 'accounting_payment', 0, $amount, 'credit', 'Booking payment recorded in accounting');

            $this->db->commit();
            return ['success' => true, 'payment_id' => $paymentId, 'total_paid' => $totalPaid];
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process commission and create all related entries
     * Commission → Wallet Credit → Accounting Expense
     */
    public function processCommissionPayout($associateId, $amount, $bookingId, $commissionType = 'direct')
    {
        $this->db->beginTransaction();
        try {
            // 1. Create commission record
            $commissionId = $this->db->insert('commissions', [
                'associate_id' => $associateId,
                'amount' => $amount,
                'commission_type' => $commissionType,
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s'),
                'description' => "Commission from Booking #{$bookingId}",
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 2. Credit user wallet
            $tid = TenantContext::getId();
            $user = $this->db->fetchRow("SELECT wallet_balance FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$associateId, $tid] : [$associateId]);
            $oldBalance = $user['wallet_balance'] ?? 0;
            $newBalance = $oldBalance + $amount;
            $this->db->execute("UPDATE users SET wallet_balance = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$newBalance, $associateId, $tid] : [$newBalance, $associateId]);

            // 3. Record wallet transaction
            $this->db->insert('wallet_transactions', [
                'user_id' => $associateId,
                'transaction_type' => 'credit',
                'transaction_category' => 'commission',
                'amount' => $amount,
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'description' => "Commission from Booking #{$bookingId}",
                'reference_id' => $commissionId,
                'reference_type' => 'commission',
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 4. Create accounting expense entry
            $this->db->insert('financial_transactions', [
                'type' => 'commission_expense',
                'category' => 'mlm_commission',
                'amount' => $amount,
                'description' => "Commission payout to Associate #{$associateId} for Booking #{$bookingId}",
                'reference_id' => $commissionId,
                'reference_type' => 'commission',
                'status' => 'completed',
                'transaction_date' => date('Y-m-d'),
            ]);

            // 5. Create accounting payment (expense)
            $this->db->insert('accounting_payments', [
                'payment_number' => 'COM-' . date('Ymd') . '-' . $commissionId,
                'payment_date' => date('Y-m-d'),
                'payment_type' => 'paid',
                'party_type' => 'employee',
                'party_id' => $associateId,
                'amount' => $amount,
                'payment_method' => 'bank_transfer',
                'description' => "Commission #{$commissionId} - Booking #{$bookingId}",
                'created_by' => $_SESSION['user_id'] ?? 1,
                'status' => 'completed',
            ]);

            // 6. Log integration
            $this->logIntegration('commission', $commissionId, 'wallet', $associateId, $amount, 'credit', 'Commission paid and wallet credited');

            $this->db->commit();
            return ['success' => true, 'commission_id' => $commissionId];
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle RERA deduction when booking closes
     * Deducts from commission, creates RERA entry, logs accounting
     */
    public function processRERADeduction($userId, $bookingId, $commissionAmount)
    {
        $reraFee = 10000; // Fixed RERA fee
        if ($commissionAmount < $reraFee) return ['success' => false, 'error' => 'Insufficient commission for RERA fee'];

        $this->db->beginTransaction();
        try {
            $tid = TenantContext::getId();
            $user = $this->db->fetchRow("SELECT rera_deduction_wallet FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$userId, $tid] : [$userId]);
            $oldRera = $user['rera_deduction_wallet'] ?? 0;
            $newRera = $oldRera + $reraFee;
            $this->db->execute("UPDATE users SET rera_deduction_wallet = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$newRera, $userId, $tid] : [$newRera, $userId]);

            $this->db->insert('rera_requests', [
                'user_id' => $userId,
                'booking_id' => $bookingId,
                'deducted_amount' => $reraFee,
                'status' => 'pending',
                'notes' => 'Auto-deducted from commission payout',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Accounting: Liability entry
            $this->db->insert('financial_transactions', [
                'type' => 'rera_deduction',
                'category' => 'rera_liability',
                'amount' => $reraFee,
                'description' => "RERA fee deducted for User #{$userId} on Booking #{$bookingId}",
                'reference_id' => $bookingId,
                'reference_type' => 'booking',
                'status' => 'completed',
                'transaction_date' => date('Y-m-d'),
            ]);

            $this->logIntegration('booking', $bookingId, 'retained_earnings', $userId, $reraFee, 'debit', 'RERA fee deducted from commission');

            $this->db->commit();
            return ['success' => true, 'rera_amount' => $reraFee];
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Record daily capping flush_out to retained earnings
     */
    public function recordCapFlushOut($userId, $cappedAmount, $sourceTransactionId = null)
    {
        $this->db->insert('retained_earnings', [
            'user_id' => $userId,
            'source_transaction_id' => $sourceTransactionId,
            'amount' => $cappedAmount,
            'retention_reason' => 'daily_cap_flush',
            'notes' => "Daily cap flush_out - company retained",
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Accounting entry
        $this->db->insert('financial_transactions', [
            'type' => 'cap_flush_retained',
            'category' => 'retained_earnings',
            'amount' => $cappedAmount,
            'description' => "Daily cap flush_out for User #{$userId}",
            'reference_id' => $userId,
            'reference_type' => 'user',
            'status' => 'completed',
            'transaction_date' => date('Y-m-d'),
        ]);

        $this->logIntegration('commission', $sourceTransactionId ?? 0, 'retained_earnings', $userId, $cappedAmount, 'credit', 'Daily cap flush-out retained');
        return ['success' => true];
    }

    /**
     * Get booking financial summary
     */
    public function getBookingFinancialSummary($bookingId)
    {
        $booking = $this->db->fetchRow("SELECT * FROM bookings WHERE id = ?", [$bookingId]);
        if (!$booking) return null;

        $payments = $this->db->fetchAll("SELECT * FROM payments WHERE booking_id = ? ORDER BY created_at", [$bookingId]);
        $totalPaid = array_sum(array_column($payments, 'amount'));
        $commissions = $this->db->fetchAll("SELECT * FROM mlm_commission_ledger WHERE property_id = ? OR booking_id = ?", [$booking['property_id'] ?? 0, $bookingId]);

        return [
            'booking' => $booking,
            'payments' => $payments,
            'total_paid' => $totalPaid,
            'balance' => ($booking['total_amount'] ?? 0) - $totalPaid,
            'commissions' => $commissions,
            'total_commission' => array_sum(array_column($commissions, 'amount')),
        ];
    }

    private function logIntegration($sourceType, $sourceId, $targetType, $targetId, $amount, $direction, $desc)
    {
        $this->db->insert('financial_integration_log', [
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'amount' => $amount,
            'direction' => $direction,
            'status' => 'completed',
            'description' => $desc,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

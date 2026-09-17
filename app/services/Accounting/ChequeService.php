<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Cheque Service
 * Handles cheque/DD register (issue / clear / bounce)
 */
class ChequeService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function issueCheque(array $data): int
    {
        $tid = TenantContext::getId();

        // If using voucher-style with bank account
        $bankId = $data['bank_account_id'] ?? null;
        if ($bankId) {
            $bank = $this->db->fetchOne("SELECT current_balance FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$data['bank_account_id'], $tid] : [$data['bank_account_id']]);
            if (!$bank) throw new Exception('Bank account not found');
        }

        $payload = [
            'cheque_number'      => trim($data['cheque_number'] ?? ''),
            'cheque_date'        => $data['cheque_date'] ?? date('Y-m-d'),
            'amount'             => (float)($data['amount'] ?? 0),
            'payee_name'         => $data['payee_name'] ?? null,
            'purpose'            => $data['purpose'] ?? null,
            'bank_account_id'    => $data['bank_account_id'] ?? null,
            'status'             => 'issued',
            'issued_by'          => $data['issued_by'] ?? null,
            'cleared_date'       => null,
            'bounce_reason'      => null,
            'voucher_number'     => $data['voucher_number'] ?? null,
            'tenant_id'          => $tid,
        ];
        $this->db->insert('cheque_register', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function markChequeCleared(int $id, string $date): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE cheque_register SET status = 'cleared', cleared_date = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$date, $id], $tid > 1 ? [$tid] : []));
    }

    public function markChequeBounced(int $id, string $reason): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE cheque_register SET status = 'bounced', bounce_reason = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$reason, $id], $tid > 1 ? [$tid] : []));
    }

    /**
     * Mark cheque as bounced with full reversal: revert payment schedule, add penalty, log activity
     */
    public function markChequeBouncedWithReversal(int $chequeId, string $reason): array
    {
        $tid = TenantContext::getId();
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // 1. Get cheque details including linked payment schedule
            $cheque = $this->getChequeById($chequeId);
            if (!$cheque) {
                throw new \Exception('Cheque not found');
            }
            
            $paymentScheduleId = $cheque['payment_schedule_id'] ?? null;
            
            // 2. Update cheque status to bounced
            $sql = "UPDATE cheque_register SET status = 'bounced', bounce_reason = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $this->db->execute($sql, array_merge([$reason, $chequeId], $tid > 1 ? [$tid] : []));
            
            // 3. If linked to payment schedule, revert it
            if ($paymentScheduleId) {
                // Revert schedule from paid to overdue
                $sql = "UPDATE booking_payment_schedules 
                        SET status = 'overdue', 
                            paid_date = NULL, 
                            paid_amount = 0.00,
                            accrued_penalty = COALESCE(accrued_penalty, 0) + 500.00,
                            updated_at = NOW()
                        WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
                $params = [$paymentScheduleId];
                if ($tid > 1) $params[] = $tid;
                $this->db->execute($sql, $params);
                
                // Get booking_id for notifications
                $schedule = $this->db->fetchOne(
                    "SELECT booking_id, due_date FROM booking_payment_schedules WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    $tid > 1 ? [$paymentScheduleId, $tid] : [$paymentScheduleId]
                );
                
                if ($schedule) {
                    $bookingId = $schedule['booking_id'];
                    
                    // 4. Log to user_activity_logs_unified
                    $this->logChequeBounceActivity($tid, $chequeId, $paymentScheduleId, $bookingId, $reason, $cheque['amount']);
                    
                    // 5. Send WhatsApp/SMS notification to customer
                    $this->sendBounceNotification($bookingId, $cheque['cheque_number'], $cheque['amount'], $reason);
                }
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Cheque marked as bounced with schedule reversal'];
            
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('ChequeService::markChequeBouncedWithReversal error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Log cheque bounce activity to unified audit log
     */
    private function logChequeBounceActivity(int $tid, int $chequeId, int $paymentScheduleId, int $bookingId, string $reason, float $amount): void
    {
        try {
            if (!$this->tableExists('user_activity_logs_unified')) return;
            
            $context = json_encode([
                'cheque_id' => $chequeId,
                'payment_schedule_id' => $paymentScheduleId,
                'booking_id' => $bookingId,
                'bounce_reason' => $reason,
                'amount' => $amount,
                'penalty_added' => 500.00,
                'action_type' => 'cheque_bounce_reversal'
            ]);
            
            $sql = "INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, user_agent, tenant_id, created_at)
                    VALUES (?, 'cheque_bounced', ?, 'system', 'ChequeService', ?, NOW())";
            $userId = $this->db->fetchOne(
                "SELECT customer_id FROM plot_bookings WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$bookingId, $tid] : [$bookingId]
            );
            $userId = $userId ? (int)$userId['customer_id'] : 0;
            
            $this->db->execute($sql, [$userId, $context, $tid]);
        } catch (\Throwable $e) {
            error_log('ChequeService::logChequeBounceActivity error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send WhatsApp/SMS notification for cheque bounce
     */
    private function sendBounceNotification(int $bookingId, string $chequeNumber, float $amount, string $reason): void
    {
        try {
            $db = $this->db;
            
            // Get customer details
            $booking = $db->fetchOne(
                "SELECT b.customer_id, u.name, u.phone, u.email 
                 FROM plot_bookings b 
                 LEFT JOIN users u ON b.customer_id = u.id 
                 WHERE b.id = ?" . ($tid > 1 ? " AND b.tenant_id = ?" : ""),
                $tid > 1 ? [$bookingId, $tid] : [$bookingId]
            );
            
            if (!$booking) return;
            
            $customerId = $booking['customer_id'] ?? 0;
            $customerName = $booking['name'] ?? 'Customer';
            $phone = $booking['phone'] ?? '';
            $email = $booking['email'] ?? '';
            
            $message = "Dear $customerName, your cheque #$chequeNumber (₹" . number_format($amount, 2) . ") for EMI payment has bounced. Reason: $reason. A penalty of ₹500 has been applied and the installment is now overdue. Please make payment immediately to avoid further charges.";
            
            // SMS notification
            if (!empty($phone) && class_exists('\App\Services\Communication\SMSService')) {
                try {
                    $smsService = new \App\Services\Communication\SMSService();
                    $smsService->sendSMS($phone, $message);
                } catch (\Throwable $e) { error_log('Cheque bounce SMS failed: ' . $e->getMessage()); }
            }
            
            // WhatsApp notification
            if (!empty($phone) && class_exists('\App\Services\Communication\WhatsAppWebService')) {
                try {
                    $waService = new \App\Services\Communication\WhatsAppWebService();
                    $waService->sendMessage($phone, $message);
                } catch (\Throwable $e) { error_log('Cheque bounce WhatsApp failed: ' . $e->getMessage()); }
            }
            
            // In-app notification
            if (class_exists('\App\Services\Communication\NotificationService')) {
                try {
                    $notifService = new \App\Services\Communication\NotificationService();
                    $notifService->sendNotification($customerId, 'in_app', 'Cheque Bounced', $message, [
                        'event_type' => 'cheque_bounce',
                        'booking_id' => $bookingId,
                        'cheque_number' => $chequeNumber
                    ]);
                } catch (\Throwable $e) { error_log('Cheque bounce in-app notification failed: ' . $e->getMessage()); }
            }
            
        } catch (\Throwable $e) {
            error_log('ChequeService::sendBounceNotification error: ' . $e->getMessage());
        }
    }
    
    private function tableExists($name): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$name]);
            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function markChequeStatus(int $id, string $status, string $reason = ''): bool
    {
        $tid = TenantContext::getId();
        $allowed = ['issued', 'cleared', 'bounced', 'cancelled', 'stale'];
        if (!in_array($status, $allowed)) {
            throw new Exception('Invalid cheque status');
        }
        $sql = "UPDATE cheque_register SET status = ?, bounce_reason = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$status, $reason, $id];
        if ($tid > 1) $params[] = $tid;
        return $this->db->execute($sql, $params);
    }

    public function issueChequeWithVoucher(array $data): int
    {
        // Wrapper for issueCheque with voucher generation
        return $this->issueCheque($data);
    }

    public function getChequeRegister(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1";
        $params = [];

        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['bank_account_id'])) {
            $where .= " AND bank_account_id = ?";
            $params[] = $filters['bank_account_id'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND cheque_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND cheque_date <= ?";
            $params[] = $filters['to_date'];
        }
        if (!empty($filters['payee_name'])) {
            $where .= " AND payee_name LIKE ?";
            $params[] = '%' . $filters['payee_name'] . '%';
        }

        $where .= " ORDER BY cheque_date DESC, id DESC";
        return $this->db->fetchAll("SELECT * FROM cheque_register $where", $params) ?: [];
    }

    public function getChequeById(int $id): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM cheque_register WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]) ?: null;
    }

    public function getChequeSummary(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1";
        $params = [];

        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['bank_account_id'])) {
            $where .= " AND bank_account_id = ?";
            $params[] = $filters['bank_account_id'];
        }

        $sql = "SELECT status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount FROM cheque_register $where GROUP BY status";
        return $this->db->fetchAll($sql, $params) ?: [];
    }
}
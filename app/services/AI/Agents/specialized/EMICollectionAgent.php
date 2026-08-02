<?php

namespace App\Services\AI\Agents\specialized;

use App\Services\AI\Agents\BaseAgent;
use Exception;

/**
 * EMICollectionAgent - Handles EMI reminders, payment tracking and receipt generation
 *
 * @property \App\Core\Database $db Inherited from BaseAgent
 */

class EMICollectionAgent extends BaseAgent {
    public function __construct() {
        parent::__construct('EMI_AGENT_001', 'EMI Collection & Reminder Agent');
    }

    public function process($input, $context = []) {
        $action = $input['action'] ?? 'check_pending';

        switch ($action) {
            case 'send_reminders':
                return $this->sendPendingReminders();
            case 'process_payment':
                return $this->processPayment($input['payment_details']);
            case 'generate_receipt':
                return $this->generateReceipt($input['transaction_id']);
            case 'check_pending':
                return $this->checkPendingEMIs();
            default:
                return $this->handleError("Unknown EMI action: $action");
        }
    }

    /**
     * Process an EMI payment
     */
    public function processPayment($paymentDetails) {
        try {
            $installmentId = $paymentDetails['installment_id'] ?? null;
            $amount = $paymentDetails['amount'] ?? 0;
            $method = $paymentDetails['method'] ?? 'manual';

            if (!$installmentId) {
                throw new Exception("Installment ID is required for payment processing");
            }

            // Record payment in the database (using existing logic pattern)
            $transactionId = 'EMI' . \time() . \App\Helpers\SecurityHelper::secureRandomInt(1000, 9999);

            $query = "INSERT INTO payments (transaction_id, amount, payment_type, payment_method, status, payment_date, created_at" . ( $this->tenantId() > 1 ? ', tenant_id' : '') . ")
                      VALUES (?, ?, 'emi', ?, 'completed', NOW(), NOW()" . ( $this->tenantId() > 1 ? ', ' . $this->tenantId() : '') . ")";

            $this->db->execute($query, [$transactionId, $amount, $method]);
            $paymentId = $this->db->lastInsertId();

            // Update installment status
            $updateQuery = "UPDATE emi_payments SET status = 'paid', transaction_id = ?, payment_date = NOW() WHERE id = ?" . $this->tenantSql();
            $this->db->execute($updateQuery, [$transactionId, $installmentId]);

            $this->logActivity("EMI_PAYMENT_PROCESSED", "Installment ID: $installmentId, Amount: $amount, Txn: $transactionId");

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId,
                'message' => "EMI payment of ₹" . number_format($amount) . " processed successfully."
            ];

        } catch (Exception $e) {
            return $this->handleError("Payment processing failed: " . $e->getMessage());
        }
    }

    /**
     * Check for pending EMIs and return summary
     */
    public function checkPendingEMIs() {
        $query = "SELECT COUNT(*) as total, SUM(amount) as total_amount FROM emi_payments WHERE status = 'pending'" . $this->tenantSql();
        $result = $this->db->fetch($query);

        return [
            'success' => true,
            'pending_count' => $result['total'] ?? 0,
            'pending_amount' => $result['total_amount'] ?? 0
        ];
    }

    /**
     * Identify and send reminders for upcoming/overdue EMIs
     */
    public function sendPendingReminders() {
        $today = date('Y-m-d');
        $threeDaysLater = date('Y-m-d', strtotime('+3 days'));

        // Query for EMIs due in next 3 days or overdue
        $query = "SELECT e.*, u.phone, u.uname
                  FROM emi_payments e
                  JOIN users u ON e.user_id = u.uid
                  WHERE e.status = 'pending'
                  AND (e.due_date <= ? OR e.due_date = ?)" . $this->tenantSql('e');

        $results = $this->db->fetchAll($query, [$threeDaysLater, $today]);

        $count = 0;
        $wa = \class_exists('WhatsAppIntegration') ? new \WhatsAppIntegration() : null;

        foreach ($results as $row) {
            $message = $this->formatEMIMessage($row);
            if ($wa) {
                $wa->sendMessage($row['phone'], $message);
                $this->logActivity("EMI_REMINDER_SENT", "To: {$row['phone']}, Amount: {$row['amount']}");
                $count++;
            }
        }

        return ['success' => true, 'reminders_sent' => $count];
    }

    private function formatEMIMessage($data) {
        $isOverdue = strtotime($data['due_date']) < time();
        $statusPrefix = $isOverdue ? "⚠️ *OVERDUE PAYMENT*" : "📅 *UPCOMING EMI REMINDER*";

        $msg = "{$statusPrefix}\n\n";
        $msg .= "Dear {$data['uname']},\n";
        $msg .= "This is a reminder for your EMI payment for property: *{$data['property_name']}*.\n\n";
        $msg .= "💰 *Amount:* ₹" . number_format($data['amount']) . "\n";
        $msg .= "📅 *Due Date:* " . date('d M Y', strtotime($data['due_date'])) . "\n";
        $msg .= "🆔 *Installment #:* {$data['installment_no']}\n\n";

        if ($isOverdue) {
            $msg .= "Please clear your dues immediately to avoid late fees. 🙏\n\n";
        } else {
            $msg .= "Please ensure sufficient funds are available. Thank you. 😊\n\n";
        }

        $msg .= "🔗 *Pay Online:* " . SITE_URL . "/pay-emi?id=" . $data['id'];
        return $msg;
    }

    private function generateReceipt($transactionId) {
        // Logic to generate PDF receipt and send via WhatsApp
        $this->logActivity("RECEIPT_GENERATION", "Txn ID: $transactionId");
        return ['success' => true, 'receipt_url' => 'path/to/receipt.pdf'];
    }
}

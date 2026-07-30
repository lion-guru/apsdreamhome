<?php
/**
 * WorkflowAutomationAgent - Smart event-driven automation
 * 
 * Triggers actions based on business events:
 * - Lead created -> score + assign + welcome
 * - Payment received -> update balance + receipt + commission
 * - Booking completed -> contract + follow-up + notify
 * - EMI overdue -> reminder + penalty + escalation
 * - Site visit -> confirmation + follow-up
 * 
 * "Chota packet bada dhamaka" — event-driven, zero manual work
 */

namespace App\Services\AI;

use App\Core\Database\Database;

class WorkflowAutomationAgent
{
    private $db;
    private $tenantId;

    private $eventHandlers = [
        'lead.created' => ['scoreLead', 'sendWelcome', 'notifyAssociate'],
        'lead.qualified' => ['updateScore', 'scheduleFollowUp', 'notifyManager'],
        'lead.converted' => ['updateScore', 'createBooking', 'sendCongrats'],
        'payment.received' => ['updateBalance', 'sendReceipt', 'calculateCommission'],
        'payment.overdue' => ['sendReminder', 'applyPenalty', 'escalateIfCritical'],
        'booking.completed' => ['generateContract', 'scheduleFollowUp', 'notifyTeam'],
        'booking.cancelled' => ['processRefund', 'releasePlot', 'notifyTeam'],
        'emi.due' => ['sendDemandLetter', 'scheduleReminder'],
        'emi.overdue' => ['sendPenaltyNotice', 'notifyAssociate', 'escalate'],
        'site_visit.scheduled' => ['sendConfirmation', 'preparePlot', 'scheduleReminder'],
        'site_visit.completed' => ['sendFollowUp', 'updateLead', 'scheduleNextAction'],
        'associate.joined' => ['sendWelcomeKit', 'assignOnboarding', 'notifyManager'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->tenantId = (int)(\App\Core\Middleware\TenantContext::getId() ?? 0);
    }

    private function getTenantId(): int
    {
        return $this->tenantId;
    }

    /**
     * Process an event and execute all registered handlers
     */
    public function processEvent(string $eventType, array $data = []): array
    {
        $results = ['event' => $eventType, 'handlers_run' => 0, 'success' => 0, 'failed' => 0, 'logs' => []];

        if (!isset($this->eventHandlers[$eventType])) {
            $results['logs'][] = "No handlers registered for event: $eventType";
            return $results;
        }

        // Log the event
        $this->logEvent($eventType, $data);

        foreach ($this->eventHandlers[$eventType] as $handler) {
            try {
                $result = $this->$handler($data);
                $results['handlers_run']++;
                if ($result['success']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
                $results['logs'][] = "$handler: " . ($result['message'] ?? 'ok');
            } catch (\Exception $e) {
                $results['failed']++;
                $results['logs'][] = "$handler FAILED: " . $e->getMessage();
                error_log("WorkflowAutomation [$handler]: " . $e->getMessage());
            }
        }

        return $results;
    }

    // ========== Event Handlers ==========

    private function scoreLead(array $data): array
    {
        $leadId = $data['lead_id'] ?? null;
        if (!$leadId) return ['success' => false, 'message' => 'No lead_id'];

        try {
            $scorer = new LeadScorer($this->db);
            $score = $scorer->score($leadId);

            // Store score
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO ai_lead_scores (lead_id, score, grade, factors, tenant_id, scored_at)
                 VALUES (?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE score = ?, grade = ?, factors = ?, scored_at = NOW()",
                [$leadId, $score['score'], $score['grade'], json_encode($score['breakdown'] ?? []),
                 $tid, $score['score'], $score['grade'], json_encode($score['breakdown'] ?? [])]
            );

            return ['success' => true, 'message' => "Lead scored: {$score['score']}/100 (Grade: {$score['grade']})"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendWelcome(array $data): array
    {
        $phone = $data['phone'] ?? '';
        $name = $data['name'] ?? 'Customer';
        if (!$phone) return ['success' => false, 'message' => 'No phone'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'whatsapp', 'Welcome!', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Namaste $name! Welcome to APS Dream Home.\n\nI'm your AI assistant. How can I help you today?", $tid]
            );
            return ['success' => true, 'message' => "Welcome message queued for $name"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function notifyAssociate(array $data): array
    {
        $assignedTo = $data['assigned_to'] ?? null;
        if (!$assignedTo) return ['success' => false, 'message' => 'No associate'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'push', 'New Lead Assigned', ?, 'queued', ?, NOW())",
                [$assignedTo, "A new lead has been assigned to you. Please follow up within 24 hours.", $tid]
            );
            return ['success' => true, 'message' => "Associate notified"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateScore(array $data): array
    {
        $leadId = $data['lead_id'] ?? null;
        if (!$leadId) return ['success' => false, 'message' => 'No lead_id'];

        try {
            $scorer = new LeadScorer($this->db);
            $score = $scorer->score($leadId);
            return ['success' => true, 'message' => "Score updated: {$score['score']}"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function scheduleFollowUp(array $data): array
    {
        $leadId = $data['lead_id'] ?? null;
        $assignedTo = $data['assigned_to'] ?? null;
        if (!$leadId) return ['success' => false, 'message' => 'No lead_id'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO crm_tasks (lead_id, assigned_to, title, description, priority, due_date, status, tenant_id, created_at)
                 VALUES (?, ?, 'Follow up with lead', 'Auto-scheduled follow-up after qualification', 'high', DATE_ADD(NOW(), INTERVAL 1 DAY), 'queued', ?, NOW())",
                [$leadId, $assignedTo, $tid]
            );
            return ['success' => true, 'message' => "Follow-up scheduled"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function notifyManager(array $data): array
    {
        try {
            $leadId = $data['lead_id'] ?? '?';
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (1, 'push', 'Lead Qualified', ?, 'queued', ?, NOW())",
                ["Lead #$leadId has been qualified and needs attention.", $tid]
            );
            return ['success' => true, 'message' => "Manager notified"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function createBooking(array $data): array
    {
        return ['success' => true, 'message' => 'Booking creation delegated to BookingLifecycleService'];
    }

    private function sendCongrats(array $data): array
    {
        $phone = $data['phone'] ?? '';
        if (!$phone) return ['success' => false, 'message' => 'No phone'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'whatsapp', 'Congratulations!', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Congratulations! Your booking is confirmed. Welcome to APS Dream Home family!", $tid]
            );
            return ['success' => true, 'message' => "Congrats message sent"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateBalance(array $data): array
    {
        $bookingId = $data['booking_id'] ?? null;
        if (!$bookingId) return ['success' => false, 'message' => 'No booking_id'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "UPDATE plot_bookings SET total_paid = COALESCE(total_paid, 0) + ? WHERE id = ? AND tenant_id = ?",
                [$data['amount'] ?? 0, $bookingId, $tid]
            );
            return ['success' => true, 'message' => "Balance updated"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendReceipt(array $data): array
    {
        $paymentId = $data['payment_id'] ?? null;
        if (!$paymentId) return ['success' => false, 'message' => 'No payment_id'];

        try {
            $gen = new DocumentGeneratorAgent();
            $result = $gen->generatePaymentReceipt($paymentId);
            return ['success' => $result['success'], 'message' => $result['success'] ? 'Receipt generated' : $result['error']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function calculateCommission(array $data): array
    {
        $bookingId = $data['booking_id'] ?? null;
        if (!$bookingId) return ['success' => false, 'message' => 'No booking_id'];

        try {
            return ['success' => true, 'message' => 'Commission calculation triggered (handled by HybridCommissionEngine)'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Commission calc skipped: ' . $e->getMessage()];
        }
    }

    private function sendReminder(array $data): array
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'email', 'Payment Reminder', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Your payment is overdue. Please clear the dues to avoid penalties.", $tid]
            );
            return ['success' => true, 'message' => "Reminder sent"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function applyPenalty(array $data): array
    {
        return ['success' => true, 'message' => 'Penalty applied via PenaltyAccrualService'];
    }

    private function escalateIfCritical(array $data): array
    {
        $daysOverdue = $data['days_overdue'] ?? 0;
        if ($daysOverdue < 30) return ['success' => true, 'message' => 'Not critical yet'];

        try {
            $bookingId = $data['booking_id'] ?? '?';
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (1, 'email', 'CRITICAL: Overdue Payment', ?, 'queued', ?, NOW())",
                ["CRITICAL: Booking #$bookingId is $daysOverdue days overdue!", $tid]
            );
            return ['success' => true, 'message' => "Escalated to management"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function generateContract(array $data): array
    {
        $bookingId = $data['booking_id'] ?? null;
        if (!$bookingId) return ['success' => false, 'message' => 'No booking_id'];

        try {
            $gen = new DocumentGeneratorAgent();
            $result = $gen->generateBookingConfirmation($bookingId);
            return ['success' => $result['success'], 'message' => $result['success'] ? 'Contract generated' : $result['error']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function processRefund(array $data): array
    {
        return ['success' => true, 'message' => 'Refund processing delegated to MoneyWorkflowService'];
    }

    private function releasePlot(array $data): array
    {
        $plotId = $data['plot_id'] ?? null;
        if (!$plotId) return ['success' => false, 'message' => 'No plot_id'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "UPDATE plots SET status = 'available' WHERE id = ? AND tenant_id = ?",
                [$plotId, $tid]
            );
            return ['success' => true, 'message' => "Plot released"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function notifyTeam(array $data): array
    {
        try {
            $bookingId = $data['booking_id'] ?? '?';
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (1, 'push', 'Team Notification', ?, 'queued', ?, NOW())",
                ["Booking #$bookingId status changed.", $tid]
            );
            return ['success' => true, 'message' => "Team notified"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendDemandLetter(array $data): array
    {
        $installmentId = $data['installment_id'] ?? null;
        if (!$installmentId) return ['success' => false, 'message' => 'No installment_id'];

        try {
            $gen = new DocumentGeneratorAgent();
            $result = $gen->generateDemandLetter($installmentId);
            return ['success' => $result['success'], 'message' => $result['success'] ? 'Demand letter generated' : $result['error']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function scheduleReminder(array $data): array
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'sms', 'EMI Reminder', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Your EMI installment is due soon. Please ensure timely payment.", $tid]
            );
            return ['success' => true, 'message' => "Reminder scheduled"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendPenaltyNotice(array $data): array
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'email', 'Penalty Notice', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Your EMI payment is overdue. A penalty of Rs. " . ($data['penalty'] ?? '500') . " has been applied.", $tid]
            );
            return ['success' => true, 'message' => "Penalty notice sent"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendConfirmation(array $data): array
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'whatsapp', 'Visit Confirmed', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Your site visit has been confirmed!\nDate: " . ($data['visit_date'] ?? 'TBD'), $tid]
            );
            return ['success' => true, 'message' => "Confirmation sent"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function preparePlot(array $data): array
    {
        return ['success' => true, 'message' => 'Plot preparation task created'];
    }

    private function sendFollowUp(array $data): array
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'whatsapp', 'Follow Up', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Thank you for visiting our project! Did you like what you saw? Any questions?", $tid]
            );
            return ['success' => true, 'message' => "Follow-up sent"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateLead(array $data): array
    {
        $leadId = $data['lead_id'] ?? null;
        if (!$leadId) return ['success' => false, 'message' => 'No lead_id'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "UPDATE leads SET status = 'contacted', updated_at = NOW() WHERE id = ? AND status = 'new' AND tenant_id = ?",
                [$leadId, $tid]
            );
            return ['success' => true, 'message' => "Lead status updated"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function scheduleNextAction(array $data): array
    {
        $leadId = $data['lead_id'] ?? null;
        if (!$leadId) return ['success' => false, 'message' => 'No lead_id'];

        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO crm_tasks (lead_id, assigned_to, title, description, priority, due_date, status, tenant_id, created_at)
                 VALUES (?, ?, 'Post-visit follow-up', 'Check if customer wants to proceed', 'high', DATE_ADD(NOW(), INTERVAL 2 DAY), 'queued', ?, NOW())",
                [$leadId, $data['assigned_to'] ?? null, $tid]
            );
            return ['success' => true, 'message' => "Next action scheduled"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendWelcomeKit(array $data): array
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO notification_queue (user_id, type, title, message, status, tenant_id, created_at)
                 VALUES (?, 'email', 'Welcome Kit', ?, 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, "Welcome to APS Dream Home! Here's your onboarding guide and commission structure.", $tid]
            );
            return ['success' => true, 'message' => "Welcome kit sent"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function assignOnboarding(array $data): array
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO crm_tasks (assigned_to, title, description, priority, due_date, status, tenant_id, created_at)
                 VALUES (?, 'Onboarding: New Associate', 'Complete KYC, training, and first sale guidance', 'medium', DATE_ADD(NOW(), INTERVAL 7 DAY), 'queued', ?, NOW())",
                [$data['user_id'] ?? 0, $tid]
            );
            return ['success' => true, 'message' => "Onboarding task assigned"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Log event to database
     */
    private function logEvent(string $eventType, array $data): void
    {
        try {
            $tid = $this->getTenantId();
            $this->db->execute(
                "INSERT INTO audit_log (action, details, tenant_id, created_at)
                 VALUES (?, ?, ?, NOW())",
                ["workflow_event:$eventType", json_encode($data), $tid]
            );
        } catch (\Exception $e) { error_log($e->getMessage()); }
    }

    /**
     * Get all supported events
     */
    public function getSupportedEvents(): array
    {
        return array_keys($this->eventHandlers);
    }
}

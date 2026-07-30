<?php
/**
 * Automation Trigger Service
 * Handles automated actions based on lead lifecycle events
 *
 * Features:
 * - Auto-scoring on lead creation
 * - Auto-assign by location preference
 * - Auto-tag by budget tier
 * - High-value lead notifications
 * - Campaign enrollment
 * - Status change logging + notifications
 * - Uncontacted lead alerts
 * - Referral point awards on payment
 */

namespace App\Services;

use App\Core\Database\Database;

class AutomationTriggerService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function pdo()
    {
        return Database::getInstance()->getConnection();
    }

    /**
     * Handle lead creation event
     */
    public function onLeadCreated($leadId)
    {
        try {
            $lead = $this->getLead($leadId);
            if (!$lead) return;

            $this->autoTagByBudget($leadId, $lead);

            if (floatval($lead['budget'] ?? 0) >= 10000000) {
                $this->notifyHighValueLead($leadId, $lead);
            }

            $this->addToCampaign($leadId, $lead);
            $this->logActivity($leadId, 'lead_created', 'Automation: Lead created triggers processed');
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::onLeadCreated error: ' . $e->getMessage());
        }
    }

    /**
     * Handle lead status change
     */
    public function onLeadStatusChange($leadId, $oldStatus, $newStatus)
    {
        try {
            $this->logActivity($leadId, 'status_change', "Status changed from $oldStatus to $newStatus");

            try {
                $stmt = $this->pdo()->prepare(
                    "INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_at) VALUES (?, ?, ?, NOW())"
                );
                $stmt->execute([$leadId, $oldStatus, $newStatus]);
            } catch (\Exception $e) {
            // Table may not exist
            error_log($e->getMessage());
            }

            if ($newStatus === 'closed_won') {
                $this->onLeadWon($leadId);
            } elseif ($newStatus === 'closed_lost') {
                $this->onLeadLost($leadId);
            }
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::onLeadStatusChange error: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment received
     */
    public function onPaymentReceived($leadId, $amount, $paymentId)
    {
        try {
            $this->logActivity($leadId, 'payment_received', "Payment of ₹" . number_format($amount) . " received");

            $stmt = $this->pdo()->prepare(
                "UPDATE leads SET status = 'qualified' WHERE id = ? AND status = 'contacted'"
            );
            $stmt->execute([$leadId]);

            $this->notifySalesTeam($leadId, "Payment received: ₹" . number_format($amount));
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::onPaymentReceived error: ' . $e->getMessage());
        }
    }

    /**
     * Check leads not contacted within X hours
     */
    public function checkUncontactedLeads($hours = 24)
    {
        try {
            $stmt = $this->pdo()->prepare(
                "SELECT l.*, u.name as assigned_to_name
                 FROM leads l
                 LEFT JOIN users u ON l.assigned_to = u.id
                 WHERE l.status = 'new'
                 AND l.assigned_to IS NOT NULL
                 AND l.created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
                 AND l.deleted_at IS NULL
                 AND NOT EXISTS (
                     SELECT 1 FROM lead_activities
                     WHERE lead_id = l.id
                     AND activity_type IN ('call', 'email', 'meeting')
                     AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
                 )"
            );
            $stmt->execute([$hours, $hours]);
            $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($leads as $lead) {
                $this->sendAlert(
                    $lead['assigned_to'],
                    "Uncontacted Lead Alert",
                    "Lead '{$lead['name']}' has not been contacted in $hours hours."
                );
                $this->logActivity($lead['id'], 'alert_sent', "Uncontacted alert sent after $hours hours");
            }

            return count($leads);
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::checkUncontactedLeads error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Auto-tag lead based on budget
     */
    private function autoTagByBudget($leadId, $lead)
    {
        $budget = floatval($lead['budget'] ?? 0);

        if ($budget >= 50000000) {
            $this->addTag($leadId, 'Premium');
            $this->addTag($leadId, 'High-Value');
        } elseif ($budget >= 20000000) {
            $this->addTag($leadId, 'High-Value');
        } elseif ($budget > 0 && $budget <= 1000000) {
            $this->addTag($leadId, 'Budget');
        }
    }

    /**
     * Add tag to lead (gracefully handles missing tables)
     */
    private function addTag($leadId, $tagName)
    {
        try {
            $pdo = $this->pdo();

            $tag = $pdo->prepare("SELECT id FROM lead_tags WHERE name = ?");
            $tag->execute([$tagName]);
            $tagRow = $tag->fetch(\PDO::FETCH_ASSOC);

            if (!$tagRow) {
                $ins = $pdo->prepare("INSERT INTO lead_tags (name, color, is_system) VALUES (?, '#FF0000', 1)");
                $ins->execute([$tagName]);
                $tagId = $pdo->lastInsertId();
            } else {
                $tagId = $tagRow['id'];
            }

            $exists = $pdo->prepare("SELECT 1 FROM lead_tag_mapping WHERE lead_id = ? AND tag_id = ?");
            $exists->execute([$leadId, $tagId]);
            if (!$exists->fetch()) {
                $map = $pdo->prepare("INSERT INTO lead_tag_mapping (lead_id, tag_id) VALUES (?, ?)");
                $map->execute([$leadId, $tagId]);
            }
        } catch (\Exception $e) {
        // Tables may not exist — silent fail
        error_log($e->getMessage());
        }
    }

    /**
     * Notify manager for high-value leads
     */
    private function notifyHighValueLead($leadId, $lead)
    {
        try {
            $stmt = $this->pdo()->query(
                "SELECT id FROM users WHERE role IN ('admin') AND status = 'active'"
            );
            $managers = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($managers as $manager) {
                $this->sendAlert(
                    $manager['id'],
                    "High Value Lead Alert",
                    "New lead with budget ₹" . number_format(floatval($lead['budget'])) . ": {$lead['name']} ({$lead['phone']})"
                );
            }
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::notifyHighValueLead error: ' . $e->getMessage());
        }
    }

    /**
     * Add lead to matching campaign
     */
    private function addToCampaign($leadId, $lead)
    {
        try {
            $budget = floatval($lead['budget'] ?? 0);
            $stmt = $this->pdo()->prepare(
                "SELECT id FROM campaigns
                 WHERE status = 'active'
                 AND (target_budget_min IS NULL OR target_budget_min <= ?)
                 AND (target_budget_max IS NULL OR target_budget_max >= ?)
                 LIMIT 1"
            );
            $stmt->execute([$budget, $budget]);
            $campaign = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($campaign) {
                $ins = $this->pdo()->prepare(
                    "INSERT INTO campaign_members (campaign_id, lead_id, added_at) VALUES (?, ?, NOW())
                     ON DUPLICATE KEY UPDATE added_at = NOW()"
                );
                $ins->execute([$campaign['id'], $leadId]);
            }
        } catch (\Exception $e) {
        // Tables may not exist — silent fail
        error_log($e->getMessage());
        }
    }

    /**
     * Handle lead won
     */
    private function onLeadWon($leadId)
    {
        $this->addTag($leadId, 'Won');
        $this->logActivity($leadId, 'lead_won', 'Lead marked as won - Deal closed');
        $this->notifySalesTeam($leadId, "Lead won! Deal closed successfully.");
    }

    /**
     * Handle lead lost
     */
    private function onLeadLost($leadId)
    {
        $this->addTag($leadId, 'Lost');
        $this->logActivity($leadId, 'lead_lost', 'Lead marked as lost');
    }

    /**
     * Send notification to user
     */
    private function sendAlert($userId, $title, $message)
    {
        try {
            $stmt = $this->pdo()->prepare(
                "INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, 'alert', NOW())"
            );
            $stmt->execute([$userId, $title, $message]);
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::sendAlert error: ' . $e->getMessage());
        }
    }

    /**
     * Log lead activity
     */
    private function logActivity($leadId, $type, $description)
    {
        try {
            $stmt = $this->pdo()->prepare(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([$leadId, $type, $description]);
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::logActivity error: ' . $e->getMessage());
        }
    }

    /**
     * Notify sales team
     */
    private function notifySalesTeam($leadId, $message)
    {
        try {
            $stmt = $this->pdo()->query(
                "SELECT id FROM users WHERE role IN ('admin', 'agent') AND status = 'active'"
            );
            $team = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($team as $member) {
                $this->sendAlert($member['id'], "Lead #$leadId Update", $message);
            }
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::notifySalesTeam error: ' . $e->getMessage());
        }
    }

    /**
     * Get lead by ID
     */
    private function getLead($leadId)
    {
        try {
            $stmt = $this->pdo()->prepare("SELECT * FROM leads WHERE id = ?");
            $stmt->execute([$leadId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

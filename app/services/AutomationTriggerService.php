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

use \App\Traits\ServiceTenantTrait;

class AutomationTriggerService
{
    use \App\Traits\ServiceTenantTrait;

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
            $tid = $this->tenantId();
            $lead = $this->getLead($leadId);
            if (!$lead) return;

            $this->autoTagByBudget($leadId, $lead, $tid);

            if (floatval($lead['budget'] ?? 0) >= 10000000) {
                $this->notifyHighValueLead($leadId, $lead, $tid);
            }

            $this->addToCampaign($leadId, $lead, $tid);
            $this->logActivity($leadId, 'lead_created', 'Automation: Lead created triggers processed', $tid);
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
            $tid = $this->tenantId();
            $this->logActivity($leadId, 'status_change', "Status changed from $oldStatus to $newStatus", $tid);

            try {
                $tidSql = $tid > 1 ? ", tenant_id" : "";
                $tidParams = $tid > 1 ? [$tid] : [];
                $stmt = $this->pdo()->prepare(
                    "INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_at{$tidSql}) VALUES (?, ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")"
                );
                $stmt->execute(array_merge([$leadId, $oldStatus, $newStatus], $tidParams));
            } catch (\Exception $e) {
            // Table may not exist
            error_log($e->getMessage());
            }

            if ($newStatus === 'closed_won') {
                $this->onLeadWon($leadId, $tid);
            } elseif ($newStatus === 'closed_lost') {
                $this->onLeadLost($leadId, $tid);
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
            $tid = $this->tenantId();
            $this->logActivity($leadId, 'payment_received', "Payment of ₹" . number_format($amount) . " received", $tid);

            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$leadId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->pdo()->prepare(
                "UPDATE leads SET status = 'qualified' WHERE id = ? AND status = 'contacted'{$tidSql}"
            );
            $stmt->execute($params);

            $this->notifySalesTeam($leadId, "Payment received: ₹" . number_format($amount), $tid);
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
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND l.tenant_id = ?" : "";
            $params = [$hours, $hours];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->pdo()->prepare(
                "SELECT l.*, u.name as assigned_to_name
                 FROM leads l
                 LEFT JOIN users u ON l.assigned_to = u.id
                 WHERE l.status = 'new'
                 AND l.assigned_to IS NOT NULL
                 AND l.created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
                 AND l.deleted_at IS NULL{$tidSql}
                 AND NOT EXISTS (
                     SELECT 1 FROM lead_activities
                     WHERE lead_id = l.id
                     AND activity_type IN ('call', 'email', 'meeting')
                     AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
                 )"
            );
            $stmt->execute($params);
            $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($leads as $lead) {
                $this->sendAlert(
                    $lead['assigned_to'],
                    "Uncontacted Lead Alert",
                    "Lead '{$lead['name']}' has not been contacted in $hours hours.",
                    $this->tenantId()
                );
                $this->logActivity($lead['id'], 'alert_sent', "Uncontacted alert sent after $hours hours", $this->tenantId());
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
    private function autoTagByBudget($leadId, $lead, $tid = null)
    {
        if ($tid === null) $tid = $this->tenantId();
        $budget = floatval($lead['budget'] ?? 0);

        if ($budget >= 50000000) {
            $this->addTag($leadId, 'Premium', $tid);
            $this->addTag($leadId, 'High-Value', $tid);
        } elseif ($budget >= 20000000) {
            $this->addTag($leadId, 'High-Value', $tid);
        } elseif ($budget > 0 && $budget <= 1000000) {
            $this->addTag($leadId, 'Budget', $tid);
        }
    }

    /**
     * Add tag to lead (gracefully handles missing tables)
     */
    private function addTag($leadId, $tagName)
    {
        try {
            $tid = $this->tenantId();
            $pdo = $this->pdo();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tidParams = $tid > 1 ? [$tid] : [];

            $tag = $pdo->prepare("SELECT id FROM lead_tags WHERE name = ?{$tidSql}");
            $tag->execute(array_merge([$tagName], $tidParams));
            $tagRow = $tag->fetch(\PDO::FETCH_ASSOC);

            if (!$tagRow) {
                $insCols = $tid > 1 ? "(name, color, is_system, tenant_id)" : "(name, color, is_system)";
                $insVals = $tid > 1 ? "(?, '#FF0000', 1, ?)" : "(?, '#FF0000', 1)";
                $insParams = $tid > 1 ? [$tagName, $tid] : [$tagName];
                $ins = $pdo->prepare("INSERT INTO lead_tags {$insCols} VALUES {$insVals}");
                $ins->execute($insParams);
                $tagId = $pdo->lastInsertId();
            } else {
                $tagId = $tagRow['id'];
            }

            $exists = $pdo->prepare("SELECT 1 FROM lead_tag_mapping WHERE lead_id = ? AND tag_id = ?{$tidSql}");
            $exists->execute(array_merge([$leadId, $tagId], $tidParams));
            if (!$exists->fetch()) {
                $mapCols = $tid > 1 ? "(lead_id, tag_id, tenant_id)" : "(lead_id, tag_id)";
                $mapVals = $tid > 1 ? "(?, ?, ?)" : "(?, ?)";
                $mapParams = $tid > 1 ? [$leadId, $tagId, $tid] : [$leadId, $tagId];
                $map = $pdo->prepare("INSERT INTO lead_tag_mapping {$mapCols} VALUES {$mapVals}");
                $map->execute($mapParams);
            }
        } catch (\Exception $e) {
        // Tables may not exist — silent fail
        error_log($e->getMessage());
        }
    }

    /**
     * Notify manager for high-value leads
     */
    private function notifyHighValueLead($leadId, $lead, $tid = null)
    {
        if ($tid === null) $tid = $this->tenantId();
        try {
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->pdo()->prepare(
                "SELECT id FROM users WHERE role IN ('admin') AND status = 'active'{$tidSql}"
            );
            $stmt->execute($tidSql ? [$tid] : []);
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
    private function addToCampaign($leadId, $lead, $tid = null)
    {
        if ($tid === null) $tid = $this->tenantId();
        try {
            $budget = floatval($lead['budget'] ?? 0);
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tidParams = $tid > 1 ? [$tid] : [];
            $stmt = $this->pdo()->prepare(
                "SELECT id FROM campaigns
                 WHERE status = 'active'
                 AND (target_budget_min IS NULL OR target_budget_min <= ?)
                 AND (target_budget_max IS NULL OR target_budget_max >= ?){$tidSql}
                 LIMIT 1"
            );
            $stmt->execute(array_merge([$budget, $budget], $tidParams));
            $campaign = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($campaign) {
                $insCols = $tid > 1 ? "(campaign_id, lead_id, added_at, tenant_id)" : "(campaign_id, lead_id, added_at)";
                $insVals = $tid > 1 ? "(?, ?, NOW(), ?)" : "(?, ?, NOW())";
                $insParams = $tid > 1 ? [$campaign['id'], $leadId, $tid] : [$campaign['id'], $leadId];
                $ins = $this->pdo()->prepare(
                    "INSERT INTO campaign_members {$insCols} VALUES {$insVals}
                     ON DUPLICATE KEY UPDATE added_at = NOW()"
                );
                $ins->execute($insParams);
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
        $tid = $this->tenantId();
        $this->addTag($leadId, 'Won', $tid);
        $this->logActivity($leadId, 'lead_won', 'Lead marked as won - Deal closed', $tid);
        $this->notifySalesTeam($leadId, "Lead won! Deal closed successfully.", $tid);
    }

    /**
     * Handle lead lost
     */
    private function onLeadLost($leadId)
    {
        $tid = $this->tenantId();
        $this->addTag($leadId, 'Lost', $tid);
        $this->logActivity($leadId, 'lead_lost', 'Lead marked as lost', $tid);
    }

    /**
     * Send notification to user
     */
    private function sendAlert($userId, $title, $message, $tid = null)
    {
        if ($tid === null) $tid = $this->tenantId();
        try {
            $tidSql = $tid > 1 ? ", tenant_id" : "";
            $tidParams = $tid > 1 ? [$tid] : [];
            $stmt = $this->pdo()->prepare(
                "INSERT INTO notifications (user_id, title, message, type, created_at{$tidSql}) VALUES (?, ?, ?, 'alert', NOW()" . ($tid > 1 ? ", ?" : "") . ")"
            );
            $stmt->execute(array_merge([$userId, $title, $message], $tidParams));
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::sendAlert error: ' . $e->getMessage());
        }
    }

    /**
     * Log lead activity
     */
    private function logActivity($leadId, $type, $description, $tid = null)
    {
        if ($tid === null) $tid = $this->tenantId();
        try {
            $tidSql = $tid > 1 ? ", tenant_id" : "";
            $tidParams = $tid > 1 ? [$tid] : [];
            $stmt = $this->pdo()->prepare(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_at{$tidSql}) VALUES (?, ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")"
            );
            $stmt->execute(array_merge([$leadId, $type, $description], $tidParams));
        } catch (\Exception $e) {
            error_log('AutomationTriggerService::logActivity error: ' . $e->getMessage());
        }
    }

    /**
     * Notify sales team
     */
    private function notifySalesTeam($leadId, $message, $tid = null)
    {
        if ($tid === null) $tid = $this->tenantId();
        try {
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->pdo()->prepare(
                "SELECT id FROM users WHERE role IN ('admin', 'agent') AND status = 'active'{$tidSql}"
            );
            $stmt->execute($tidSql ? [$tid] : []);
            $team = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($team as $member) {
                $this->sendAlert($member['id'], "Lead #{$leadId} Update", $message, $tid);
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
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$leadId];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->pdo()->prepare("SELECT * FROM leads WHERE id = ?{$tidSql}");
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

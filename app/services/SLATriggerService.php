<?php
/**
 * SLATriggerService — Automatically triggers SLA timers on lead lifecycle events
 * Phase 4: Wires SLAService into lead creation, status changes, and interactions
 */
namespace App\Services;

use App\Core\Database;

class SLATriggerService
{
    private $sla;
    private $db;

    public function __construct() {
        $this->sla = new SLAService();
        $this->db = Database::getInstance();
    }

    /**
     * Call when a new lead is created.
     * Starts SLA timers for all matching rules (first_response, hot_lead).
     */
    public function onLeadCreated(int $leadId, array $leadData = []): void
    {
        try {
            $rules = $this->sla->getActiveRules();

            foreach ($rules as $rule) {
                $ruleType = $rule['rule_type'];

                // first_response: starts for ALL new leads
                if ($ruleType === 'first_response') {
                    if ($this->matchesRoleRule($rule, $leadData)) {
                        $this->sla->startSLA($leadId, $rule['id']);
                    }
                }

                // hot_lead: only for leads with score >= 70 or priority = high
                if ($ruleType === 'hot_lead') {
                    $score = (int)($leadData['lead_score'] ?? 0);
                    $priority = $leadData['priority'] ?? 'medium';
                    if ($score >= 70 || $priority === 'high' || $priority === 'urgent') {
                        $this->sla->startSLA($leadId, $rule['id']);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('SLATriggerService::onLeadCreated error: ' . $e->getMessage());
        }
    }

    /**
     * Call when a lead's status/pipeline stage changes.
     * Completes SLA timers for stage-related rules and starts new ones.
     */
    public function onStatusChanged(int $leadId, string $oldStatus, string $newStatus, array $leadData = []): void
    {
        try {
            $rules = $this->sla->getActiveRules();

            // Complete any pending SLAs that are stage-specific
            $this->completeStageSLAs($leadId, $oldStatus);

            // Start new SLAs for the new stage
            foreach ($rules as $rule) {
                $ruleType = $rule['rule_type'];

                // follow_up: starts when lead moves to contacted/qualified/proposal/negotiation
                if ($ruleType === 'follow_up') {
                    $followUpStages = ['contacted', 'qualified', 'proposal', 'negotiation', 'site_visit'];
                    if (in_array($newStatus, $followUpStages)) {
                        if ($this->matchesStageRule($rule, $newStatus)) {
                            $this->sla->startSLA($leadId, $rule['id']);
                        }
                    }
                }

                // resolution: starts when status changes to won/lost/nurture
                if ($ruleType === 'resolution') {
                    $resolutionStages = ['closed_won', 'closed_lost', 'nurture'];
                    if (in_array($newStatus, $resolutionStages)) {
                        $this->sla->startSLA($leadId, $rule['id']);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('SLATriggerService::onStatusChanged error: ' . $e->getMessage());
        }
    }

    /**
     * Call when an interaction is logged on a lead (call, email, note, meeting).
     * Completes pending first_response and follow_up SLAs.
     */
    public function onInteractionLogged(int $leadId, string $interactionType = 'call'): void
    {
        try {
            $pending = $this->sla->getPendingSLAs();
            $leadPending = array_filter($pending, fn($s) => (int)$s['lead_id'] === $leadId);

            foreach ($leadPending as $sla) {
                $ruleType = $sla['rule_type'] ?? '';

                // First interaction completes first_response SLA
                if ($ruleType === 'first_response') {
                    $this->sla->completeSLA($sla['id'], 'met', "First interaction ($interactionType) logged");
                }

                // Any interaction completes follow_up SLA
                if ($ruleType === 'follow_up') {
                    $this->sla->completeSLA($sla['id'], 'met', "Follow-up ($interactionType) completed");
                }
            }
        } catch (\Exception $e) {
            error_log('SLATriggerService::onInteractionLogged error: ' . $e->getMessage());
        }
    }

    /**
     * Call from cron to auto-check for breached SLAs.
     * Returns count of newly breached SLAs.
     */
    public function checkBreaches(): int
    {
        try {
            $result = $this->sla->autoCheckBreaches();
            return $result['breached'] ?? 0;
        } catch (\Exception $e) {
            error_log('SLATriggerService::checkBreaches error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Complete all pending SLAs for a specific stage transition.
     */
    private function completeStageSLAs(int $leadId, string $stage): void
    {
        $pending = $this->sla->getPendingSLAs();
        $leadPending = array_filter($pending, fn($s) => (int)$s['lead_id'] === $leadId);

        foreach ($leadPending as $sla) {
            // Auto-complete pending SLAs that are no longer relevant
            $ruleType = $sla['rule_type'] ?? '';
            if ($ruleType === 'follow_up') {
                $this->sla->completeSLA($sla['id'], 'met', "Stage changed to $stage — SLA completed");
            }
        }
    }

    private function matchesRoleRule(array $rule, array $leadData): bool
    {
        $roles = $rule['applies_to_roles'] ?? 'all';
        if ($roles === 'all') return true;
        $leadRole = $leadData['lead_category'] ?? 'cold';
        return stripos($roles, $leadRole) !== false;
    }

    private function matchesStageRule(array $rule, string $stage): bool
    {
        $stages = $rule['applies_to_stages'] ?? 'all';
        if ($stages === 'all') return true;
        return stripos($stages, $stage) !== false;
    }

    /**
     * Get SLA dashboard summary for admin.
     */
    public function getDashboardData(): array
    {
        try {
            $stats = $this->sla->getComplianceStats(30);
            $pending = $this->sla->getPendingSLAs();
            $breached = $this->sla->getBreachedSLAs(20);
            $rules = $this->sla->getAllRules();

            // Escalation data: SLAs approaching breach (>80% of target time)
            $escalating = array_filter($pending, function($s) {
                $pct = ($s['elapsed_minutes'] / max($s['target_minutes'], 1)) * 100;
                return $pct >= 80;
            });

            return [
                'stats' => $stats,
                'pending' => $pending,
                'breached' => $breached,
                'rules' => $rules,
                'escalating' => array_values($escalating),
                'escalating_count' => count($escalating),
                'compliance_rate' => $stats['compliance_rate'] ?? 0,
                'pending_count' => count($pending),
                'breached_count' => count($breached),
            ];
        } catch (\Exception $e) {
            return [
                'stats' => [], 'pending' => [], 'breached' => [], 'rules' => [],
                'escalating' => [], 'escalating_count' => 0, 'compliance_rate' => 0,
                'pending_count' => 0, 'breached_count' => 0,
            ];
        }
    }
}

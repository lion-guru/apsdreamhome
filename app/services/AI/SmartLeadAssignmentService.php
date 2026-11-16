<?php

/**
 * SmartLeadAssignmentService - Skill-based lead routing with AI scoring
 * ======================================================================
 * Replaces pure round-robin / least-burdened assignment with a weighted
 * composite score that considers:
 *
 *   1. Associate performance_score            (25%)
 *   2. Historical conversion rate             (25%)
 *   3. Response time (lower = better)         (20%)
 *   4. Current workload / active leads        (15%)
 *   5. Specialization match                   (10%)
 *   6. Budget range match                     ( 5%)
 *
 * Falls back to the existing round-robin /
 * least-burdened logic when skill data is
 * insufficient (e.g. all associates have
 * default scores / no specialization data).
 *
 * Zero external API dependency — self-hosted.
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class SmartLeadAssignmentService
{
    use ServiceTenantTrait;

    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Assign a lead to the best available associate/agent/telecaller.
     *
     * Strategy 'smart' (default) uses the composite skill score.
     * Strategy 'round_robin' or 'least_burdened' delegates to the
     * legacy behaviour in LeadAssignmentService.
     *
     * Returns: ['success' => true, 'assigned_to' => int, 'strategy' => string,
     *           'score' => float, 'factors' => array]
     */
    public function assignLead(int $leadId, string $strategy = 'smart'): array
    {
        try {
            $tid = (int) $this->tenantId();

            // --- Legacy strategies delegate to existing behaviour ---------
            if (in_array($strategy, ['round_robin', 'least_burdened'], true)) {
                return $this->legacyAssignLead($leadId, $strategy);
            }

            // --- Load the lead -------------------------------------------
            $lead = $this->getLead($leadId);
            if (!$lead) {
                return ['success' => false, 'message' => 'Lead not found'];
            }

            // --- Get candidate associates --------------------------------
            $candidates = $this->getCandidateAssociates($tid);
            if (empty($candidates)) {
                return ['success' => false, 'message' => 'No available associates found'];
            }

            // --- Score each candidate ------------------------------------
            $scored = [];
            foreach ($candidates as $cand) {
                $score = $this->scoreAssociateForLead($cand, $lead, $tid);
                $cand = array_merge($cand, $score);
                $scored[] = $cand;
            }

            // --- If all scores are zero (no skill data), fall back -------
            $hasSkillData = array_reduce($scored, function ($carry, $c) {
                return $carry || ($c['skill_score'] > 0);
            }, false);

            if (!$hasSkillData) {
                return $this->legacyAssignLead($leadId, 'least_burdened');
            }

            // --- Pick the highest total score ----------------------------
            usort($scored, fn ($a, $b) => $b['total_score'] <=> $a['total_score']);
            $best = $scored[0];

            // --- Persist the assignment ----------------------------------
            $this->persistAssignment($leadId, $best['user_id'], $best['total_score'], $best['factors'], $tid);

            return [
                'success' => true,
                'assigned_to' => (int) $best['user_id'],
                'strategy' => 'smart',
                'score' => (float) $best['total_score'],
                'factors' => $best['factors'],
                'associate_name' => $best['name'] ?? '',
                'associate_role' => $best['role'] ?? '',
            ];

        } catch (\Throwable $e) {
            error_log("SmartLeadAssignmentService error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal assignment error'];
        }
    }

    /**
     * Auto-assign a batch of unassigned leads using the smart strategy.
     */
    public function autoAssignBatch(int $limit = 50): array
    {
        $tid = (int) $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND l.tenant_id = ?" : "";
        $p = $tid > 1 ? [$limit, $tid] : [$limit];

        $rows = $this->db->fetchAll(
            "SELECT l.id FROM leads l
             WHERE l.assigned_to IS NULL
               AND l.deleted_at IS NULL
               AND l.status NOT IN ('converted','dead','closed','lost'){$tenantFilter}
             ORDER BY l.created_at ASC
             LIMIT ?",
            $p
        );

        $assigned = 0;
        $failed = 0;
        $results = [];

        foreach ($rows as $row) {
            $result = $this->assignLead((int) $row['id'], 'smart');
            if ($result['success']) {
                $assigned++;
                $results[] = ['lead_id' => (int) $row['id'], 'assigned_to' => $result['assigned_to'], 'score' => $result['score']];
            } else {
                $failed++;
            }
        }

        return [
            'assigned' => $assigned,
            'failed' => $failed,
            'total' => count($rows),
            'strategy' => 'smart',
            'details' => $results,
        ];
    }

    /**
     * Score a single associate for a specific lead (0-100 total).
     * $associate is the enriched candidate row from getCandidateAssociates().
     * Returns ['skill_score' => float, 'total_score' => float, 'factors' => array].
     */
    public function scoreAssociateForLead(array $associate, array $lead, int $tid): array
    {
        $tid = (int) $tid;
        $associateUserId = (int) ($associate['user_id'] ?? 0);

        $factors = [];

        // 1. Performance score (25%)
        $perfScore = (float) ($associate['performance_score'] ?? 50);
        $perfWeighted = $perfScore * 0.25;
        $factors['performance'] = [
            'weight' => 25,
            'score' => round($perfScore, 1),
            'weighted' => round($perfWeighted, 2),
            'label' => 'Performance score',
        ];

        // 2. Conversion rate (25%)
        $convRate = $this->getConversionRate($associateUserId, $tid);
        $convWeighted = $convRate * 0.25;
        $factors['conversion'] = [
            'weight' => 25,
            'score' => round($convRate * 100, 1),
            'weighted' => round($convWeighted, 2),
            'label' => 'Conversion rate',
        ];

        // 3. Response time (20%) - lower is better
        $respHours = (float) ($associate['response_time_avg_hours'] ?? 24);
        $respScore = $this->scoreResponseTime($respHours);
        $respWeighted = $respScore * 0.20;
        $factors['response_time'] = [
            'weight' => 20,
            'score' => round($respScore, 1),
            'weighted' => round($respWeighted, 2),
            'label' => 'Response time (' . round($respHours, 1) . 'h avg)',
        ];

        // 4. Workload / active leads (15%) - fewer is better
        $workloadScore = $this->scoreWorkload($associateUserId, $tid);
        $workWeighted = $workloadScore * 0.15;
        $factors['workload'] = [
            'weight' => 15,
            'score' => round($workloadScore, 1),
            'weighted' => round($workWeighted, 2),
            'label' => 'Current workload',
        ];

        // 5. Specialization match (10%)
        $specScore = $this->scoreSpecialization($associate, $lead);
        $specWeighted = $specScore * 0.10;
        $factors['specialization'] = [
            'weight' => 10,
            'score' => round($specScore, 1),
            'weighted' => round($specWeighted, 2),
            'label' => 'Specialization match',
        ];

        // 6. Budget match (5%)
        $budgetScore = $this->scoreBudget($associate, $lead);
        $budWeighted = $budgetScore * 0.05;
        $factors['budget'] = [
            'weight' => 5,
            'score' => round($budgetScore, 1),
            'weighted' => round($budWeighted, 2),
            'label' => 'Budget range match',
        ];

        $skillScore = $perfWeighted + $convWeighted + $respWeighted + $workWeighted + $specWeighted + $budWeighted;

        return [
            'skill_score' => round($skillScore, 2),
            'total_score' => round($skillScore, 2),
            'factors' => $factors,
            'associate_id' => $associateUserId,
            'performance_score' => $perfScore,
            'conversion_rate' => round($convRate * 100, 1),
            'active_leads' => $associate['active_leads'] ?? 0,
        ];
    }

    /**
     * Get performance stats for an associate (conversion rate, etc.)
     */
    public function getAssociatePerformance(int $userId): array
    {
        $tid = (int) $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        // Leads assigned to this associate
        $assigned = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ?{$tenantFilter}",
            array_merge([$userId], $params)
        )['cnt'] ?? 0);

        // Converted leads (status = closed_won or converted)
        $converted = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ?
             AND status IN ('closed_won','converted'){$tenantFilter}",
            array_merge([$userId], $params)
        )['cnt'] ?? 0);

        $convRate = $assigned > 0 ? $converted / $assigned : 0;

        // Approved commission amount (successful deals)
        $commRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount),0) as total FROM mlm_commission_ledger
             WHERE beneficiary_user_id = ? AND status = 'approved'{$tenantFilter}",
            array_merge([$userId], $params)
        );
        $totalCommission = (float) ($commRow['total'] ?? 0);

        // Active (open) leads
        $active = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ?
             AND status NOT IN ('closed_won','closed_lost','converted','dead','cancelled'){$tenantFilter}",
            array_merge([$userId], $params)
        )['cnt'] ?? 0);

        return [
            'user_id' => $userId,
            'assigned_leads' => $assigned,
            'converted_leads' => $converted,
            'conversion_rate' => round($convRate * 100, 2),
            'active_leads' => $active,
            'total_commission' => $totalCommission,
        ];
    }

    /* ──────────────────────────── private helpers ───────────────────────── */

    private function getLead(int $leadId): ?array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND l.tenant_id = ?" : "";
        $params = $tid > 1 ? [$leadId, $tid] : [$leadId];

        $stmt = $this->db->prepare(
            "SELECT l.* FROM leads l WHERE l.id = ?{$tenantFilter} AND l.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function getCandidateAssociates(int $tid): array
    {
        $tenantFilter = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $sql = "
            SELECT u.id as user_id, u.name, u.role,
                   ap.performance_score, ap.response_time_avg_hours,
                   ap.specializations, ap.preferred_locations,
                   ap.preferred_budget_min, ap.preferred_budget_max,
                   ap.total_sales, ap.registration_count, ap.level
            FROM users u
            LEFT JOIN associate_profiles ap ON ap.user_id = u.id
            WHERE u.role IN ('agent','telecaller','associate')
              AND u.status = 'active'
              AND (u.deleted_at IS NULL OR u.deleted_at = '')
              {$tenantFilter}
            ORDER BY u.name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Attach active-lead count to each candidate
        foreach ($rows as &$c) {
            $c['active_leads'] = (int) ($this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ?
                 AND status NOT IN ('closed_won','closed_lost','converted','dead','cancelled')
                 {$tenantFilter}",
                array_merge([$c['user_id']], $params)
            )['cnt'] ?? 0);
        }
        unset($c);

        return $rows;
    }

    private function scoreResponseTime(float $hours): float
    {
        // < 1h = 100, 1-4h = 80, 4-24h = 60, 24-72h = 40, 72h+ = 20
        if ($hours <= 1) return 100;
        if ($hours <= 4) return 80;
        if ($hours <= 24) return 60;
        if ($hours <= 72) return 40;
        return 20;
    }

    private function scoreWorkload(int $userId, int $tid): float
    {
        $sql = "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ?
             AND status NOT IN ('closed_won','closed_lost','converted','dead','cancelled')";
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
        }
        $active = (int) ($this->db->fetchOne(
            $sql,
            $tid > 1 ? [$userId, $tid] : [$userId]
        )['cnt'] ?? 0);

        // 0 active = 100, 1-3 = 80, 4-7 = 60, 8-12 = 40, 13+ = 20
        if ($active === 0) return 100;
        if ($active <= 3) return 80;
        if ($active <= 7) return 60;
        if ($active <= 12) return 40;
        return 20;
    }

    private function scoreSpecialization(array $associate, array $lead): float
    {
        $specs = $associate['specializations'] ?? '';
        $prefLocs = $associate['preferred_locations'] ?? '';

        $score = 0;
        $matched = 0;

        // Match property_interest against specializations
        $interest = $lead['property_interest'] ?? '';
        if ($interest && $specs) {
            $specList = array_map('trim', explode(',', $specs));
            foreach ($specList as $s) {
                if (stripos($interest, $s) !== false || stripos($s, $interest) !== false) {
                    $score += 50;
                    $matched++;
                    break;
                }
            }
        }

        // Match colony against preferred_locations
        $colonyName = $this->getColonyName($lead['colony_id'] ?? null);
        if ($colonyName && $prefLocs) {
            $locList = array_map('trim', explode(',', $prefLocs));
            foreach ($locList as $l) {
                if (stripos($colonyName, $l) !== false || stripos($l, $colonyName) !== false) {
                    $score += 50;
                    $matched++;
                    break;
                }
            }
        }

        if ($matched === 0) {
            // No specialization data or no match → neutral 50
            return 50;
        }

        return min(100, $score);
    }

    private function scoreBudget(array $associate, array $lead): float
    {
        $leadBudget = (float) ($lead['budget'] ?? 0);
        $min = (float) ($associate['preferred_budget_min'] ?? 0);
        $max = (float) ($associate['preferred_budget_max'] ?? 0);

        if ($leadBudget <= 0) {
            return 50; // Neutral when no budget specified
        }

        if ($min > 0 && $max > 0) {
            if ($leadBudget >= $min && $leadBudget <= $max) {
                return 100; // Perfect match
            }
            if ($leadBudget < $min) {
                return 60; // Below range but close
            }
            return 40; // Above range
        }

        return 50; // Neutral when no range set
    }

    private function getColonyName(?int $colonyId): ?string
    {
        if (!$colonyId) {
            return null;
        }
        $row = $this->db->fetchOne("SELECT name FROM colonies WHERE id = ?", [$colonyId]);
        return $row ? $row['name'] : null;
    }

    private function getConversionRate(int $userId, int $tid): float
    {
        $sql = "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ?";
        if ($tid > 1) {
            $sql .= " AND tenant_id = ?";
        }
        $assigned = (int) ($this->db->fetchOne(
            $sql,
            $tid > 1 ? [$userId, $tid] : [$userId]
        )['cnt'] ?? 0);

        if ($assigned === 0) {
            return 0.0;
        }

        $sql2 = "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ?
             AND status IN ('closed_won','converted')";
        if ($tid > 1) {
            $sql2 .= " AND tenant_id = ?";
        }
        $converted = (int) ($this->db->fetchOne(
            $sql2,
            $tid > 1 ? [$userId, $tid] : [$userId]
        )['cnt'] ?? 0);

        return $converted / $assigned;
    }

    private function legacyAssignLead(int $leadId, string $strategy): array
    {
        // Delegate to the existing LeadAssignmentService for legacy strategies
        $legacy = new \App\Services\CRM\LeadAssignmentService($this->db);
        return $legacy->assignLead($leadId);
    }

    private function persistAssignment(int $leadId, int $associateId, float $score, array $factors, int $tid): void
    {
        // Update the lead
        $this->db->query(
            "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$associateId, $leadId, $tid] : [$associateId, $leadId]
        );

        // Log in crm_assignments
        try {
            $cols = "lead_id, assigned_from, assigned_to, assigned_by, reason, is_active, tenant_id";
            $assignedBy = $associateId;
            $this->db->query(
                "INSERT INTO crm_assignments ($cols) VALUES (?, NULL, ?, ?, 'smart_assign', 1, ?)",
                [$leadId, $associateId, $assignedBy, $tid > 1 ? $tid : 1]
            );
        } catch (\Throwable $e) {
            error_log("Failed to log smart assignment: " . $e->getMessage());
        }
    }
}

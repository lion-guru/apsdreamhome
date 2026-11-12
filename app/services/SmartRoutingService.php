<?php
/**
 * SmartRoutingService — Auto-assign inquiries/leads to best associate
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Config\Config;

class SmartRoutingService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find best associate for a lead/inquiry
     */
    public function findBestAssociate(array $leadData): array
    {
        $candidates = $this->getAvailableAssociates();
        
        if (empty($candidates)) {
            return ['success' => false, 'error' => 'No available associates'];
        }

        // Score each candidate
        $scored = [];
        foreach ($candidates as $associate) {
            $score = $this->calculateAssociateScore($associate, $leadData);
            $scored[] = array_merge($associate, ['match_score' => $score]);
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

        return [
            'success' => true,
            'best_match' => $scored[0] ?? null,
            'alternatives' => array_slice($scored, 1, 3),
        ];
    }

    /**
     * Get available associates with current workload
     */
    private function getAvailableAssociates(): array
    {
        return $this->db->fetchAll("
            SELECT
                u.id, u.name, u.email, u.phone, u.role,
                COALESCE(ap.legacy_id, a.employee_id) AS employee_id, a.designation, a.department,
                COALESCE(ap.max_leads_capacity, a.max_leads_capacity) AS max_leads_capacity,
                COALESCE(ap.current_leads_count, a.current_leads_count) AS current_leads_count,
                COALESCE(ap.specializations, a.specializations) AS specializations,
                COALESCE(ap.preferred_locations, a.preferred_locations) AS preferred_locations,
                a.preferred_budget_range,
                COALESCE(ap.performance_score, a.performance_score) AS performance_score,
                COALESCE(ap.response_time_avg_hours, a.response_time_avg_hours) AS response_time_avg_hours
            FROM users u
            LEFT JOIN associate_profiles ap ON ap.user_id = u.id
            JOIN associates a ON a.user_id = u.id
            WHERE u.status = 'active'
            AND u.role IN ('associate', 'agent')
            AND a.status = 'active'
            AND (COALESCE(ap.max_leads_capacity, a.max_leads_capacity) IS NULL OR COALESCE(ap.current_leads_count, a.current_leads_count) < COALESCE(ap.max_leads_capacity, a.max_leads_capacity))
            ORDER BY COALESCE(ap.performance_score, a.performance_score, 0) DESC, COALESCE(ap.current_leads_count, a.current_leads_count, 0) ASC
        ");
    }

    /**
     * Calculate match score for associate-lead pair
     */
    private function calculateAssociateScore(array $associate, array $leadData): float
    {
        $score = 0;

        // 1. Workload balance (30% weight) - prefer associates with fewer leads
        $capacity = $associate['max_leads_capacity'] ?? 50;
        $current = $associate['current_leads_count'] ?? 0;
        $utilization = $capacity > 0 ? ($current / $capacity) : 0;
        $workloadScore = max(0, 100 - ($utilization * 100));
        $score += $workloadScore * 0.30;

        // 2. Specialization match (25% weight)
        $specScore = $this->calculateSpecializationMatch($associate, $leadData);
        $score += $specScore * 0.25;

        // 3. Location preference match (20% weight)
        $locationScore = $this->calculateLocationMatch($associate, $leadData);
        $score += $locationScore * 0.20;

        // 4. Budget range match (15% weight)
        $budgetScore = $this->calculateBudgetMatch($associate, $leadData);
        $score += $budgetScore * 0.15;

        // 5. Performance score (10% weight)
        $perfScore = $associate['performance_score'] ?? 50;
        $score += ($perfScore / 100) * 100 * 0.10;

        // 6. Response time (bonus for fast responders)
        $responseTime = $associate['response_time_avg_hours'] ?? 24;
        if ($responseTime <= 1) $score += 10;
        elseif ($responseTime <= 4) $score += 5;
        elseif ($responseTime <= 12) $score += 2;

        return round($score, 1);
    }

    /**
     * Calculate specialization match
     */
    private function calculateSpecializationMatch(array $associate, array $leadData): float
    {
        $specs = $associate['specializations'] ?? '';
        if (empty($specs)) return 50; // Neutral if no specialization set

        $specList = array_map('trim', explode(',', strtolower($specs)));
        $score = 0;
        $matches = 0;

        // Check property type match
        if (!empty($leadData['property_type'])) {
            $propType = strtolower($leadData['property_type']);
            foreach ($specList as $spec) {
                if (stripos($spec, $propType) !== false || stripos($propType, $spec) !== false) {
                    $matches++;
                    $score += 30;
                }
            }
        }

        // Check budget range specialization
        if (!empty($leadData['budget']) || !empty($leadData['budget_max'])) {
            $budget = $leadData['budget'] ?? $leadData['budget_max'] ?? 0;
            foreach ($specList as $spec) {
                if (stripos($spec, 'luxury') !== false && $budget > 10000000) $matches += 30;
                if (stripos($spec, 'affordable') !== false && $budget <= 5000000) $matches += 30;
                if (stripos($spec, 'mid') !== false && $budget > 5000000 && $budget <= 10000000) $matches += 30;
            }
        }

        // Check location specialization
        if (!empty($leadData['city']) || !empty($leadData['preferred_location'])) {
            $location = strtolower($leadData['city'] ?? $leadData['preferred_location'] ?? '');
            foreach ($specList as $spec) {
                if (stripos($spec, $location) !== false) {
                    $matches += 20;
                }
            }
        }

        return min(100, $matches * 20); // Cap at 100
    }

    /**
     * Calculate location match
     */
    private function calculateLocationMatch(array $associate, array $leadData): float
    {
        $preferredLocations = $associate['preferred_locations'] ?? '';
        if (empty($preferredLocations)) return 50;

        $prefList = array_map('trim', explode(',', strtolower($preferredLocations)));
        $leadLocation = strtolower($leadData['city'] ?? $leadData['preferred_location'] ?? '');

        if (empty($leadLocation)) return 50;

        foreach ($prefList as $pref) {
            if (stripos($pref, $leadLocation) !== false || stripos($leadLocation, $pref) !== false) {
                return 100;
            }
        }

        // Check if associate covers the state/region
        $leadState = strtolower($leadData['state'] ?? '');
        foreach ($prefList as $pref) {
            if (stripos($pref, $leadState) !== false || stripos($leadState, $pref) !== false) {
                return 75;
            }
        }

        return 25;
    }

    /**
     * Calculate budget match
     */
    private function calculateBudgetMatch(array $associate, array $leadData): float
    {
        $budgetRange = $associate['preferred_budget_range'] ?? '';
        if (empty($budgetRange)) return 50;

        // Parse budget range (format: "min-max" e.g. "500000-10000000")
        if (strpos($budgetRange, '-') !== false) {
            [$min, $max] = explode('-', $budgetRange);
            $min = (float)trim($min);
            $max = (float)trim($max);

            $leadBudget = $leadData['budget'] ?? $leadData['budget_max'] ?? 0;
            
            if ($leadBudget >= $min && $leadBudget <= $max) {
                return 100;
            } elseif ($leadBudget >= $min * 0.8 && $leadBudget <= $max * 1.2) {
                return 75;
            } elseif ($leadBudget >= $min * 0.5 && $leadBudget <= $max * 2) {
                return 50;
            }
        }

        return 40;
    }

    /**
     * Assign lead to associate
     */
    public function assignLead(int $leadId, int $associateId, int $assignedBy = null): array
    {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $associate = $this->db->fetch("SELECT * FROM users WHERE id = ? AND role IN ('associate','agent')", [$associateId]);
        if (!$associate) {
            return ['success' => false, 'error' => 'Associate not found'];
        }

        try {
            $this->db->execute(
                "UPDATE leads SET assigned_to = ?, assigned_at = NOW() WHERE id = ?",
                [$associateId, $leadId]
            );

            // Log activity
            $this->logAssignment($leadId, $associateId, $assignedBy ?? $associateId);

            // Notify associate (implement notification)
            $this->notifyAssociate($associateId, $leadId);

            // Update associate lead count
            $this->db->execute(
                "UPDATE associates SET current_leads_count = current_leads_count + 1 WHERE user_id = ?",
                [$associateId]
            );

            return ['success' => true, 'lead_id' => $leadId, 'associate_id' => $associateId];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reassign lead
     */
    public function reassignLead(int $leadId, int $newAssociateId, int $reassignedBy): array
    {
        // Get current associate
        $lead = $this->db->fetch("SELECT assigned_to FROM leads WHERE id = ?", [$leadId]);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $oldAssociateId = $lead['assigned_to'];

        try {
            // Decrement old associate count
            if ($oldAssociateId) {
                $this->db->execute(
                    "UPDATE associates SET current_leads_count = GREATEST(0, current_leads_count - 1) WHERE user_id = ?",
                    [$oldAssociateId]
                );
            }

            // Assign to new associate
            $result = $this->assignLead($leadId, $newAssociateId, $reassignedBy);
            
            if ($result['success']) {
                $this->logReassignment($leadId, $oldAssociateId, $newAssociateId, $reassignedBy);
            }

            return $result;
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Auto-assign based on best match
     */
    public function autoAssign(int $leadId): array
    {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $match = $this->findBestAssociate($lead);
        
        if (!$match['success'] || !$match['best_match']) {
            return ['success' => false, 'error' => 'No suitable associate available'];
        }

        return $this->assignLead($leadId, $match['best_match']['id']);
    }

    /**
     * Log assignment activity
     */
    private function logAssignment(int $leadId, int $associateId, int $assignedBy): void
    {
        $this->db->execute(
            "INSERT INTO lead_assignment_log (lead_id, associate_id, assigned_by, action, created_at) VALUES (?, ?, ?, 'assigned', NOW())",
            [$leadId, $associateId, $assignedBy]
        );

        // Add to lead_activities
        $associate = $this->db->fetch("SELECT name FROM users WHERE id = ?", [$associateId]);
        $this->db->execute(
            "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (?, 'assignment', ?, ?, NOW())",
            [$leadId, "Lead assigned to {$associate['name']}", $assignedBy]
        );
    }

    /**
     * Log reassignment
     */
    private function logReassignment(int $leadId, int $oldAssociateId, int $newAssociateId, int $reassignedBy): void
    {
        $oldAssociate = $oldAssociateId ? $this->db->fetch("SELECT name FROM users WHERE id = ?", [$oldAssociateId]) : null;
        $newAssociate = $this->db->fetch("SELECT name FROM users WHERE id = ?", [$newAssociateId]);

        $this->db->execute(
            "INSERT INTO lead_assignment_log (lead_id, associate_id, assigned_by, action, old_associate_id, created_at) VALUES (?, ?, ?, 'reassigned', ?, NOW())",
            [$leadId, $newAssociateId, $reassignedBy, $oldAssociateId]
        );

        $this->db->execute(
            "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (?, 'reassignment', ?, ?, NOW())",
            [$leadId, "Lead reassigned from {$oldAssociate['name']} to {$newAssociate['name']}", $reassignedBy]
        );
    }

    /**
     * Notify associate of new assignment
     */
    private function notifyAssociate(int $associateId, int $leadId): void
    {
        // Create notification
        $lead = $this->db->fetch("SELECT name, phone FROM leads WHERE id = ?", [$leadId]);
        
        $this->db->execute(
            "INSERT INTO notifications (user_id, type, title, message, related_id, related_type, created_at) VALUES (?, 'lead_assigned', ?, ?, ?, 'lead', NOW())",
            [
                $associateId,
                'New Lead Assigned',
                "You have been assigned a new lead: {$lead['name']} ({$lead['phone']})",
                $leadId
            ]
        );

        // Could also send email/SMS/push notification here
    }

    /**
     * Get routing stats for admin
     */
    public function getRoutingStats(): array
    {
        $stats = [];

        // Associates with current load
        $stats['associates'] = $this->db->fetchAll("
            SELECT u.id, u.name, u.email,
                   COALESCE(ap.current_leads_count, a.current_leads_count) AS current_leads_count,
                   COALESCE(ap.max_leads_capacity, a.max_leads_capacity) AS max_leads_capacity,
                   ROUND((COALESCE(ap.current_leads_count, a.current_leads_count) / NULLIF(COALESCE(ap.max_leads_capacity, a.max_leads_capacity),0)) * 100, 1) as utilization_pct,
                   COALESCE(ap.performance_score, a.performance_score) AS performance_score
            FROM users u
            LEFT JOIN associate_profiles ap ON ap.user_id = u.id
            JOIN associates a ON a.user_id = u.id
            WHERE u.role IN ('associate','agent') AND u.status = 'active' AND a.status = 'active'
            ORDER BY utilization_pct ASC
        ");

        // Assignment stats (last 30 days)
        $stats['assignments_last_30_days'] = $this->db->fetchColumn("
            SELECT COUNT(*) FROM lead_assignment_log 
            WHERE action = 'assigned' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ") ?? 0;

        $stats['reassignments_last_30_days'] = $this->db->fetchColumn("
            SELECT COUNT(*) FROM lead_assignment_log 
            WHERE action = 'reassigned' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ") ?? 0;

        // Average utilization
        $stats['avg_utilization'] = round($this->db->fetchColumn("
            SELECT AVG((current_leads_count / NULLIF(max_leads_capacity,0)) * 100)
            FROM associate_profiles WHERE status = 'active' AND max_leads_capacity > 0
        ") ?? 0, 1);

        // Unassigned leads
        $stats['unassigned_leads'] = $this->db->fetchColumn("
            SELECT COUNT(*) FROM leads 
            WHERE (assigned_to IS NULL OR assigned_to = 0) 
            AND deleted_at IS NULL 
            AND status NOT IN ('closed_lost', 'closed_won')
        ") ?? 0;

        return $stats;
    }
}
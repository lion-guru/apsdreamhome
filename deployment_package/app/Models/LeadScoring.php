<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Lead Scoring System Model
 * AI-powered lead scoring based on demographics, behavior, engagement, and preferences
 */
class LeadScoring extends Model
{
    protected $table = 'lead_scores';

    /**
     * Calculate lead score
     */
    public function calculateLeadScore(int $leadId, bool $forceRecalculate = false): array
    {
        $lead = $this->getLeadData($leadId);
        if (!$lead) {
            return ['success' => false, 'message' => 'Lead not found'];
        }

        // Check if we need to recalculate
        $existingScore = $this->findByLeadId($leadId);
        if (!$forceRecalculate && $existingScore && !$this->shouldRecalculate($existingScore)) {
            return [
                'success' => true,
                'score' => $existingScore['current_score'],
                'grade' => $existingScore['grade'],
                'message' => 'Using cached score'
            ];
        }

        // Calculate new score
        $scoreBreakdown = $this->calculateScoreBreakdown($lead);
        $totalScore = array_sum($scoreBreakdown['scores']);
        $grade = $this->calculateGrade($totalScore);

        // Update or create score record
        $scoreData = [
            'lead_id' => $leadId,
            'current_score' => $totalScore,
            'score_breakdown' => json_encode($scoreBreakdown),
            'grade' => $grade,
            'last_calculated' => date('Y-m-d H:i:s'),
            'next_calculation' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'calculation_count' => ($existingScore['calculation_count'] ?? 0) + 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existingScore) {
            $this->update($existingScore['id'], $scoreData);
        } else {
            $this->insert($scoreData);
        }

        // Log scoring history
        $this->logScoringHistory($leadId, 'scored', $totalScore - ($existingScore['current_score'] ?? 0), $existingScore['current_score'] ?? 0, $totalScore);

        // Check thresholds and trigger actions
        $this->checkThresholdsAndTriggerActions($leadId, $totalScore, $grade);

        return [
            'success' => true,
            'score' => $totalScore,
            'grade' => $grade,
            'breakdown' => $scoreBreakdown
        ];
    }

    /**
     * Calculate detailed score breakdown
     */
    private function calculateScoreBreakdown(array $lead): array
    {
        $breakdown = [
            'categories' => [],
            'scores' => [],
            'rules_applied' => []
        ];

        $categories = ['demographic', 'behavioral', 'engagement', 'property_preference', 'budget', 'timeline', 'source'];

        foreach ($categories as $category) {
            $categoryScore = $this->calculateCategoryScore($lead, $category);
            $breakdown['categories'][$category] = $categoryScore;
            $breakdown['scores'][] = $categoryScore['total_score'];
            $breakdown['rules_applied'] = array_merge($breakdown['rules_applied'], $categoryScore['rules_applied']);
        }

        // Apply decay to old scores
        $breakdown['decay_applied'] = $this->applyScoreDecay($lead['id'], $breakdown);

        return $breakdown;
    }

    /**
     * Calculate score for a specific category
     */
    private function calculateCategoryScore(array $lead, string $category): array
    {
        $rules = $this->getActiveRules($category);
        $totalScore = 0;
        $rulesApplied = [];

        foreach ($rules as $rule) {
            $ruleResult = $this->evaluateRule($lead, $rule);
            if ($ruleResult['applies']) {
                $scoreToAdd = min($ruleResult['score'], $rule['score_points']);
                $totalScore += $scoreToAdd;

                $rulesApplied[] = [
                    'rule_id' => $rule['id'],
                    'rule_name' => $rule['rule_name'],
                    'score_added' => $scoreToAdd,
                    'max_score' => $rule['score_points']
                ];
            }
        }

        return [
            'total_score' => $totalScore,
            'rules_count' => count($rulesApplied),
            'rules_applied' => $rulesApplied
        ];
    }

    /**
     * Evaluate if a rule applies to a lead
     */
    private function evaluateRule(array $lead, array $rule): array
    {
        $fieldName = $rule['field_name'];
        $fieldValue = $rule['field_value'];
        $criteriaType = $rule['criteria_type'];

        // Get lead field value
        $leadValue = $this->getLeadFieldValue($lead, $fieldName);

        if ($leadValue === null) {
            return ['applies' => false, 'score' => 0];
        }

        $applies = false;
        $score = $rule['score_points'];

        switch ($criteriaType) {
            case 'exact_match':
                $applies = $leadValue == $fieldValue;
                break;
            case 'contains':
                $applies = stripos($leadValue, $fieldValue) !== false;
                break;
            case 'greater_than':
                $applies = (float)$leadValue > (float)$fieldValue;
                break;
            case 'less_than':
                $applies = (float)$leadValue < (float)$fieldValue;
                break;
            case 'between':
                $values = explode(',', $fieldValue);
                if (count($values) === 2) {
                    $applies = (float)$leadValue >= (float)$values[0] && (float)$leadValue <= (float)$values[1];
                }
                break;
            case 'not_empty':
                $applies = !empty($leadValue);
                break;
        }

        return [
            'applies' => $applies,
            'score' => $score
        ];
    }

    /**
     * Get lead field value
     */
    private function getLeadFieldValue(array $lead, string $fieldName): mixed
    {
        // Direct field mapping
        if (isset($lead[$fieldName])) {
            return $lead[$fieldName];
        }

        // Calculated or derived fields
        switch ($fieldName) {
            case 'property_views':
                return $this->getLeadPropertyViews($lead['id']);
            case 'visit_count':
                return $this->getLeadVisitCount($lead['id']);
            case 'email_response':
                return $this->hasLeadRespondedToEmail($lead['id']) ? 'yes' : 'no';
            case 'webinar_attendance':
                return $this->hasLeadAttendedWebinar($lead['id']) ? 'yes' : 'no';
            case 'brochure_download':
                return $this->hasLeadDownloadedBrochure($lead['id']) ? 'yes' : 'no';
            case 'budget_max':
                return $lead['budget_max'] ?? $lead['budget'];
            case 'timeline':
                return $this->getLeadTimeline($lead['timeline']);
            default:
                return null;
        }
    }

    /**
     * Get active rules for a category
     */
    private function getActiveRules(string $category): array
    {
        return $this->query(
            "SELECT * FROM lead_scoring_rules WHERE category = ? AND is_active = 1 ORDER BY priority DESC",
            [$category]
        )->fetchAll();
    }

    /**
     * Calculate grade based on score
     */
    private function calculateGrade(int $score): string
    {
        $thresholds = $this->query(
            "SELECT grade FROM scoring_thresholds
             WHERE min_score <= ? AND (max_score IS NULL OR max_score >= ?) AND is_active = 1
             ORDER BY min_score DESC LIMIT 1",
            [$score, $score]
        )->fetch();

        return $thresholds['grade'] ?? 'F';
    }

    /**
     * Apply score decay
     */
    private function applyScoreDecay(int $leadId, array &$breakdown): array
    {
        $decayRules = $this->query(
            "SELECT * FROM lead_scoring_history
             WHERE lead_id = ? AND action = 'scored' AND applied_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
             ORDER BY applied_at DESC LIMIT 10",
            [$leadId]
        )->fetchAll();

        $decayApplied = [];
        foreach ($decayRules as $rule) {
            $decayDays = $this->getRuleDecayDays($rule['rule_id']);
            if ($decayDays && strtotime($rule['applied_at']) < strtotime("-{$decayDays} days")) {
                $decayAmount = max(1, $rule['points_change'] * 0.1); // 10% decay
                $breakdown['scores'] = array_map(function($score) use ($decayAmount) {
                    return max(0, $score - $decayAmount);
                }, $breakdown['scores']);

                $decayApplied[] = [
                    'rule_id' => $rule['rule_id'],
                    'decay_amount' => $decayAmount,
                    'decay_days' => $decayDays
                ];

                // Log decay
                $this->logScoringHistory($leadId, $rule['rule_id'], -$decayAmount, $rule['new_score'], $rule['new_score'] - $decayAmount, 'decay');
            }
        }

        return $decayApplied;
    }

    /**
     * Get rule decay days
     */
    private function getRuleDecayDays(int $ruleId): ?int
    {
        $rule = $this->query("SELECT decay_days FROM lead_scoring_rules WHERE id = ?", [$ruleId])->fetch();
        return $rule['decay_days'] ?? null;
    }

    /**
     * Check thresholds and trigger actions
     */
    private function checkThresholdsAndTriggerActions(int $leadId, int $score, string $grade): void
    {
        $threshold = $this->query(
            "SELECT * FROM scoring_thresholds
             WHERE min_score <= ? AND (max_score IS NULL OR max_score >= ?) AND is_active = 1
             ORDER BY min_score DESC LIMIT 1",
            [$score, $score]
        )->fetch();

        if (!$threshold) return;

        // Trigger actions based on threshold
        switch ($threshold['action_required']) {
            case 'immediate_followup':
                $this->triggerImmediateFollowup($leadId, $threshold);
                break;
            case 'schedule_followup':
                $this->scheduleFollowup($leadId, $threshold);
                break;
            case 'auto_assign':
                $this->autoAssignLead($leadId, $threshold);
                break;
        }

        // Send alerts if configured
        if ($threshold['email_alert']) {
            $this->sendThresholdAlert($leadId, $threshold, 'email');
        }
        if ($threshold['sms_alert']) {
            $this->sendThresholdAlert($leadId, $threshold, 'sms');
        }
    }

    /**
     * Log scoring history
     */
    private function logScoringHistory(int $leadId, string $action, int $pointsChange, int $oldScore, int $newScore, ?int $ruleId = null, string $reason = null): void
    {
        $this->query(
            "INSERT INTO lead_scoring_history
             (lead_id, rule_id, action, points_change, old_score, new_score, reason, applied_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$leadId, $ruleId, $action, $pointsChange, $oldScore, $newScore, $reason]
        );
    }

    /**
     * Find score record by lead ID
     */
    private function findByLeadId(int $leadId): ?array
    {
        return $this->query("SELECT * FROM lead_scores WHERE lead_id = ?", [$leadId])->fetch();
    }

    /**
     * Check if score should be recalculated
     */
    private function shouldRecalculate(array $existingScore): bool
    {
        if (!$existingScore['next_calculation']) return true;

        return strtotime($existingScore['next_calculation']) <= time();
    }

    /**
     * Get lead data
     */
    private function getLeadData(int $leadId): ?array
    {
        // This would integrate with your leads table
        // For now, return mock data
        return [
            'id' => $leadId,
            'name' => 'Sample Lead',
            'email' => 'lead@example.com',
            'budget_max' => 15000000,
            'timeline' => 'within_6_months',
            'lead_source' => 'website',
            'property_types' => 'apartment,villa',
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
        ];
    }

    /**
     * Get leads by score range
     */
    public function getLeadsByScoreRange(int $minScore, int $maxScore = null, int $limit = 50): array
    {
        $query = "SELECT ls.*, l.name as lead_name, l.email, l.phone
                  FROM lead_scores ls
                  LEFT JOIN leads l ON ls.lead_id = l.id
                  WHERE ls.current_score >= ?";

        $params = [$minScore];

        if ($maxScore !== null) {
            $query .= " AND ls.current_score <= ?";
            $params[] = $maxScore;
        }

        $query .= " ORDER BY ls.current_score DESC LIMIT ?";
        $params[] = $limit;

        return $this->query($query, $params)->fetchAll();
    }

    /**
     * Get scoring analytics
     */
    public function getScoringAnalytics(string $period = '30 days'): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $analytics = $this->query(
            "SELECT
                COUNT(DISTINCT lead_id) as total_leads_scored,
                AVG(current_score) as avg_score,
                COUNT(CASE WHEN grade = 'A+' THEN 1 END) as hot_leads,
                COUNT(CASE WHEN grade = 'A' THEN 1 END) as warm_leads,
                COUNT(CASE WHEN grade IN ('B+', 'B') THEN 1 END) as moderate_leads,
                COUNT(CASE WHEN grade IN ('C+', 'C', 'D', 'F') THEN 1 END) as cold_leads
             FROM lead_scores
             WHERE last_calculated >= ?",
            [$startDate]
        )->fetch();

        // Get rule effectiveness
        $ruleStats = $this->query(
            "SELECT lsr.rule_name, COUNT(lsh.id) as applications, AVG(lsh.points_change) as avg_points
             FROM lead_scoring_history lsh
             LEFT JOIN lead_scoring_rules lsr ON lsh.rule_id = lsr.id
             WHERE lsh.applied_at >= ? AND lsh.action = 'scored'
             GROUP BY lsr.id, lsr.rule_name
             ORDER BY applications DESC LIMIT 10",
            [$startDate]
        )->fetchAll();

        $analytics['top_rules'] = $ruleStats;

        return $analytics ?: [
            'total_leads_scored' => 0,
            'avg_score' => 0,
            'hot_leads' => 0,
            'warm_leads' => 0,
            'moderate_leads' => 0,
            'cold_leads' => 0,
            'top_rules' => []
        ];
    }

    /**
     * Bulk recalculate scores
     */
    public function bulkRecalculateScores(array $leadIds = null, int $batchSize = 50): array
    {
        if ($leadIds === null) {
            // Get all leads that need recalculation
            $leadIds = $this->query(
                "SELECT DISTINCT lead_id FROM lead_scores
                 WHERE next_calculation <= NOW() AND is_locked = 0"
            )->fetchAll();
            $leadIds = array_column($leadIds, 'lead_id');
        }

        $processed = 0;
        $successful = 0;
        $failed = 0;

        foreach (array_chunk($leadIds, $batchSize) as $batch) {
            foreach ($batch as $leadId) {
                $result = $this->calculateLeadScore($leadId, true);
                if ($result['success']) {
                    $successful++;
                } else {
                    $failed++;
                }
                $processed++;
            }

            // Small delay between batches to prevent overloading
            usleep(100000); // 0.1 seconds
        }

        return [
            'processed' => $processed,
            'successful' => $successful,
            'failed' => $failed,
            'message' => "Bulk recalculation completed: {$successful} successful, {$failed} failed"
        ];
    }

    /**
     * Create custom scoring rule
     */
    public function createScoringRule(array $ruleData): array
    {
        $ruleRecord = [
            'rule_name' => $ruleData['rule_name'],
            'rule_description' => $ruleData['rule_description'] ?? null,
            'category' => $ruleData['category'],
            'criteria_type' => $ruleData['criteria_type'],
            'field_name' => $ruleData['field_name'],
            'field_value' => $ruleData['field_value'],
            'comparison_operator' => $ruleData['comparison_operator'] ?? null,
            'score_points' => $ruleData['score_points'],
            'max_occurrences' => $ruleData['max_occurrences'] ?? 1,
            'decay_days' => $ruleData['decay_days'] ?? null,
            'is_active' => $ruleData['is_active'] ?? 1,
            'priority' => $ruleData['priority'] ?? 0,
            'created_by' => $ruleData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $ruleId = $this->insertInto('lead_scoring_rules', $ruleRecord);

        return [
            'success' => true,
            'rule_id' => $ruleId,
            'message' => 'Scoring rule created successfully'
        ];
    }

    /**
     * Update scoring threshold
     */
    public function updateScoringThreshold(int $thresholdId, array $thresholdData): array
    {
        $this->query(
            "UPDATE scoring_thresholds SET
             threshold_name = ?, min_score = ?, max_score = ?, grade = ?, description = ?,
             action_required = ?, email_alert = ?, sms_alert = ?, auto_assign = ?, assigned_user_id = ?,
             updated_at = NOW()
             WHERE id = ?",
            [
                $thresholdData['threshold_name'],
                $thresholdData['min_score'],
                $thresholdData['max_score'] ?? null,
                $thresholdData['grade'],
                $thresholdData['description'] ?? null,
                $thresholdData['action_required'],
                $thresholdData['email_alert'] ?? 0,
                $thresholdData['sms_alert'] ?? 0,
                $thresholdData['auto_assign'] ?? 0,
                $thresholdData['assigned_user_id'] ?? null,
                $thresholdId
            ]
        );

        return [
            'success' => true,
            'message' => 'Scoring threshold updated successfully'
        ];
    }

    // Helper methods (implementations would depend on your actual data structure)
    private function getLeadPropertyViews(int $leadId): int { return rand(0, 10); }
    private function getLeadVisitCount(int $leadId): int { return rand(1, 20); }
    private function hasLeadRespondedToEmail(int $leadId): bool { return rand(0, 1) === 1; }
    private function hasLeadAttendedWebinar(int $leadId): bool { return rand(0, 1) === 1; }
    private function hasLeadDownloadedBrochure(int $leadId): bool { return rand(0, 1) === 1; }
    private function getLeadTimeline(string $timeline): string { return $timeline; }

    private function triggerImmediateFollowup(int $leadId, array $threshold): void {
        // Implementation would trigger immediate follow-up workflow
    }

    private function scheduleFollowup(int $leadId, array $threshold): void {
        // Implementation would schedule follow-up task
    }

    private function autoAssignLead(int $leadId, array $threshold): void {
        // Implementation would auto-assign lead to specified user
    }

    private function sendThresholdAlert(int $leadId, array $threshold, string $channel): void {
        // Implementation would send alert via specified channel
    }
}

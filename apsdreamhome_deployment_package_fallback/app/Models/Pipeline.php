<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Lead Pipeline Kanban Board Model
 * Handles lead pipeline management, stage movements, activities, and analytics
 */
class Pipeline extends Model
{
    protected $table = 'lead_pipeline';

    /**
     * Add lead to pipeline
     */
    public function addLeadToPipeline(int $leadId, array $pipelineData = []): array
    {
        // Check if lead is already in pipeline
        $existing = $this->query("SELECT id FROM lead_pipeline WHERE lead_id = ?", [$leadId])->fetch();

        if ($existing) {
            return ['success' => false, 'message' => 'Lead is already in pipeline'];
        }

        $pipelineRecord = [
            'lead_id' => $leadId,
            'current_stage_id' => $pipelineData['stage_id'] ?? $this->getDefaultStageId(),
            'assigned_to' => $pipelineData['assigned_to'] ?? null,
            'assigned_by' => $pipelineData['assigned_by'] ?? null,
            'assigned_at' => $pipelineData['assigned_to'] ? date('Y-m-d H:i:s') : null,
            'priority' => $pipelineData['priority'] ?? 'normal',
            'tags' => json_encode($pipelineData['tags'] ?? []),
            'deal_value' => $pipelineData['deal_value'] ?? null,
            'expected_close_date' => $pipelineData['expected_close_date'] ?? null,
            'confidence_percentage' => $pipelineData['confidence_percentage'] ?? 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $pipelineId = $this->insert($pipelineRecord);

        // Log initial stage entry
        $this->logStageMovement($leadId, null, $pipelineRecord['current_stage_id'], $pipelineData['assigned_by'] ?? null, 'Initial pipeline entry');

        return [
            'success' => true,
            'pipeline_id' => $pipelineId,
            'message' => 'Lead added to pipeline successfully'
        ];
    }

    /**
     * Move lead to different stage
     */
    public function moveLeadToStage(int $leadId, int $newStageId, array $movementData = []): array
    {
        $pipeline = $this->query("SELECT * FROM lead_pipeline WHERE lead_id = ? AND is_active = 1", [$leadId])->fetch();

        if (!$pipeline) {
            return ['success' => false, 'message' => 'Lead not found in pipeline'];
        }

        if ($pipeline['current_stage_id'] == $newStageId) {
            return ['success' => false, 'message' => 'Lead is already in this stage'];
        }

        $oldStageId = $pipeline['current_stage_id'];

        // Calculate time spent in previous stage
        $timeInStage = null;
        if ($pipeline['entered_stage_at']) {
            $enteredTime = strtotime($pipeline['entered_stage_at']);
            $currentTime = time();
            $timeInStage = round(($currentTime - $enteredTime) / 60); // minutes
        }

        // Update pipeline record
        $this->update($pipeline['id'], [
            'current_stage_id' => $newStageId,
            'entered_stage_at' => date('Y-m-d H:i:s'),
            'stage_deadline' => $movementData['stage_deadline'] ?? null,
            'last_activity' => date('Y-m-d H:i:s'),
            'last_activity_type' => 'stage_movement',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log stage movement
        $this->logStageMovement($leadId, $oldStageId, $newStageId, $movementData['moved_by'] ?? null, $movementData['reason'] ?? 'Manual movement');

        // Execute automated actions for new stage
        $this->executeStageActions($leadId, $newStageId);

        // Update analytics
        $this->updatePipelineAnalytics($oldStageId, $newStageId);

        return [
            'success' => true,
            'message' => 'Lead moved to new stage successfully',
            'old_stage' => $oldStageId,
            'new_stage' => $newStageId,
            'time_in_previous_stage' => $timeInStage
        ];
    }

    /**
     * Assign lead to user
     */
    public function assignLead(int $leadId, int $userId, int $assignedBy): array
    {
        $pipeline = $this->query("SELECT * FROM lead_pipeline WHERE lead_id = ? AND is_active = 1", [$leadId])->fetch();

        if (!$pipeline) {
            return ['success' => false, 'message' => 'Lead not found in pipeline'];
        }

        $this->update($pipeline['id'], [
            'assigned_to' => $userId,
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log assignment
        $this->logActivity($leadId, 'assignment', "Lead assigned to user", [
            'assigned_to' => $userId,
            'assigned_by' => $assignedBy
        ], $assignedBy);

        return [
            'success' => true,
            'message' => 'Lead assigned successfully'
        ];
    }

    /**
     * Get pipeline kanban data
     */
    public function getPipelineKanban(array $filters = []): array
    {
        $stages = $this->getPipelineStages();

        foreach ($stages as &$stage) {
            $stage['leads'] = $this->getLeadsInStage($stage['id'], $filters);
        }

        return [
            'stages' => $stages,
            'total_leads' => array_sum(array_column($stages, 'lead_count')),
            'filters_applied' => $filters
        ];
    }

    /**
     * Get leads in specific stage
     */
    private function getLeadsInStage(int $stageId, array $filters = []): array
    {
        $query = "SELECT lp.*, l.name as lead_name, l.email, l.phone, l.created_at as lead_created_at,
                         u.auser as assigned_user_name,
                         ps.stage_name, ps.stage_color, ps.probability_percentage
                  FROM lead_pipeline lp
                  LEFT JOIN leads l ON lp.lead_id = l.id
                  LEFT JOIN admin u ON lp.assigned_to = u.aid
                  LEFT JOIN pipeline_stages ps ON lp.current_stage_id = ps.id
                  WHERE lp.current_stage_id = ? AND lp.is_active = 1";

        $params = [$stageId];

        // Apply filters
        if (!empty($filters['assigned_to'])) {
            $query .= " AND lp.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['priority'])) {
            $query .= " AND lp.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['tags'])) {
            $query .= " AND JSON_CONTAINS(lp.tags, ?)";
            $params[] = json_encode($filters['tags']);
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND lp.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND lp.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $query .= " ORDER BY lp.priority DESC, lp.entered_stage_at ASC";

        $leads = $this->query($query, $params)->fetchAll();

        // Add additional data for each lead
        foreach ($leads as &$lead) {
            $lead['tags'] = json_decode($lead['tags'], true);
            $lead['activities'] = $this->getLeadActivities($lead['lead_id'], 5);
            $lead['days_in_stage'] = $this->calculateDaysInStage($lead['entered_stage_at']);
            $lead['is_overdue'] = $this->isLeadOverdue($lead);
        }

        return $leads;
    }

    /**
     * Add activity to lead
     */
    public function addActivity(array $activityData): array
    {
        $activityRecord = [
            'lead_id' => $activityData['lead_id'],
            'activity_type' => $activityData['activity_type'],
            'activity_title' => $activityData['activity_title'],
            'activity_description' => $activityData['activity_description'] ?? null,
            'activity_date' => $activityData['activity_date'] ?? date('Y-m-d H:i:s'),
            'duration_minutes' => $activityData['duration_minutes'] ?? null,
            'outcome' => $activityData['outcome'] ?? 'pending',
            'outcome_details' => $activityData['outcome_details'] ?? null,
            'performed_by' => $activityData['performed_by'],
            'assigned_to' => $activityData['assigned_to'] ?? null,
            'is_completed' => $activityData['is_completed'] ?? 0,
            'completed_at' => $activityData['is_completed'] ? date('Y-m-d H:i:s') : null,
            'reminder_date' => $activityData['reminder_date'] ?? null,
            'attachments' => json_encode($activityData['attachments'] ?? []),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $activityId = $this->insertInto('pipeline_activities', $activityRecord);

        // Update lead last activity
        $this->query(
            "UPDATE lead_pipeline SET last_activity = ?, last_activity_type = ? WHERE lead_id = ?",
            [date('Y-m-d H:i:s'), $activityData['activity_type'], $activityData['lead_id']]
        );

        return [
            'success' => true,
            'activity_id' => $activityId,
            'message' => 'Activity added successfully'
        ];
    }

    /**
     * Get pipeline stages
     */
    public function getPipelineStages(): array
    {
        return $this->query(
            "SELECT ps.*, COUNT(lp.id) as lead_count
             FROM pipeline_stages ps
             LEFT JOIN lead_pipeline lp ON ps.id = lp.current_stage_id AND lp.is_active = 1
             WHERE ps.is_active = 1
             GROUP BY ps.id
             ORDER BY ps.stage_order ASC"
        )->fetchAll();
    }

    /**
     * Get pipeline analytics
     */
    public function getPipelineAnalytics(string $period = '30 days'): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $analytics = [
            'stage_distribution' => $this->getStageDistribution(),
            'conversion_rates' => $this->getConversionRates($startDate),
            'average_time_in_stages' => $this->getAverageTimeInStages($startDate),
            'team_performance' => $this->getTeamPerformance($startDate),
            'lead_velocity' => $this->getLeadVelocity($startDate),
            'revenue_pipeline' => $this->getRevenuePipeline()
        ];

        return $analytics;
    }

    /**
     * Get stage distribution
     */
    private function getStageDistribution(): array
    {
        return $this->query(
            "SELECT ps.stage_name, ps.stage_color, COUNT(lp.id) as lead_count,
                    ROUND((COUNT(lp.id) / (SELECT COUNT(*) FROM lead_pipeline WHERE is_active = 1)) * 100, 2) as percentage
             FROM pipeline_stages ps
             LEFT JOIN lead_pipeline lp ON ps.id = lp.current_stage_id AND lp.is_active = 1
             WHERE ps.is_active = 1
             GROUP BY ps.id, ps.stage_name, ps.stage_color
             ORDER BY ps.stage_order ASC"
        )->fetchAll();
    }

    /**
     * Get conversion rates
     */
    private function getConversionRates(string $startDate): array
    {
        // Calculate conversion rates between stages
        $stages = $this->getPipelineStages();
        $conversionRates = [];

        for ($i = 0; $i < count($stages) - 1; $i++) {
            $currentStage = $stages[$i];
            $nextStage = $stages[$i + 1];

            $conversions = $this->query(
                "SELECT COUNT(DISTINCT pmh.lead_id) as converted
                 FROM pipeline_movement_history pmh
                 WHERE pmh.to_stage_id = ? AND pmh.from_stage_id = ? AND pmh.moved_at >= ?",
                [$nextStage['id'], $currentStage['id'], $startDate]
            )->fetch()['converted'];

            $totalInStage = $currentStage['lead_count'];

            $conversionRates[] = [
                'from_stage' => $currentStage['stage_name'],
                'to_stage' => $nextStage['stage_name'],
                'converted' => $conversions,
                'total' => $totalInStage,
                'rate' => $totalInStage > 0 ? round(($conversions / $totalInStage) * 100, 2) : 0
            ];
        }

        return $conversionRates;
    }

    /**
     * Get average time in stages
     */
    private function getAverageTimeInStages(string $startDate): array
    {
        return $this->query(
            "SELECT ps.stage_name,
                    AVG(pmh.time_in_previous_stage) / 1440 as avg_days,
                    MIN(pmh.time_in_previous_stage) / 1440 as min_days,
                    MAX(pmh.time_in_previous_stage) / 1440 as max_days
             FROM pipeline_movement_history pmh
             LEFT JOIN pipeline_stages ps ON pmh.from_stage_id = ps.id
             WHERE pmh.moved_at >= ? AND pmh.time_in_previous_stage IS NOT NULL
             GROUP BY pmh.from_stage_id, ps.stage_name
             ORDER BY ps.stage_order ASC",
            [$startDate]
        )->fetchAll();
    }

    /**
     * Get team performance
     */
    private function getTeamPerformance(string $startDate): array
    {
        return $this->query(
            "SELECT u.auser as user_name,
                    COUNT(lp.id) as leads_assigned,
                    COUNT(CASE WHEN lp.priority = 'high' THEN 1 END) as high_priority_leads,
                    AVG(lp.confidence_percentage) as avg_confidence,
                    SUM(lp.deal_value) as total_pipeline_value
             FROM lead_pipeline lp
             LEFT JOIN admin u ON lp.assigned_to = u.aid
             WHERE lp.assigned_to IS NOT NULL AND lp.is_active = 1
             GROUP BY lp.assigned_to, u.auser
             ORDER BY leads_assigned DESC"
        )->fetchAll();
    }

    /**
     * Get lead velocity
     */
    private function getLeadVelocity(string $startDate): array
    {
        $velocity = $this->query(
            "SELECT DATE(pmh.moved_at) as date,
                    COUNT(*) as movements,
                    AVG(pmh.time_in_previous_stage) / 1440 as avg_days_in_stage
             FROM pipeline_movement_history pmh
             WHERE pmh.moved_at >= ?
             GROUP BY DATE(pmh.moved_at)
             ORDER BY date DESC LIMIT 30",
            [$startDate]
        )->fetchAll();

        return array_reverse($velocity);
    }

    /**
     * Get revenue pipeline
     */
    private function getRevenuePipeline(): array
    {
        return $this->query(
            "SELECT ps.stage_name, ps.probability_percentage,
                    SUM(lp.deal_value) as total_value,
                    SUM(lp.deal_value * (ps.probability_percentage / 100)) as weighted_value,
                    COUNT(lp.id) as deal_count
             FROM lead_pipeline lp
             LEFT JOIN pipeline_stages ps ON lp.current_stage_id = ps.id
             WHERE lp.is_active = 1 AND lp.deal_value > 0
             GROUP BY lp.current_stage_id, ps.stage_name, ps.probability_percentage
             ORDER BY ps.stage_order ASC"
        )->fetchAll();
    }

    /**
     * Create pipeline filter
     */
    public function createFilter(array $filterData): array
    {
        $filterRecord = [
            'user_id' => $filterData['user_id'],
            'filter_name' => $filterData['filter_name'],
            'filter_criteria' => json_encode($filterData['filter_criteria']),
            'is_default' => $filterData['is_default'] ?? 0,
            'is_shared' => $filterData['is_shared'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $filterId = $this->insertInto('pipeline_filters', $filterRecord);

        return [
            'success' => true,
            'filter_id' => $filterId,
            'message' => 'Filter created successfully'
        ];
    }

    /**
     * Get user filters
     */
    public function getUserFilters(int $userId): array
    {
        $filters = $this->query(
            "SELECT * FROM pipeline_filters WHERE user_id = ? OR is_shared = 1 ORDER BY is_default DESC, filter_name ASC",
            [$userId]
        )->fetchAll();

        foreach ($filters as &$filter) {
            $filter['filter_criteria'] = json_decode($filter['filter_criteria'], true);
        }

        return $filters;
    }

    // Helper methods

    private function getDefaultStageId(): int
    {
        $stage = $this->query("SELECT id FROM pipeline_stages WHERE is_default = 1 AND is_active = 1 LIMIT 1")->fetch();
        return $stage ? $stage['id'] : 1;
    }

    private function logStageMovement(int $leadId, ?int $fromStageId, int $toStageId, ?int $movedBy, string $reason = null): void
    {
        $pipeline = $this->query("SELECT entered_stage_at FROM lead_pipeline WHERE lead_id = ?", [$leadId])->fetch();
        $timeInStage = null;

        if ($pipeline && $pipeline['entered_stage_at'] && $fromStageId) {
            $enteredTime = strtotime($pipeline['entered_stage_at']);
            $currentTime = time();
            $timeInStage = round(($currentTime - $enteredTime) / 60);
        }

        $this->query(
            "INSERT INTO pipeline_movement_history
             (lead_id, from_stage_id, to_stage_id, moved_by, time_in_previous_stage, movement_reason)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$leadId, $fromStageId, $toStageId, $movedBy, $timeInStage, $reason]
        );
    }

    private function logActivity(int $leadId, string $activityType, string $title, array $metadata = [], int $performedBy = null): void
    {
        $this->addActivity([
            'lead_id' => $leadId,
            'activity_type' => $activityType,
            'activity_title' => $title,
            'activity_description' => json_encode($metadata),
            'performed_by' => $performedBy,
            'is_completed' => 1
        ]);
    }

    private function executeStageActions(int $leadId, int $stageId): void
    {
        $stage = $this->query("SELECT automated_actions FROM pipeline_stages WHERE id = ?", [$stageId])->fetch();

        if (!$stage || !$stage['automated_actions']) {
            return;
        }

        $actions = json_decode($stage['automated_actions'], true);

        // Execute automated actions (would integrate with campaign system)
        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'send_email':
                    // Trigger email campaign
                    break;
                case 'assign_task':
                    // Create task for user
                    break;
                case 'update_priority':
                    // Update lead priority
                    break;
            }
        }
    }

    private function updatePipelineAnalytics(int $oldStageId, int $newStageId): void
    {
        $today = date('Y-m-d');

        // Update old stage exit
        if ($oldStageId) {
            $this->query(
                "UPDATE pipeline_analytics SET leads_exited = leads_exited + 1 WHERE stage_id = ? AND date = ?",
                [$oldStageId, $today]
            );
        }

        // Update new stage entry
        $this->query(
            "INSERT INTO pipeline_analytics (stage_id, date, leads_entered, leads_in_stage)
             VALUES (?, ?, 1, 1)
             ON DUPLICATE KEY UPDATE leads_entered = leads_entered + 1, leads_in_stage = leads_in_stage + 1",
            [$newStageId, $today]
        );
    }

    private function getLeadActivities(int $leadId, int $limit = 10): array
    {
        return $this->query(
            "SELECT * FROM pipeline_activities
             WHERE lead_id = ?
             ORDER BY activity_date DESC LIMIT ?",
            [$leadId, $limit]
        )->fetchAll();
    }

    private function calculateDaysInStage(string $enteredAt): int
    {
        $enteredTime = strtotime($enteredAt);
        $currentTime = time();
        return round(($currentTime - $enteredTime) / (60 * 60 * 24));
    }

    private function isLeadOverdue(array $lead): bool
    {
        if (!$lead['stage_deadline']) {
            return false;
        }

        return strtotime($lead['stage_deadline']) < time();
    }
}

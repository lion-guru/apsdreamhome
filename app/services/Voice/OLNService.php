<?php

namespace App\Services\Voice;

use App\Core\Middleware\TenantContext;

class OLNService
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function getDueFollowUps($agentType = null)
    {
        try {
            $tid = TenantContext::getId();
            $sql = "SELECT l.*, u.name as lead_name, u.phone, u.email
                    FROM leads l
                    LEFT JOIN users u ON l.user_id = u.id
                    WHERE l.follow_up_date <= CURDATE()
                    AND l.follow_up_date IS NOT NULL
                    AND (l.status NOT IN ('closed', 'dnd', 'not_interested') OR l.status IS NULL)";
            $params = [];

            if ($tid > 1) {
                $sql .= " AND l.tenant_id = ?";
                $params[] = $tid;
            }

            if ($agentType) {
                $sql .= " AND l.assigned_agent_type = ?";
                $params[] = $agentType;
            }

            $sql .= " ORDER BY l.follow_up_date ASC, l.priority DESC LIMIT 100";

            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            error_log("OLNService::getDueFollowUps error: " . $e->getMessage());
            return [];
        }
    }

    public function autoAssignLeads($limit = 20)
    {
        try {
            $tid = TenantContext::getId();
            $tenantWhere = $tid > 1 ? " AND l.tenant_id = ?" : "";
            $unassigned = $this->db->fetchAll(
                "SELECT l.*, u.name as lead_name, u.phone, u.email
                 FROM leads l
                 LEFT JOIN users u ON l.user_id = u.id
                 WHERE l.assigned_to IS NULL
                 AND l.status NOT IN ('closed', 'dnd', 'not_interested')" . $tenantWhere . "
                 ORDER BY l.priority DESC, l.created_at ASC
                 LIMIT ?",
                $tid > 1 ? [$tid, $limit] : [$limit]
            );

            $agentWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $users = $this->db->fetchAll(
                "SELECT * FROM ai_calling_agents WHERE is_active = 1" . $agentWhere . " ORDER BY current_load ASC",
                $tid > 1 ? [$tid] : []
            );

            if (empty($users)) {
                return ['assigned' => 0, 'message' => 'No active users available'];
            }

            $assigned = 0;
            $agentIndex = 0;
            $agentCount = count($users);

            foreach ($unassigned as $lead) {
                $agent = $users[$agentIndex % $agentCount];
                $agentIndex++;

                $tenantCol = $tid > 1 ? ', tenant_id' : '';
                $tenantVal = $tid > 1 ? ', ?' : '';
                $scheduleParams = [
                    $lead['id'],
                    $lead['user_id'],
                    $lead['phone'] ?? '',
                    'followup',
                    $agent['id'],
                    $lead['priority'] ?? 5
                ];
                if ($tid > 1) {
                    $scheduleParams[] = $tid;
                }
                $this->db->execute(
                    "INSERT INTO ai_calling_schedule (lead_id, user_id, phone, agent_type, assigned_agent, scheduled_date, status, priority, created_at{$tenantCol})
                     VALUES (?, ?, ?, ?, ?, CURDATE(), 'scheduled', ?, NOW(){$tenantVal})",
                    $scheduleParams
                );

                $this->db->execute(
                    "UPDATE leads SET assigned_to = ?, assigned_agent_type = 'followup' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    $tid > 1 ? [$agent['id'], $lead['id'], $tid] : [$agent['id'], $lead['id']]
                );

                $this->db->execute(
                    "UPDATE ai_calling_agents SET current_load = current_load + 1 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    $tid > 1 ? [$agent['id'], $tid] : [$agent['id']]
                );

                $assigned++;
            }

            return ['assigned' => $assigned, 'total' => count($unassigned)];
        } catch (\Exception $e) {
            error_log("OLNService::autoAssignLeads error: " . $e->getMessage());
            return ['assigned' => 0, 'error' => $e->getMessage()];
        }
    }

    public function getLeadJourney($leadId)
    {
        try {
            $tid = TenantContext::getId();
            $tenantWhere = $tid > 1 ? " AND l.tenant_id = ?" : "";
            $lead = $this->db->fetch(
                "SELECT l.*, u.name as lead_name, u.phone, u.email
                 FROM leads l
                 LEFT JOIN users u ON l.user_id = u.id
                 WHERE l.id = ?" . $tenantWhere,
                $tid > 1 ? [$leadId, $tid] : [$leadId]
            );

            if (!$lead) {
                return null;
            }

            $sessionsTenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $sessionsParams = $tid > 1 ? [$leadId, $tid] : [$leadId];
            $sessions = $this->db->fetchAll(
                "SELECT * FROM ai_call_sessions WHERE lead_id = ?" . $sessionsTenantWhere . " ORDER BY started_at DESC",
                $sessionsParams
            );

            $scheduleTenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $scheduleParams = $tid > 1 ? [$leadId, $tid] : [$leadId];
            $schedule = $this->db->fetchAll(
                "SELECT * FROM ai_calling_schedule WHERE lead_id = ?" . $scheduleTenantWhere . " ORDER BY created_at DESC",
                $scheduleParams
            );

            $actTenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $actParams = $tid > 1 ? [$leadId, $tid] : [$leadId];
            $activities = $this->db->fetchAll(
                "SELECT * FROM lead_activities WHERE lead_id = ?" . $actTenantWhere . " ORDER BY created_at DESC",
                $actParams
            );

            return [
                'lead' => $lead,
                'call_sessions' => $sessions,
                'scheduled_calls' => $schedule,
                'activities' => $activities
            ];
        } catch (\Exception $e) {
            error_log("OLNService::getLeadJourney error: " . $e->getMessage());
            return null;
        }
    }

    public function advanceStage($leadId, $newStage, $notes = '')
    {
        try {
            $tid = TenantContext::getId();
            $validStages = ['new', 'contacted', 'interested', 'qualified', 'viewing', 'negotiated', 'closed', 'not_interested', 'dnd'];
            if (!in_array($newStage, $validStages)) {
                return ['success' => false, 'message' => 'Invalid stage: ' . $newStage];
            }

            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $current = $this->db->fetch("SELECT status FROM leads WHERE id = ?" . $tenantWhere, $tid > 1 ? [$leadId, $tid] : [$leadId]);
            if (!$current) {
                return ['success' => false, 'message' => 'Lead not found'];
            }

            $this->db->execute(
                "UPDATE leads SET status = ?, last_stage_change = NOW() WHERE id = ?" . $tenantWhere,
                $tid > 1 ? [$newStage, $leadId, $tid] : [$newStage, $leadId]
            );

            $tenantCol = $tid > 1 ? ', tenant_id' : '';
            $tenantVal = $tid > 1 ? ', ?' : '';
            $activityParams = [
                $leadId,
                "Stage advanced from {$current['status']} to {$newStage}" . ($notes ? " - $notes" : ""),
                json_encode(['from' => $current['status'], 'to' => $newStage, 'notes' => $notes])
            ];
            if ($tid > 1) {
                $activityParams[] = $tid;
            }
            $this->db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, metadata, created_at{$tenantCol})
                 VALUES (?, 'stage_change', ?, ?, NOW(){$tenantVal})",
                $activityParams
            );

            if ($newStage === 'viewing') {
                $lead = $this->db->fetch("SELECT user_id, property_interest FROM leads WHERE id = ?" . $tenantWhere, $tid > 1 ? [$leadId, $tid] : [$leadId]);
                if ($lead) {
                    $scheduleParams = [$leadId, $lead['user_id']];
                    if ($tid > 1) {
                        $scheduleParams[] = $tid;
                    }
                    $this->db->execute(
                        "INSERT INTO ai_calling_schedule (lead_id, user_id, agent_type, scheduled_date, priority, status, created_at{$tenantCol})
                         VALUES (?, ?, 'site_visit', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 1, 'scheduled', NOW(){$tenantVal})",
                        $scheduleParams
                    );
                }
            }

            if ($newStage === 'not_interested') {
                $this->db->execute(
                    "UPDATE leads SET dnd_until = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?" . $tenantWhere,
                    $tid > 1 ? [$leadId, $tid] : [$leadId]
                );
            }

            return ['success' => true, 'stage' => $newStage];
        } catch (\Exception $e) {
            error_log("OLNService::advanceStage error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function calculateLeadScore($leadId)
    {
        try {
            $tid = TenantContext::getId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?" . $tenantWhere, $tid > 1 ? [$leadId, $tid] : [$leadId]);
            if (!$lead) {
                return ['score' => 0, 'category' => 'cold'];
            }

            $score = 0;

            $sessions = $this->db->fetchAll(
                "SELECT * FROM ai_call_sessions WHERE lead_id = ?" . $tenantWhere,
                $tid > 1 ? [$leadId, $tid] : [$leadId]
            );

            $answeredCalls = 0;
            $totalDuration = 0;
            foreach ($sessions as $s) {
                if ($s['status'] === 'completed') {
                    $answeredCalls++;
                    $duration = 0;
                    if ($s['started_at'] && $s['ended_at']) {
                        $duration = strtotime($s['ended_at']) - strtotime($s['started_at']);
                        $totalDuration += $duration;
                    }
                    if ($duration > 60) $score += 10;
                    if ($duration > 180) $score += 10;
                    $sentimentWeight = ($s['sentiment'] ?? 'neutral') === 'positive' ? 15 : ($s['sentiment'] === 'negative' ? -10 : 5);
                    $score += $sentimentWeight;
                }
            }

            $score += min($answeredCalls * 10, 30);

            if (!empty($lead['property_interest'])) $score += 10;
            if (!empty($lead['budget_range'])) $score += 10;
            if (!empty($lead['timeline']) && $lead['timeline'] <= 3) $score += 10;

            $score = max(0, min(100, $score));

            $category = 'cold';
            if ($score >= 70) $category = 'hot';
            elseif ($score >= 40) $category = 'warm';

            return ['score' => $score, 'category' => $category, 'answered_calls' => $answeredCalls, 'total_duration' => $totalDuration];
        } catch (\Exception $e) {
            error_log("OLNService::calculateLeadScore error: " . $e->getMessage());
            return ['score' => 0, 'category' => 'cold'];
        }
    }

    public function getNurturingAnalytics($days = 30)
    {
        try {
            $tid = TenantContext::getId();
            $since = date('Y-m-d', strtotime("-{$days} days"));
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";

            $leadsByStage = $this->db->fetchAll(
                "SELECT COALESCE(status, 'new') as stage, COUNT(*) as count
                 FROM leads WHERE (created_at >= ? OR last_stage_change >= ?)" . $tenantWhere . "
                 GROUP BY status ORDER BY count DESC",
                $tid > 1 ? [$since, $since, $tid] : [$since, $since]
            );

            $conversionFunnel = $this->db->fetchAll(
                "SELECT
                    SUM(CASE WHEN status IN ('interested','qualified','viewing','negotiated') THEN 1 ELSE 0 END) as interested,
                    SUM(CASE WHEN status = 'viewing' THEN 1 ELSE 0 END) as viewing,
                    SUM(CASE WHEN status = 'negotiated' THEN 1 ELSE 0 END) as negotiated,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    COUNT(*) as total
                 FROM leads WHERE created_at >= ?" . $tenantWhere,
                $tid > 1 ? [$since, $tid] : [$since]
            );

            $avgTimeToConvert = $this->db->fetch(
                "SELECT AVG(DATEDIFF(COALESCE(last_stage_change, updated_at), created_at)) as avg_days
                 FROM leads WHERE status = 'closed' AND created_at >= ?" . $tenantWhere,
                $tid > 1 ? [$since, $tid] : [$since]
            );

            $bestCallingTimes = $this->db->fetchAll(
                "SELECT HOUR(started_at) as hour, COUNT(*) as calls,
                        SUM(CASE WHEN sentiment = 'positive' THEN 1 ELSE 0 END) as positive
                 FROM ai_call_sessions WHERE started_at >= ?" . $tenantWhere . "
                 GROUP BY HOUR(started_at) ORDER BY positive DESC LIMIT 5",
                $tid > 1 ? [$since, $tid] : [$since]
            );

            $agentPerformance = $this->db->fetchAll(
                "SELECT a.id, a.name, COUNT(s.id) as total_calls,
                        SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN s.sentiment = 'positive' THEN 1 ELSE 0 END) as positive_calls,
                        AVG(CASE WHEN s.started_at AND s.ended_at
                            THEN TIMESTAMPDIFF(SECOND, s.started_at, s.ended_at) ELSE 0 END) as avg_duration
                 FROM ai_calling_agents a
                 LEFT JOIN ai_call_sessions s ON a.id = s.agent_id AND s.started_at >= ?" . $tenantWhere . "
                 GROUP BY a.id, a.name",
                $tid > 1 ? [$since, $tid] : [$since]
            );

            return [
                'leads_by_stage' => $leadsByStage,
                'conversion_funnel' => $conversionFunnel[0] ?? [],
                'avg_time_to_convert_days' => round($avgTimeToConvert['avg_days'] ?? 0),
                'best_calling_times' => $bestCallingTimes,
                'agent_performance' => $agentPerformance
            ];
        } catch (\Exception $e) {
            error_log("OLNService::getNurturingAnalytics error: " . $e->getMessage());
            return [];
        }
    }

    public function bulkScheduleFollowUps($leadIds, $agentType = 'followup')
    {
        try {
            if (empty($leadIds)) {
                return ['success' => false, 'message' => 'No lead IDs provided'];
            }

            $tid = TenantContext::getId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $scheduled = 0;
            foreach ($leadIds as $leadId) {
                $lead = $this->db->fetch("SELECT id, user_id, phone, assigned_to FROM leads WHERE id = ?" . $tenantWhere, $tid > 1 ? [$leadId, $tid] : [$leadId]);
                if (!$lead) continue;

                $tenantCol = $tid > 1 ? ', tenant_id' : '';
                $tenantVal = $tid > 1 ? ', ?' : '';
                $scheduleParams = [$leadId, $lead['user_id'], $lead['phone'] ?? '', $agentType, $lead['assigned_to']];
                if ($tid > 1) {
                    $scheduleParams[] = $tid;
                }
                $this->db->execute(
                    "INSERT INTO ai_calling_schedule (lead_id, user_id, phone, agent_type, assigned_agent, scheduled_date, status, created_at{$tenantCol})
                     VALUES (?, ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'scheduled', NOW(){$tenantVal})",
                    $scheduleParams
                );
                $scheduled++;
            }

            return ['success' => true, 'scheduled' => $scheduled];
        } catch (\Exception $e) {
            error_log("OLNService::bulkScheduleFollowUps error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

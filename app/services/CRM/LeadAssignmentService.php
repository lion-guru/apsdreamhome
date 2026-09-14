<?php
/**
 * CRM Lead Assignment Service
 * Handles Round-Robin, Attendance-based, and Least-Burdened lead routing
 */

namespace App\Services\CRM;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class LeadAssignmentService
{
    use ServiceTenantTrait;

    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Assign a lead to the best available agent/telecaller/associate via weighted round-robin
     */
    public function assignLead(int $leadId): array
    {
        try {
            $tid = (int)$this->tenantId();
            $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // 1. Get Settings
            $strategy = $this->getSetting('crm_lead_assignment_strategy', 'round_robin', $tid);
            $requireAttendance = $this->getSetting('crm_require_attendance', '0', $tid);

            // 2. Get active agents, telecallers, and associates
            $query = "SELECT u.id as user_id, u.role as user_role
                      FROM users u
                      WHERE u.role IN ('agent', 'telecaller', 'associate') AND u.status = 'active'";

            $params = [];
            if ($tid > 1) {
                $query .= " AND u.tenant_id = ?";
                $params[] = $tid;
            }

            if ($requireAttendance === '1') {
                $today = date('Y-m-d');
                $query .= " AND EXISTS (
                                SELECT 1 FROM employee_attendance a
                                JOIN employees e ON a.employee_id = e.id
                                WHERE e.user_id = u.id
                                AND a.attendance_date = ?
                                AND a.check_in_time IS NOT NULL
                                AND a.check_out_time IS NULL
                            )";
                $params[] = $today;
            }

            $query .= " ORDER BY u.name";
            $assignees = $this->db->fetchAll($query, $params);

            if (empty($assignees)) {
                return ['success' => false, 'message' => 'No available agents/telecallers found'];
            }

            $assignedUserId = null;

            if ($strategy === 'least_burdened') {
                // Find assignee with fewest active leads
                $minLeads = null;
                foreach ($assignees as $a) {
                    $userId = $a['user_id'];
                    $q = "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ? AND status NOT IN ('converted', 'dead', 'closed', 'lost')";
                    $p = [$userId];
                    if ($tid > 1) {
                        $q .= " AND tenant_id = ?";
                        $p[] = $tid;
                    }
                    $activeCount = (int)($this->db->fetchOne($q, $p)['cnt'] ?? 0);

                    if ($minLeads === null || $activeCount < $minLeads) {
                        $minLeads = $activeCount;
                        $assignedUserId = $userId;
                    }
                }
            } else {
                // Weighted Round Robin — pick the assignee with the oldest last assignment
                $oldestTime = null;
                foreach ($assignees as $a) {
                    $userId = $a['user_id'];
                    $q = "SELECT MAX(created_at) as last_assigned FROM crm_assignments WHERE assigned_to = ?";
                    $p = [$userId];
                    if ($tid > 1) {
                        $q .= " AND tenant_id = ?";
                        $p[] = $tid;
                    }
                    $lastAssigned = $this->db->fetchOne($q, $p)['last_assigned'] ?? '2000-01-01 00:00:00';

                    if ($oldestTime === null || strtotime($lastAssigned) < strtotime($oldestTime)) {
                        $oldestTime = $lastAssigned;
                        $assignedUserId = $userId;
                    }
                }
            }

            if ($assignedUserId) {
                // Get the current assigned_to (for the 'from' field)
                $leadRow = $this->db->fetchOne("SELECT assigned_to FROM leads WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$leadId, $tid] : [$leadId]);
                $fromUserId = $leadRow ? ($leadRow['assigned_to'] ?? null) : null;

                // Assign to the selected user
                $q = "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
                $p = [$assignedUserId, $leadId];
                if ($tid > 1) {
                    $q .= " AND tenant_id = ?";
                    $p[] = $tid;
                }
                $this->db->query($q, $p);

                // Log the assignment in crm_assignments
                try {
                    $cols = "lead_id, assigned_from, assigned_to, assigned_by, reason, is_active, tenant_id";
                    $vals = "?, ?, ?, ?, 'auto_assign', 1, ?";
                    $logParams = [$leadId, $fromUserId, $assignedUserId, $fromUserId, $tid > 1 ? $tid : 1];
                    $this->db->query("INSERT INTO crm_assignments ($cols) VALUES ($vals)", $logParams);
                } catch (\Exception $e) {
                    error_log("Failed to log lead assignment: " . $e->getMessage());
                }

                return ['success' => true, 'assigned_to' => $assignedUserId, 'message' => 'Lead assigned successfully'];
            }

            return ['success' => false, 'message' => 'Failed to determine assignee'];

        } catch (\Exception $e) {
            error_log("LeadAssignmentService error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal assignment error'];
        }
    }

    /**
     * Auto-assign a batch of unassigned leads
     */
    public function autoAssignBatch(int $limit = 50): array
    {
        $tid = (int)$this->tenantId();
        $tenantSql = $tid > 1 ? " AND l.tenant_id = ?" : "";
        $p = $tid > 1 ? [$limit, $tid] : [$limit];

        $rows = $this->db->fetchAll(
            "SELECT l.id FROM leads l WHERE l.assigned_to IS NULL AND l.status NOT IN ('converted','dead','closed','lost') {$tenantSql} ORDER BY l.created_at ASC LIMIT ?",
            $p
        );

        $assigned = 0;
        $failed = 0;
        foreach ($rows as $row) {
            $result = $this->assignLead((int)$row['id']);
            if ($result['success']) {
                $assigned++;
            } else {
                $failed++;
            }
        }

        return ['assigned' => $assigned, 'failed' => $failed, 'total' => count($rows)];
    }

    private function getSetting(string $key, string $default, int $tid): string
    {
        try {
            $q = "SELECT value FROM settings WHERE `key` = ?";
            $p = [$key];
            if ($tid > 1) {
                $q .= " AND tenant_id = ?";
                $p[] = $tid;
            }
            $row = $this->db->fetchOne($q, $p);
            return $row ? $row['value'] : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

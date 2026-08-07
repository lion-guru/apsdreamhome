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
     * Assign a lead to the best available telecaller
     */
    public function assignLead(int $leadId): array
    {
        try {
            $tid = (int)$this->tenantId();
            $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            // 1. Get Settings
            $strategy = $this->getSetting('crm_lead_assignment_strategy', 'round_robin', $tid);
            $requireAttendance = $this->getSetting('crm_require_attendance', '0', $tid);

            // 2. Get active telecallers
            // Join users with employees to ensure we have a valid telecaller
            $query = "SELECT u.id as user_id, e.id as employee_id 
                      FROM users u 
                      JOIN employees e ON u.id = e.user_id
                      WHERE u.role = 'telecaller' AND u.status = 'active'";
            
            $params = [];
            if ($tid > 1) {
                $query .= " AND u.tenant_id = ?";
                $params[] = $tid;
            }

            if ($requireAttendance === '1') {
                $today = date('Y-m-d');
                // Check if clocked in today and not clocked out
                $query .= " AND EXISTS (
                                SELECT 1 FROM employee_attendance a 
                                WHERE a.employee_id = e.id 
                                AND a.attendance_date = ? 
                                AND a.check_in_time IS NOT NULL 
                                AND a.check_out_time IS NULL
                            )";
                $params[] = $today;
            }

            $telecallers = $this->db->fetchAll($query, $params);

            if (empty($telecallers)) {
                return ['success' => false, 'message' => 'No available telecallers found'];
            }

            $assignedEmployeeId = null;

            if ($strategy === 'least_burdened') {
                // Find telecaller with fewest active leads
                $minLeads = null;
                foreach ($telecallers as $tc) {
                    $empId = $tc['employee_id'];
                    $q = "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ? AND status NOT IN ('converted', 'dead')";
                    $p = [$empId];
                    if ($tid > 1) {
                        $q .= " AND tenant_id = ?";
                        $p[] = $tid;
                    }
                    $activeCount = (int)($this->db->fetchOne($q, $p)['cnt'] ?? 0);

                    if ($minLeads === null || $activeCount < $minLeads) {
                        $minLeads = $activeCount;
                        $assignedEmployeeId = $empId;
                    }
                }
            } else {
                // Round Robin
                // Get the telecaller with the oldest last_assigned_at timestamp
                $oldestTime = null;
                foreach ($telecallers as $tc) {
                    $empId = $tc['employee_id'];
                    $q = "SELECT MAX(assigned_at) as last_assigned FROM lead_assignments_log WHERE employee_id = ?";
                    $p = [$empId];
                    if ($tid > 1) {
                        $q .= " AND tenant_id = ?";
                        $p[] = $tid;
                    }
                    $lastAssigned = $this->db->fetchOne($q, $p)['last_assigned'] ?? '2000-01-01 00:00:00';
                    
                    if ($oldestTime === null || strtotime($lastAssigned) < strtotime($oldestTime)) {
                        $oldestTime = $lastAssigned;
                        $assignedEmployeeId = $empId;
                    }
                }
            }

            if ($assignedEmployeeId) {
                // Assign to the selected telecaller
                $q = "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
                $p = [$assignedEmployeeId, $leadId];
                if ($tid > 1) {
                    $q .= " AND tenant_id = ?";
                    $p[] = $tid;
                }
                $this->db->query($q, $p);

                // Log the assignment
                // We assume lead_assignments_log exists. If not, this might fail, but it's okay, catch block handles it.
                $cols = "lead_id, employee_id, assigned_at";
                $vals = "?, ?, NOW()";
                $logParams = [$leadId, $assignedEmployeeId];
                if ($tid > 1) {
                    $cols .= ", tenant_id";
                    $vals .= ", ?";
                    $logParams[] = $tid;
                }
                
                try {
                    $this->db->query("INSERT INTO lead_assignments_log ($cols) VALUES ($vals)", $logParams);
                } catch (\Exception $e) {
                    // Ignore if table doesn't exist yet, just error log it
                    error_log("Failed to log lead assignment: " . $e->getMessage());
                }

                return ['success' => true, 'assigned_to' => $assignedEmployeeId, 'message' => 'Lead assigned successfully'];
            }

            return ['success' => false, 'message' => 'Failed to determine assignee'];

        } catch (\Exception $e) {
            error_log("LeadAssignmentService error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal assignment error'];
        }
    }

    private function getSetting(string $key, string $default, int $tid): string
    {
        try {
            $q = "SELECT value FROM settings WHERE key_name = ?";
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

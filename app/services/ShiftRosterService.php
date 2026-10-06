<?php
/**
 * ShiftRosterService
 * Handles shift scheduling, roster management, and overtime approval workflow
 */

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class ShiftRosterService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    /* ═════════════════════════════════════════════════════════════
       SHIFT TYPES
       ═════════════════════════════════════════════════════════════ */

    /**
     * Get all shift types
     */
    public function getShiftTypes(): array
    {
        return $this->fetchAll("
            SELECT * FROM shift_types 
            WHERE is_active = 1 AND tenant_id = ?
            ORDER BY start_time
        ", [$this->tenantId()]);
    }

    /**
     * Create shift type
     */
    public function createShiftType(array $data): array
    {
        $this->pdo->beginTransaction();
        try {
            $startTime = $data['start_time'];
            $endTime = $data['end_time'];
            $duration = $this->calculateDuration($startTime, $endTime);
            $code = strtoupper(str_replace(' ', '_', $data['name']));
            
            $tid = $this->tenantId();
            $stmt = $this->pdo->prepare("
                INSERT INTO shift_types (name, code, description, start_time, end_time, duration_hours, color, is_active, tenant_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())
            ");
            $stmt->execute([
                $data['name'], $code, $data['description'] ?? '', $startTime, $endTime, 
                $duration, $data['color'] ?? '#007bff', $tid
            ]);
            
            $shiftTypeId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();
            return ['success' => true, 'shift_type_id' => $shiftTypeId, 'message' => 'Shift type created'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Calculate duration between start and end time (handles overnight shifts)
     */
    private function calculateDuration(string $start, string $end): float
    {
        $startSec = strtotime($start);
        $endSec = strtotime($end);
        if ($endSec < $startSec) $endSec += 86400; // Next day
        return round(($endSec - $startSec) / 3600, 2);
    }

    /* ═════════════════════════════════════════════════════════════
       ROSTER SCHEDULING
       ═════════════════════════════════════════════════════════════ */

    /**
     * Get roster for a date range
     */
    public function getRoster(string $startDate, string $endDate, int $employeeId = 0): array
    {
        $sql = "
            SELECT es.*, st.name as shift_name, st.color, st.start_time, st.end_time, st.duration_hours,
                   u.name as employee_name, e.employee_code, e.designation, e.department
            FROM employee_shifts es
            LEFT JOIN shift_types st ON es.shift_type_id = st.id
            LEFT JOIN employees e ON es.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE es.shift_date BETWEEN ? AND ? AND es.tenant_id = ?
        ";
        $params = [$startDate, $endDate, $this->tenantId()];
        
        if ($employeeId) {
            $sql .= " AND es.employee_id = ?";
            $params[] = $employeeId;
        }
        
        $sql .= " ORDER BY es.shift_date, st.start_time";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Assign shift to employee
     */
    public function assignShift(array $data): array
    {
        $this->pdo->beginTransaction();
        try {
            $employeeId = (int)($data['employee_id'] ?? 0);
            $shiftTypeId = (int)($data['shift_type_id'] ?? 0);
            $shiftDate = $data['shift_date'] ?? date('Y-m-d');
            $startTime = $data['start_time'] ?? '';
            $endTime = $data['end_time'] ?? '';
            
            if (!$employeeId || !$shiftTypeId) {
                throw new \Exception('Employee and shift type required');
            }

            // Check if employee already has shift on this date
            $existing = $this->fetchOne("
                SELECT id FROM employee_shifts 
                WHERE employee_id = ? AND shift_date = ? AND tenant_id = ?
            ", [$employeeId, $shiftDate, $this->tenantId()]);

            if ($existing) {
                throw new \Exception('Employee already has a shift assigned on this date');
            }

            // Get shift type details
            $shiftType = $this->fetchOne("SELECT * FROM shift_types WHERE id = ? AND tenant_id = ?", [$shiftTypeId, $this->tenantId()]);
            if (!$shiftType) throw new \Exception('Shift type not found');

            $duration = $this->calculateDuration(
                $startTime ?: $shiftType['start_time'],
                $endTime ?: $shiftType['end_time']
            );

            $tid = $this->tenantId();
            $stmt = $this->pdo->prepare("
                INSERT INTO employee_shifts (employee_id, shift_type_id, shift_date, start_time, end_time, duration_hours, status, tenant_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?, NOW())
            ");
            $stmt->execute([
                $employeeId, $shiftTypeId, $shiftDate,
                $startTime ?: $shiftType['start_time'],
                $endTime ?: $shiftType['end_time'],
                $duration, $tid
            ]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Shift assigned successfully'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Bulk assign shifts (weekly roster)
     */
    public function assignWeeklyRoster(int $employeeId, int $shiftTypeId, string $startDate, int $weeks = 4): array
    {
        $this->pdo->beginTransaction();
        try {
            $shiftType = $this->fetchOne("SELECT * FROM shift_types WHERE id = ? AND tenant_id = ?", [$shiftTypeId, $this->tenantId()]);
            if (!$shiftType) throw new \Exception('Shift type not found');

            $start = new \DateTime($startDate);
            $end = clone $start;
            $end->modify("+{$weeks} weeks");

            $assigned = 0;
            $skipped = 0;

            while ($start < $end) {
                $dateStr = $start->format('Y-m-d');
                
                // Check if already assigned
                $existing = $this->fetchOne("
                    SELECT id FROM employee_shifts 
                    WHERE employee_id = ? AND shift_date = ? AND tenant_id = ?
                ", [$employeeId, $dateStr, $this->tenantId()]);

                if (!$existing) {
                    $duration = $shiftType['duration_hours'];
                    $this->execute("
                        INSERT INTO employee_shifts (employee_id, shift_type_id, shift_date, start_time, end_time, duration_hours, status, tenant_id, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?, NOW())
                    ", [$employeeId, $shiftTypeId, $dateStr, $shiftType['start_time'], $shiftType['end_time'], $duration, $this->tenantId()]);
                    $assigned++;
                } else {
                    $skipped++;
                }

                $start->modify('+1 day');
            }

            $this->pdo->commit();
            return ['success' => true, 'assigned' => $assigned, 'skipped' => $skipped];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get employee's upcoming shifts
     */
    public function getUpcomingShifts(int $employeeId, int $days = 7): array
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$days} days"));
        return $this->getRoster($startDate, $endDate, $employeeId);
    }

    /* ═════════════════════════════════════════════════════════════
       OVERTIME MANAGEMENT
       ═════════════════════════════════════════════════════════════ */

    /**
     * Request overtime
     */
    public function requestOvertime(int $employeeId, array $data): array
    {
        $this->pdo->beginTransaction();
        try {
            $overtimeDate = $data['overtime_date'] ?? date('Y-m-d');
            $hours = (float)($data['hours'] ?? 0);
            $reason = $data['reason'] ?? '';

            if ($hours <= 0) throw new \Exception('Overtime hours must be greater than 0');

            $tid = $this->tenantId();
            $stmt = $this->pdo->prepare("
                INSERT INTO overtime_requests (employee_id, overtime_date, hours, reason, status, tenant_id, created_at)
                VALUES (?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([$employeeId, $overtimeDate, $hours, $reason, $tid]);

            $requestId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();
            return ['success' => true, 'request_id' => $requestId, 'message' => 'Overtime request submitted'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get pending overtime requests (for approval)
     */
    public function getPendingOvertime(int $approverId = 0): array
    {
        $sql = "
            SELECT otr.*, u.name as employee_name, e.employee_code, e.designation, e.department,
                   a.name as approver_name
            FROM overtime_requests otr
            LEFT JOIN employees e ON otr.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN users a ON otr.approved_by = a.id
            WHERE otr.status = 'pending' AND otr.tenant_id = ?
        ";
        $params = [$this->tenantId()];

        if ($approverId) {
            $sql .= " AND (e.department = (SELECT department FROM employees WHERE id = ?) OR otr.approved_by = ?)";
            $params[] = $approverId; $params[] = $approverId;
        }

        $sql .= " ORDER BY otr.created_at";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Approve/Reject overtime request
     */
    public function processOvertime(int $requestId, string $action, int $approverId, string $remarks = ''): array
    {
        if (!in_array($action, ['approve', 'reject'])) {
            return ['success' => false, 'message' => 'Invalid action'];
        }

        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE overtime_requests 
                SET status = ?, approved_by = ?, approved_at = NOW(), remarks = ?
                WHERE id = ? AND tenant_id = ? AND status = 'pending'
            ");
            $stmt->execute([$action === 'approve' ? 'approved' : 'rejected', $approverId, $remarks, $requestId, $this->tenantId()]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Request not found or already processed'];
            }

            return ['success' => true, 'message' => "Overtime request {$action}d"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get overtime history for employee
     */
    public function getEmployeeOvertime(int $employeeId, string $startDate = '', string $endDate = ''): array
    {
        $sql = "SELECT * FROM overtime_requests WHERE employee_id = ? AND tenant_id = ?";
        $params = [$employeeId, $this->tenantId()];

        if ($startDate) { $sql .= " AND overtime_date >= ?"; $params[] = $startDate; }
        if ($endDate) { $sql .= " AND overtime_date <= ?"; $params[] = $endDate; }

        $sql .= " ORDER BY overtime_date DESC";
        return $this->fetchAll($sql, [$employeeId, $this->tenantId()]);
    }

    /**
     * Calculate overtime pay for payroll
     */
    public function calculateOvertimePay(int $employeeId, string $month, string $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $requests = $this->fetchAll("
            SELECT SUM(hours) as total_hours
            FROM overtime_requests
            WHERE employee_id = ? AND status = 'approved' 
            AND overtime_date BETWEEN ? AND ? AND tenant_id = ?
        ", [$employeeId, $startDate, $endDate, $this->tenantId()]);

        $totalHours = (float)($requests[0]['total_hours'] ?? 0);

        // Get basic salary for OT rate calculation
        $emp = $this->fetchOne("
            SELECT ss.basic_salary FROM salary_structures ss
            WHERE ss.employee_id = ? AND ss.status = 'active'
            ORDER BY ss.effective_date DESC LIMIT 1
        ", [$employeeId]);

        $basicSalary = (float)($basicSalary ?? 0);
        $hourlyRate = $basicSalary > 0 ? $basicSalary / 26 / 8 : 0; // Daily / 8 hours
        $otRate = $hourlyRate * 2; // Double rate for overtime
        $otAmount = round($totalHours * $otRate, 2);

        return [
            'total_hours' => $totalHours,
            'hourly_rate' => round($hourlyRate, 2),
            'ot_rate' => round($otRate, 2),
            'ot_amount' => $otAmount,
        ];
    }

    /* ═════════════════════════════════════════════════════════════
       REPORTS
       ═════════════════════════════════════════════════════════════ */

    /**
     * Get shift coverage report
     */
    public function getShiftCoverageReport(string $startDate, string $endDate): array
    {
        return $this->fetchAll("
            SELECT st.name as shift_name, st.color,
                   es.shift_date,
                   COUNT(es.id) as assigned_count,
                   GROUP_CONCAT(u.name SEPARATOR ', ') as employee_names
            FROM employee_shifts es
            LEFT JOIN shift_types st ON es.shift_type_id = st.id
            LEFT JOIN employees e ON es.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE es.shift_date BETWEEN ? AND ? AND es.tenant_id = ?
            GROUP BY st.id, es.shift_date
            ORDER BY es.shift_date, st.start_time
        ", [$startDate, $endDate, $this->tenantId()]);
    }

    /**
     * Get overtime summary report
     */
    public function getOvertimeSummaryReport(string $startDate, string $endDate): array
    {
        $requests = $this->fetchAll("
            SELECT otr.*, u.name as employee_name, e.department
            FROM overtime_requests otr
            LEFT JOIN employees e ON otr.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE otr.overtime_date BETWEEN ? AND ? AND otr.tenant_id = ?
            ORDER BY otr.overtime_date, u.name
        ", [$startDate, $endDate, $this->tenantId()]);

        $summary = [
            'total_requests' => count($requests),
            'total_hours' => 0,
            'by_status' => ['pending' => 0, 'approved' => 0, 'rejected' => 0],
            'by_department' => [],
            'details' => $requests,
        ];

        foreach ($requests as $r) {
            $summary['total_hours'] += (float)$r['hours'];
            $summary['by_status'][$r['status']] = ($summary['by_status'][$r['status']] ?? 0) + 1;
            
            $dept = $r['department'] ?? 'Unknown';
            $summary['by_department'][$dept] = ($summary['by_department'][$dept] ?? 0) + (float)$r['hours'];
        }

        return $summary;
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('ShiftRosterService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('ShiftRosterService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('ShiftRosterService::execute: ' . $e->getMessage());
            return 0;
        }
    }
}
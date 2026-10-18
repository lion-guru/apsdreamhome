<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class OvertimeService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get overtime policy for department/role
     */
    public function getPolicy($department = null, $role = null): array
    {
        $query = "SELECT * FROM overtime_policies WHERE tenant_id = 1 AND is_active = 1";
        $params = [1];

        if ($department) {
            $query .= " AND (JSON_CONTAINS(applicable_departments, ?) OR applicable_departments IS NULL)";
            $params[] = json_encode($department);
        }

        $query .= " ORDER BY id LIMIT 1";
        return $this->db->fetchOne($query, $params) ?: [
            'weekday_multiplier' => 1.5,
            'weekend_multiplier' => 2.0,
            'holiday_multiplier' => 2.0,
            'max_hours_per_day' => 4,
            'max_hours_per_month' => 50,
            'requires_approval' => true,
            'auto_approve_threshold' => 2.0
        ];
    }

    /**
     * Request overtime
     */
    public function requestOvertime(int $employeeId, array $data): array
    {
        // Validate employee
        $emp = $this->db->fetchOne("
            SELECT e.id, e.user_id, e.name, e.department, e.designation
            FROM employees e
            WHERE e.id = ? AND e.status = 'active'{$this->tenantSql()}
        ", array_merge([$employeeId], $this->tVal()));

        if (!$emp) {
            return ['success' => false, 'message' => 'Employee not found or inactive'];
        }

        // Validate inputs
        $date = $data['date'] ?? date('Y-m-d');
        $startTime = $data['start_time'] ?? '';
        $endTime = $data['end_time'] ?? '';
        $reason = $data['reason'] ?? '';

        if (!$startTime || !$endTime) {
            return ['success' => false, 'message' => 'Start and end time required'];
        }

        $start = new \DateTime($date . ' ' . $startTime);
        $end = new \DateTime($date . ' ' . $endTime);

        if ($end <= $start) {
            return ['success' => false, 'message' => 'End time must be after start time'];
        }

        $hours = round(($end->getTimestamp() - $start->getTimestamp()) / 3600, 2);

        // Check daily limit
        $policy = $this->getPolicy($emp['department']);
        if ($hours > $policy['max_hours_per_day']) {
            return ['success' => false, 'message' => "Cannot exceed {$policy['max_hours_per_day']} hours per day"];
        }

        // Check monthly limit
        $monthStart = date('Y-m-01', strtotime($date));
        $monthEnd = date('Y-m-t', strtotime($date));
        $monthlyHours = $this->db->fetchOne("
            SELECT COALESCE(SUM(hours), 0) as total
            FROM overtime_requests
            WHERE employee_id = ? AND date BETWEEN ? AND ? AND status IN ('pending','approved','paid')
        ", [$employeeId, $monthStart, $monthEnd]);

        $monthlyTotal = ($monthlyHours['total'] ?? 0) + $hours;
        if ($monthlyTotal > $policy['max_hours_per_month']) {
            return ['success' => false, 'message' => "Monthly overtime limit {$policy['max_hours_per_month']} hours exceeded"];
        }

        // Determine multiplier
        $dayOfWeek = (int)$start->format('w'); // 0=Sunday, 6=Saturday
        $isHoliday = $this->isHoliday($date);
        $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;

        $multiplier = $isHoliday ? $policy['holiday_multiplier'] : ($isWeekend ? $policy['weekend_multiplier'] : $policy['weekday_multiplier']);

        // Calculate hourly rate (basic / 30 / 8)
        $basicSalary = $this->db->fetchOne("
            SELECT COALESCE(ss.basic_salary, e.salary, 0) as basic
            FROM employees e
            LEFT JOIN salary_structures ss ON ss.employee_id = e.id AND ss.status = 'active'
            WHERE e.id = ? ORDER BY ss.effective_date DESC LIMIT 1
        ", [$employeeId]);

        $basicSalary = (float)($basicSalary['basic'] ?? 0);
        $hourlyRate = $basicSalary > 0 ? round($basicSalary / 30 / 8, 2) : 0;

        if ($hourlyRate <= 0) {
            return ['success' => false, 'message' => 'Cannot calculate hourly rate - salary not configured'];
        }

        $amount = round($hours * $hourlyRate * $multiplier, 2);

        // Check for existing request on same date
        $existing = $this->db->fetchOne("
            SELECT id FROM overtime_requests
            WHERE employee_id = ? AND date = ? AND status IN ('pending','approved')
        ", [$employeeId, $date]);

        if ($existing) {
            return ['success' => false, 'message' => 'Overtime already requested for this date'];
        }

        // Determine status (auto-approve if under threshold)
        $status = 'pending';
        if (!$policy['requires_approval'] || $hours <= $policy['auto_approve_threshold']) {
            $status = 'approved';
        }

        $this->execute("
            INSERT INTO overtime_requests
            (tenant_id, employee_id, date, start_time, end_time, hours, rate_multiplier, hourly_rate, amount, reason, status, requested_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [1, $employeeId, $date, $startTime, $endTime, $hours, $multiplier, $hourlyRate, $amount, $reason, $status]);

        $requestId = $this->db->lastInsertId();

        return [
            'success' => true,
            'message' => 'Overtime request ' . ($status === 'approved' ? 'approved' : 'submitted'),
            'request_id' => $requestId,
            'details' => [
                'hours' => $hours,
                'rate_multiplier' => $multiplier,
                'hourly_rate' => $hourlyRate,
                'amount' => $amount,
                'status' => $status
            ]
        ];
    }

    /**
     * Check if date is a holiday
     */
    private function isHoliday(string $date): bool
    {
        $result = $this->db->fetchOne("
            SELECT 1 FROM holiday_calendar
            WHERE date = ? AND is_active = 1 AND tenant_id = 1
        ", [$date]);
        return (bool)$result;
    }

    /**
     * Get employee's overtime requests
     */
    public function getEmployeeRequests(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT * FROM overtime_requests
            WHERE employee_id = ?{$this->tenantSql()}
            ORDER BY overtime_date DESC, requested_at DESC
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Get pending requests (admin/manager)
     */
    public function getPendingRequests($department = null): array
    {
        $query = "
            SELECT orr.*, e.name as employee_name, e.employee_code, e.department, e.designation
            FROM overtime_requests orr
            JOIN employees e ON orr.employee_id = e.id
            WHERE orr.status = 'pending' AND orr.tenant_id = 1
        ";
        $params = [1];

        if ($department) {
            $query .= " AND e.department = ?";
            $params[] = $department;
        }

        $query .= " ORDER BY orr.date DESC, orr.requested_at ASC";
        return $this->fetchAll($query, $params);
    }

    /**
     * Approve overtime request
     */
    public function approveRequest(int $requestId, int $approverId): array
    {
        $req = $this->fetchOne("SELECT * FROM overtime_requests WHERE id = ?{$this->tenantSql()}", [$requestId]);
        if (!$req) return ['success' => false, 'message' => 'Request not found'];
        if ($req['status'] !== 'pending') return ['success' => false, 'message' => 'Request is not pending'];

        $this->execute("
            UPDATE overtime_requests
            SET status = 'approved', approved_at = NOW(), approved_by = ?
            WHERE id = ?{$this->tenantSql()}
        ", array_merge([$approverId, $requestId], $this->tVal()));

        return ['success' => true, 'message' => 'Overtime approved'];
    }

    /**
     * Reject overtime request
     */
    public function rejectRequest(int $requestId, int $rejecterId, string $reason): array
    {
        $req = $this->fetchOne("SELECT * FROM overtime_requests WHERE id = ?{$this->tenantSql()}", [$requestId]);
        if (!$req) return ['success' => false, 'message' => 'Request not found'];
        if ($req['status'] !== 'pending') return ['success' => false, 'message' => 'Request is not pending'];

        $this->execute("
            UPDATE overtime_requests
            SET status = 'rejected', rejected_at = NOW(), rejected_by = ?, rejection_reason = ?
            WHERE id = ?{$this->tenantSql()}
        ", array_merge([$rejecterId, $reason, $requestId], $this->tVal()));

        return ['success' => true, 'message' => 'Overtime rejected'];
    }

    /**
     * Mark overtime as paid (link to payslip)
     */
    public function markAsPaid(int $requestId, int $payslipId): array
    {
        $req = $this->fetchOne("SELECT * FROM overtime_requests WHERE id = ?{$this->tenantSql()}", [$requestId]);
        if (!$req) return ['success' => false, 'message' => 'Request not found'];
        if ($req['status'] !== 'approved') return ['success' => false, 'message' => 'Overtime not approved'];

        $this->execute("
            UPDATE overtime_requests
            SET status = 'paid', paid_at = NOW(), payslip_id = ?
            WHERE id = ?{$this->tenantSql()}
        ", array_merge([$payslipId, $requestId], $this->tVal()));

        return ['success' => true, 'message' => 'Overtime marked as paid'];
    }

    /**
     * Get employee's overtime history
     */
    public function getEmployeeHistory(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT * FROM overtime_requests
            WHERE employee_id = ?{$this->tenantSql()}
            ORDER BY date DESC
        ", array_merge([$employeeId], $this->tVal()));
    }

    /**
     * Get department overtime summary
     */
    public function getDepartmentSummary($department, $month = null): array
    {
        $month = $month ?? date('Y-m');
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        return $this->db->fetchAll("
            SELECT e.name, e.employee_code,
                   SUM(CASE WHEN orr.status = 'approved' THEN orr.hours ELSE 0 END) as approved_hours,
                   SUM(CASE WHEN orr.status = 'paid' THEN orr.hours ELSE 0 END) as paid_hours,
                   SUM(CASE WHEN orr.status = 'pending' THEN orr.hours ELSE 0 END) as pending_hours,
                   SUM(CASE WHEN orr.status = 'approved' THEN orr.amount ELSE 0 END) as approved_amount,
                   SUM(CASE WHEN orr.status = 'paid' THEN orr.amount ELSE 0 END) as paid_amount
            FROM overtime_requests orr
            JOIN employees e ON orr.employee_id = e.id
            WHERE e.department = ? AND orr.date BETWEEN ? AND ? AND orr.tenant_id = 1
            GROUP BY e.id, e.name, e.employee_code
            ORDER BY e.name
        ", [$department, $start, $end]);
    }

    /**
     * Get overtime policy for employee
     */
    public function getEmployeePolicy(int $employeeId): array
    {
        $emp = $this->db->fetchOne("SELECT department FROM employees WHERE id = ?", [$employeeId]);
        return $this->getPolicy($emp['department'] ?? null);
    }
}
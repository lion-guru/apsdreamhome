<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Leave Management Model
 * Handles leave types, requests, balances, and approvals
 */
class Leave extends Model
{
    protected $table = 'leave_requests';
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approved_notes',
        'emergency_contact',
        'work_coverage',
        'attachment_path',
        'created_at',
        'updated_at'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Submit a leave request
     */
    public function submitRequest(array $data): array
    {
        $employeeId = $data['employee_id'];
        $leaveTypeId = $data['leave_type_id'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $reason = $data['reason'];

        // Validate dates
        if (!$this->validateDates($startDate, $endDate)) {
            return ['success' => false, 'message' => 'Invalid date range'];
        }

        // Calculate total days
        $totalDays = $this->calculateWorkingDays($startDate, $endDate);

        // Check leave balance
        if (!$this->checkLeaveBalance($employeeId, $leaveTypeId, $totalDays)) {
            return ['success' => false, 'message' => 'Insufficient leave balance'];
        }

        // Check for conflicting leave requests
        if ($this->hasConflictingRequests($employeeId, $startDate, $endDate)) {
            return ['success' => false, 'message' => 'You already have a leave request for these dates'];
        }

        $requestData = [
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $reason,
            'status' => self::STATUS_PENDING,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'work_coverage' => $data['work_coverage'] ?? null,
            'attachment_path' => $data['attachment_path'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $requestId = $this->insert($requestData);

        return [
            'success' => true,
            'request_id' => $requestId,
            'message' => 'Leave request submitted successfully'
        ];
    }

    /**
     * Get leave requests for an employee
     */
    public function getEmployeeRequests(int $employeeId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT lr.*, lt.name as leave_type_name, lt.code as leave_type_code, lt.color as leave_type_color,
                       a.auser as approved_by_name
                FROM leave_requests lr
                LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN admin a ON lr.approved_by = a.aid
                WHERE lr.employee_id = ?
                ORDER BY lr.created_at DESC
                LIMIT ? OFFSET ?";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId, $limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Get pending leave requests for approval
     */
    public function getPendingRequests(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT lr.*, lt.name as leave_type_name, lt.code as leave_type_code,
                       e.name as employee_name, e.employee_code,
                       d.name as department_name
                FROM leave_requests lr
                LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN employees e ON lr.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE lr.status = 'pending'
                ORDER BY lr.created_at ASC
                LIMIT ? OFFSET ?";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Approve or reject a leave request
     */
    public function processRequest(int $requestId, string $action, int $approverId, string $notes = null): array
    {
        $request = $this->find($requestId);

        if (!$request) {
            return ['success' => false, 'message' => 'Leave request not found'];
        }

        if ($request['status'] !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Request has already been processed'];
        }

        $updateData = [
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($action === 'approve') {
            $updateData['status'] = self::STATUS_APPROVED;

            // Update leave balance
            $this->deductLeaveBalance($request['employee_id'], $request['leave_type_id'], $request['total_days']);
        } elseif ($action === 'reject') {
            $updateData['status'] = self::STATUS_REJECTED;
        } else {
            return ['success' => false, 'message' => 'Invalid action'];
        }

        $this->update($requestId, $updateData);

        return [
            'success' => true,
            'message' => "Leave request " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully"
        ];
    }

    /**
     * Get leave balance for an employee
     */
    public function getLeaveBalance(int $employeeId, int $year = null): array
    {
        $year = $year ?? date('Y');

        $sql = "SELECT lb.*, lt.name as leave_type_name, lt.code as leave_type_code,
                       lt.days_per_year, lt.color
                FROM employee_leave_balances lb
                LEFT JOIN leave_types lt ON lb.leave_type_id = lt.id
                WHERE lb.employee_id = ? AND lb.year = ?
                ORDER BY lt.name";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId, $year]);

        return $stmt->fetchAll();
    }

    /**
     * Initialize leave balance for an employee for a year
     */
    public function initializeLeaveBalance(int $employeeId, int $year = null): void
    {
        $year = $year ?? date('Y');

        // Get all active leave types
        $leaveTypes = $this->getActiveLeaveTypes();

        foreach ($leaveTypes as $leaveType) {
            // Check if balance already exists
            $existing = $this->query(
                "SELECT id FROM employee_leave_balances WHERE employee_id = ? AND leave_type_id = ? AND year = ?",
                [$employeeId, $leaveType['id'], $year]
            )->fetch();

            if (!$existing) {
                $this->insert([
                    'employee_id' => $employeeId,
                    'leave_type_id' => $leaveType['id'],
                    'year' => $year,
                    'allocated_days' => $leaveType['days_per_year'],
                    'used_days' => 0,
                    'remaining_days' => $leaveType['days_per_year'],
                    'carried_forward' => 0
                ]);
            }
        }
    }

    /**
     * Get active leave types
     */
    public function getActiveLeaveTypes(): array
    {
        return $this->query("SELECT * FROM leave_types WHERE status = 'active' ORDER BY name")->fetchAll();
    }

    /**
     * Validate date range
     */
    private function validateDates(string $startDate, string $endDate): bool
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $today = new DateTime();

        return $start <= $end && $start >= $today;
    }

    /**
     * Calculate working days between dates (excluding weekends)
     */
    private function calculateWorkingDays(string $startDate, string $endDate): float
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = $start->diff($end);

        $totalDays = $interval->days + 1; // Include both start and end dates
        $workingDays = 0;

        $current = clone $start;
        for ($i = 0; $i < $totalDays; $i++) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($current->format('w') != 0 && $current->format('w') != 6) {
                $workingDays++;
            }
            $current->modify('+1 day');
        }

        return $workingDays;
    }

    /**
     * Check if employee has sufficient leave balance
     */
    private function checkLeaveBalance(int $employeeId, int $leaveTypeId, float $requestedDays): bool
    {
        $balance = $this->query(
            "SELECT remaining_days FROM employee_leave_balances
             WHERE employee_id = ? AND leave_type_id = ? AND year = ?",
            [$employeeId, $leaveTypeId, date('Y')]
        )->fetch();

        return $balance && $balance['remaining_days'] >= $requestedDays;
    }

    /**
     * Check for conflicting leave requests
     */
    private function hasConflictingRequests(int $employeeId, string $startDate, string $endDate): bool
    {
        $conflicts = $this->query(
            "SELECT id FROM leave_requests
             WHERE employee_id = ? AND status IN ('pending', 'approved')
             AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?) OR (start_date <= ? AND end_date >= ?))",
            [$employeeId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]
        )->fetchAll();

        return count($conflicts) > 0;
    }

    /**
     * Deduct leave balance after approval
     */
    private function deductLeaveBalance(int $employeeId, int $leaveTypeId, float $days): void
    {
        $this->query(
            "UPDATE employee_leave_balances
             SET used_days = used_days + ?, remaining_days = remaining_days - ?
             WHERE employee_id = ? AND leave_type_id = ? AND year = ?",
            [$days, $days, $employeeId, $leaveTypeId, date('Y')]
        );
    }

    /**
     * Get leave calendar for an employee
     */
    public function getLeaveCalendar(int $employeeId, int $year, int $month): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($startDate)));

        $leaves = $this->query(
            "SELECT start_date, end_date, status, lt.name as leave_type, lt.color
             FROM leave_requests lr
             LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
             WHERE lr.employee_id = ? AND lr.status = 'approved'
             AND ((lr.start_date BETWEEN ? AND ?) OR (lr.end_date BETWEEN ? AND ?) OR (lr.start_date <= ? AND lr.end_date >= ?))",
            [$employeeId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]
        )->fetchAll();

        return $leaves;
    }
}

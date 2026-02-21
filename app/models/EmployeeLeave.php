<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Employee Leave Management Model
 * Handles leave requests, approvals, and balance tracking
 */
class EmployeeLeave extends Model
{
    protected $table = 'employee_leaves';
    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_at',
        'updated_at'
    ];

    const TYPE_SICK = 'sick';
    const TYPE_CASUAL = 'casual';
    const TYPE_EARNED = 'earned';
    const TYPE_PAID = 'paid';
    const TYPE_UNPAID = 'unpaid';
    const TYPE_MATERNITY = 'maternity';
    const TYPE_PATERNITY = 'paternity';
    const TYPE_MARRIAGE = 'marriage';
    const TYPE_COMP_OFF = 'comp_off';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get leave balance for employee
     */
    public function getLeaveBalance(int $employeeId, int $year = null): array
    {
        $year = $year ?? date('Y');
        $db = Database::getInstance();

        // Get employee's leave allocation
        $sql = "SELECT * FROM employee_leave_allocations 
                WHERE employee_id = ? AND year = ?";
        $allocations = $db->query($sql, [$employeeId, $year])->fetchAll(\PDO::FETCH_ASSOC);

        // Get used leaves
        $usedSql = "SELECT leave_type, SUM(total_days) as used_days 
                    FROM employee_leaves 
                    WHERE employee_id = ? AND YEAR(start_date) = ? 
                    AND status = 'approved'
                    GROUP BY leave_type";
        $used = $db->query($usedSql, [$employeeId, $year])->fetchAll(\PDO::FETCH_KEY_PAIR);

        $balance = [];
        foreach ($allocations as $alloc) {
            $type = $alloc['leave_type'];
            $balance[$type] = [
                'allocated' => $alloc['days'],
                'used' => $used[$type] ?? 0,
                'remaining' => $alloc['days'] - ($used[$type] ?? 0),
                'carry_forward' => $alloc['carry_forward'] ?? 0
            ];
        }

        return $balance;
    }

    /**
     * Apply for leave
     */
    public function applyLeave(int $employeeId, array $data): array
    {
        $startDate = new DateTime($data['start_date']);
        $endDate = new DateTime($data['end_date']);
        $totalDays = $startDate->diff($endDate)->days + 1;

        // Check leave balance
        $balance = $this->getLeaveBalance($employeeId);
        $leaveType = $data['leave_type'];

        if (!isset($balance[$leaveType])) {
            return ['success' => false, 'message' => 'Invalid leave type'];
        }

        if ($balance[$leaveType]['remaining'] < $totalDays && $leaveType !== self::TYPE_UNPAID) {
            return ['success' => false, 'message' => 'Insufficient leave balance'];
        }

        // Check for overlapping leaves
        $overlap = $this->checkOverlap($employeeId, $data['start_date'], $data['end_date']);
        if ($overlap) {
            return ['success' => false, 'message' => 'Leave dates overlap with existing approved leave'];
        }

        $leaveData = [
            'employee_id' => $employeeId,
            'leave_type' => $leaveType,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'] ?? null,
            'attachment' => $data['attachment'] ?? null,
            'status' => self::STATUS_PENDING,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->insert($leaveData);

        // Notify approvers
        $this->notifyApprovers($employeeId, $id);

        return [
            'success' => true,
            'id' => $id,
            'total_days' => $totalDays
        ];
    }

    /**
     * Check for overlapping leaves
     */
    public function checkOverlap(int $employeeId, string $startDate, string $endDate): bool
    {
        $db = Database::getInstance();

        $sql = "SELECT COUNT(*) FROM employee_leaves 
                WHERE employee_id = ? 
                AND status = 'approved'
                AND (
                    (start_date <= ? AND end_date >= ?)
                    OR (start_date <= ? AND end_date >= ?)
                    OR (start_date >= ? AND end_date <= ?)
                )";

        $count = $db->query($sql, [
            $employeeId, 
            $startDate, $startDate,
            $endDate, $endDate,
            $startDate, $endDate
        ])->fetchColumn();

        return $count > 0;
    }

    /**
     * Approve leave request
     */
    public function approveLeave(int $leaveId, int $approvedBy, string $notes = null): array
    {
        $leave = $this->find($leaveId);

        if (!$leave) {
            return ['success' => false, 'message' => 'Leave request not found'];
        }

        if ($leave['status'] !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Leave request already processed'];
        }

        $this->update($leaveId, [
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Notify employee
        $this->notifyEmployee($leave['employee_id'], $leaveId, 'approved');

        return ['success' => true];
    }

    /**
     * Reject leave request
     */
    public function rejectLeave(int $leaveId, int $rejectedBy, string $reason): array
    {
        $leave = $this->find($leaveId);

        if (!$leave) {
            return ['success' => false, 'message' => 'Leave request not found'];
        }

        if ($leave['status'] !== self::STATUS_PENDING) {
            return ['success' => false, 'message' => 'Leave request already processed'];
        }

        $this->update($leaveId, [
            'status' => self::STATUS_REJECTED,
            'approved_by' => $rejectedBy,
            'rejection_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Notify employee
        $this->notifyEmployee($leave['employee_id'], $leaveId, 'rejected', $reason);

        return ['success' => true];
    }

    /**
     * Cancel leave request
     */
    public function cancelLeave(int $leaveId, int $employeeId): array
    {
        $leave = $this->find($leaveId);

        if (!$leave || $leave['employee_id'] != $employeeId) {
            return ['success' => false, 'message' => 'Leave request not found'];
        }

        if ($leave['status'] === self::STATUS_CANCELLED) {
            return ['success' => false, 'message' => 'Leave already cancelled'];
        }

        // Can only cancel if leave hasn't started or is pending
        if ($leave['status'] === self::STATUS_APPROVED && strtotime($leave['start_date']) <= time()) {
            return ['success' => false, 'message' => 'Cannot cancel leave that has already started'];
        }

        $this->update($leaveId, [
            'status' => self::STATUS_CANCELLED,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return ['success' => true];
    }

    /**
     * Get pending leave requests for manager
     */
    public function getPendingRequests(int $managerId = null): array
    {
        $db = Database::getInstance();

        $sql = "SELECT l.*, e.name as employee_name, e.employee_code, d.name as department_name
                FROM employee_leaves l
                JOIN employees e ON l.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE l.status = 'pending'";

        if ($managerId) {
            $sql .= " AND e.manager_id = " . (int)$managerId;
        }

        $sql .= " ORDER BY l.created_at DESC";

        return $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get leave history
     */
    public function getLeaveHistory(int $employeeId, int $year = null): array
    {
        $year = $year ?? date('Y');

        return $this->where('employee_id', $employeeId)
            ->where('YEAR(start_date)', $year)
            ->orderBy('start_date', 'DESC')
            ->get();
    }

    /**
     * Get team on leave today
     */
    public function getTeamOnLeave(int $departmentId = null): array
    {
        $db = Database::getInstance();

        $sql = "SELECT l.*, e.name as employee_name, e.employee_code
                FROM employee_leaves l
                JOIN employees e ON l.employee_id = e.id
                WHERE l.status = 'approved'
                AND CURDATE() BETWEEN l.start_date AND l.end_date";

        if ($departmentId) {
            $sql .= " AND e.department_id = " . (int)$departmentId;
        }

        return $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Initialize leave allocation for new employee
     */
    public function initializeLeaveAllocation(int $employeeId): void
    {
        $db = Database::getInstance();
        $year = date('Y');

        $defaultAllocations = [
            self::TYPE_SICK => 6,
            self::TYPE_CASUAL => 6,
            self::TYPE_EARNED => 15,
            self::TYPE_PAID => 12,
            self::TYPE_MATERNITY => 180,
            self::TYPE_PATERNITY => 7,
            self::TYPE_MARRIAGE => 5
        ];

        foreach ($defaultAllocations as $type => $days) {
            $sql = "INSERT INTO employee_leave_allocations 
                    (employee_id, leave_type, days, year, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $db->query($sql, [$employeeId, $type, $days, $year]);
        }
    }

    /**
     * Notify approvers about new leave request
     */
    protected function notifyApprovers(int $employeeId, int $leaveId): void
    {
        $db = Database::getInstance();

        // Get employee's manager
        $sql = "SELECT e.manager_id, e.name FROM employees e WHERE e.id = ?";
        $employee = $db->query($sql, [$employeeId])->fetch(\PDO::FETCH_ASSOC);

        if ($employee && $employee['manager_id']) {
            // Create notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->create([
                'user_id' => $employee['manager_id'],
                'type' => 'leave_request',
                'title' => 'New Leave Request',
                'message' => $employee['name'] . ' has submitted a leave request',
                'data' => json_encode(['leave_id' => $leaveId]),
                'link' => '/employee/leaves/approve/' . $leaveId
            ]);
        }
    }

    /**
     * Notify employee about leave status change
     */
    protected function notifyEmployee(int $employeeId, int $leaveId, string $status, string $reason = null): void
    {
        $notificationService = new \App\Services\NotificationService();
        
        $message = $status === 'approved' 
            ? 'Your leave request has been approved' 
            : 'Your leave request has been rejected';

        if ($reason) {
            $message .= '. Reason: ' . $reason;
        }

        $notificationService->create([
            'user_id' => $employeeId,
            'type' => 'leave_status',
            'title' => 'Leave Request ' . ucfirst($status),
            'message' => $message,
            'data' => json_encode(['leave_id' => $leaveId, 'status' => $status]),
            'link' => '/employee/leaves/view/' . $leaveId
        ]);
    }
}

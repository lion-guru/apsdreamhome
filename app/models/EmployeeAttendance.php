<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Employee Attendance Model
 * Handles check-in/out with location tracking
 */
class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendance';
    protected $fillable = [
        'employee_id',
        'check_in_time',
        'check_out_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_location',
        'check_out_location',
        'check_in_photo',
        'check_out_photo',
        'check_in_ip',
        'check_out_ip',
        'work_hours',
        'overtime_hours',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'created_at',
        'updated_at'
    ];

    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_HALF_DAY = 'half_day';
    const STATUS_EARLY_LEAVE = 'early_leave';
    const STATUS_WORK_FROM_HOME = 'wfh';

    /**
     * Check in employee with location
     */
    public function checkIn(int $employeeId, array $data): array
    {
        $now = new DateTime();
        $workStartTime = new DateTime('09:00:00');
        $status = self::STATUS_PRESENT;

        // Check if late (after 9 AM)
        if ($now > $workStartTime) {
            $status = self::STATUS_LATE;
        }

        // Check if already checked in today
        $existing = $this->where('employee_id', $employeeId)
            ->where('DATE(check_in_time)', date('Y-m-d'))
            ->first();

        if ($existing && !$existing['check_out_time']) {
            return ['success' => false, 'message' => 'Already checked in'];
        }

        $checkInData = [
            'employee_id' => $employeeId,
            'check_in_time' => $now->format('Y-m-d H:i:s'),
            'check_in_latitude' => $data['latitude'] ?? null,
            'check_in_longitude' => $data['longitude'] ?? null,
            'check_in_location' => $data['address'] ?? null,
            'check_in_photo' => $data['photo'] ?? null,
            'check_in_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'status' => $data['status'] ?? $status,
            'notes' => $data['notes'] ?? null,
            'created_at' => $now->format('Y-m-d H:i:s')
        ];

        $id = $this->insert($checkInData);

        return [
            'success' => true,
            'id' => $id,
            'check_in_time' => $checkInData['check_in_time'],
            'status' => $checkInData['status']
        ];
    }

    /**
     * Check out employee
     */
    public function checkOut(int $employeeId, array $data): array
    {
        $now = new DateTime();

        // Find today's check-in record
        $record = $this->where('employee_id', $employeeId)
            ->where('DATE(check_in_time)', date('Y-m-d'))
            ->whereNull('check_out_time')
            ->first();

        if (!$record) {
            return ['success' => false, 'message' => 'No check-in record found for today'];
        }

        $checkInTime = new DateTime($record['check_in_time']);
        $workHours = $checkInTime->diff($now)->h + ($checkInTime->diff($now)->i / 60);
        $overtimeHours = max(0, $workHours - 9); // 9 hours standard

        $updateData = [
            'check_out_time' => $now->format('Y-m-d H:i:s'),
            'check_out_latitude' => $data['latitude'] ?? null,
            'check_out_longitude' => $data['longitude'] ?? null,
            'check_out_location' => $data['address'] ?? null,
            'check_out_photo' => $data['photo'] ?? null,
            'check_out_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'work_hours' => round($workHours, 2),
            'overtime_hours' => round($overtimeHours, 2),
            'updated_at' => $now->format('Y-m-d H:i:s')
        ];

        // Update status if early leave
        $workEndTime = new DateTime('18:00:00');
        if ($now < $workEndTime && $workHours < 4) {
            $updateData['status'] = self::STATUS_HALF_DAY;
        } elseif ($now < $workEndTime) {
            $updateData['status'] = self::STATUS_EARLY_LEAVE;
        }

        $this->update($record['id'], $updateData);

        return [
            'success' => true,
            'check_out_time' => $updateData['check_out_time'],
            'work_hours' => $updateData['work_hours'],
            'overtime_hours' => $updateData['overtime_hours'],
            'status' => $updateData['status'] ?? $record['status']
        ];
    }

    /**
     * Get attendance history
     */
    public function getHistory(int $employeeId, string $startDate = null, string $endDate = null): array
    {
        $query = $this->where('employee_id', $employeeId);

        if ($startDate) {
            $query->where('check_in_time >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $query->where('check_in_time <=', $endDate . ' 23:59:59');
        }

        return $query->orderBy('check_in_time', 'DESC')->get();
    }

    /**
     * Get monthly attendance summary
     */
    public function getMonthlySummary(int $employeeId, int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($startDate)));

        $records = $this->getHistory($employeeId, $startDate, $endDate);

        $summary = [
            'total_days' => count($records),
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'half_day' => 0,
            'early_leave' => 0,
            'wfh' => 0,
            'total_work_hours' => 0,
            'total_overtime' => 0,
            'avg_work_hours' => 0
        ];

        foreach ($records as $record) {
            switch ($record['status']) {
                case self::STATUS_PRESENT:
                    $summary['present']++;
                    break;
                case self::STATUS_LATE:
                    $summary['late']++;
                    break;
                case self::STATUS_HALF_DAY:
                    $summary['half_day']++;
                    break;
                case self::STATUS_EARLY_LEAVE:
                    $summary['early_leave']++;
                    break;
                case self::STATUS_WORK_FROM_HOME:
                    $summary['wfh']++;
                    break;
            }
            $summary['total_work_hours'] += $record['work_hours'] ?? 0;
            $summary['total_overtime'] += $record['overtime_hours'] ?? 0;
        }

        $summary['avg_work_hours'] = $summary['total_days'] > 0 
            ? round($summary['total_work_hours'] / $summary['total_days'], 2) 
            : 0;

        return $summary;
    }

    /**
     * Get today's team attendance
     */
    public function getTeamAttendance(int $departmentId = null): array
    {
        $db = Database::getInstance();
        
        $sql = "SELECT e.id, e.name, e.department_id, e.employee_code,
                       a.check_in_time, a.check_out_time, a.status, a.work_hours
                FROM employees e
                LEFT JOIN employee_attendance a ON e.id = a.employee_id 
                    AND DATE(a.check_in_time) = CURDATE()
                WHERE e.status = 'active'";

        if ($departmentId) {
            $sql .= " AND e.department_id = " . (int)$departmentId;
        }

        return $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Mark absent for employees who didn't check in
     */
    public function markAbsentForMissing(): int
    {
        $db = Database::getInstance();
        
        // Get all active employees who haven't checked in today
        $sql = "INSERT INTO employee_attendance (employee_id, check_in_time, status, created_at)
                SELECT e.id, NOW(), 'absent', NOW()
                FROM employees e
                WHERE e.status = 'active'
                AND e.id NOT IN (
                    SELECT employee_id FROM employee_attendance 
                    WHERE DATE(check_in_time) = CURDATE()
                )";

        return $db->exec($sql);
    }
}

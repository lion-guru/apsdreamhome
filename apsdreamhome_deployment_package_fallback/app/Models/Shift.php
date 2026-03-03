<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Shift Scheduling Model
 * Handles shift types, employee shifts, scheduling, and time-off requests
 */
class Shift extends Model
{
    protected $table = 'employee_shifts';
    protected $fillable = [
        'employee_id',
        'shift_type_id',
        'shift_date',
        'start_time',
        'end_time',
        'actual_start_time',
        'actual_end_time',
        'duration_hours',
        'status',
        'notes',
        'assigned_by',
        'confirmed_at',
        'created_at',
        'updated_at'
    ];

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    /**
     * Create a new shift for an employee
     */
    public function createEmployeeShift(array $data): array
    {
        $shiftData = [
            'employee_id' => $data['employee_id'],
            'shift_type_id' => $data['shift_type_id'],
            'shift_date' => $data['shift_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => $data['status'] ?? self::STATUS_SCHEDULED,
            'notes' => $data['notes'] ?? null,
            'assigned_by' => $data['assigned_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $shiftId = $this->insert($shiftData);

        return [
            'success' => true,
            'shift_id' => $shiftId,
            'message' => 'Employee shift created successfully'
        ];
    }

    /**
     * Get shifts for an employee within a date range
     */
    public function getEmployeeShifts(int $employeeId, string $startDate, string $endDate): array
    {
        $sql = "SELECT es.*, st.name as shift_type_name, st.code as shift_type_code,
                       st.start_time, st.end_time, st.duration_hours, st.color
                FROM employee_shifts es
                LEFT JOIN shift_types st ON es.shift_type_id = st.id
                WHERE es.employee_id = ? AND es.shift_date BETWEEN ? AND ?
                ORDER BY es.shift_date ASC, es.start_time ASC";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId, $startDate, $endDate]);

        return $stmt->fetchAll();
    }

    /**
     * Get all shifts for a specific date
     */
    public function getShiftsByDate(string $date): array
    {
        $sql = "SELECT es.*, st.name as shift_type_name, st.code as shift_type_code,
                       st.color, e.name as employee_name, e.employee_code
                FROM employee_shifts es
                LEFT JOIN shift_types st ON es.shift_type_id = st.id
                LEFT JOIN employees e ON es.employee_id = e.id
                WHERE es.shift_date = ? AND es.status != 'cancelled'
                ORDER BY es.start_time ASC";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$date]);

        return $stmt->fetchAll();
    }

    /**
     * Update shift status
     */
    public function updateShiftStatus(int $shiftId, string $status, array $additionalData = []): array
    {
        $updateData = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Add status-specific fields
        if ($status === self::STATUS_CONFIRMED && isset($additionalData['confirmed_at'])) {
            $updateData['confirmed_at'] = $additionalData['confirmed_at'];
        }

        if ($status === self::STATUS_IN_PROGRESS && isset($additionalData['actual_start_time'])) {
            $updateData['actual_start_time'] = $additionalData['actual_start_time'];
        }

        if ($status === self::STATUS_COMPLETED) {
            if (isset($additionalData['actual_end_time'])) {
                $updateData['actual_end_time'] = $additionalData['actual_end_time'];
            }
            if (isset($additionalData['duration_hours'])) {
                $updateData['duration_hours'] = $additionalData['duration_hours'];
            }
        }

        $this->update($shiftId, $updateData);

        return [
            'success' => true,
            'message' => 'Shift status updated successfully'
        ];
    }

    /**
     * Check for shift conflicts
     */
    public function checkShiftConflicts(int $employeeId, string $shiftDate, string $startTime, string $endTime, int $excludeShiftId = null): array
    {
        $sql = "SELECT id, start_time, end_time, status
                FROM employee_shifts
                WHERE employee_id = ? AND shift_date = ? AND status NOT IN ('cancelled', 'completed')";

        $params = [$employeeId, $shiftDate];

        if ($excludeShiftId) {
            $sql .= " AND id != ?";
            $params[] = $excludeShiftId;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $existingShifts = $stmt->fetchAll();
        $conflicts = [];

        foreach ($existingShifts as $shift) {
            // Check for time overlap
            if (($startTime >= $shift['start_time'] && $startTime < $shift['end_time']) ||
                ($endTime > $shift['start_time'] && $endTime <= $shift['end_time']) ||
                ($startTime <= $shift['start_time'] && $endTime >= $shift['end_time'])) {
                $conflicts[] = $shift;
            }
        }

        return [
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts
        ];
    }

    /**
     * Create a shift schedule (recurring)
     */
    public function createShiftSchedule(array $data): array
    {
        $scheduleData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'shift_type_id' => $data['shift_type_id'],
            'department_id' => $data['department_id'] ?? null,
            'days_of_week' => json_encode($data['days_of_week']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'is_active' => 1,
            'created_by' => $data['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $scheduleId = $this->insertInto('shift_schedules', $scheduleData);

        return [
            'success' => true,
            'schedule_id' => $scheduleId,
            'message' => 'Shift schedule created successfully'
        ];
    }

    /**
     * Generate shifts from schedule for a date range
     */
    public function generateShiftsFromSchedule(int $scheduleId, string $startDate, string $endDate): array
    {
        $db = Database::getInstance();

        // Get schedule details
        $schedule = $db->query("SELECT * FROM shift_schedules WHERE id = ?", [$scheduleId])->fetch();
        if (!$schedule) {
            return ['success' => false, 'message' => 'Schedule not found'];
        }

        $daysOfWeek = json_decode($schedule['days_of_week'], true);
        $shiftType = $db->query("SELECT * FROM shift_types WHERE id = ?", [$schedule['shift_type_id']])->fetch();

        if (!$shiftType) {
            return ['success' => false, 'message' => 'Shift type not found'];
        }

        $generatedShifts = 0;
        $currentDate = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);

        // Get employees for this schedule (if department-based, get all employees in department)
        $employees = [];
        if ($schedule['department_id']) {
            $employees = $db->query("SELECT id, name FROM employees WHERE department_id = ? AND status = 'active'",
                                  [$schedule['department_id']])->fetchAll();
        } else {
            // Get assigned employees for this schedule
            $assignments = $db->query(
                "SELECT DISTINCT e.id, e.name FROM shift_assignments sa
                 LEFT JOIN employees e ON sa.employee_id = e.id
                 WHERE sa.schedule_id = ?",
                [$scheduleId]
            )->fetchAll();
            $employees = $assignments;
        }

        while ($currentDate <= $endDateTime) {
            $dayOfWeek = (int)$currentDate->format('w'); // 0 = Sunday, 6 = Saturday

            if (in_array($dayOfWeek, $daysOfWeek)) {
                foreach ($employees as $employee) {
                    // Check if shift already exists
                    $existing = $db->query(
                        "SELECT id FROM employee_shifts
                         WHERE employee_id = ? AND shift_date = ? AND shift_type_id = ?",
                        [$employee['id'], $currentDate->format('Y-m-d'), $schedule['shift_type_id']]
                    )->fetch();

                    if (!$existing) {
                        $this->createEmployeeShift([
                            'employee_id' => $employee['id'],
                            'shift_type_id' => $schedule['shift_type_id'],
                            'shift_date' => $currentDate->format('Y-m-d'),
                            'start_time' => $shiftType['start_time'],
                            'end_time' => $shiftType['end_time'],
                            'status' => self::STATUS_SCHEDULED,
                            'assigned_by' => $schedule['created_by']
                        ]);
                        $generatedShifts++;
                    }
                }
            }

            $currentDate->modify('+1 day');
        }

        return [
            'success' => true,
            'generated_shifts' => $generatedShifts,
            'message' => "Generated {$generatedShifts} shifts successfully"
        ];
    }

    /**
     * Get available shift types
     */
    public function getShiftTypes(): array
    {
        return $this->query("SELECT * FROM shift_types WHERE is_active = 1 ORDER BY name")->fetchAll();
    }

    /**
     * Get shift schedules
     */
    public function getShiftSchedules(): array
    {
        $sql = "SELECT ss.*, st.name as shift_type_name, st.code as shift_type_code,
                       d.name as department_name
                FROM shift_schedules ss
                LEFT JOIN shift_types st ON ss.shift_type_id = st.id
                LEFT JOIN departments d ON ss.department_id = d.id
                WHERE ss.is_active = 1
                ORDER BY ss.created_at DESC";

        return $this->query($sql)->fetchAll();
    }

    /**
     * Calculate shift duration
     */
    public function calculateShiftDuration(string $startTime, string $endTime, int $breakDuration = 0): float
    {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);

        if ($end < $start) {
            // Overnight shift
            $end->modify('+1 day');
        }

        $interval = $start->diff($end);
        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        // Subtract break time
        $totalMinutes -= $breakDuration;

        return round($totalMinutes / 60, 2);
    }

    /**
     * Get shift statistics
     */
    public function getShiftStats(string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-m-01');
        $endDate = $endDate ?? date('Y-m-t');

        $sql = "SELECT
                    COUNT(*) as total_shifts,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_shifts,
                    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_shifts,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_shifts,
                    COUNT(CASE WHEN status = 'no_show' THEN 1 END) as no_show_shifts,
                    SUM(duration_hours) as total_hours,
                    AVG(duration_hours) as avg_shift_hours
                FROM employee_shifts
                WHERE shift_date BETWEEN ? AND ?";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);

        $stats = $stmt->fetch();

        // Get shift type breakdown
        $typeStats = $db->query(
            "SELECT st.name as shift_type, COUNT(es.id) as count
             FROM employee_shifts es
             LEFT JOIN shift_types st ON es.shift_type_id = st.id
             WHERE es.shift_date BETWEEN ? AND ?
             GROUP BY st.id, st.name
             ORDER BY count DESC",
            [$startDate, $endDate]
        )->fetchAll();

        $stats['shift_types'] = $typeStats;

        return $stats ?: [
            'total_shifts' => 0,
            'completed_shifts' => 0,
            'scheduled_shifts' => 0,
            'cancelled_shifts' => 0,
            'no_show_shifts' => 0,
            'total_hours' => 0,
            'avg_shift_hours' => 0,
            'shift_types' => []
        ];
    }

    /**
     * Request time off
     */
    public function requestTimeOff(array $data): array
    {
        $timeOffData = [
            'employee_id' => $data['employee_id'],
            'request_type' => $data['request_type'] ?? 'personal',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'reason' => $data['reason'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $requestId = $this->insertInto('time_off_requests', $timeOffData);

        return [
            'success' => true,
            'request_id' => $requestId,
            'message' => 'Time-off request submitted successfully'
        ];
    }

    /**
     * Get time-off requests for an employee
     */
    public function getTimeOffRequests(int $employeeId): array
    {
        $sql = "SELECT tor.*, a.auser as approved_by_name
                FROM time_off_requests tor
                LEFT JOIN admin a ON tor.approved_by = a.aid
                WHERE tor.employee_id = ?
                ORDER BY tor.created_at DESC";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId]);

        return $stmt->fetchAll();
    }

    /**
     * Get employee's current shift
     */
    public function getCurrentShift(int $employeeId): ?array
    {
        $currentTime = date('H:i:s');
        $today = date('Y-m-d');

        $sql = "SELECT es.*, st.name as shift_type_name, st.code as shift_type_code
                FROM employee_shifts es
                LEFT JOIN shift_types st ON es.shift_type_id = st.id
                WHERE es.employee_id = ?
                  AND es.shift_date = ?
                  AND es.start_time <= ?
                  AND es.end_time >= ?
                  AND es.status IN ('confirmed', 'in_progress')
                ORDER BY es.start_time ASC
                LIMIT 1";

        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$employeeId, $today, $currentTime, $currentTime]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Clock in/out for shift
     */
    public function clockInOut(int $employeeId, string $action): array
    {
        $currentTime = date('H:i:s');
        $currentDate = date('Y-m-d');

        // Find current or next shift
        $shift = $this->query(
            "SELECT * FROM employee_shifts
             WHERE employee_id = ? AND shift_date = ?
             AND ((start_time <= ? AND end_time >= ?) OR start_time > ?)
             AND status IN ('scheduled', 'confirmed')
             ORDER BY start_time ASC LIMIT 1",
            [$employeeId, $currentDate, $currentTime, $currentTime, $currentTime]
        )->fetch();

        if (!$shift) {
            return ['success' => false, 'message' => 'No active shift found for current time'];
        }

        if ($action === 'clock_in') {
            if ($shift['actual_start_time']) {
                return ['success' => false, 'message' => 'Already clocked in for this shift'];
            }

            $this->updateShiftStatus($shift['id'], self::STATUS_IN_PROGRESS, [
                'actual_start_time' => $currentTime
            ]);

            return [
                'success' => true,
                'message' => 'Successfully clocked in',
                'shift_id' => $shift['id']
            ];
        } elseif ($action === 'clock_out') {
            if (!$shift['actual_start_time']) {
                return ['success' => false, 'message' => 'Must clock in first'];
            }

            if ($shift['actual_end_time']) {
                return ['success' => false, 'message' => 'Already clocked out for this shift'];
            }

            $startTime = new DateTime($shift['actual_start_time']);
            $endTime = new DateTime($currentTime);
            $duration = $startTime->diff($endTime);
            $hours = $duration->h + ($duration->i / 60);

            $this->updateShiftStatus($shift['id'], self::STATUS_COMPLETED, [
                'actual_end_time' => $currentTime,
                'duration_hours' => round($hours, 2)
            ]);

            return [
                'success' => true,
                'message' => 'Successfully clocked out',
                'shift_id' => $shift['id'],
                'hours_worked' => round($hours, 2)
            ];
        }

        return ['success' => false, 'message' => 'Invalid action'];
    }
}

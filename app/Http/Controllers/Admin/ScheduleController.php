<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;
use \App\Traits\TenantAwareTrait;

class ScheduleController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->requireAdmin();

        try {
            $adminId = $_SESSION['admin_id'] ?? 0;

            // Today's date
            $today = date('Y-m-d');
            $dayOfWeek = date('w');

            // Today's scheduled shifts with employee names
            $todayShifts = $this->db->fetchAll(
                "SELECT es.*, e.name as employee_name, e.department,
                        st.name as shift_type_name, st.color, st.start_time as shift_start_time,
                        st.end_time as shift_end_time
                 FROM employee_shifts es
                 JOIN users e ON es.employee_id = e.id
                 JOIN shift_types st ON es.shift_type_id = st.id
                 WHERE es.shift_date = ?
                 ORDER BY st.start_time",
                [$today]
            );

            // Total users (active)
            [$tidSql, $tidParams] = $this->tenantWhere();
            $totalEmployees = $this->db->fetch(
                "SELECT COUNT(*) as count FROM users WHERE status = 'active'{$tidSql}", $tidParams
            )['count'] ?? 0;

            // users on shift today
            $onShiftToday = count($todayShifts);

            // Shift types count
            $shiftTypesCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM shift_types WHERE is_active = 1"
            )['count'] ?? 0;

            // Coverage rate
            $coverageRate = $totalEmployees > 0 ? round(($onShiftToday / $totalEmployees) * 100) : 0;

            // Status breakdown for today
            $statusBreakdown = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count
                 FROM employee_shifts
                 WHERE shift_date = ?
                 GROUP BY status",
                [$today]
            );

            // Upcoming 7 days schedule count
            $upcomingWeek = $this->db->fetchAll(
                "SELECT shift_date, COUNT(*) as count
                 FROM employee_shifts
                 WHERE shift_date >= ? AND shift_date < DATE_ADD(?, INTERVAL 7 DAY)
                 GROUP BY shift_date
                 ORDER BY shift_date",
                [$today, $today]
            );

            // Department-wise coverage
            [$tidSql2, $tidParams2] = $this->tenantWhere();
            $deptCoverage = $this->db->fetchAll(
                "SELECT e.department,
                        COUNT(DISTINCT e.id) as total,
                        COUNT(DISTINCT es.id) as scheduled
                 FROM users e
                 LEFT JOIN employee_shifts es ON e.id = es.employee_id AND es.shift_date = ?
                 WHERE e.status = 'active'{$tidSql2}
                 GROUP BY e.department",
                array_merge([$today], $tidParams2)
            );

            // Recent schedule changes
            $recentChanges = $this->db->fetchAll(
                "SELECT es.*, e.name as employee_name, st.name as shift_type
                 FROM employee_shifts es
                 JOIN users e ON es.employee_id = e.id
                 JOIN shift_types st ON es.shift_type_id = st.id
                 ORDER BY es.updated_at DESC
                 LIMIT 10"
            );

            return $this->render('admin/schedule/index', [
                'page_title' => 'Work Schedule Management',
                'today_shifts' => $todayShifts,
                'total_employees' => $totalEmployees,
                'on_shift_today' => $onShiftToday,
                'shift_types_count' => $shiftTypesCount,
                'coverage_rate' => $coverageRate,
                'status_breakdown' => $statusBreakdown,
                'upcoming_week' => $upcomingWeek,
                'dept_coverage' => $deptCoverage,
                'recent_changes' => $recentChanges,
                'today' => $today,
                'day_of_week' => $dayOfWeek,
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading schedule dashboard: ' . $e->getMessage());
            return $this->render('admin/schedule/index', [
                'page_title' => 'Work Schedule Management',
                'error' => true
            ]);
        }
    }

    public function shiftTypes()
    {
        $this->requireAdmin();

        try {
            $shiftTypes = $this->db->fetchAll(
                "SELECT st.*,
                        (SELECT COUNT(*) FROM employee_shifts es WHERE es.shift_type_id = st.id) as assigned_count
                 FROM shift_types st
                 ORDER BY st.start_time"
            );

            return $this->render('admin/schedule/shift_types', [
                'page_title' => 'Shift Types',
                'shift_types' => $shiftTypes,
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading shift types: ' . $e->getMessage());
            return $this->redirect('/admin/schedule');
        }
    }

    public function storeShiftType()
    {
        $this->requireAdmin();

        try {
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            $description = $_POST['description'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $durationHours = $_POST['duration_hours'] ?? 0;
            $isOvernight = isset($_POST['is_overnight']) ? 1 : 0;
            $breakDuration = (int)($_POST['break_duration'] ?? 60);
            $color = $_POST['color'] ?? '#007bff';

            if (empty($name) || empty($code) || empty($startTime) || empty($endTime)) {
                $this->setFlash('error', 'Name, code, start time, and end time are required.');
                return $this->redirect('/admin/schedule/shift-types');
            }

            $this->db->execute(
                "INSERT INTO shift_types (name, code, description, start_time, end_time, duration_hours, is_overnight, break_duration, color, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [$name, $code, $description, $startTime, $endTime, $durationHours, $isOvernight, $breakDuration, $color]
            );

            $this->setFlash('success', 'Shift type created successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error creating shift type: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/shift-types');
    }

    public function updateShiftType($id)
    {
        $this->requireAdmin();

        try {
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            $description = $_POST['description'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $durationHours = $_POST['duration_hours'] ?? 0;
            $isOvernight = isset($_POST['is_overnight']) ? 1 : 0;
            $breakDuration = (int)($_POST['break_duration'] ?? 60);
            $color = $_POST['color'] ?? '#007bff';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name) || empty($code) || empty($startTime) || empty($endTime)) {
                $this->setFlash('error', 'Name, code, start time, and end time are required.');
                return $this->redirect('/admin/schedule/shift-types');
            }

            $this->db->execute(
                "UPDATE shift_types SET name=?, code=?, description=?, start_time=?, end_time=?,
                 duration_hours=?, is_overnight=?, break_duration=?, color=?, is_active=?, updated_at=NOW()
                 WHERE id=?",
                [$name, $code, $description, $startTime, $endTime, $durationHours, $isOvernight, $breakDuration, $color, $isActive, $id]
            );

            $this->setFlash('success', 'Shift type updated successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error updating shift type: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/shift-types');
    }

    public function deleteShiftType($id)
    {
        $this->requireAdmin();

        try {
            // Check if shift type is in use
            $assigned = $this->db->fetch(
                "SELECT COUNT(*) as count FROM employee_shifts WHERE shift_type_id = ?",
                [$id]
            )['count'] ?? 0;

            $scheduled = $this->db->fetch(
                "SELECT COUNT(*) as count FROM shift_schedules WHERE shift_type_id = ?",
                [$id]
            )['count'] ?? 0;

            if ($assigned > 0 || $scheduled > 0) {
                // Soft disable instead of delete
                $this->db->execute(
                    "UPDATE shift_types SET is_active = 0, updated_at = NOW() WHERE id = ?",
                    [$id]
                );
                $this->setFlash('warning', 'Shift type is in use. It has been deactivated instead of deleted.');
            } else {
                $this->db->execute("DELETE FROM shift_types WHERE id = ?", [$id]);
                $this->setFlash('success', 'Shift type deleted successfully.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error deleting shift type: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/shift-types');
    }

    public function employeeShifts()
    {
        $this->requireAdmin();

        try {
            $employeeId = $_GET['employee_id'] ?? '';
            $shiftTypeId = $_GET['shift_type_id'] ?? '';
            $status = $_GET['status'] ?? '';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';

            $where = [];
            $params = [];

            if (!empty($employeeId)) {
                $where[] = "es.employee_id = ?";
                $params[] = $employeeId;
            }
            if (!empty($shiftTypeId)) {
                $where[] = "es.shift_type_id = ?";
                $params[] = $shiftTypeId;
            }
            if (!empty($status)) {
                $where[] = "es.status = ?";
                $params[] = $status;
            }
            if (!empty($dateFrom)) {
                $where[] = "es.shift_date >= ?";
                $params[] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $where[] = "es.shift_date <= ?";
                $params[] = $dateTo;
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : 'WHERE 1=1';

            $assignments = $this->db->fetchAll(
                "SELECT es.*, e.name as employee_name, e.department, e.designation,
                        st.name as shift_type_name, st.color, st.start_time as shift_start_time,
                        st.end_time as shift_end_time
                 FROM employee_shifts es
                 JOIN users e ON es.employee_id = e.id
                 JOIN shift_types st ON es.shift_type_id = st.id
                 $whereClause
                 ORDER BY es.shift_date DESC, st.start_time",
                $params
            );

            // For filters
            [$tidSql, $tidParams] = $this->tenantWhere();
            $users = $this->db->fetchAll(
                "SELECT id, name, department FROM users WHERE status = 'active'{$tidSql} ORDER BY name", $tidParams
            );
            $shiftTypes = $this->db->fetchAll(
                "SELECT id, name FROM shift_types WHERE is_active = 1 ORDER BY name"
            );

            return $this->render('admin/schedule/employee_shifts', [
                'page_title' => 'Employee Shift Assignments',
                'assignments' => $assignments,
                'users' => $users,
                'shift_types' => $shiftTypes,
                'filters' => [
                    'employee_id' => $employeeId,
                    'shift_type_id' => $shiftTypeId,
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading employee shifts: ' . $e->getMessage());
            return $this->redirect('/admin/schedule');
        }
    }

    public function assignShift()
    {
        $this->requireAdmin();

        try {
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $shiftTypeId = (int)($_POST['shift_type_id'] ?? 0);
            $shiftDate = $_POST['shift_date'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if ($employeeId <= 0 || $shiftTypeId <= 0 || empty($shiftDate)) {
                $this->setFlash('error', 'Employee, shift type, and date are required.');
                return $this->redirect('/admin/schedule/employee-shifts');
            }

            // Get shift type times if not specified
            if (empty($startTime) || empty($endTime)) {
                $shiftType = $this->db->fetch(
                    "SELECT start_time, end_time, duration_hours FROM shift_types WHERE id = ?",
                    [$shiftTypeId]
                );
                if ($shiftType) {
                    $startTime = $startTime ?: $shiftType['start_time'];
                    $endTime = $endTime ?: $shiftType['end_time'];
                }
            }

            // Check for existing assignment on same day
            $existing = $this->db->fetch(
                "SELECT id FROM employee_shifts WHERE employee_id = ? AND shift_date = ? AND status NOT IN ('cancelled', 'no_show')",
                [$employeeId, $shiftDate]
            );

            if ($existing) {
                $this->setFlash('warning', 'Employee already has a shift scheduled for this date. Update the existing assignment instead.');
                return $this->redirect('/admin/schedule/employee-shifts');
            }

            $adminId = $_SESSION['admin_id'] ?? 0;

            $this->db->execute(
                "INSERT INTO employee_shifts (employee_id, shift_type_id, shift_date, start_time, end_time, status, notes, assigned_by, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 'scheduled', ?, ?, NOW(), NOW())",
                [$employeeId, $shiftTypeId, $shiftDate, $startTime, $endTime, $notes, $adminId]
            );

            $this->setFlash('success', 'Shift assigned successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error assigning shift: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/employee-shifts');
    }

    public function updateAssignment($id)
    {
        $this->requireAdmin();

        try {
            $shiftTypeId = (int)($_POST['shift_type_id'] ?? 0);
            $shiftDate = $_POST['shift_date'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $actualStart = $_POST['actual_start_time'] ?? null;
            $actualEnd = $_POST['actual_end_time'] ?? null;

            $allowedStatuses = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
            if (!empty($status) && !in_array($status, $allowedStatuses)) {
                $status = 'scheduled';
            }

            $fields = [];
            $params = [];

            if (!empty($shiftTypeId)) {
                $fields[] = "shift_type_id = ?";
                $params[] = $shiftTypeId;
            }
            if (!empty($shiftDate)) {
                $fields[] = "shift_date = ?";
                $params[] = $shiftDate;
            }
            if (!empty($startTime)) {
                $fields[] = "start_time = ?";
                $params[] = $startTime;
            }
            if (!empty($endTime)) {
                $fields[] = "end_time = ?";
                $params[] = $endTime;
            }
            if (!empty($status)) {
                $fields[] = "status = ?";
                $params[] = $status;
            }
            if (!empty($notes)) {
                $fields[] = "notes = ?";
                $params[] = $notes;
            }
            if ($actualStart !== null && $actualStart !== '') {
                $fields[] = "actual_start_time = ?";
                $params[] = $actualStart;
            }
            if ($actualEnd !== null && $actualEnd !== '') {
                $fields[] = "actual_end_time = ?";
                $params[] = $actualEnd;
            }

            if (empty($fields)) {
                $this->setFlash('warning', 'No changes provided.');
                return $this->redirect('/admin/schedule/employee-shifts');
            }

            $fields[] = "updated_at = NOW()";
            $params[] = (int)$id;

            $this->db->execute(
                "UPDATE employee_shifts SET " . implode(', ', $fields) . " WHERE id = ?",
                $params
            );

            $this->setFlash('success', 'Assignment updated successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error updating assignment: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/employee-shifts');
    }

    public function removeAssignment($id)
    {
        $this->requireAdmin();

        try {
            $this->db->execute("DELETE FROM employee_shifts WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Assignment removed successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error removing assignment: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/employee-shifts');
    }

    public function shiftSchedule()
    {
        $this->requireAdmin();

        try {
            $weekOffset = (int)($_GET['week'] ?? 0);
            $departmentId = $_GET['department_id'] ?? '';

            // Calculate week start (Monday) and end (Sunday)
            $monday = strtotime('monday this week' . ($weekOffset >= 0 ? ' +' . $weekOffset : ' ' . $weekOffset) . ' weeks');
            $sunday = strtotime('sunday this week' . ($weekOffset >= 0 ? ' +' . $weekOffset : ' ' . $weekOffset) . ' weeks');
            $weekStart = date('Y-m-d', $monday);
            $weekEnd = date('Y-m-d', $sunday);

            // Build dates array for the week
            $weekDates = [];
            for ($i = 0; $i < 7; $i++) {
                $date = date('Y-m-d', strtotime($weekStart . ' +' . $i . ' days'));
                $weekDates[] = [
                    'date' => $date,
                    'day' => date('D', strtotime($date)),
                    'day_full' => date('l', strtotime($date)),
                    'is_today' => ($date === date('Y-m-d')),
                ];
            }

            // Get users
            [$tidSql, $tidParams] = $this->tenantWhere();
            $users = $this->db->fetchAll(
                "SELECT e.id, e.name, e.department, e.designation
                 FROM users e
                 WHERE e.status = 'active'{$tidSql}
                 ORDER BY e.name", $tidParams
            );

            // Get shifts for this week
            $scheduleData = $this->db->fetchAll(
                "SELECT es.*, e.name as employee_name, e.department as emp_department,
                        st.name as shift_type_name, st.color, st.start_time as shift_start_time,
                        st.end_time as shift_end_time
                 FROM employee_shifts es
                 JOIN users e ON es.employee_id = e.id
                 JOIN shift_types st ON es.shift_type_id = st.id
                 WHERE es.shift_date BETWEEN ? AND ?
                 ORDER BY es.shift_date, e.name",
                [$weekStart, $weekEnd]
            );

            // Organize by employee_id x date
            $scheduleGrid = [];
            foreach ($scheduleData as $s) {
                $scheduleGrid[$s['employee_id']][$s['shift_date']] = $s;
            }

            $shiftTypes = $this->db->fetchAll(
                "SELECT id, name, start_time, end_time, color FROM shift_types WHERE is_active = 1 ORDER BY start_time"
            );

            return $this->render('admin/schedule/shift_schedule', [
                'page_title' => 'Shift Schedule',
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'week_offset' => $weekOffset,
                'week_dates' => $weekDates,
                'users' => $users,
                'schedule_grid' => $scheduleGrid,
                'shift_types' => $shiftTypes,
                'department_id' => $departmentId,
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading schedule: ' . $e->getMessage());
            return $this->redirect('/admin/schedule');
        }
    }

    public function createSchedule()
    {
        $this->requireAdmin();

        try {
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $shiftTypeId = (int)($_POST['shift_type_id'] ?? 0);
            $scheduleDate = $_POST['schedule_date'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $isRotational = isset($_POST['is_rotational']) ? 1 : 0;
            $rotationGroup = $_POST['rotation_group'] ?? '';

            if ($employeeId <= 0 || $shiftTypeId <= 0 || empty($scheduleDate)) {
                $this->setFlash('error', 'Employee, shift type, and date are required.');
                return $this->redirect('/admin/schedule/shift-schedule');
            }

            if (empty($startTime) || empty($endTime)) {
                $shiftType = $this->db->fetch(
                    "SELECT start_time, end_time FROM shift_types WHERE id = ?",
                    [$shiftTypeId]
                );
                if ($shiftType) {
                    $startTime = $startTime ?: $shiftType['start_time'];
                    $endTime = $endTime ?: $shiftType['end_time'];
                }
            }

            $adminId = $_SESSION['admin_id'] ?? 0;

            $this->db->execute(
                "INSERT INTO employee_shifts (employee_id, shift_type_id, shift_date, start_time, end_time, status, notes, assigned_by, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 'scheduled', ?, ?, NOW(), NOW())",
                [$employeeId, $shiftTypeId, $scheduleDate, $startTime, $endTime, $notes, $adminId]
            );

            $this->setFlash('success', 'Schedule entry created successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error creating schedule entry: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/shift-schedule?week=' . ($_POST['week_offset'] ?? 0));
    }

    public function bulkSchedule()
    {
        $this->requireAdmin();

        try {
            $department = $_POST['department'] ?? '';
            $shiftTypeId = (int)($_POST['shift_type_id'] ?? 0);
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($department) || $shiftTypeId <= 0 || empty($startDate) || empty($endDate)) {
                $this->setFlash('error', 'Department, shift type, start date, and end date are required.');
                return $this->redirect('/admin/schedule/shift-schedule');
            }

            // Get shift type times
            $shiftType = $this->db->fetch(
                "SELECT start_time, end_time FROM shift_types WHERE id = ?",
                [$shiftTypeId]
            );

            if (!$shiftType) {
                $this->setFlash('error', 'Shift type not found.');
                return $this->redirect('/admin/schedule/shift-schedule');
            }

            // Get users in department
            [$tidSql, $tidParams] = $this->tenantWhere();
            $users = $this->db->fetchAll(
                "SELECT id FROM users WHERE department = ? AND status = 'active'{$tidSql}",
                array_merge([$department], $tidParams)
            );

            if (empty($users)) {
                $this->setFlash('warning', 'No active users found in the selected department.');
                return $this->redirect('/admin/schedule/shift-schedule');
            }

            $adminId = $_SESSION['admin_id'] ?? 0;
            $created = 0;
            $skipped = 0;

            // Iterate through date range
            $current = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $interval = new \DateInterval('P1D');

            while ($current <= $end) {
                $dateStr = $current->format('Y-m-d');

                foreach ($users as $emp) {
                    // Check existing
                    $existing = $this->db->fetch(
                        "SELECT id FROM employee_shifts WHERE employee_id = ? AND shift_date = ? AND status NOT IN ('cancelled', 'no_show')",
                        [$emp['id'], $dateStr]
                    );

                    if ($existing) {
                        $skipped++;
                        continue;
                    }

                    $this->db->execute(
                        "INSERT INTO employee_shifts (employee_id, shift_type_id, shift_date, start_time, end_time, status, notes, assigned_by, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, 'scheduled', ?, ?, NOW(), NOW())",
                        [$emp['id'], $shiftTypeId, $dateStr, $shiftType['start_time'], $shiftType['end_time'], $notes, $adminId]
                    );
                    $created++;
                }

                $current->add($interval);
            }

            $this->setFlash('success', "Bulk schedule complete: {$created} shifts created, {$skipped} skipped (already scheduled).");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error in bulk scheduling: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/shift-schedule');
    }

    public function workSchedules()
    {
        $this->requireAdmin();

        try {
            $department = $_GET['department'] ?? '';

            $where = '';
            $params = [];

            [$tidSql, $tidParams] = $this->tenantWhere();
            if (!empty($department)) {
                $where = "WHERE e.department = ?";
                $params[] = $department;
            } else {
                $where = ''; // will rely on $tidSql
            }

            $users = $this->db->fetchAll(
                "SELECT e.id, e.name, e.department, e.designation,
                        ws.id as ws_id, ws.shift_start, ws.shift_end, ws.work_days, ws.is_active as ws_active
                 FROM users e
                 LEFT JOIN work_schedules ws ON e.id = ws.employee_id
                 " . ($where ?: 'WHERE 1=1') . "{$tidSql}
                 ORDER BY e.name",
                array_merge($params, $tidParams)
            );

            $departments = $this->db->fetchAll(
                "SELECT DISTINCT department FROM users WHERE status = 'active' AND department IS NOT NULL AND department != ''{$tidSql} ORDER BY department", $tidParams
            );

            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            return $this->render('admin/schedule/work_schedules', [
                'page_title' => 'Work Schedules',
                'users' => $users,
                'departments' => $departments,
                'department' => $department,
                'day_names' => $dayNames,
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading work schedules: ' . $e->getMessage());
            return $this->redirect('/admin/schedule');
        }
    }

    public function storeWorkSchedule()
    {
        $this->requireAdmin();

        try {
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $workDays = $_POST['work_days'] ?? '';
            $shiftStart = $_POST['shift_start'] ?? '';
            $shiftEnd = $_POST['shift_end'] ?? '';
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $wsId = (int)($_POST['ws_id'] ?? 0);

            if ($employeeId <= 0 || empty($workDays) || empty($shiftStart) || empty($shiftEnd)) {
                $this->setFlash('error', 'Employee, work days, and shift times are required.');
                return $this->redirect('/admin/schedule/work-schedules');
            }

            // Normalize work_days: convert array to comma-separated string
            if (is_array($workDays)) {
                $workDays = implode(',', $workDays);
            }

            if ($wsId > 0) {
                try {
                    // Update existing
                    $this->db->execute(
                        "UPDATE work_schedules SET shift_start = ?, shift_end = ?, work_days = ?, is_active = ? WHERE id = ? AND employee_id = ?",
                        [$shiftStart, $shiftEnd, $workDays, $isActive, $wsId, $employeeId]
                    );
                } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
                error_log($e->getMessage());
                }
            } else {
                // Check if employee already has a schedule
                $existing = $this->db->fetch(
                    "SELECT id FROM work_schedules WHERE employee_id = ?",
                    [$employeeId]
                );
                if ($existing) {
                    $this->db->execute(
                        "UPDATE work_schedules SET shift_start = ?, shift_end = ?, work_days = ?, is_active = ? WHERE id = ?",
                        [$shiftStart, $shiftEnd, $workDays, $isActive, $existing['id']]
                    );
                } else {
                    try {
                        $this->db->execute(
                            "INSERT INTO work_schedules (employee_id, shift_start, shift_end, work_days, is_active, created_at)
                             VALUES (?, ?, ?, ?, ?, NOW())",
                            [$employeeId, $shiftStart, $shiftEnd, $workDays, $isActive]
                        );
                    } catch (\Throwable $e) {
                    // Gracefully handle dropped table ref
                    error_log($e->getMessage());
                    }
                }
            }

            $this->setFlash('success', 'Work schedule saved successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error saving work schedule: ' . $e->getMessage());
        }

        return $this->redirect('/admin/schedule/work-schedules');
    }

    public function weeklyView()
    {
        $this->requireAdmin();

        try {
            $weekOffset = (int)($_GET['week'] ?? 0);
            $department = $_GET['department'] ?? '';

            $monday = strtotime('monday this week' . ($weekOffset >= 0 ? ' +' . $weekOffset : ' ' . $weekOffset) . ' weeks');
            $sunday = strtotime('sunday this week' . ($weekOffset >= 0 ? ' +' . $weekOffset : ' ' . $weekOffset) . ' weeks');
            $weekStart = date('Y-m-d', $monday);
            $weekEnd = date('Y-m-d', $sunday);

            $weekDates = [];
            for ($i = 0; $i < 7; $i++) {
                $date = date('Y-m-d', strtotime($weekStart . ' +' . $i . ' days'));
                $weekDates[] = [
                    'date' => $date,
                    'day' => date('D', strtotime($date)),
                    'day_full' => date('l', strtotime($date)),
                    'is_today' => ($date === date('Y-m-d')),
                ];
            }

            $where = '';
            $params = [];
            if (!empty($department)) {
                $where = "AND e.department = ?";
                $params[] = $department;
            }
            [$tidSql, $tidParams] = $this->tenantWhere();

            $users = $this->db->fetchAll(
                "SELECT e.id, e.name, e.department, e.designation
                 FROM users e
                 WHERE e.status = 'active' {$where}{$tidSql}
                 ORDER BY e.name",
                array_merge($params, $tidParams)
            );

            // Get all shifts for this week
            $allShifts = $this->db->fetchAll(
                "SELECT es.*, st.name as shift_type_name, st.color, st.start_time as st_start, st.end_time as st_end
                 FROM employee_shifts es
                 JOIN shift_types st ON es.shift_type_id = st.id
                 WHERE es.shift_date BETWEEN ? AND ?
                 ORDER BY es.employee_id, es.shift_date",
                [$weekStart, $weekEnd]
            );

            $scheduleGrid = [];
            foreach ($allShifts as $s) {
                $scheduleGrid[$s['employee_id']][$s['shift_date']] = $s;
            }

            // Get work schedules
            $workSchedules = [];
            try {
                $wsData = $this->db->fetchAll(
                    "SELECT * FROM work_schedules WHERE is_active = 1"
                );
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
            foreach ($wsData as $ws) {
                $workSchedules[$ws['employee_id']] = $ws;
            }

            $departments = $this->db->fetchAll(
                "SELECT DISTINCT department FROM users WHERE status = 'active' AND department IS NOT NULL AND department != ''{$tidSql} ORDER BY department", $tidParams
            );

            return $this->render('admin/schedule/weekly_view', [
                'page_title' => 'Weekly Schedule View',
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'week_offset' => $weekOffset,
                'week_dates' => $weekDates,
                'users' => $users,
                'schedule_grid' => $scheduleGrid,
                'work_schedules' => $workSchedules,
                'departments' => $departments,
                'department' => $department,
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading weekly view: ' . $e->getMessage());
            return $this->redirect('/admin/schedule');
        }
    }

    public function rotation()
    {
        $this->requireAdmin();

        try {
            // Get rotation schedules from shift_schedules table
            $rotations = $this->db->fetchAll(
                "SELECT ss.*, st.name as shift_type_name, st.color,
                        (SELECT COUNT(*) FROM employee_shifts es WHERE es.shift_type_id = ss.shift_type_id AND es.shift_date >= ss.start_date AND (ss.end_date IS NULL OR es.shift_date <= ss.end_date)) as assigned_count
                 FROM shift_schedules ss
                 JOIN shift_types st ON ss.shift_type_id = st.id
                 ORDER BY ss.name"
            );

            $shiftTypes = $this->db->fetchAll(
                "SELECT id, name, start_time, end_time, color FROM shift_types WHERE is_active = 1 ORDER BY name"
            );

            [$tidSql, $tidParams] = $this->tenantWhere();
            $departments = $this->db->fetchAll(
                "SELECT DISTINCT department FROM users WHERE status = 'active' AND department IS NOT NULL AND department != ''{$tidSql} ORDER BY department", $tidParams
            );

            return $this->render('admin/schedule/rotation', [
                'page_title' => 'Shift Rotation Management',
                'rotations' => $rotations,
                'shift_types' => $shiftTypes,
                'departments' => $departments,
            ]);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading rotation data: ' . $e->getMessage());
            return $this->redirect('/admin/schedule');
        }
    }
}

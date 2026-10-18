<?php

namespace App\Services;

use App\Core\Database\Database;

class EmployeeWorkloadService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getWorkloadConfig($employeeId)
    {
        $config = $this->db->fetchOne(
            "SELECT * FROM employee_workload WHERE tenant_id = 1 AND employee_id = ?",
            [$employeeId]
        );
        
        if (!$config) {
            // Initialize default
            $this->db->execute(
                "INSERT IGNORE INTO employee_workload (tenant_id, employee_id) VALUES (1, ?)",
                [$employeeId]
            );
            $config = [
                'employee_id' => $employeeId,
                'max_concurrent_tasks' => 10,
                'max_workload_percentage' => 80.00,
                'preferred_departments' => null,
                'auto_assign_enabled' => 1,
                'auto_assign_priority' => 'medium',
                'work_hours_start' => '09:00:00',
                'work_hours_end' => '18:00:00',
                'timezone' => 'Asia/Kolkata'
            ];
        }
        return $config;
    }

    public function updateWorkloadConfig($employeeId, $data)
    {
        $allowed = [
            'max_concurrent_tasks', 'max_workload_percentage', 'preferred_departments',
            'auto_assign_enabled', 'auto_assign_priority', 'work_hours_start',
            'work_hours_end', 'timezone'
        ];
        
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $params[] = $employeeId;
        $this->db->execute(
            "UPDATE employee_workload SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE employee_id = ? AND tenant_id = 1",
            $params
        );
        
        return ['success' => true];
    }

    public function getCurrentWorkload($employeeId)
    {
        // Get active tasks count
        $activeTasks = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM tasks 
             WHERE assigned_to = ? AND status IN ('pending', 'in_progress') AND due_date >= CURDATE()",
            [$employeeId]
        );
        
        $config = $this->getWorkloadConfig($employeeId);
        $maxTasks = $config['max_concurrent_tasks'] ?? 10;
        $currentCount = $activeTasks['count'] ?? 0;
        
        $percentage = $maxTasks > 0 ? round(($currentTasks / $maxTasks) * 100, 2) : 0;
        
        return [
            'active_tasks' => $currentCount,
            'max_tasks' => $maxTasks,
            'capacity_remaining' => max(0, $maxTasks - $currentCount),
            'workload_percentage' => $percentage,
            'is_overloaded' => $percentage > ($config['max_workload_percentage'] ?? 80),
            'is_underloaded' => $percentage < 30
        ];
    }

    public function getDepartmentWorkload($department)
    {
        $employees = $this->db->fetchAll(
            "SELECT e.id, e.name, e.role, emp.department
             FROM users e
             JOIN employees emp ON emp.user_id = e.id
             WHERE e.status = 'active' AND emp.status = 'active' AND emp.department = ?",
            [$department]
        );
        
        $workloads = [];
        foreach ($employees as $emp) {
            $wl = $this->getCurrentWorkload($emp['id']);
            $workloads[] = [
                'employee_id' => $emp['id'],
                'name' => $emp['name'],
                'role' => $emp['role'],
                'active_tasks' => $wl['active_tasks'],
                'max_tasks' => $wl['max_tasks'],
                'workload_percentage' => $wl['workload_percentage'],
                'is_overloaded' => $wl['is_overloaded'],
                'is_underloaded' => $wl['is_underloaded']
            ];
        }
        
        return $workloads;
    }

    public function getOverloadedEmployees($threshold = 80)
    {
        return $this->db->fetchAll(
            "SELECT e.id, e.name, e.role, emp.department,
                    (SELECT COUNT(*) FROM tasks WHERE assigned_to = e.id AND status IN ('pending', 'in_progress') AND due_date >= CURDATE()) as active_tasks,
                    COALESCE(ew.max_concurrent_tasks, 10) as max_tasks
             FROM users e
             JOIN employees emp ON emp.user_id = e.id
             LEFT JOIN employee_workload ew ON ew.employee_id = e.id AND ew.tenant_id = 1
             WHERE e.status = 'active' 
             AND emp.status = 'active'
             AND ((SELECT COUNT(*) FROM tasks WHERE assigned_to = e.id AND status IN ('pending', 'in_progress') AND due_date >= CURDATE()) / COALESCE(ew.max_concurrent_tasks, 10)) * 100 > ?",
            [$threshold]
        );
    }

    public function getUnderloadedEmployees($threshold = 30)
    {
        return $this->db->fetchAll(
            "SELECT e.id, e.name, e.role, emp.department,
                    (SELECT COUNT(*) FROM tasks WHERE assigned_to = e.id AND status IN ('pending', 'in_progress') AND due_date >= CURDATE()) as active_tasks,
                    COALESCE(ew.max_concurrent_tasks, 10) as max_tasks
             FROM users e
             JOIN employees emp ON emp.user_id = e.id
             LEFT JOIN employee_workload ew ON ew.employee_id = e.id AND ew.tenant_id = 1
             WHERE e.status = 'active' 
             AND emp.status = 'active'
             AND ((SELECT COUNT(*) FROM tasks WHERE assigned_to = e.id AND status IN ('pending', 'in_progress') AND due_date >= CURDATE()) / COALESCE(ew.max_concurrent_tasks, 10)) * 100 < ?",
            [$threshold]
        );
    }

    public function isEmployeeAvailable($employeeId, $date = null)
    {
        $date = $date ?? date('Y-m-d');
        $dayOfWeek = date('w', strtotime($date));
        
        // Check availability schedule
        $avail = $this->db->fetchOne(
            "SELECT * FROM employee_availability 
             WHERE employee_id = ? AND day_of_week = ? AND is_available = 1
             AND (valid_from IS NULL OR valid_from <= ?)
             AND (valid_until IS NULL OR valid_until >= ?)",
            [$employeeId, $dayOfWeek, $date, $date]
        );
        
        if (!$avail) {
            // Default: available on weekdays 9-6
            return $dayOfWeek >= 1 && $dayOfWeek <= 5;
        }
        
        return true;
    }

    public function getEmployeeAvailability($employeeId, $startDate, $endDate)
    {
        return $this->db->fetchAll(
            "SELECT * FROM employee_availability 
             WHERE employee_id = ? 
             AND (valid_from IS NULL OR valid_from <= ?)
             AND (valid_until IS NULL OR valid_until >= ?)
             ORDER BY day_of_week",
            [$employeeId, $endDate, $startDate]
        );
    }
}
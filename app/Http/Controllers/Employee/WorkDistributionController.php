<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * Work Distribution Controller
 * Handles smart task assignment and work distribution
 */
class WorkDistributionController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Smart task assignment system
     */
    public function assignTask($taskData)
    {
        try {
            // 1. Validate task data
            $this->validateTaskData($taskData);
            
            // 2. Determine task department and role requirements
            $department = $taskData['department'];
            $requiredRole = $taskData['required_role'] ?? null;
            $priority = $taskData['priority'] ?? 'medium';
            
            // 3. Get available employees in department
            $availableEmployees = $this->getAvailableEmployees($department, $requiredRole);
            
            if (empty($availableEmployees)) {
                throw new Exception("No available employees found for this task");
            }
            
            // 4. Calculate workload balance
            $workloads = $this->calculateWorkloads($availableEmployees);
            
            // 5. Consider skill matching if required
            $skillMatch = $this->matchSkills($taskData, $availableEmployees);
            
            // 6. Find best employee for assignment
            $assignedTo = $this->findBestEmployee($availableEmployees, $workloads, $skillMatch, $priority);
            
            // 7. Create the task
            $taskId = $this->createTask($taskData, $assignedTo);
            
            // 8. Notify employee
            $this->notifyEmployee($assignedTo, $taskId, $taskData);
            
            // 9. Log the assignment
            $this->logWorkDistribution($taskId, $assignedTo, $taskData);
            
            return [
                'success' => true,
                'task_id' => $taskId,
                'assigned_to' => $assignedTo,
                'employee_name' => $this->getEmployeeName($assignedTo),
                'message' => 'Task assigned successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get available employees for task assignment
     */
    private function getAvailableEmployees($department, $requiredRole = null)
    {
        $query = "SELECT e.id, e.name, e.role, e.department, 
                        e.workload_capacity, e.current_workload,
                        e.skills, e.performance_score
                 FROM employees e
                 WHERE e.status = 'active'
                 AND e.department = ?
                 AND e.current_workload < e.workload_capacity";
        
        $params = [$department];
        
        if ($requiredRole) {
            $query .= " AND e.role = ?";
            $params[] = $requiredRole;
        }
        
        $query .= " ORDER BY e.performance_score DESC, e.current_workload ASC";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Calculate workloads for employees
     */
    private function calculateWorkloads($employees)
    {
        $workloads = [];
        
        foreach ($employees as $employee) {
            // Get current active tasks count
            $taskQuery = "SELECT COUNT(*) as active_tasks
                          FROM tasks
                          WHERE assigned_to = ?
                          AND status IN ('pending', 'in_progress')
                          AND deadline >= CURDATE()";
            
            $taskCount = $this->db->fetchOne($taskQuery, [$employee['id']]);
            
            // Calculate workload percentage
            $workloadPercentage = ($taskCount['active_tasks'] / $employee['workload_capacity']) * 100;
            
            $workloads[$employee['id']] = [
                'active_tasks' => $taskCount['active_tasks'],
                'workload_percentage' => $workloadPercentage,
                'capacity_remaining' => $employee['workload_capacity'] - $taskCount['active_tasks']
            ];
        }
        
        return $workloads;
    }

    /**
     * Match skills with task requirements
     */
    private function matchSkills($taskData, $employees)
    {
        $skillMatch = [];
        $requiredSkills = $taskData['required_skills'] ?? [];
        
        if (empty($requiredSkills)) {
            // If no specific skills required, all employees have equal match
            foreach ($employees as $employee) {
                $skillMatch[$employee['id']] = 100;
            }
            return $skillMatch;
        }
        
        foreach ($employees as $employee) {
            $employeeSkills = json_decode($employee['skills'] ?? '[]', true);
            $matchingSkills = array_intersect($requiredSkills, $employeeSkills);
            
            // Calculate skill match percentage
            $matchPercentage = count($requiredSkills) > 0 ? 
                              (count($matchingSkills) / count($requiredSkills)) * 100 : 0;
            
            $skillMatch[$employee['id']] = $matchPercentage;
        }
        
        return $skillMatch;
    }

    /**
     * Find best employee for task assignment
     */
    private function findBestEmployee($employees, $workloads, $skillMatch, $priority)
    {
        $bestEmployee = null;
        $bestScore = -1;
        
        foreach ($employees as $employee) {
            $employeeId = $employee['id'];
            
            // Calculate assignment score
            $score = 0;
            
            // Workload factor (less workload = higher score)
            $workloadFactor = 100 - $workloads[$employeeId]['workload_percentage'];
            $score += $workloadFactor * 0.4; // 40% weight
            
            // Skill match factor
            $skillFactor = $skillMatch[$employeeId] ?? 0;
            $score += $skillFactor * 0.4; // 40% weight
            
            // Performance factor
            $performanceFactor = $employee['performance_score'] ?? 50;
            $score += $performanceFactor * 0.2; // 20% weight
            
            // Priority adjustment
            if ($priority === 'high') {
                // For high priority, prefer employees with better performance
                $score += ($performanceFactor - 50) * 0.3;
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestEmployee = $employee;
            }
        }
        
        return $bestEmployee['id'];
    }

    /**
     * Create task in database
     */
    private function createTask($taskData, $assignedTo)
    {
        $query = "INSERT INTO tasks (
                    title, description, department, required_role,
                    required_skills, priority, deadline, estimated_hours,
                    assigned_to, assigned_by, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $params = [
            $taskData['title'],
            $taskData['description'],
            $taskData['department'],
            $taskData['required_role'] ?? null,
            json_encode($taskData['required_skills'] ?? []),
            $taskData['priority'] ?? 'medium',
            $taskData['deadline'],
            $taskData['estimated_hours'] ?? null,
            $assignedTo,
            $taskData['assigned_by']
        ];
        
        $this->db->execute($query, $params);
        
        return $this->db->lastInsertId();
    }

    /**
     * Notify employee of new task assignment
     */
    private function notifyEmployee($employeeId, $taskId, $taskData)
    {
        // Create notification
        $notificationQuery = "INSERT INTO notifications (
                                employee_id, type, title, message, 
                                related_id, created_at, status
                              ) VALUES (?, 'task_assigned', ?, ?, ?, NOW(), 'unread')";
        
        $message = "New task assigned: {$taskData['title']}. " .
                   "Priority: {$taskData['priority']}. " .
                   "Deadline: {$taskData['deadline']}";
        
        $this->db->execute($notificationQuery, [
            $employeeId,
            'Task Assigned',
            $message,
            $taskId
        ]);
        
        // Send email notification (if configured)
        $this->sendEmailNotification($employeeId, $taskData, $taskId);
    }

    /**
     * Send email notification to employee
     */
    private function sendEmailNotification($employeeId, $taskData, $taskId)
    {
        // Get employee email
        $employeeQuery = "SELECT name, email FROM employees WHERE id = ?";
        $employee = $this->db->fetchOne($employeeQuery, [$employeeId]);
        
        if ($employee && !empty($employee['email'])) {
            // Here you would integrate with your email service
            // For now, just log the notification
            error_log("Email notification sent to {$employee['email']} for task {$taskId}");
        }
    }

    /**
     * Log work distribution for analytics
     */
    private function logWorkDistribution($taskId, $assignedTo, $taskData)
    {
        $query = "INSERT INTO work_distribution_logs (
                    task_id, assigned_to, department, priority,
                    assignment_method, created_at
                ) VALUES (?, ?, ?, ?, 'smart_algorithm', NOW())";
        
        $this->db->execute($query, [
            $taskId,
            $assignedTo,
            $taskData['department'],
            $taskData['priority'] ?? 'medium'
        ]);
    }

    /**
     * Get employee name
     */
    private function getEmployeeName($employeeId)
    {
        $query = "SELECT name FROM employees WHERE id = ? LIMIT 1";
        $result = $this->db->fetchOne($query, [$employeeId]);
        return $result['name'] ?? 'Unknown';
    }

    /**
     * Validate task data
     */
    private function validateTaskData($taskData)
    {
        $required = ['title', 'description', 'department', 'assigned_by', 'deadline'];
        
        foreach ($required as $field) {
            if (empty($taskData[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }
        
        // Validate deadline format
        if (!strtotime($taskData['deadline'])) {
            throw new Exception("Invalid deadline format");
        }
        
        // Validate department exists
        $deptQuery = "SELECT COUNT(*) as count FROM departments WHERE name = ?";
        $deptResult = $this->db->fetchOne($deptQuery, [$taskData['department']]);
        
        if ($deptResult['count'] == 0) {
            throw new Exception("Invalid department specified");
        }
    }

    /**
     * Get work distribution analytics
     */
    public function getDistributionAnalytics($department = null, $dateRange = 7)
    {
        try {
            $whereClause = "";
            $params = [];
            
            if ($department) {
                $whereClause = "AND wd.department = ?";
                $params[] = $department;
            }
            
            $dateFilter = "DATE(wd.created_at) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
            $params[] = $dateRange;
            
            $query = "SELECT 
                        wd.department,
                        COUNT(*) as total_assignments,
                        AVG(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) * 100 as completion_rate,
                        AVG(TIMESTAMPDIFF(HOUR, wd.created_at, t.completed_at)) as avg_completion_hours,
                        e.role as employee_role,
                        COUNT(DISTINCT wd.assigned_to) as unique_employees
                    FROM work_distribution_logs wd
                    JOIN tasks t ON wd.task_id = t.id
                    JOIN employees e ON wd.assigned_to = e.id
                    WHERE {$dateFilter} {$whereClause}
                    GROUP BY wd.department, e.role
                    ORDER BY total_assignments DESC";
            
            return $this->db->fetchAll($query, $params);
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Rebalance workloads
     */
    public function rebalanceWorkloads($department)
    {
        try {
            // Get employees with high workloads
            $highWorkloadQuery = "SELECT e.id, e.name, e.current_workload, e.workload_capacity
                                 FROM employees e
                                 WHERE e.department = ?
                                 AND e.status = 'active'
                                 AND (e.current_workload / e.workload_capacity) > 0.8
                                 ORDER BY (e.current_workload / e.workload_capacity) DESC";
            
            $overloadedEmployees = $this->db->fetchAll($highWorkloadQuery, [$department]);
            
            // Get employees with low workloads
            $lowWorkloadQuery = "SELECT e.id, e.name, e.current_workload, e.workload_capacity
                                FROM employees e
                                WHERE e.department = ?
                                AND e.status = 'active'
                                AND (e.current_workload / e.workload_capacity) < 0.5
                                ORDER BY (e.current_workload / e.workload_capacity) ASC";
            
            $underloadedEmployees = $this->db->fetchAll($lowWorkloadQuery, [$department]);
            
            $rebalancingActions = [];
            
            // Reassign tasks from overloaded to underloaded employees
            foreach ($overloadedEmployees as $overloaded) {
                $tasksToReassign = min(2, $overloaded['current_workload'] - $overloaded['workload_capacity']);
                
                if ($tasksToReassign > 0 && !empty($underloadedEmployees)) {
                    // Get reassignable tasks
                    $reassignableQuery = "SELECT t.id, t.title, t.priority
                                         FROM tasks t
                                         WHERE t.assigned_to = ?
                                         AND t.status = 'pending'
                                         AND t.deadline > DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                                         ORDER BY t.priority DESC, t.deadline ASC
                                         LIMIT ?";
                    
                    $tasks = $this->db->fetchAll($reassignableQuery, [$overloaded['id'], $tasksToReassign]);
                    
                    foreach ($tasks as $task) {
                        if (!empty($underloadedEmployees)) {
                            $underloaded = array_shift($underloadedEmployees);
                            
                            // Reassign task
                            $updateQuery = "UPDATE tasks 
                                           SET assigned_to = ?, reassigned_at = NOW(), reassigned_from = ?
                                           WHERE id = ?";
                            
                            $this->db->execute($updateQuery, [$underloaded['id'], $overloaded['id'], $task['id']]);
                            
                            // Update employee workloads
                            $this->updateEmployeeWorkload($overloaded['id']);
                            $this->updateEmployeeWorkload($underloaded['id']);
                            
                            // Log rebalancing
                            $this->logRebalancing($task['id'], $overloaded['id'], $underloaded['id']);
                            
                            $rebalancingActions[] = [
                                'task_id' => $task['id'],
                                'task_title' => $task['title'],
                                'from_employee' => $overloaded['name'],
                                'to_employee' => $underloaded['name']
                            ];
                            
                            // Notify employees
                            $this->notifyReassignment($task['id'], $overloaded['id'], $underloaded['id']);
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'rebalanced_tasks' => count($rebalancingActions),
                'actions' => $rebalancingActions
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update employee workload
     */
    private function updateEmployeeWorkload($employeeId)
    {
        $query = "UPDATE employees 
                  SET current_workload = (
                      SELECT COUNT(*) 
                      FROM tasks 
                      WHERE assigned_to = ? 
                      AND status IN ('pending', 'in_progress')
                  )
                  WHERE id = ?";
        
        $this->db->execute($query, [$employeeId, $employeeId]);
    }

    /**
     * Log task rebalancing
     */
    private function logRebalancing($taskId, $fromEmployee, $toEmployee)
    {
        $query = "INSERT INTO task_rebalancing_logs (
                    task_id, from_employee, to_employee, rebalanced_at
                ) VALUES (?, ?, ?, NOW())";
        
        $this->db->execute($query, [$taskId, $fromEmployee, $toEmployee]);
    }

    /**
     * Notify employees of task reassignment
     */
    private function notifyReassignment($taskId, $fromEmployee, $toEmployee)
    {
        // Notify employee who lost the task
        $this->createNotification($fromEmployee, 'task_reassigned', 
            "Task #{$taskId} has been reassigned to another team member", $taskId);
        
        // Notify employee who received the task
        $this->createNotification($toEmployee, 'task_assigned', 
            "You have been assigned task #{$taskId}", $taskId);
    }

    /**
     * Create notification
     */
    private function createNotification($employeeId, $type, $message, $relatedId)
    {
        $query = "INSERT INTO notifications (
                    employee_id, type, message, related_id, created_at, status
                ) VALUES (?, ?, ?, ?, NOW(), 'unread')";
        
        $this->db->execute($query, [$employeeId, $type, $message, $relatedId]);
    }
}

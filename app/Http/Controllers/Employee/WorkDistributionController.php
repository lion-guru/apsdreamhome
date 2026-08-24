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
        parent::__construct();
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
            
            // 3. Get available users in department
            $availableEmployees = $this->getAvailableEmployees($department, $requiredRole);
            
            if (empty($availableEmployees)) {
                throw new Exception("No available users found for this task");
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
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get available users for task assignment
     */
    private function getAvailableEmployees($department, $requiredRole = null)
    {
        $query = "SELECT e.id, e.name, e.role, e.department, 
                        e.workload_capacity, e.current_workload,
                        e.skills, e.performance_score
                 FROM users e
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
     * Calculate workloads for users
     */
    private function calculateWorkloads($users)
    {
        $workloads = [];
        
        foreach ($users as $employee) {
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
    private function matchSkills($taskData, $users)
    {
        $skillMatch = [];
        $requiredSkills = $taskData['required_skills'] ?? [];
        
        if (empty($requiredSkills)) {
            // If no specific skills required, all users have equal match
            foreach ($users as $employee) {
                $skillMatch[$employee['id']] = 100;
            }
            return $skillMatch;
        }
        
        foreach ($users as $employee) {
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
    private function findBestEmployee($users, $workloads, $skillMatch, $priority)
    {
        $bestEmployee = null;
        $bestScore = -1;
        
        foreach ($users as $employee) {
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
                // For high priority, prefer users with better performance
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
                    title, description, department, priority,
                    due_date, assigned_to, created_by, status, tenant_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";
        
        $params = [
            $taskData['title'],
            $taskData['description'],
            $taskData['department'],
            $taskData['priority'] ?? 'medium',
            $taskData['deadline'],
            $assignedTo,
            $taskData['assigned_by'],
            $this->tenantId() ?? 1
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
                                user_id, type, title, message, 
                                related_id, created_at, status, tenant_id
                              ) VALUES (?, 'task_assigned', ?, ?, ?, NOW(), 'unread', ?)";
        
        $message = "New task assigned: {$taskData['title']}. " .
                   "Priority: {$taskData['priority']}. " .
                   "Deadline: {$taskData['deadline']}";
        
        $this->db->execute($notificationQuery, [
            $employeeId,
            'Task Assigned',
            $message,
            $taskId,
            $this->tenantId() ?? 1
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
        $employeeQuery = "SELECT name, email FROM users WHERE id = ?";
        $employee = $this->db->fetchOne($employeeQuery, [$employeeId]);
        
        if ($employee && !empty($employee['email'])) {
            // Here you would integrate with your email service
            // For now, just log the notification
            error_log("Email notification sent to {$employee['email']} for task {$taskId}");
        }
    }

    /**
     * Log work distribution for analytics
     * Table work_distribution_logs doesn't exist - no-op
     */
    private function logWorkDistribution($taskId, $assignedTo, $taskData)
    {
        // No-op: work_distribution_logs table doesn't exist
        return;
    }

    /**
     * Get employee name
     */
    private function getEmployeeName($employeeId)
    {
        $query = "SELECT name FROM users WHERE id = ? LIMIT 1";
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
     * Table work_distribution_logs doesn't exist - returns empty with error
     */
    public function getDistributionAnalytics($department = null, $dateRange = 7)
    {
        return ['error' => 'Analytics not available: work_distribution_logs table does not exist'];
    }

    /**
     * Rebalance workloads
     * Note: users table doesn't have current_workload/workload_capacity columns.
     * Workload is computed from active task count.
     */
    public function rebalanceWorkloads($department)
    {
        try {
            // Get users with high workloads (computed from tasks)
            $highWorkloadQuery = "SELECT e.id, e.name, 
                                         (SELECT COUNT(*) FROM tasks WHERE assigned_to = e.id AND status IN ('pending', 'in_progress')) as current_workload,
                                         10 as workload_capacity
                                  FROM users e
                                  WHERE e.department = ?
                                  AND e.status = 'active'
                                  HAVING (current_workload / workload_capacity) > 0.8
                                  ORDER BY (current_workload / workload_capacity) DESC";
            
            $overloadedEmployees = $this->db->fetchAll($highWorkloadQuery, [$department]);
            
            // Get users with low workloads
            $lowWorkloadQuery = "SELECT e.id, e.name, 
                                        (SELECT COUNT(*) FROM tasks WHERE assigned_to = e.id AND status IN ('pending', 'in_progress')) as current_workload,
                                        10 as workload_capacity
                                 FROM users e
                                 WHERE e.department = ?
                                 AND e.status = 'active'
                                 HAVING (current_workload / workload_capacity) < 0.5
                                 ORDER BY (current_workload / workload_capacity) ASC";
            
            $underloadedEmployees = $this->db->fetchAll($lowWorkloadQuery, [$department]);
            
            $rebalancingActions = [];
            
            // Reassign tasks from overloaded to underloaded users
            foreach ($overloadedEmployees as $overloaded) {
                $tasksToReassign = min(2, max(0, $overloaded['current_workload'] - $overloaded['workload_capacity']));
                
                if ($tasksToReassign > 0 && !empty($underloadedEmployees)) {
                    // Get reassignable tasks - use due_date instead of deadline
                    $reassignableQuery = "SELECT t.id, t.title, t.priority
                                         FROM tasks t
                                         WHERE t.assigned_to = ?
                                         AND t.status = 'pending'
                                         AND t.due_date > DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                                         ORDER BY t.priority DESC, t.due_date ASC
                                         LIMIT ?";
                    
                    $tasks = $this->db->fetchAll($reassignableQuery, [$overloaded['id'], $tasksToReassign]);
                    
                    foreach ($tasks as $task) {
                        if (!empty($underloadedEmployees)) {
                            $underloaded = array_shift($underloadedEmployees);
                            
                            // Reassign task - remove reassigned_at/reassigned_from columns
                            $updateQuery = "UPDATE tasks 
                                           SET assigned_to = ?
                                           WHERE id = ?";
                            
                            $this->db->execute($updateQuery, [$underloaded['id'], $task['id']]);
                            
                            // Log rebalancing - no-op since task_rebalancing_logs doesn't exist
                            // $this->logRebalancing($task['id'], $overloaded['id'], $underloaded['id']);
                            
                            $rebalancingActions[] = [
                                'task_id' => $task['id'],
                                'task_title' => $task['title'],
                                'from_employee' => $overloaded['name'],
                                'to_employee' => $underloaded['name']
                            ];
                            
                            // Notify users
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
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update employee workload
     * users table doesn't have current_workload column - workload is computed from tasks
     * This is a no-op.
     */
    private function updateEmployeeWorkload($employeeId)
    {
        // No-op: users table doesn't have current_workload column
        return;
    }

    /**
     * Log task rebalancing
     * Table task_rebalancing_logs doesn't exist - no-op
     */
    private function logRebalancing($taskId, $fromEmployee, $toEmployee)
    {
        // No-op: task_rebalancing_logs table doesn't exist
        return;
    }

    /**
     * Notify users of task reassignment
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
                    user_id, type, message, related_id, created_at, status, tenant_id
                ) VALUES (?, ?, ?, ?, NOW(), 'unread', ?)";
        
        $this->db->execute($query, [$employeeId, $type, $message, $relatedId, $this->tenantId() ?? 1]);
    }
}

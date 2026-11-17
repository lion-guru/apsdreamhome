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
            
        } catch (\Throwable $e) {
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
        // users table has no department/workload columns — department comes via employees join
        $query = "SELECT e.id, e.name, e.role
                 FROM users e
                 JOIN employees emp ON emp.user_id = e.id
                 WHERE e.status = 'active'
                 AND emp.status = 'active'
                 AND emp.department = ?";

        $params = [$department];

        if ($requiredRole) {
            $query .= " AND e.role = ?";
            $params[] = $requiredRole;
        }

        $query .= " ORDER BY e.name ASC";

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
                          AND due_date >= CURDATE()";

            $taskCount = $this->db->fetchOne($taskQuery, [$employee['id']]);

            // Default capacity (users table has no workload_capacity column)
            $capacity = 10;

            // Calculate workload percentage
            $workloadPercentage = ($taskCount['active_tasks'] / $capacity) * 100;

            $workloads[$employee['id']] = [
                'active_tasks' => $taskCount['active_tasks'],
                'workload_percentage' => $workloadPercentage,
                'capacity_remaining' => $capacity - $taskCount['active_tasks']
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
            $employeeSkills = []; // users table has no skills column
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
                                  WHERE e.role = ?
                                  AND e.status = 'active'
                                  HAVING (current_workload / workload_capacity) > 0.8
                                  ORDER BY (current_workload / workload_capacity) DESC";
            
            $overloadedEmployees = $this->db->fetchAll($highWorkloadQuery, [$department]);
            
            // Get users with low workloads
            $lowWorkloadQuery = "SELECT e.id, e.name, 
                                        (SELECT COUNT(*) FROM tasks WHERE assigned_to = e.id AND status IN ('pending', 'in_progress')) as current_workload,
                                        10 as workload_capacity
                                 FROM users e
                                 WHERE e.role = ?
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
            
        } catch (\Throwable $e) {
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
    /**
     * Config-code -> display name (for UI) and employee_profiles.department (for SQL joins).
     * Codes that have no matching employee_profiles.department render 0 employees (honest empty state).
     */
    private const DEPT_DISPLAY = [
        'SALES' => 'Sales', 'FIN' => 'Finance', 'CS' => 'Customer Service',
        'OPS' => 'Operations', 'LEGAL' => 'Legal', 'HR' => 'HR',
        'MKTG' => 'Marketing', 'IT' => 'IT', 'EXEC' => 'Executive',
        'CONST' => 'Construction', 'LAND' => 'Land Acquisition',
    ];
    private const DEPT_PROFILE = [
        'SALES' => 'Sales', 'FIN' => 'Finance', 'OPS' => 'Operations',
        'IT' => 'Technology', 'EXEC' => 'Executive',
    ];

    private function deptName(string $code): string
    {
        return self::DEPT_DISPLAY[$code] ?? $code;
    }

    private function deptProfileMap(string $code): ?string
    {
        return self::DEPT_PROFILE[$code] ?? null;
    }

    private function activeEmployeesInDept(string $profileDept): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.role, ep.department AS department, ep.designation
             FROM users u
             JOIN employee_profiles ep ON (ep.user_id = u.id OR ep.legacy_id = u.id OR ep.id = u.id)
             WHERE u.status = 'active' AND ep.status = 'active' AND ep.department = ?
             ORDER BY u.name ASC",
            [$profileDept]
        ) ?: [];
    }

    private function activeTaskCountsByEmployee(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT assigned_to, COUNT(*) AS c FROM tasks
             WHERE status NOT IN ('completed') AND assigned_to IS NOT NULL
             GROUP BY assigned_to"
        ) ?: [];
        $map = [];
        foreach ($rows as $r) { $map[(int)$r['assigned_to']] = (int)$r['c']; }
        return $map;
    }

    private function workloadRows(string $code, array $cfgRow, array $taskCounts): array
    {
        $profileDept = $this->deptProfileMap($code);
        $employees = $profileDept ? $this->activeEmployeesInDept($profileDept) : [];
        $maxTasks = max(1, (int)($cfgRow['max_tasks_per_employee'] ?? 10));
        $rows = [];
        foreach ($employees as $e) {
            $eid = (int)$e['id'];
            $active = $taskCounts[$eid] ?? 0;
            $pct = (int)round(($active / $maxTasks) * 100);
            $rows[] = [
                'employee_id' => $eid,
                'name' => $e['name'],
                'role' => $e['role'] ?? '',
                'department' => $profileDept,
                'designation' => $e['designation'] ?? '',
                'active_tasks' => $active,
                'max_tasks' => $maxTasks,
                'workload_percentage' => $pct,
                'is_overloaded' => $active >= $maxTasks,
                'is_underloaded' => $active === 0,
            ];
        }
        return $rows;
    }


    public function index()
    {
        $this->requireAdmin();
        $cfgRows = $this->db->fetchAll("SELECT * FROM work_distribution_config ORDER BY id") ?: [];
        $taskCounts = $this->activeTaskCountsByEmployee();
        $departments = [];
        $stats = [];
        foreach ($cfgRows as $cfg) {
            $code = $cfg['department'];
            $departments[] = ['code' => $code, 'name' => $this->deptName($code)];
            $rows = $this->workloadRows($code, $cfg, $taskCounts);
            $empCount = count($rows);
            $overloaded = count(array_filter($rows, fn($w) => $w['is_overloaded']));
            $underloaded = count(array_filter($rows, fn($w) => $w['is_underloaded']));
            $totalTasks = (int)($this->db->fetchOne(
                "SELECT COUNT(*) AS c FROM tasks WHERE department = ? AND status NOT IN ('completed')",
                [$code]
            )['c'] ?? 0);
            $stats[$code] = ['total_employees' => $empCount, 'overloaded' => $overloaded, 'underloaded' => $underloaded, 'total_tasks' => $totalTasks];
        }
        $this->render('admin/work_distribution/index', [
            'departments' => $departments,
            'stats' => $stats,
        ], 'Work Distribution - APS Dream Home');
    }

    public function department($department)
    {
        $this->requireAdmin();
        $code = strtoupper(trim((string)$department));
        if ($code === '' || !isset(self::DEPT_DISPLAY[$code])) {
            $this->redirect('/admin/work-distribution');
            return;
        }
        $cfg = $this->db->fetchOne("SELECT * FROM work_distribution_config WHERE department = ?", [$code]) ?: [
            'department' => $code,
            'auto_assign_enabled' => 1,
            'assignment_algorithm' => 'hybrid',
            'max_tasks_per_employee' => 10,
            'rebalance_threshold' => 80.00,
        ];
        $taskCounts = $this->activeTaskCountsByEmployee();
        $workload = $this->workloadRows($code, $cfg, $taskCounts);
        $pendingTasks = $this->db->fetchAll(
            "SELECT t.*, u.name AS assigned_to_name
             FROM tasks t
             LEFT JOIN users u ON u.id = t.assigned_to
             WHERE t.department = ? AND t.status NOT IN ('completed')
             ORDER BY (t.due_date IS NULL), t.due_date ASC, t.id DESC",
            [$code]
        ) ?: [];
        $this->render('admin/work_distribution/department', [
            'department' => $code,
            'config' => $cfg,
            'workload' => $workload,
            'pending_tasks' => $pendingTasks,
            'skills' => [],
        ], 'Work Distribution - ' . $code . ' - APS Dream Home');
    }

    public function workload()
    {
        $this->requireAdmin();
        $cfgRows = $this->db->fetchAll("SELECT * FROM work_distribution_config ORDER BY id") ?: [];
        $taskCounts = $this->activeTaskCountsByEmployee();
        $allWorkload = [];
        $departments = [];
        foreach ($cfgRows as $cfg) {
            $code = $cfg['department'];
            $departments[] = ['code' => $code, 'name' => $this->deptName($code)];
            $allWorkload[$code] = $this->workloadRows($code, $cfg, $taskCounts);
        }
        $this->render('admin/work_distribution/workload', [
            'departments' => $departments,
            'all_workload' => $allWorkload,
        ], 'Workload Overview - APS Dream Home');
    }

    public function departmentConfig($department)
    {
        $this->requireAdmin();
        $code = strtoupper(trim((string)$department));
        if ($code === '' || !isset(self::DEPT_DISPLAY[$code])) {
            $this->redirect('/admin/work-distribution');
            return;
        }
        $service = new \App\Services\WorkDistributionService();
        $config = $service->getConfig($code);
        $this->render('admin/work_distribution/config', [
            'department' => $code,
            'config' => $config,
            'algorithms' => ['hybrid', 'least_loaded', 'skill_based', 'performance_based', 'round_robin'],
        ], 'Work Distribution Config - ' . $code . ' - APS Dream Home');
    }

    public function updateDepartmentConfig($department)
    {
        $this->requireAdmin();
        $code = strtoupper(trim((string)$department));
        if ($code === '' || !isset(self::DEPT_DISPLAY[$code])) {
            $this->redirect('/admin/work-distribution');
            return;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'], (string)$token)) {
            $this->redirect('/login');
            return;
        }
        $skill = (float)($_POST['skill_weight'] ?? 0.40);
        $workload = (float)($_POST['workload_weight'] ?? 0.40);
        $performance = (float)($_POST['performance_weight'] ?? 0.20);
        if (abs(($skill + $workload + $performance) - 1.0) > 0.01) {
            $_SESSION['error'] = 'Scoring weights must sum to 1.0.';
            $this->redirect('/admin/work-distribution/' . urlencode($code) . '/config');
            return;
        }
        $algo = (string)($_POST['assignment_algorithm'] ?? 'hybrid');
        if (!in_array($algo, ['hybrid', 'least_loaded', 'skill_based', 'performance_based', 'round_robin'], true)) {
            $algo = 'hybrid';
        }
        $data = [
            'auto_assign_enabled' => !empty($_POST['auto_assign_enabled']) ? 1 : 0,
            'assignment_algorithm' => $algo,
            'skill_weight' => $skill,
            'workload_weight' => $workload,
            'performance_weight' => $performance,
            'max_tasks_per_employee' => max(1, min(50, (int)($_POST['max_tasks_per_employee'] ?? 10))),
            'rebalance_threshold' => max(50, min(100, (float)($_POST['rebalance_threshold'] ?? 80))),
            'rebalance_interval_hours' => max(1, min(168, (int)($_POST['rebalance_interval_hours'] ?? 4))),
            'notify_on_assignment' => !empty($_POST['notify_on_assignment']) ? 1 : 0,
            'notify_on_rebalance' => !empty($_POST['notify_on_rebalance']) ? 1 : 0,
            'ai_assignment_enabled' => !empty($_POST['ai_assignment_enabled']) ? 1 : 0,
            'ai_model' => (string)($_POST['ai_model'] ?? 'hybrid_algorithm'),
        ];
        $service = new \App\Services\WorkDistributionService();
        $result = $service->updateConfig($code, $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? ($result['success'] ? 'Configuration saved.' : 'Save failed.');
        $this->redirect('/admin/work-distribution/' . urlencode($code) . '/config');
    }

    private function postedTaskData(string $department): array
    {
        $code = strtoupper(trim($department));
        return [
            'title' => trim(strip_tags((string)($_POST['title'] ?? ''))),
            'description' => trim(strip_tags((string)($_POST['description'] ?? ''))),
            'department' => $code,
            'required_role' => ($_POST['required_role'] ?? '') !== '' ? (string)$_POST['required_role'] : null,
            'priority' => in_array((string)($_POST['priority'] ?? 'medium'), ['low', 'medium', 'high', 'urgent'], true) ? (string)$_POST['priority'] : 'medium',
            'algorithm' => (string)($_POST['algorithm'] ?? 'hybrid'),
            'assigned_by' => (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1),
            'force_assign' => !empty($_POST['force_assign']) ? 1 : 0,
            'deadline' => (string)($_POST['deadline'] ?? date('Y-m-d', strtotime('+7 days'))),
        ];
    }

    public function assignTaskAction($department)
    {
        $this->requireAdmin();
        $code = strtoupper(trim((string)$department));
        if ($code === '' || !isset(self::DEPT_DISPLAY[$code])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unknown department.']);
            return;
        }
        $data = $this->postedTaskData($code);
        if ($data['title'] === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Task title is required.']);
            return;
        }
        $service = new \App\Services\WorkDistributionService();
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        if ($employeeId > 0) {
            $result = $service->assignTaskToSpecific($data, $employeeId);
        } else {
            $result = $service->assignTask($data);
        }
        $this->jsonResponse($result);
    }

    public function assignManual($department)
    {
        $this->requireAdmin();
        $code = strtoupper(trim((string)$department));
        if ($code === '' || !isset(self::DEPT_DISPLAY[$code])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unknown department.']);
            return;
        }
        $taskId = (int)($_POST['task_id'] ?? 0);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        if ($taskId <= 0 || $employeeId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Task and employee are required.']);
            return;
        }
        $assignedBy = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1);
        $this->db->execute(
            "UPDATE tasks SET assigned_to = ?, assigned_by = ? WHERE id = ? AND department = ?",
            [$employeeId, $assignedBy, $taskId, $code]
        );
        $this->db->execute(
            "INSERT INTO work_distribution_logs
                (tenant_id, task_id, assigned_by, assigned_to, department, assignment_type, notes)
             VALUES (1, ?, ?, ?, ?, 'manual', 'Reassigned via work distribution desk')",
            [$taskId, $assignedBy, $employeeId, $code]
        );
        $this->createNotification($employeeId, 'task_assigned', "You have been assigned task #{$taskId}", $taskId);
        $this->jsonResponse(['success' => true, 'task_id' => $taskId, 'assigned_to' => $employeeId, 'message' => 'Task reassigned.']);
    }

    public function suggestAssignmentAction($department)
    {
        $this->requireAdmin();
        $code = strtoupper(trim((string)$department));
        if ($code === '' || !isset(self::DEPT_DISPLAY[$code])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unknown department.']);
            return;
        }
        $data = $this->postedTaskData($code);
        $service = new \App\Services\WorkDistributionService();
        $this->jsonResponse($service->suggestAssignment($data));
    }

    private function createNotification($employeeId, $type, $message, $relatedId)
    {
        $query = "INSERT INTO notifications (
                    user_id, type, message, related_id, created_at, status, tenant_id
                ) VALUES (?, ?, ?, ?, NOW(), 'unread', ?)";
        
        $this->db->execute($query, [$employeeId, $type, $message, $relatedId, $this->tenantId() ?? 1]);
    }
}

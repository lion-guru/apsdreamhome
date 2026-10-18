<?php

namespace App\Services;

use App\Core\Database\Database;

class WorkDistributionService
{
    private $db;
    private $skillService;
    private $workloadService;
    private $performanceService;
    private $notificationService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->skillService = new \App\Services\EmployeeSkillService();
        $this->workloadService = new \App\Services\EmployeeWorkloadService();
        $this->performanceService = new \App\Services\EmployeePerformanceService();
        // $this->notificationService = new \App\Services\NotificationService(); // If exists
    }

    public function getConfig($department)
    {
        $config = $this->db->fetchOne(
            "SELECT * FROM work_distribution_config WHERE tenant_id = 1 AND department = ?",
            [$department]
        );
        
        if (!$config) {
            $this->db->execute(
                "INSERT IGNORE INTO work_distribution_config (tenant_id, department) VALUES (1, ?)",
                [$department]
            );
            $config = [
                'department' => $department,
                'auto_assign_enabled' => 1,
                'assignment_algorithm' => 'hybrid',
                'skill_weight' => 0.40,
                'workload_weight' => 0.40,
                'performance_weight' => 0.20,
                'max_tasks_per_employee' => 10,
                'rebalance_threshold' => 80.00,
                'rebalance_interval_hours' => 4,
                'notify_on_assignment' => 1,
                'notify_on_rebalance' => 1,
                'ai_assignment_enabled' => 0
            ];
        }
        return $config;
    }

    public function updateConfig($department, $data)
    {
        $allowed = [
            'auto_assign_enabled', 'assignment_algorithm', 'skill_weight',
            'workload_weight', 'performance_weight', 'max_tasks_per_employee',
            'rebalance_threshold', 'rebalance_interval_hours', 'notify_on_assignment',
            'notify_on_rebalance', 'ai_assignment_enabled', 'ai_model'
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
            return ['success' => false, 'message' => 'No valid fields'];
        }
        
        $params[] = $department;
        $this->db->execute(
            "UPDATE work_distribution_config SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE tenant_id = 1 AND department = ?",
            $params
        );
        
        return ['success' => true];
    }

    public function assignTask($taskData)
    {
        $this->validateTaskData($taskData);
        
        $department = $taskData['department'];
        $config = $this->getConfig($department);
        
        if (!$config['auto_assign_enabled'] && empty($taskData['force_assign'])) {
            return ['success' => false, 'message' => 'Auto-assignment disabled for this department'];
        }
        
        $algorithm = $taskData['algorithm'] ?? $config['assignment_algorithm'];
        
        // Get available employees
        $employees = $this->getAvailableEmployees($department, $taskData['required_role'] ?? null);
        
        if (empty($employees)) {
            return ['success' => false, 'message' => 'No available employees in department'];
        }
        
        // Filter by workload config
        $employees = $this->filterByWorkloadConfig($employees);
        
        if (empty($employees)) {
            return ['success' => false, 'message' => 'All employees at capacity'];
        }
        
        // Calculate assignment scores
        $scored = $this->calculateAssignmentScores($employees, $taskData, $config);
        
        // Select best employee
        $assignedTo = $this->selectBestEmployee($scored, $algorithm, $config);
        
        // Create task
        $taskId = $this->createTask($taskData, $assignedTo);
        
        // Log assignment
        $this->logAssignment($taskId, $taskData['assigned_by'], $assignedTo, $department, 'auto', $scored[$assignedTo] ?? []);
        
        // Notify
        if ($config['notify_on_assignment']) {
            $this->notifyEmployee($assignedTo, $taskId, $taskData);
        }
        
        return [
            'success' => true,
            'task_id' => $taskId,
            'assigned_to' => $assignedTo,
            'employee_name' => $this->getEmployeeName($assignedTo),
            'assignment_score' => $scored[$assignedTo]['total_score'] ?? 0,
            'message' => 'Task assigned successfully'
        ];
    }

    public function assignTaskToSpecific($taskData, $employeeId)
    {
        $this->validateTaskData($taskData);
        
        // Verify employee exists and is available
        $emp = $this->db->fetchOne(
            "SELECT id, name FROM users WHERE id = ? AND status = 'active'",
            [$employeeId]
        );
        
        if (!$emp) {
            return ['success' => false, 'message' => 'Employee not found or inactive'];
        }
        
        // Check workload
        $wl = (new \App\Services\EmployeeWorkloadService())->getCurrentWorkload($employeeId);
        if ($wl['is_overloaded']) {
            return ['success' => false, 'message' => 'Employee is overloaded'];
        }
        
        $taskId = $this->createTask($taskData, $employeeId);
        $this->logAssignment($taskId, $taskData['assigned_by'], $employeeId, $taskData['department'], 'manual', []);
        
        $config = $this->getConfig($taskData['department']);
        if ($config['notify_on_assignment']) {
            $this->notifyEmployee($employeeId, $taskId, $taskData);
        }
        
        return ['success' => true, 'task_id' => $taskId, 'assigned_to' => $employeeId, 'message' => 'Task manually assigned'];
    }

    private function getAvailableEmployees($department, $requiredRole = null)
    {
        $query = "SELECT e.id, e.name, e.role, emp.department, emp.designation, emp.incentive_model
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

    private function filterByWorkloadConfig($employees)
    {
        $filtered = [];
        foreach ($employees as $emp) {
            $wl = (new \App\Services\EmployeeWorkloadService())->getCurrentWorkload($emp['id']);
            if (!$wl['is_overloaded']) {
                $filtered[] = $emp;
            }
        }
        return $filtered;
    }

    private function calculateAssignmentScores($employees, $taskData, $config)
    {
        $requiredSkills = $taskData['required_skills'] ?? [];
        $priority = $taskData['priority'] ?? 'medium';
        
        $scored = [];
        foreach ($employees as $emp) {
            $employeeId = $emp['id'];
            
            // Workload factor
            $wl = (new \App\Services\EmployeeWorkloadService())->getCurrentWorkload($employeeId);
            $workloadFactor = 100 - $wl['workload_percentage'];
            
            // Skill match factor
            $skillFactor = 100;
            if (!empty($requiredSkills)) {
                $gap = (new \App\Services\EmployeeSkillService())->getSkillGap($employeeId, $requiredSkills);
                if (!empty($gap)) {
                    $totalGap = array_sum(array_column($gap, 'gap'));
                    $skillFactor = max(0, 100 - ($totalGap * 15));
                }
            }
            
            // Performance factor
            $perf = (new \App\Services\EmployeePerformanceService())->getLatestPerformance($employeeId);
            $performanceFactor = $perf['overall_score'] ?? 50;
            
            // Availability factor
            $availFactor = $this->isEmployeeAvailableToday($emp['id']) ? 100 : 0;
            
            // Weighted score
            $weights = [
                'workload' => $config['workload_weight'] ?? 0.40,
                'skill' => $config['skill_weight'] ?? 0.40,
                'performance' => $config['performance_weight'] ?? 0.20
            ];
            
            $totalScore = ($workloadFactor * $weights['workload']) +
                         ($skillFactor * $weights['skill']) +
                         ($performanceFactor * $weights['performance']);
            
            // Availability gate
            if ($availFactor === 0) {
                $totalScore = 0;
            }
            
            // Priority boost for high priority tasks
            if ($taskData['priority'] === 'high' || $taskData['priority'] === 'urgent') {
                $totalScore += ($performanceFactor - 50) * 0.2;
            }
            
            $scored[$emp['id']] = [
                'employee' => $emp,
                'total_score' => round($totalScore, 2),
                'workload_factor' => round($workloadFactor, 2),
                'skill_factor' => round($skillFactor, 2),
                'performance_factor' => round($performanceFactor, 2),
                'availability' => $availFactor,
                'current_workload' => (new \App\Services\EmployeeWorkloadService())->getCurrentWorkload($emp['id'])
            ];
        }
        
        return $scored;
    }

    private function selectBestEmployee($scored, $algorithm, $config)
    {
        switch ($algorithm) {
            case 'least_loaded':
                uasort($scored, fn($a, $b) => $a['current_workload']['active_tasks'] <=> $b['current_workload']['active_tasks']);
                return array_key_first($scored);
                
            case 'skill_based':
                uasort($scored, fn($a, $b) => $b['skill_factor'] <=> $a['skill_factor']);
                return array_key_first($scored);
                
            case 'performance_based':
                uasort($scored, fn($a, $b) => $b['performance_factor'] <=> $a['performance_factor']);
                return array_key_first($scored);
                
            case 'round_robin':
                // Simple round robin - get last assigned employee
                $lastAssigned = $this->db->fetchOne(
                    "SELECT assigned_to FROM work_distribution_logs 
                     WHERE department = ? ORDER BY created_at DESC LIMIT 1",
                    [$taskData['department']]
                );
                $employees = array_keys($scored);
                if ($lastAssigned && in_array($lastAssigned['assigned_to'], $employees)) {
                    $idx = array_search($lastAssigned['assigned_to'], $employees);
                    return $employees[($idx + 1) % count($employees)];
                }
                return array_key_first($scored);
                
            case 'hybrid':
            default:
                // Use weighted score
                uasort($scored, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
                return array_key_first($scored);
        }
    }

    public function suggestAssignment($taskData)
    {
        $department = $taskData['department'];
        $config = $this->getConfig($department);
        $employees = $this->getAvailableEmployees($department, $taskData['required_role'] ?? null);
        $employees = $this->filterByWorkloadConfig($employees);
        
        if (empty($employees)) {
            return ['suggestions' => [], 'message' => 'No available employees'];
        }
        
        $scored = $this->calculateAssignmentScores($employees, $taskData, $config);
        
        // Sort by score desc
        uasort($scored, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
        
        $suggestions = [];
        foreach (array_slice($scored, 0, 3, true) as $empId => $score) {
            $suggestions[] = [
                'employee_id' => $empId,
                'name' => $score['employee']['name'],
                'role' => $score['employee']['role'],
                'score' => $score['total_score'],
                'workload' => $score['current_workload']['workload_percentage'],
                'skill_match' => $score['skill_factor'],
                'performance' => $score['performance_factor'],
                'reasons' => $this->generateReasons($score)
            ];
        }
        
        return ['suggestions' => $suggestions];
    }

    private function generateReasons($score)
    {
        $reasons = [];
        if ($score['skill_factor'] > 80) $reasons[] = 'Excellent skill match';
        elseif ($score['skill_factor'] > 50) $reasons[] = 'Good skill match';
        elseif ($score['skill_factor'] < 30) $reasons[] = 'Skill gaps identified';
        
        if ($score['workload_factor'] > 70) $reasons[] = 'Low current workload';
        elseif ($score['workload_factor'] < 30) $reasons[] = 'High current workload';
        
        if ($score['performance_factor'] > 70) $reasons[] = 'High performer';
        elseif ($score['performance_factor'] < 40) $reasons[] = 'Needs performance improvement';
        
        return $reasons;
    }

    private function isEmployeeAvailableToday($employeeId)
    {
        return (new \App\Services\EmployeeWorkloadService())->isEmployeeAvailable($employeeId);
    }

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
            $taskData['deadline'] ?? date('Y-m-d', strtotime('+7 days')),
            $assignedTo,
            $taskData['assigned_by'],
            $this->tenantId() ?? 1
        ];
        
        $this->db->execute($query, $params);
        return $this->db->lastInsertId();
    }

    private function logAssignment($taskId, $assignedBy, $assignedTo, $department, $type, $scoreData)
    {
        $this->db->execute(
            "INSERT INTO work_distribution_logs 
             (tenant_id, task_id, assigned_by, assigned_to, department, assignment_type, 
              assignment_score, workload_before, workload_after, skill_match, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                1, $taskId, $assignedBy, $assignedTo, $department, $type,
                $scoreData['total_score'] ?? null,
                $scoreData['current_workload']['workload_percentage'] ?? null,
                (($scoreData['current_workload']['active_tasks'] + 1) / 
                 ($scoreData['current_workload']['max_tasks'] ?? 10)) * 100 ?? null,
                $scoreData['skill_factor'] ?? null,
                'Auto-assigned via ' . $type
            ]
        );
    }

    private function notifyEmployee($employeeId, $taskId, $taskData)
    {
        $this->db->execute(
            "INSERT INTO notifications (user_id, type, title, message, related_id, created_at, status, tenant_id)
             VALUES (?, 'task_assigned', ?, ?, ?, NOW(), 'unread', 1)",
            [
                $employeeId,
                'New Task Assigned',
                "New task: {$taskData['title']}. Priority: {$taskData['priority']}. Deadline: {$taskData['deadline']}",
                $taskId
            ]
        );
    }

    private function getEmployeeName($employeeId)
    {
        $result = $this->db->fetchOne("SELECT name FROM users WHERE id = ?", [$employeeId]);
        return $result['name'] ?? 'Unknown';
    }

    private function validateTaskData($taskData)
    {
        $required = ['title', 'description', 'department', 'assigned_by', 'deadline'];
        foreach ($required as $field) {
            if (empty($taskData[$field])) {
                throw new \Exception("Required field '{$field}' is missing");
            }
        }
        if (!strtotime($taskData['deadline'])) {
            throw new \Exception("Invalid deadline format");
        }
    }

    public function rebalanceWorkloads($department)
    {
        $config = $this->getConfig($department);
        $threshold = $config['rebalance_threshold'] ?? 80;
        
        return (new \App\Services\EmployeeWorkloadService())->rebalanceWorkloads($department);
    }

    public function getAssignmentHistory($department = null, $limit = 50)
    {
        $query = "SELECT wdl.*, u1.name as assigned_by_name, u2.name as assigned_to_name
                  FROM work_distribution_logs wdl
                  JOIN users u1 ON u1.id = wdl.assigned_by
                  JOIN users u2 ON u2.id = wdl.assigned_to
                  WHERE wdl.tenant_id = 1";
        $params = [];
        
        if ($department) {
            $query .= " AND wdl.department = ?";
            $params[] = $department;
        }
        
        $query .= " ORDER BY wdl.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($query, $params);
    }

    public function getAssignmentAnalytics($department = null, $days = 30)
    {
        $query = "SELECT 
                    assignment_type,
                    COUNT(*) as count,
                    AVG(assignment_score) as avg_score,
                    AVG(workload_before) as avg_workload_before,
                    AVG(workload_after) as avg_workload_after,
                    AVG(skill_match) as avg_skill_match
                  FROM work_distribution_logs
                  WHERE tenant_id = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $params = [$days];
        
        if ($department) {
            $query .= " AND department = ?";
            $params[] = $department;
        }
        
        $query .= " GROUP BY assignment_type";
        
        return $this->db->fetchAll($query, $params);
    }

    private function tenantId()
    {
        return $_SESSION['tenant_id'] ?? 1;
    }
}
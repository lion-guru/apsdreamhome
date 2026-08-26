<?php

namespace App\Http\Controllers\Admin;

use App\Services\Scheduler\TaskSchedulerService;

/**
 * Admin Scheduler Controller
 * Manage scheduled tasks from admin panel
 */
class AdminSchedulerController extends AdminController
{
    private $schedulerService;
    
    public function __construct()
    {
        parent::__construct();
        $this->schedulerService = new TaskSchedulerService();
    }
    
    /**
     * Task scheduler dashboard
     */
    public function index(): void
    {
        $tasks = $this->schedulerService->getTasks(false);
        $health = $this->schedulerService->getHealth();
        
        $this->render('admin/scheduler/index', [
            'tasks' => $tasks,
            'health' => $health,
            'title' => 'Task Scheduler'
        ]);
    }
    
    /**
     * View task details and history
     */
    public function taskDetails(int $taskId): void
    {
        $db = \App\Core\Database\Database::getInstance();
        
        // Get task
        $sql = "SELECT * FROM scheduled_tasks WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$task) {
            $_SESSION['error'] = 'Task not found';
            redirect('/admin/scheduler');
            exit;
        }
        
        // Get execution history
        $history = $this->schedulerService->getExecutionHistory($taskId, 50);
        
        // Get stats
        $stats = $this->schedulerService->getTaskStats($taskId);
        
        $this->render('admin/scheduler/task_details', [
            'task' => $task,
            'history' => $history,
            'stats' => $stats,
            'title' => 'Task: ' . ($task['name'] ?? 'Unknown')
        ]);
    }
    
    /**
     * Create new task
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $name = $_POST['name'] ?? '';
            $command = $_POST['command'] ?? '';
            $schedule = $_POST['schedule'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($name) || empty($command) || empty($schedule)) {
                $_SESSION['error'] = 'Name, command and schedule are required';
                redirect('/admin/scheduler/create');
                exit;
            }
            
            $result = $this->schedulerService->createTask($name, $command, $schedule, [
                'description' => $description,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'timeout' => (int) ($_POST['timeout'] ?? 300)
            ]);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Task created successfully';
                redirect('/admin/scheduler');
            } else {
                $_SESSION['error'] = $result['error'];
                redirect('/admin/scheduler/create');
            }
            exit;
        }
        
        $this->render('admin/scheduler/create', [
            'title' => 'Create Scheduled Task'
        ]);
    }
    
    /**
     * Edit task
     */
    public function edit(int $taskId): void
    {
        $db = \App\Core\Database\Database::getInstance();
        
        $sql = "SELECT * FROM scheduled_tasks WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$task) {
            $_SESSION['error'] = 'Task not found';
            redirect('/admin/scheduler');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $updateData = [
                'name' => $_POST['name'] ?? $task['name'],
                'description' => $_POST['description'] ?? $task['description'],
                'command' => $_POST['command'] ?? $task['command'],
                'schedule' => $_POST['schedule'] ?? $task['schedule'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'timeout_seconds' => (int) ($_POST['timeout'] ?? $task['timeout_seconds'])
            ];
            
            $result = $this->schedulerService->updateTask($taskId, $updateData);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Task updated successfully';
                redirect('/admin/scheduler');
            } else {
                $_SESSION['error'] = $result['error'];
                redirect('/admin/scheduler/edit/' . $taskId);
            }
            exit;
        }
        
        $this->render('admin/scheduler/edit', [
            'task' => $task,
            'title' => 'Edit Task'
        ]);
    }
    
    /**
     * Delete task
     */
    public function delete(int $taskId): void
    {
        $this->schedulerService->deleteTask($taskId);
        $_SESSION['success'] = 'Task deleted successfully';
        redirect('/admin/scheduler');
        exit;
    }
    
    /**
     * Run task manually
     */
    public function runTask(int $taskId): void
    {
        $result = $this->schedulerService->executeTask($taskId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Task executed successfully. Time: ' . ($result['execution_time'] ?? 'N/A') . 's';
        } else {
            $_SESSION['error'] = 'Task execution failed: ' . ($result['error'] ?? 'Unknown error');
        }
        
        redirect('/admin/scheduler/task/' . $taskId);
        exit;
    }
    
    /**
     * View execution logs
     */
    public function logs(): void
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 100;
        
        $db = \App\Core\Database\Database::getInstance();
        
        $sql = "SELECT tel.*, st.name AS task_name
            FROM task_execution_logs tel
            LEFT JOIN scheduled_tasks st ON tel.task_id = st.id
            ORDER BY tel.started_at DESC
            LIMIT ? OFFSET ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$perPage, ($page - 1) * $perPage]);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get stats
        $statsSql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM task_execution_logs
            WHERE started_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stats = $db->query($statsSql)->fetch(\PDO::FETCH_ASSOC);
        
        $this->render('admin/scheduler/logs', [
            'logs' => $logs,
            'stats' => $stats,
            'page' => $page,
            'title' => 'Execution Logs'
        ]);
    }
    
    /**
     * Scheduler health check
     */
    public function health(): void
    {
        $health = $this->schedulerService->getHealth();
        
        // Get recent executions
        $db = \App\Core\Database\Database::getInstance();
        $sql = "SELECT 
            DATE_FORMAT(started_at, '%H:%i') as time,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success
            FROM task_execution_logs
            WHERE started_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY DATE_FORMAT(started_at, '%H')
            ORDER BY time";
        
        $hourlyStats = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->render('admin/scheduler/health', [
            'health' => $health,
            'hourly_stats' => $hourlyStats,
            'title' => 'Scheduler Health'
        ]);
    }
    
    /**
     * Cleanup old logs
     */
    public function cleanup(): void
    {
        $days = (int) ($_POST['days'] ?? 30);
        $deleted = $this->schedulerService->cleanupLogs($days);
        
        $_SESSION['success'] = "Cleaned up $deleted old log entries";
        redirect('/admin/scheduler/logs');
        exit;
    }
}

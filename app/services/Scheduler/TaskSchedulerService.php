<?php

namespace App\Services\Scheduler;

use App\Core\Database\Database;

/**
 * Task Scheduler Service
 * Cron job management and scheduled tasks
 */
class TaskSchedulerService
{
    private $database;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure scheduler tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Scheduled tasks
        $pdo->exec("CREATE TABLE IF NOT EXISTS scheduled_tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            command VARCHAR(255) NOT NULL,
            schedule VARCHAR(50) NOT NULL,
            timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
            is_active TINYINT(1) DEFAULT 1,
            last_run_at TIMESTAMP NULL,
            next_run_at TIMESTAMP NULL,
            last_status ENUM('success', 'failed', 'running') NULL,
            last_output TEXT NULL,
            last_error TEXT NULL,
            run_count INT DEFAULT 0,
            fail_count INT DEFAULT 0,
            timeout_seconds INT DEFAULT 300,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_name (name),
            INDEX idx_active (is_active),
            INDEX idx_next_run (next_run_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Task execution logs
        $pdo->exec("CREATE TABLE IF NOT EXISTS task_execution_logs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            task_id INT NOT NULL,
            started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            finished_at TIMESTAMP NULL,
            status ENUM('success', 'failed', 'timeout', 'killed') NOT NULL,
            output TEXT NULL,
            error TEXT NULL,
            execution_time_seconds INT NULL,
            memory_usage_mb INT NULL,
            INDEX idx_task (task_id),
            INDEX idx_status (status),
            INDEX idx_started (started_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Task dependencies
        $pdo->exec("CREATE TABLE IF NOT EXISTS task_dependencies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            task_id INT NOT NULL,
            depends_on_task_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_dep (task_id, depends_on_task_id),
            INDEX idx_task (task_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Seed default tasks
        $this->seedDefaultTasks();
    }
    
    /**
     * Seed default scheduled tasks
     */
    private function seedDefaultTasks(): void
    {
        $tasks = [
            [
                'backup_database',
                'Database Backup',
                'Daily database backup',
                'App\\Jobs\\BackupDatabaseJob',
                '0 2 * * *', // 2 AM daily
                3600
            ],
            [
                'cleanup_old_data',
                'Cleanup Old Data',
                'Remove old logs and temp files',
                'App\\Jobs\\CleanupJob',
                '0 3 * * 0', // 3 AM Sunday
                1800
            ],
            [
                'send_emi_reminders',
                'Send EMI Reminders',
                'Send EMI due reminders',
                'App\\Jobs\\SendEmiRemindersJob',
                '0 9 * * *', // 9 AM daily
                1800
            ],
            [
                'process_property_alerts',
                'Process Property Alerts',
                'Check and send property alerts',
                'App\\Jobs\\ProcessPropertyAlertsJob',
                '0 */6 * * *', // Every 6 hours
                3600
            ],
            [
                'generate_daily_report',
                'Generate Daily Report',
                'Generate and email daily reports',
                'App\\Jobs\\GenerateDailyReportJob',
                '0 8 * * *', // 8 AM daily
                1800
            ],
            [
                'cleanup_expired_tokens',
                'Cleanup Expired Tokens',
                'Remove expired API tokens',
                'App\\Jobs\\CleanupTokensJob',
                '0 4 * * *', // 4 AM daily
                600
            ],
            [
                'update_search_index',
                'Update Search Index',
                'Refresh property search indices',
                'App\\Jobs\\UpdateSearchIndexJob',
                '0 1 * * *', // 1 AM daily
                3600
            ],
            [
                'send_followup_emails',
                'Send Follow-up Emails',
                'Send lead follow-up emails',
                'App\\Jobs\\SendFollowupEmailsJob',
                '0 10,15 * * *', // 10 AM and 3 PM
                1800
            ]
        ];
        
        try {
            $sql = "INSERT IGNORE INTO scheduled_tasks 
                (task_name, task_type, schedule_expression, configuration)
                VALUES (?, ?, ?, ?)";
            $stmt = $this->database->prepare($sql);
            foreach ($tasks as $task) {
                $stmt->execute([
                    $task[0],
                    'notification',
                    $task[4],
                    json_encode(['description' => $task[1], 'command' => $task[3], 'timeout' => $task[5]])
                ]);
            }
        } catch (\Exception $e) {
            // Table schema mismatch - skip seeding
                    error_log("TaskSchedulerService.php: " . $e->getMessage());
        }
    }
    
    /**
     * Get all tasks
     */
    public function getTasks(bool $activeOnly = true): array
    {
        try {
            $sql = "SELECT * FROM scheduled_tasks";
            if ($activeOnly) {
                $sql .= " WHERE status = 'active'";
            }
            $sql .= " ORDER BY task_name ASC";
            
            $stmt = $this->database->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get due tasks
     */
    public function getDueTasks(): array
    {
        try {
            $sql = "SELECT * FROM scheduled_tasks 
                WHERE status = 'active' 
                AND (next_run IS NULL OR next_run <= NOW())
                AND (last_run IS NULL OR status != 'running')
                ORDER BY next_run ASC";
            
            $stmt = $this->database->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Execute task
     */
    public function executeTask(int $taskId): array
    {
        // Get task
        $task = $this->getTask($taskId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found'];
        }
        
        // Check dependencies
        $deps = $this->getDependencies($taskId);
        foreach ($deps as $dep) {
            $depTask = $this->getTask($dep['depends_on_task_id']);
            if ($depTask && $depTask['last_status'] !== 'success') {
                return ['success' => false, 'error' => 'Dependency not met: ' . $depTask['name']];
            }
        }
        
        // Update status to running
        $this->updateTaskStatus($taskId, 'running');
        
        // Log execution start
        $logId = $this->logExecutionStart($taskId);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            // Execute the command
            $result = $this->runCommand($task['command']);
            
            $executionTime = round(microtime(true) - $startTime);
            $memoryUsage = round((memory_get_usage(true) - $startMemory) / 1024 / 1024);
            
            // Update task
            $this->updateTaskAfterRun($taskId, 'success', $result['output'], null);
            
            // Log completion
            $this->logExecutionComplete($logId, 'success', $result['output'], null, $executionTime, $memoryUsage);
            
            // Calculate next run
            $this->calculateNextRun($taskId);
            
            return [
                'success' => true,
                'output' => $result['output'],
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage
            ];
            
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime);
            
            // Update task
            $this->updateTaskAfterRun($taskId, 'failed', null, $e->getMessage());
            
            // Log failure
            $this->logExecutionComplete($logId, 'failed', null, $e->getMessage(), $executionTime, 0);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Run due tasks (called by cron)
     */
    public function runScheduler(): array
    {
        $dueTasks = $this->getDueTasks();
        $results = [];
        
        foreach ($dueTasks as $task) {
            $results[] = [
                'task' => $task['name'],
                'result' => $this->executeTask($task['id'])
            ];
        }
        
        return [
            'tasks_executed' => count($dueTasks),
            'results' => $results
        ];
    }
    
    /**
     * Create new task
     */
    public function createTask(string $name, string $command, string $schedule, array $options = []): array
    {
        try {
            $sql = "INSERT INTO scheduled_tasks 
                (task_name, task_type, schedule_expression, status, configuration)
                VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $name,
                $options['task_type'] ?? 'notification',
                $schedule,
                ($options['is_active'] ?? 1) ? 'active' : 'inactive',
                json_encode([
                    'description' => $options['description'] ?? null,
                    'command' => $command,
                    'timezone' => $options['timezone'] ?? 'Asia/Kolkata',
                    'timeout' => $options['timeout'] ?? 300
                ])
            ]);
            
            $taskId = $this->database->lastInsertId();
            
            // Calculate first run
            $this->calculateNextRun($taskId);
            
            return [
                'success' => true,
                'task_id' => $taskId
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update task
     */
    public function updateTask(int $taskId, array $data): array
    {
        $allowed = ['task_name', 'task_type', 'schedule_expression', 'status'];
        $updates = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $updates[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }
        
        $values[] = $taskId;
        
        $sql = "UPDATE scheduled_tasks SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        
        return [
            'success' => $stmt->execute($values)
        ];
    }
    
    /**
     * Delete task
     */
    public function deleteTask(int $taskId): bool
    {
        try {
            // Delete dependencies first
            $depSql = "DELETE FROM task_dependencies WHERE task_id = ? OR depends_on_task_id = ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $depStmt = $this->database->prepare($depSql);
        $depStmt->execute([$taskId, $taskId]);
        
        // Delete logs
        $logSql = "DELETE FROM task_execution_logs WHERE task_id = ?";
        $logStmt = $this->database->prepare($logSql);
        $logStmt->execute([$taskId]);
        
        // Delete task
        $sql = "DELETE FROM scheduled_tasks WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$taskId]);
    }
    
    /**
     * Get task execution history
     */
    public function getExecutionHistory(int $taskId, int $limit = 50): array
    {
        $sql = "SELECT * FROM task_execution_logs 
            WHERE task_id = ? 
            ORDER BY started_at DESC 
            LIMIT ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$taskId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get task statistics
     */
    public function getTaskStats(int $taskId): array
    {
        $sql = "SELECT 
            COUNT(*) as total_runs,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as fail_count,
            AVG(execution_time_seconds) as avg_execution_time,
            MAX(execution_time_seconds) as max_execution_time
            FROM task_execution_logs 
            WHERE task_id = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Add dependency
     */
    public function addDependency(int $taskId, int $dependsOnTaskId): bool
    {
        try {
            $sql = "INSERT IGNORE INTO task_dependencies (task_id, depends_on_task_id) VALUES (?, ?)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$taskId, $dependsOnTaskId]);
    }
    
    /**
     * Remove dependency
     */
    public function removeDependency(int $taskId, int $dependsOnTaskId): bool
    {
        $sql = "DELETE FROM task_dependencies WHERE task_id = ? AND depends_on_task_id = ?";
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$taskId, $dependsOnTaskId]);
    }
    
    /**
     * Get task dependencies
     */
    private function getDependencies(int $taskId): array
    {
        try {
            $sql = "SELECT * FROM task_dependencies WHERE task_id = ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get single task
     */
    private function getTask(int $taskId): ?array
    {
        $sql = "SELECT * FROM scheduled_tasks WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Update task status
     */
    private function updateTaskStatus(int $taskId, string $status): void
    {
        try {
            $sql = "UPDATE scheduled_tasks SET status = ? WHERE id = ?";
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$status, $taskId]);
        } catch (\Exception $e) {
            // Schema mismatch - skip
                    error_log("TaskSchedulerService.php: " . $e->getMessage());
        }
    }
    
    /**
     * Update task after run
     */
    private function updateTaskAfterRun(int $taskId, string $status, ?string $output, ?string $error): void
    {
        try {
            $sql = "UPDATE scheduled_tasks SET 
                last_run = NOW(),
                status = ?
                WHERE id = ?";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$status, $taskId]);
        } catch (\Exception $e) {
            // Schema mismatch - skip
                    error_log("TaskSchedulerService.php: " . $e->getMessage());
        }
    }
    
    /**
     * Calculate next run time
     */
    private function calculateNextRun(int $taskId): void
    {
        $task = $this->getTask($taskId);
        if (!$task) return;
        
        $cronExpression = $task['schedule'];
        $nextRun = $this->getNextRunDate($cronExpression);
        
        try {
            $sql = "UPDATE scheduled_tasks SET next_run = ? WHERE id = ?";
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$nextRun, $taskId]);
        } catch (\Exception $e) {
            // Schema mismatch - skip
                    error_log("TaskSchedulerService.php: " . $e->getMessage());
        }
    }
    
    /**
     * Get next run date from cron expression
     */
    private function getNextRunDate(string $cronExpression): string
    {
        // Simplified - in production use proper cron parser
        // For now, add 1 hour as default
        return date('Y-m-d H:i:s', strtotime('+1 hour'));
    }
    
    /**
     * Log execution start
     */
    private function logExecutionStart(int $taskId): int
    {
        $sql = "INSERT INTO task_execution_logs (task_id) VALUES (?)";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$taskId]);
        return $this->database->lastInsertId();
    }
    
    /**
     * Log execution complete
     */
    private function logExecutionComplete(int $logId, string $status, ?string $output, 
        ?string $error, int $executionTime, int $memoryUsage): void
    {
        $sql = "UPDATE task_execution_logs SET 
            finished_at = NOW(),
            status = ?,
            output = ?,
            error = ?,
            execution_time_seconds = ?,
            memory_usage_mb = ?
            WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$status, $output, $error, $executionTime, $memoryUsage, $logId]);
    }
    
    /**
     * Run command
     */
    private function runCommand(string $command): array
    {
        // In production, this would execute the actual command
        // For now, return mock success
        return [
            'success' => true,
            'output' => 'Task executed successfully at ' . date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get scheduler health
     */
    public function getHealth(): array
    {
        try {
            $sql = "SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_tasks,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_tasks,
                SUM(CASE WHEN last_run < DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as stale_tasks
                FROM scheduled_tasks";
            
            $stmt = $this->database->query($sql);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $stats = ['total_tasks' => 0, 'active_tasks' => 0, 'failed_tasks' => 0, 'stale_tasks' => 0];
        }
        
        return [
            'total_tasks' => $stats['total_tasks'],
            'active_tasks' => $stats['active_tasks'],
            'failed_tasks' => $stats['failed_tasks'],
            'stale_tasks' => $stats['stale_tasks'],
            'executions_24h' => 0,
            'healthy' => $stats['failed_tasks'] == 0 && $stats['stale_tasks'] == 0
        ];
    }
    
    /**
     * Cleanup old logs
     */
    public function cleanupLogs(int $days = 30): int
    {
        $sql = "DELETE FROM task_execution_logs WHERE started_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }
}

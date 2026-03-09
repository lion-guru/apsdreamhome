<?php

namespace App\Services\Async;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Async Task Manager Service
 * Handles background job processing with proper MVC patterns
 */
class AsyncTaskManagerService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $workers = [];
    private bool $running = false;

    // Task priorities
    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_CRITICAL = 4;

    // Task statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'max_workers' => 5,
            'max_retries' => 3,
            'retry_delay' => 300, // 5 minutes
            'task_timeout' => 3600, // 1 hour
            'cleanup_interval' => 3600 // 1 hour
        ], $config);
        
        $this->initializeTaskTables();
    }

    /**
     * Create async task
     */
    public function createTask(string $type, array $data, int $priority = self::PRIORITY_NORMAL, array $options = []): array
    {
        try {
            $taskId = $this->createTaskRecord($type, $data, $priority, $options);

            $this->logger->info("Async task created", [
                'task_id' => $taskId,
                'type' => $type,
                'priority' => $priority
            ]);

            return [
                'success' => true,
                'message' => 'Task created successfully',
                'task_id' => $taskId
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to create task", [
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create task: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process pending tasks
     */
    public function processTasks(int $limit = 10): array
    {
        try {
            $processed = 0;
            $completed = 0;
            $failed = 0;
            $errors = [];

            // Get pending tasks
            $sql = "SELECT * FROM async_tasks 
                    WHERE status = ? 
                    ORDER BY priority DESC, created_at ASC 
                    LIMIT ?";
            
            $pendingTasks = $this->db->fetchAll($sql, [self::STATUS_PENDING, $limit]);

            foreach ($pendingTasks as $task) {
                try {
                    $result = $this->processSingleTask($task);
                    
                    if ($result['success']) {
                        $completed++;
                    } else {
                        $failed++;
                        $errors[] = "Task ID {$task['id']}: {$result['message']}";
                    }
                    
                    $processed++;
                    
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Task ID {$task['id']}: {$e->getMessage()}";
                    $processed++;
                }
            }

            return [
                'success' => true,
                'message' => "Processed {$processed} tasks",
                'processed' => $processed,
                'completed' => $completed,
                'failed' => $failed,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to process tasks", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to process tasks: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get task by ID
     */
    public function getTask(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM async_tasks WHERE id = ?";
            $task = $this->db->fetchOne($sql, [$id]);
            
            if ($task) {
                $task['data'] = json_decode($task['data'] ?? '{}', true) ?? [];
                $task['result'] = json_decode($task['result'] ?? '{}', true) ?? [];
                $task['options'] = json_decode($task['options'] ?? '{}', true) ?? [];
            }
            
            return $task;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get task", ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get tasks by filters
     */
    public function getTasks(array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM async_tasks WHERE 1=1";
            $params = [];

            // Add filters
            if (!empty($filters['type'])) {
                $sql .= " AND type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['priority'])) {
                $sql .= " AND priority = ?";
                $params[] = $filters['priority'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND created_at <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $tasks = $this->db->fetchAll($sql, $params);
            
            foreach ($tasks as &$task) {
                $task['data'] = json_decode($task['data'] ?? '{}', true) ?? [];
                $task['result'] = json_decode($task['result'] ?? '{}', true) ?? [];
                $task['options'] = json_decode($task['options'] ?? '{}', true) ?? [];
            }
            
            return $tasks;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get tasks", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Cancel task
     */
    public function cancelTask(int $id): array
    {
        try {
            $sql = "UPDATE async_tasks 
                    SET status = ?, cancelled_at = NOW(), updated_at = NOW() 
                    WHERE id = ? AND status = ?";
            
            $affectedRows = $this->db->execute($sql, [
                self::STATUS_CANCELLED,
                $id,
                self::STATUS_PENDING
            ]);

            if ($affectedRows > 0) {
                $this->logger->info("Task cancelled", ['task_id' => $id]);
                return [
                    'success' => true,
                    'message' => 'Task cancelled successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Task not found or cannot be cancelled'
                ];
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to cancel task", [
                'task_id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to cancel task: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retry failed tasks
     */
    public function retryFailedTasks(int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM async_tasks 
                    WHERE status = ? AND retry_count < ? 
                    AND (last_retry_at IS NULL OR last_retry_at < DATE_SUB(NOW(), INTERVAL ? SECOND)) 
                    ORDER BY created_at ASC 
                    LIMIT ?";
            
            $failedTasks = $this->db->fetchAll($sql, [
                self::STATUS_FAILED,
                $this->config['max_retries'],
                $this->config['retry_delay'],
                $limit
            ]);

            $retried = 0;
            $successCount = 0;
            $failureCount = 0;

            foreach ($failedTasks as $task) {
                try {
                    // Update retry count and status
                    $this->db->execute(
                        "UPDATE async_tasks SET retry_count = retry_count + 1, status = ?, last_retry_at = NOW() WHERE id = ?",
                        [self::STATUS_PENDING, $task['id']]
                    );

                    // Process task again
                    $result = $this->processSingleTask($task);
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failureCount++;
                    }
                    
                    $retried++;
                    
                } catch (\Exception $e) {
                    $failureCount++;
                    $retried++;
                }
            }

            return [
                'success' => true,
                'message' => "Retried {$retried} failed tasks",
                'retried' => $retried,
                'success_count' => $successCount,
                'failure_count' => $failureCount
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to retry tasks", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to retry tasks: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get task statistics
     */
    public function getTaskStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total tasks
            $sql = "SELECT COUNT(*) as total FROM async_tasks";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            $stats['total_tasks'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Tasks by status
            $statusSql = "SELECT status, COUNT(*) as count FROM async_tasks";
            $statusParams = [];
            
            if (!empty($filters['date_from'])) {
                $statusSql .= " WHERE created_at >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            $statusSql .= " GROUP BY status";
            
            $statusStats = $this->db->fetchAll($statusSql, $statusParams);
            $stats['by_status'] = [];
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // Tasks by type
            $typeSql = "SELECT type, COUNT(*) as count FROM async_tasks";
            $typeParams = [];
            
            if (!empty($filters['date_from'])) {
                $typeSql .= " WHERE created_at >= ?";
                $typeParams[] = $filters['date_from'];
            }
            
            $typeSql .= " GROUP BY type ORDER BY count DESC LIMIT 10";
            
            $typeStats = $this->db->fetchAll($typeSql, $typeParams);
            $stats['by_type'] = [];
            foreach ($typeStats as $stat) {
                $stats['by_type'][$stat['type']] = $stat['count'];
            }

            // Performance metrics
            $perfSql = "SELECT 
                        AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration,
                        MAX(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as max_duration,
                        COUNT(*) as total_completed
                    FROM async_tasks 
                    WHERE status = ? AND started_at IS NOT NULL AND completed_at IS NOT NULL";
            
            $perfParams = [];
            
            if (!empty($filters['date_from'])) {
                $perfSql .= " AND created_at >= ?";
                $perfParams[] = $filters['date_from'];
            }
            
            $perfStats = $this->db->fetchOne($perfSql, array_merge([self::STATUS_COMPLETED], $perfParams));
            $stats['performance'] = $perfStats ?? [
                'avg_duration' => 0,
                'max_duration' => 0,
                'total_completed' => 0
            ];

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get task stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clean old completed tasks
     */
    public function cleanOldTasks(int $days = 30): array
    {
        try {
            $sql = "DELETE FROM async_tasks 
                    WHERE status IN (?, ?) 
                    AND (completed_at < DATE_SUB(NOW(), INTERVAL ? DAY) 
                         OR cancelled_at < DATE_SUB(NOW(), INTERVAL ? DAY))";
            
            $deletedRows = $this->db->execute($sql, [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                $days,
                $days
            ]);

            $this->logger->info("Old tasks cleaned", [
                'days' => $days,
                'deleted_rows' => $deletedRows
            ]);

            return [
                'success' => true,
                'message' => "Cleaned tasks older than {$days} days",
                'deleted_rows' => $deletedRows
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to clean old tasks", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to clean old tasks: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeTaskTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS async_tasks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(100) NOT NULL,
                data JSON,
                result JSON,
                options JSON,
                priority INT NOT NULL DEFAULT 2,
                status ENUM('pending', 'running', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
                error_message TEXT,
                retry_count INT DEFAULT 0,
                last_retry_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                started_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                cancelled_at TIMESTAMP NULL,
                INDEX idx_status (status),
                INDEX idx_priority (priority),
                INDEX idx_type (type),
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function createTaskRecord(string $type, array $data, int $priority, array $options): string
    {
        $sql = "INSERT INTO async_tasks (type, data, priority, status, options, created_at) 
                VALUES (?, ?, ?, 'pending', ?, NOW())";
        
        $this->db->execute($sql, [
            $type,
            json_encode($data),
            $priority,
            json_encode($options)
        ]);
        
        return $this->db->lastInsertId();
    }

    private function processSingleTask(array $task): array
    {
        try {
            // Update task status to running
            $this->db->execute(
                "UPDATE async_tasks SET status = ?, started_at = NOW() WHERE id = ?",
                [self::STATUS_RUNNING, $task['id']]
            );

            $startTime = microtime(true);
            $taskData = json_decode($task['data'], true) ?? [];
            $taskOptions = json_decode($task['options'], true) ?? [];

            // Process task based on type
            $result = $this->executeTask($task['type'], $taskData, $taskOptions);

            $executionTime = (microtime(true) - $startTime);

            // Update task with result
            $sql = "UPDATE async_tasks 
                    SET status = ?, result = ?, completed_at = NOW(), updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->execute($sql, [
                $result['success'] ? self::STATUS_COMPLETED : self::STATUS_FAILED,
                json_encode($result),
                $task['id']
            ]);

            $this->logger->info("Task processed", [
                'task_id' => $task['id'],
                'type' => $task['type'],
                'success' => $result['success'],
                'execution_time' => round($executionTime, 2)
            ]);

            return $result;

        } catch (\Exception $e) {
            // Update task with error
            $this->db->execute(
                "UPDATE async_tasks SET status = ?, error_message = ?, updated_at = NOW() WHERE id = ?",
                [self::STATUS_FAILED, $e->getMessage(), $task['id']]
            );

            return [
                'success' => false,
                'message' => 'Task execution failed: ' . $e->getMessage()
            ];
        }
    }

    private function executeTask(string $type, array $data, array $options): array
    {
        switch ($type) {
            case 'send_email':
                return $this->executeSendEmailTask($data, $options);
            
            case 'process_image':
                return $this->executeProcessImageTask($data, $options);
            
            case 'generate_report':
                return $this->executeGenerateReportTask($data, $options);
            
            case 'cleanup_temp':
                return $this->executeCleanupTask($data, $options);
            
            case 'backup_data':
                return $this->executeBackupTask($data, $options);
            
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown task type: ' . $type
                ];
        }
    }

    private function executeSendEmailTask(array $data, array $options): array
    {
        // Mock email sending
        usleep(500000); // 0.5 second delay
        
        return [
            'success' => true,
            'message' => 'Email sent successfully',
            'data' => [
                'to' => $data['to'] ?? 'unknown',
                'subject' => $data['subject'] ?? 'No subject',
                'sent_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    private function executeProcessImageTask(array $data, array $options): array
    {
        // Mock image processing
        usleep(1000000); // 1 second delay
        
        return [
            'success' => true,
            'message' => 'Image processed successfully',
            'data' => [
                'file_path' => $data['file_path'] ?? 'unknown',
                'processed_size' => '800x600',
                'format' => 'jpeg',
                'processed_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    private function executeGenerateReportTask(array $data, array $options): array
    {
        // Mock report generation
        usleep(2000000); // 2 second delay
        
        return [
            'success' => true,
            'message' => 'Report generated successfully',
            'data' => [
                'report_type' => $data['report_type'] ?? 'unknown',
                'file_path' => '/reports/report_' . uniqid() . '.pdf',
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    private function executeCleanupTask(array $data, array $options): array
    {
        // Mock cleanup
        usleep(300000); // 0.3 second delay
        
        return [
            'success' => true,
            'message' => 'Cleanup completed successfully',
            'data' => [
                'files_deleted' => rand(10, 100),
                'space_freed' => rand(1024, 10240) . ' MB',
                'cleaned_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    private function executeBackupTask(array $data, array $options): array
    {
        // Mock backup
        usleep(5000000); // 5 second delay
        
        return [
            'success' => true,
            'message' => 'Backup completed successfully',
            'data' => [
                'backup_type' => $data['backup_type'] ?? 'full',
                'file_path' => '/backups/backup_' . uniqid() . '.zip',
                'size' => rand(100, 1000) . ' MB',
                'backed_up_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
}

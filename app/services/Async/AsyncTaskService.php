<?php

namespace App\Services\Async;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Config;

/**
 * Async Task Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AsyncTaskService
{
    private $database;
    private $logger;
    private $config;
    
    // Task Priorities
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_CRITICAL = 4;

    // Task Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new Logger();
        $this->config = Config::getInstance();
        
        $this->createTaskTables();
    }
    
    /**
     * Create task tables
     */
    private function createTaskTables()
    {
        try {
            // Tasks table
            $sql = "CREATE TABLE IF NOT EXISTS async_tasks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                task_name VARCHAR(255) NOT NULL,
                task_type VARCHAR(100) NOT NULL,
                parameters JSON,
                priority INT DEFAULT 2,
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                started_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                result JSON,
                error_message TEXT,
                retry_count INT DEFAULT 0,
                max_retries INT DEFAULT 3,
                progress_percentage INT DEFAULT 0,
                assigned_worker VARCHAR(100)
            )";
            $this->database->query($sql);

            // Task queue table
            $sql = "CREATE TABLE IF NOT EXISTS task_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                task_id INT,
                queue_name VARCHAR(100) DEFAULT 'default',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (task_id) REFERENCES async_tasks(id) ON DELETE CASCADE
            )";
            $this->database->query($sql);
            
        } catch (\Exception $e) {
            $this->logger->error('Error creating task tables', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create a new task
     */
    public function createTask($taskName, $taskType, array $parameters = [], $priority = self::PRIORITY_NORMAL, $maxRetries = 3)
    {
        try {
            $sql = "INSERT INTO async_tasks (task_name, task_type, parameters, priority, max_retries)
                    VALUES (?, ?, ?, ?, ?)";
            
            $params = [
                $taskName,
                $taskType,
                json_encode($parameters),
                $priority,
                $maxRetries
            ];
            
            $this->database->query($sql, $params);
            $taskId = $this->database->lastInsertId();
            
            // Add to queue
            $this->addToQueue($taskId, 'default');
            
            $this->logger->info('Task created', [
                'task_id' => $taskId,
                'task_name' => $taskName,
                'task_type' => $taskType,
                'priority' => $priority
            ]);
            
            return [
                'success' => true,
                'task_id' => $taskId
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create task', [
                'error' => $e->getMessage(),
                'task_name' => $taskName,
                'task_type' => $taskType
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create task: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Add task to queue
     */
    private function addToQueue($taskId, $queueName = 'default')
    {
        $sql = "INSERT INTO task_queue (task_id, queue_name) VALUES (?, ?)";
        $this->database->query($sql, [$taskId, $queueName]);
    }
    
    /**
     * Get next task to process
     */
    public function getNextTask($workerName = null, $queueName = 'default')
    {
        try {
            $sql = "SELECT t.* FROM async_tasks t
                    JOIN task_queue q ON t.id = q.task_id
                    WHERE t.status = ? AND q.queue_name = ?
                    ORDER BY t.priority DESC, t.created_at ASC
                    LIMIT 1";
            
            $task = $this->database->selectOne($sql, [self::STATUS_PENDING, $queueName]);
            
            if (!$task) {
                return [
                    'success' => false,
                    'message' => 'No tasks available'
                ];
            }
            
            // Mark task as running
            $this->updateTaskStatus($task['id'], self::STATUS_RUNNING, $workerName);
            
            return [
                'success' => true,
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get next task', [
                'error' => $e->getMessage(),
                'worker' => $workerName,
                'queue' => $queueName
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get next task: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update task status
     */
    public function updateTaskStatus($taskId, $status, $assignedWorker = null)
    {
        try {
            $updates = ["status = ?", "updated_at = NOW()"];
            $params = [$status];

            if ($status === self::STATUS_RUNNING) {
                $updates[] = "started_at = NOW()";
                if ($assignedWorker) {
                    $updates[] = "assigned_worker = ?";
                    $params[] = $assignedWorker;
                }
            } elseif ($status === self::STATUS_COMPLETED) {
                $updates[] = "completed_at = NOW()";
            } elseif ($status === self::STATUS_FAILED) {
                $updates[] = "retry_count = retry_count + 1";
            }

            $params[] = $taskId;
            $sql = "UPDATE async_tasks SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $this->database->query($sql, $params);
            
            $this->logger->info('Task status updated', [
                'task_id' => $taskId,
                'status' => $status,
                'worker' => $assignedWorker
            ]);
            
            return [
                'success' => true,
                'message' => 'Task status updated successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update task status', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'status' => $status
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update task status: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update task progress
     */
    public function updateTaskProgress($taskId, $progressPercentage, $result = null)
    {
        try {
            $sql = "UPDATE async_tasks SET progress_percentage = ?, result = ?, updated_at = NOW() WHERE id = ?";
            $resultJson = $result ? json_encode($result) : null;
            
            $this->database->query($sql, [$progressPercentage, $resultJson, $taskId]);
            
            $this->logger->info('Task progress updated', [
                'task_id' => $taskId,
                'progress' => $progressPercentage
            ]);
            
            return [
                'success' => true,
                'message' => 'Task progress updated successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update task progress', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'progress' => $progressPercentage
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update task progress: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mark task as completed with result
     */
    public function completeTask($taskId, $result = null)
    {
        try {
            $resultJson = $result ? json_encode($result) : null;
            $sql = "UPDATE async_tasks SET status = ?, result = ?, completed_at = NOW(), updated_at = NOW() WHERE id = ?";
            
            $this->database->query($sql, [self::STATUS_COMPLETED, $resultJson, $taskId]);
            
            $this->logger->info('Task completed', [
                'task_id' => $taskId,
                'result' => $resultJson
            ]);
            
            return [
                'success' => true,
                'message' => 'Task completed successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to complete task', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to complete task: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mark task as failed
     */
    public function failTask($taskId, $errorMessage)
    {
        try {
            $sql = "UPDATE async_tasks SET status = ?, error_message = ?, updated_at = NOW() WHERE id = ?";
            
            $this->database->query($sql, [self::STATUS_FAILED, $errorMessage, $taskId]);
            
            $this->logger->error('Task failed', [
                'task_id' => $taskId,
                'error' => $errorMessage
            ]);
            
            return [
                'success' => true,
                'message' => 'Task marked as failed'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark task as failed', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to mark task as failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get task status
     */
    public function getTaskStatus($taskId)
    {
        try {
            $sql = "SELECT status, progress_percentage, result, error_message, retry_count, created_at, started_at, completed_at
                    FROM async_tasks WHERE id = ?";
            
            $task = $this->database->selectOne($sql, [$taskId]);
            
            if (!$task) {
                return [
                    'success' => false,
                    'message' => 'Task not found'
                ];
            }
            
            // Decode JSON fields
            if ($task['result']) {
                $task['result'] = json_decode($task['result'], true);
            }
            
            return [
                'success' => true,
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get task status', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get task status: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all tasks with optional filters
     */
    public function getTasks($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM async_tasks WHERE 1=1";
            $params = [];

            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['task_type'])) {
                $sql .= " AND task_type = ?";
                $params[] = $filters['task_type'];
            }

            if (!empty($filters['assigned_worker'])) {
                $sql .= " AND assigned_worker = ?";
                $params[] = $filters['assigned_worker'];
            }

            if (!empty($filters['priority'])) {
                $sql .= " AND priority = ?";
                $params[] = $filters['priority'];
            }

            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $tasks = $this->database->select($sql, $params);
            
            // Decode JSON fields
            foreach ($tasks as &$task) {
                if ($task['parameters']) {
                    $task['parameters'] = json_decode($task['parameters'], true);
                }
                if ($task['result']) {
                    $task['result'] = json_decode($task['result'], true);
                }
            }
            
            return [
                'success' => true,
                'data' => $tasks
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get tasks', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve tasks: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process email sending task
     */
    public function processEmailTask($task)
    {
        try {
            $parameters = json_decode($task['parameters'], true);
            
            if (!$parameters || !isset($parameters['to']) || !isset($parameters['subject'])) {
                return $this->failTask($task['id'], 'Invalid email parameters');
            }
            
            // Update progress
            $this->updateTaskProgress($task['id'], 25, ['status' => 'preparing']);
            
            // Simulate email preparation
            sleep(1);
            
            $this->updateTaskProgress($task['id'], 50, ['status' => 'sending']);
            
            // Simulate email sending
            sleep(2);
            
            $this->updateTaskProgress($task['id'], 75, ['status' => 'finalizing']);
            
            // Simulate finalization
            sleep(1);
            
            $result = [
                'success' => true,
                'message_id' => 'email_' . $task['id'] . '_' . time(),
                'sent_at' => date('Y-m-d H:i:s'),
                'recipient' => $parameters['to'],
                'subject' => $parameters['subject']
            ];
            
            return $this->completeTask($task['id'], $result);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to process email task', [
                'error' => $e->getMessage(),
                'task_id' => $task['id']
            ]);
            
            return $this->failTask($task['id'], $e->getMessage());
        }
    }
    
    /**
     * Process image processing task
     */
    public function processImageTask($task)
    {
        try {
            $parameters = json_decode($task['parameters'], true);
            
            if (!$parameters || !isset($parameters['image_path'])) {
                return $this->failTask($task['id'], 'Invalid image parameters');
            }
            
            // Update progress
            $this->updateTaskProgress($task['id'], 20, ['status' => 'loading image']);
            
            // Simulate image loading
            sleep(1);
            
            $this->updateTaskProgress($task['id'], 40, ['status' => 'processing']);
            
            // Simulate image processing
            sleep(3);
            
            $this->updateTaskProgress($task['id'], 70, ['status' => 'creating thumbnails']);
            
            // Simulate thumbnail creation
            sleep(2);
            
            $this->updateTaskProgress($task['id'], 90, ['status' => 'optimizing']);
            
            // Simulate optimization
            sleep(1);
            
            $result = [
                'success' => true,
                'images_processed' => 1,
                'thumbnails_created' => 3,
                'optimized_size' => '75%',
                'output_path' => '/processed/' . basename($parameters['image_path']),
                'processing_time' => '7 seconds'
            ];
            
            return $this->completeTask($task['id'], $result);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to process image task', [
                'error' => $e->getMessage(),
                'task_id' => $task['id']
            ]);
            
            return $this->failTask($task['id'], $e->getMessage());
        }
    }
    
    /**
     * Process report generation task
     */
    public function processReportTask($task)
    {
        try {
            $parameters = json_decode($task['parameters'], true);
            
            if (!$parameters || !isset($parameters['report_type'])) {
                return $this->failTask($task['id'], 'Invalid report parameters');
            }
            
            // Update progress
            $this->updateTaskProgress($task['id'], 10, ['status' => 'collecting data']);
            
            // Simulate data collection
            sleep(2);
            
            $this->updateTaskProgress($task['id'], 40, ['status' => 'generating report']);
            
            // Simulate report generation
            sleep(4);
            
            $this->updateTaskProgress($task['id'], 80, ['status' => 'formatting']);
            
            // Simulate formatting
            sleep(2);
            
            $this->updateTaskProgress($task['id'], 95, ['status' => 'saving']);
            
            // Simulate saving
            sleep(1);
            
            $result = [
                'success' => true,
                'report_type' => $parameters['report_type'],
                'file_path' => '/reports/' . $parameters['report_type'] . '_' . date('Y-m-d_H-i-s') . '.pdf',
                'file_size' => '2.5MB',
                'records_processed' => 1250,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->completeTask($task['id'], $result);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to process report task', [
                'error' => $e->getMessage(),
                'task_id' => $task['id']
            ]);
            
            return $this->failTask($task['id'], $e->getMessage());
        }
    }
    
    /**
     * Get task statistics
     */
    public function getTaskStats()
    {
        try {
            $stats = [];
            
            // Total tasks by status
            $stats['by_status'] = $this->database->select(
                "SELECT status, COUNT(*) as count FROM async_tasks GROUP BY status"
            );
            
            // Total tasks by type
            $stats['by_type'] = $this->database->select(
                "SELECT task_type, COUNT(*) as count FROM async_tasks GROUP BY task_type"
            );
            
            // Total tasks by priority
            $stats['by_priority'] = $this->database->select(
                "SELECT priority, COUNT(*) as count FROM async_tasks GROUP BY priority"
            );
            
            // Recent tasks
            $stats['recent'] = $this->database->select(
                "SELECT * FROM async_tasks ORDER BY created_at DESC LIMIT 10"
            );
            
            // Performance metrics
            $stats['performance'] = $this->database->selectOne(
                "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_tasks,
                    AVG(CASE WHEN completed_at IS NOT NULL AND started_at IS NOT NULL 
                        THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) ELSE NULL END) as avg_completion_time
                 FROM async_tasks"
            );
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get task statistics', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve task statistics: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Clean up old completed tasks
     */
    public function cleanupOldTasks($daysOld = 30)
    {
        try {
            $sql = "DELETE FROM async_tasks
                    WHERE status = 'completed'
                    AND completed_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            
            $this->database->query($sql, [$daysOld]);
            
            $deletedCount = $this->database->rowCount();
            
            $this->logger->info('Old tasks cleaned up', [
                'days_old' => $daysOld,
                'deleted_count' => $deletedCount
            ]);
            
            return [
                'success' => true,
                'message' => "Cleaned up {$deletedCount} old tasks",
                'deleted_count' => $deletedCount
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup old tasks', [
                'error' => $e->getMessage(),
                'days_old' => $daysOld
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to cleanup old tasks: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cancel task
     */
    public function cancelTask($taskId)
    {
        try {
            $sql = "UPDATE async_tasks SET status = ?, updated_at = NOW() WHERE id = ? AND status IN (?, ?)";
            
            $this->database->query($sql, [self::STATUS_CANCELLED, $taskId, self::STATUS_PENDING, self::STATUS_RUNNING]);
            
            $this->logger->info('Task cancelled', [
                'task_id' => $taskId
            ]);
            
            return [
                'success' => true,
                'message' => 'Task cancelled successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to cancel task', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to cancel task: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Retry failed task
     */
    public function retryTask($taskId)
    {
        try {
            $sql = "UPDATE async_tasks SET status = ?, error_message = NULL, updated_at = NOW() 
                    WHERE id = ? AND status = ? AND retry_count < max_retries";
            
            $this->database->query($sql, [self::STATUS_PENDING, $taskId, self::STATUS_FAILED]);
            
            $this->logger->info('Task retry initiated', [
                'task_id' => $taskId
            ]);
            
            return [
                'success' => true,
                'message' => 'Task retry initiated successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to retry task', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retry task: ' . $e->getMessage()
            ];
        }
    }
}
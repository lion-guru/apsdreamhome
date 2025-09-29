<?php
/**
 * Async Task Manager
 * Handles background job processing and task management
 */

class AsyncTaskManager {
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

    private $conn;
    private $logger;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->createTaskTables();
    }

    /**
     * Create task tables
     */
    private function createTaskTables() {
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

        $this->conn->query($sql);

        // Task queue table
        $sql = "CREATE TABLE IF NOT EXISTS task_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            task_id INT,
            queue_name VARCHAR(100) DEFAULT 'default',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (task_id) REFERENCES async_tasks(id) ON DELETE CASCADE
        )";

        $this->conn->query($sql);
    }

    /**
     * Create a new task
     */
    public function createTask($taskName, $taskType, $parameters = [], $priority = self::PRIORITY_NORMAL, $maxRetries = 3) {
        $parametersJson = json_encode($parameters);

        $sql = "INSERT INTO async_tasks (task_name, task_type, parameters, priority, max_retries)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssii", $taskName, $taskType, $parametersJson, $priority, $maxRetries);

        $result = $stmt->execute();
        $taskId = $stmt->insert_id;
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Task created: $taskName (ID: $taskId)", 'info', 'task');
        }

        return $result ? $taskId : false;
    }

    /**
     * Get next task to process
     */
    public function getNextTask($workerName = null) {
        // Get highest priority pending task
        $sql = "SELECT t.* FROM async_tasks t
                LEFT JOIN task_queue q ON t.id = q.task_id
                WHERE t.status = 'pending'
                ORDER BY t.priority DESC, t.created_at ASC
                LIMIT 1";

        $result = $this->conn->query($sql);

        if ($result->num_rows === 0) {
            return null;
        }

        $task = $result->fetch_assoc();

        // Mark task as running
        $this->updateTaskStatus($task['id'], self::STATUS_RUNNING, $workerName);

        return $task;
    }

    /**
     * Update task status
     */
    public function updateTaskStatus($taskId, $status, $assignedWorker = null) {
        $updates = ["status = '$status'", "updated_at = NOW()"];

        if ($status === self::STATUS_RUNNING) {
            $updates[] = "started_at = NOW()";
            if ($assignedWorker) {
                $updates[] = "assigned_worker = '$assignedWorker'";
            }
        } elseif ($status === self::STATUS_COMPLETED) {
            $updates[] = "completed_at = NOW()";
        } elseif ($status === self::STATUS_FAILED) {
            $updates[] = "retry_count = retry_count + 1";
        }

        $sql = "UPDATE async_tasks SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $taskId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Task $taskId status updated to: $status", 'info', 'task');
        }

        return $result;
    }

    /**
     * Update task progress
     */
    public function updateTaskProgress($taskId, $progressPercentage, $result = null) {
        $sql = "UPDATE async_tasks SET progress_percentage = ?, result = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $resultJson = $result ? json_encode($result) : null;
        $stmt->bind_param("isi", $progressPercentage, $resultJson, $taskId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Mark task as completed with result
     */
    public function completeTask($taskId, $result = null) {
        $resultJson = $result ? json_encode($result) : null;
        $sql = "UPDATE async_tasks SET status = ?, result = ?, completed_at = NOW(), updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $status = self::STATUS_COMPLETED;
        $stmt->bind_param("ssi", $status, $resultJson, $taskId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Task $taskId completed successfully", 'info', 'task');
        }

        return $result;
    }

    /**
     * Mark task as failed
     */
    public function failTask($taskId, $errorMessage) {
        $sql = "UPDATE async_tasks SET status = ?, error_message = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $status = self::STATUS_FAILED;
        $stmt->bind_param("ssi", $status, $errorMessage, $taskId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("Task $taskId failed: $errorMessage", 'error', 'task');
        }

        return $result;
    }

    /**
     * Get task status
     */
    public function getTaskStatus($taskId) {
        $sql = "SELECT status, progress_percentage, result, error_message, retry_count
                FROM async_tasks WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $result = $stmt->get_result();
        $status = $result->fetch_assoc();
        $stmt->close();

        return $status;
    }

    /**
     * Get all tasks with optional filters
     */
    public function getTasks($filters = []) {
        $sql = "SELECT * FROM async_tasks WHERE 1=1";
        $params = [];
        $types = "";

        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (isset($filters['task_type'])) {
            $sql .= " AND task_type = ?";
            $params[] = $filters['task_type'];
            $types .= "s";
        }

        $sql .= " ORDER BY created_at DESC";

        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        $stmt->close();

        return $tasks;
    }

    /**
     * Process email sending task
     */
    public function processEmailTask($task) {
        $parameters = json_decode($task['parameters'], true);

        // Here you would implement actual email sending
        // For now, we'll simulate the process

        $this->updateTaskProgress($task['id'], 50, ['status' => 'processing']);

        // Simulate email sending delay
        sleep(2);

        $result = [
            'success' => true,
            'message_id' => 'email_' . $task['id'],
            'sent_at' => date('Y-m-d H:i:s')
        ];

        $this->completeTask($task['id'], $result);
        return true;
    }

    /**
     * Process property image processing task
     */
    public function processImageTask($task) {
        $parameters = json_decode($task['parameters'], true);

        $this->updateTaskProgress($task['id'], 25, ['status' => 'downloading']);

        // Simulate image processing
        sleep(3);

        $this->updateTaskProgress($task['id'], 75, ['status' => 'processing']);

        sleep(2);

        $result = [
            'success' => true,
            'images_processed' => 5,
            'thumbnails_created' => 5,
            'watermarks_added' => true
        ];

        $this->completeTask($task['id'], $result);
        return true;
    }

    /**
     * Clean up old completed tasks
     */
    public function cleanupOldTasks($daysOld = 30) {
        $sql = "DELETE FROM async_tasks
                WHERE status = 'completed'
                AND completed_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $daysOld);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}
?>

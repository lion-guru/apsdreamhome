<?php
/**
 * Advanced Asynchronous Task and Background Job Processing System
 * Provides robust management of background tasks, job queuing, and distributed processing
 */

// require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/config_manager.php';
require_once __DIR__ . '/event_monitor.php';

class AsyncTaskManager {
    // Task Priorities
    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_CRITICAL = 4;

    // Task Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    // Execution Modes
    public const MODE_SYNC = 'sync';
    public const MODE_ASYNC = 'async';
    public const MODE_DISTRIBUTED = 'distributed';

    // Task Storage and Management
    private $taskQueue = [];
    private $completedTasks = [];
    private $failedTasks = [];

    // System Dependencies
    private $logger;
    private $config;
    private $eventMonitor;

    // Configuration Parameters
    private $maxConcurrentTasks = 5;
    private $taskTimeout = 300; // 5 minutes
    private $retryAttempts = 3;

    // Distributed Processing
    private $workerPools = [];

    public function __construct() {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();
        $this->eventMonitor = new EventMonitor();

        // Load configuration
        $this->loadConfiguration();
    }

    /**
     * Load task processing configuration
     */
    private function loadConfiguration() {
        $this->maxConcurrentTasks = $this->config->get(
            'MAX_CONCURRENT_TASKS', 
            5
        );
        $this->taskTimeout = $this->config->get(
            'TASK_TIMEOUT_SECONDS', 
            300
        );
        $this->retryAttempts = $this->config->get(
            'TASK_RETRY_ATTEMPTS', 
            3
        );
    }

    /**
     * Create a new asynchronous task
     * 
     * @param callable $task Task to execute
     * @param array $params Task parameters
     * @param int $priority Task priority
     * @param string $mode Execution mode
     * @return string Task unique identifier
     */
    public function createTask(
        callable $task, 
        array $params = [], 
        $priority = self::PRIORITY_NORMAL,
        $mode = self::MODE_ASYNC
    ) {
        $taskId = $this->generateTaskId();

        $taskRecord = [
            'id' => $taskId,
            'task' => $task,
            'params' => $params,
            'priority' => $priority,
            'status' => self::STATUS_PENDING,
            'created_at' => time(),
            'attempts' => 0,
            'mode' => $mode
        ];

        // Log task creation
        $this->eventMonitor->logEvent('ASYNC_TASK_CREATED', [
            'task_id' => $taskId,
            'priority' => $priority,
            'mode' => $mode
        ]);

        // Add to task queue
        $this->taskQueue[] = $taskRecord;

        // Sort queue by priority
        $this->sortTaskQueue();

        return $taskId;
    }

    /**
     * Sort task queue by priority
     */
    private function sortTaskQueue() {
        usort($this->taskQueue, function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Execute pending tasks
     */
    public function processTasks() {
        $runningTasks = 0;

        foreach ($this->taskQueue as &$task) {
            // Check concurrent task limit
            if ($runningTasks >= $this->maxConcurrentTasks) {
                break;
            }

            // Skip non-pending tasks
            if ($task['status'] !== self::STATUS_PENDING) {
                continue;
            }

            // Check retry attempts
            if ($task['attempts'] >= $this->retryAttempts) {
                $task['status'] = self::STATUS_FAILED;
                $this->failedTasks[] = $task;
                continue;
            }

            // Execute task based on mode
            try {
                $task['status'] = self::STATUS_RUNNING;
                $task['attempts']++;

                $result = $this->executeTask($task);

                $task['status'] = self::STATUS_COMPLETED;
                $task['result'] = $result;
                $this->completedTasks[] = $task;

                $runningTasks++;
            } catch (\Exception $e) {
                $task['status'] = self::STATUS_FAILED;
                $task['error'] = $e->getMessage();
                $this->failedTasks[] = $task;

                // Log task failure
                $this->logger->error('Async Task Failed', [
                    'task_id' => $task['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Remove completed and failed tasks
        $this->cleanupTaskQueue();
    }

    /**
     * Execute a single task
     * 
     * @param array &$task Task record
     * @return mixed Task execution result
     */
    private function executeTask(&$task) {
        // Set execution timeout
        $startTime = microtime(true);
        
        // Execute task with timeout
        $result = call_user_func_array(
            $task['task'], 
            $task['params']
        );

        // Check execution time
        $executionTime = microtime(true) - $startTime;
        if ($executionTime > $this->taskTimeout) {
            throw new \RuntimeException("Task exceeded timeout");
        }

        return $result;
    }

    /**
     * Clean up completed and failed tasks
     */
    private function cleanupTaskQueue() {
        $this->taskQueue = array_filter($this->taskQueue, function($task) {
            return in_array($task['status'], [
                self::STATUS_PENDING, 
                self::STATUS_RUNNING
            ]);
        });
    }

    /**
     * Add a worker to distributed processing pool
     * 
     * @param callable $worker Worker function
     */
    public function addWorker(callable $worker) {
        $this->workerPools[] = $worker;
    }

    /**
     * Distributed task processing
     */
    public function processDistributedTasks() {
        if (empty($this->workerPools)) {
            throw new \RuntimeException("No workers available");
        }

        foreach ($this->taskQueue as &$task) {
            if ($task['mode'] === self::MODE_DISTRIBUTED) {
                // Select worker from pool
                $worker = $this->selectWorker();
                
                try {
                    $result = call_user_func_array(
                        $worker, 
                        [$task['task'], $task['params']]
                    );
                    $task['status'] = self::STATUS_COMPLETED;
                    $task['result'] = $result;
                } catch (\Exception $e) {
                    $task['status'] = self::STATUS_FAILED;
                    $task['error'] = $e->getMessage();
                }
            }
        }
    }

    /**
     * Select a worker from the pool
     * 
     * @return callable Selected worker
     */
    private function selectWorker() {
        // Simple round-robin worker selection
        static $currentWorker = 0;
        $worker = $this->workerPools[$currentWorker];
        $currentWorker = ($currentWorker + 1) % count($this->workerPools);
        return $worker;
    }

    /**
     * Generate unique task identifier
     * 
     * @return string Unique task ID
     */
    private function generateTaskId() {
        return uniqid('task_', true);
    }

    /**
     * Get task status
     * 
     * @param string $taskId Task identifier
     * @return array Task status information
     */
    public function getTaskStatus($taskId) {
        foreach ($this->taskQueue as $task) {
            if ($task['id'] === $taskId) {
                return $task;
            }
        }

        // Check completed and failed tasks
        $allTasks = array_merge(
            $this->completedTasks, 
            $this->failedTasks
        );

        foreach ($allTasks as $task) {
            if ($task['id'] === $taskId) {
                return $task;
            }
        }

        throw new \RuntimeException("Task not found");
    }

    /**
     * Generate task processing report
     * 
     * @return array Task processing statistics
     */
    public function getTaskReport() {
        return [
            'total_tasks' => count($this->taskQueue),
            'pending_tasks' => count(array_filter(
                $this->taskQueue, 
                fn($task) => $task['status'] === self::STATUS_PENDING
            )),
            'running_tasks' => count(array_filter(
                $this->taskQueue, 
                fn($task) => $task['status'] === self::STATUS_RUNNING
            )),
            'completed_tasks' => count($this->completedTasks),
            'failed_tasks' => count($this->failedTasks)
        ];
    }

    /**
     * Demonstrate async task processing capabilities
     */
    public function demonstrateAsyncTasks() {
        // Simulate various tasks
        $this->createTask(
            function($x, $y) {
                usleep(100000);  // Simulate delay
                return $x * $y;
            }, 
            [5, 7], 
            self::PRIORITY_NORMAL
        );

        $this->createTask(
            function($data) {
                // Simulate data processing
                return array_map(fn($x) => $x * 2, $data);
            }, 
            [[1, 2, 3, 4]], 
            self::PRIORITY_HIGH
        );

        // Process tasks
        $this->processTasks();

        // Generate and display report
        $report = $this->getTaskReport();
        print_r($report);
    }
}

// Global helper function for easy task management
function async_tasks() {
    return new AsyncTaskManager();
}

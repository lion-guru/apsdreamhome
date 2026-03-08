<?php

namespace App\Services\Async;

/**
 * Async Task Manager Proxy - APS Dream Home
 * Proxies legacy AsyncTaskManager calls to modern AsyncTaskService
 * Maintains backward compatibility while using modern architecture
 */
class AsyncTaskManagerProxy
{
    private $asyncTaskService;

    public function __construct()
    {
        $this->asyncTaskService = new AsyncTaskService();
    }

    /**
     * Create task tables (proxy to modern service)
     */
    public function createTaskTables()
    {
        return $this->asyncTaskService->createTaskTables();
    }

    /**
     * Add a new task
     */
    public function addTask($taskName, $taskType, $parameters = [], $priority = self::PRIORITY_NORMAL)
    {
        return $this->asyncTaskService->addTask($taskName, $taskType, $parameters, $priority);
    }

    /**
     * Get tasks by status
     */
    public function getTasksByStatus($status = 'pending', $limit = 50, $offset = 0)
    {
        return $this->asyncTaskService->getTasksByStatus($status, $limit, $offset);
    }

    /**
     * Process task queue
     */
    public function processTaskQueue($maxTasks = 10)
    {
        return $this->asyncTaskService->processTaskQueue($maxTasks);
    }

    /**
     * Get task by ID
     */
    public function getTask($taskId)
    {
        return $this->asyncTaskService->getTask($taskId);
    }

    /**
     * Update task status
     */
    public function updateTaskStatus($taskId, $status, $result = null, $errorMessage = null)
    {
        return $this->asyncTaskService->updateTaskStatus($taskId, $status, $result, $errorMessage);
    }

    /**
     * Cancel task
     */
    public function cancelTask($taskId)
    {
        return $this->asyncTaskService->cancelTask($taskId);
    }

    /**
     * Get task statistics
     */
    public function getTaskStats()
    {
        return $this->asyncTaskService->getTaskStats();
    }

    /**
     * Clean up old tasks
     */
    public function cleanupOldTasks($daysOld = 30)
    {
        return $this->asyncTaskService->cleanupOldTasks($daysOld);
    }

    /**
     * Proxy any other methods to the modern service
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->asyncTaskService, $name)) {
            return call_user_func_array([$this->asyncTaskService, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in AsyncTaskManagerProxy or AsyncTaskService.");
    }

    // Legacy constants for backward compatibility
    const PRIORITY_LOW = AsyncTaskService::PRIORITY_LOW;
    const PRIORITY_NORMAL = AsyncTaskService::PRIORITY_NORMAL;
    const PRIORITY_HIGH = AsyncTaskService::PRIORITY_HIGH;
    const PRIORITY_CRITICAL = AsyncTaskService::PRIORITY_CRITICAL;

    const STATUS_PENDING = AsyncTaskService::STATUS_PENDING;
    const STATUS_RUNNING = AsyncTaskService::STATUS_RUNNING;
    const STATUS_COMPLETED = AsyncTaskService::STATUS_COMPLETED;
    const STATUS_FAILED = AsyncTaskService::STATUS_FAILED;
    const STATUS_CANCELLED = AsyncTaskService::STATUS_CANCELLED;
}

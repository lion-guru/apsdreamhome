<?php

namespace App\Http\Controllers\Async;

use App\Http\Controllers\Admin\AdminController;
use App\Services\Async\AsyncTaskService;

/**
 * Async Task Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AsyncController extends AdminController
{
    private $taskService;

    public function __construct()
    {
        parent::__construct();
        $this->taskService = new AsyncTaskService();
    }

    /**
     * Show task dashboard
     */
    public function dashboard($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        // Get task statistics
        $statsResult = $this->taskService->getTaskStats();

        $data = [
            'title' => 'Task Dashboard - APS Dream Home',
            'user' => ['id' => ($_SESSION['user_id'] ?? 0), 'name' => ($_SESSION['user_name'] ?? ''), 'email' => ($_SESSION['user_email'] ?? ''), 'role' => ($_SESSION['role'] ?? '')],
            'stats' => $statsResult['success'] ? $statsResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        $this->render('async/dashboard', $data);
    }

    /**
     * Show tasks list
     */
    public function tasks($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $filters = [
            'status' => $_GET['status'] ?? null,
            'task_type' => $_GET['task_type'] ?? null,
            'assigned_worker' => $_GET['assigned_worker'] ?? null,
            'priority' => $_GET['priority'] ?? null
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->taskService->getTasks($filters, $limit, $offset);

        $data = [
            'title' => 'Tasks - APS Dream Home',
            'user' => ['id' => ($_SESSION['user_id'] ?? 0), 'name' => ($_SESSION['user_name'] ?? ''), 'email' => ($_SESSION['user_email'] ?? ''), 'role' => ($_SESSION['role'] ?? '')],
            'tasks' => $result['success'] ? $result['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        $this->render('async/tasks', $data);
    }

    /**
     * Show create task form
     */
    public function createTask($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $data = [
            'title' => 'Create Task - APS Dream Home',
            'user' => ['id' => ($_SESSION['user_id'] ?? 0), 'name' => ($_SESSION['user_name'] ?? ''), 'email' => ($_SESSION['user_email'] ?? ''), 'role' => ($_SESSION['role'] ?? '')],
            'priorities' => [
                AsyncTaskService::PRIORITY_LOW => 'Low',
                AsyncTaskService::PRIORITY_NORMAL => 'Normal',
                AsyncTaskService::PRIORITY_HIGH => 'High',
                AsyncTaskService::PRIORITY_CRITICAL => 'Critical'
            ],
            'task_types' => ['email', 'image_processing', 'report_generation', 'data_export', 'backup'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        $this->render('async/create_task', $data);
    }

    /**
     * Handle create task
     */
    public function handleCreateTask($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $taskName = trim($request['post']['task_name'] ?? '');
        $taskType = trim($request['post']['task_type'] ?? '');
        $priority = intval($request['post']['priority'] ?? AsyncTaskService::PRIORITY_NORMAL);
        $maxRetries = intval($request['post']['max_retries'] ?? 3);

        // Build parameters based on task type
        $parameters = [];

        switch ($taskType) {
            case 'email':
                $parameters = [
                    'to' => trim($request['post']['email_to'] ?? ''),
                    'subject' => trim($request['post']['email_subject'] ?? ''),
                    'message' => trim($request['post']['email_message'] ?? ''),
                    'template' => trim($request['post']['email_template'] ?? 'default')
                ];
                break;

            case 'image_processing':
                $parameters = [
                    'image_path' => trim($request['post']['image_path'] ?? ''),
                    'operations' => $request['post']['image_operations'] ?? ['resize', 'optimize'],
                    'output_format' => trim($request['post']['output_format'] ?? 'jpg')
                ];
                break;

            case 'report_generation':
                $parameters = [
                    'report_type' => trim($request['post']['report_type'] ?? ''),
                    'date_range' => [
                        'start' => $request['post']['date_start'] ?? '',
                        'end' => $request['post']['date_end'] ?? ''
                    ],
                    'format' => trim($request['post']['report_format'] ?? 'pdf')
                ];
                break;

            case 'data_export':
                $parameters = [
                    'export_type' => trim($request['post']['export_type'] ?? ''),
                    'table' => trim($request['post']['export_table'] ?? ''),
                    'filters' => json_decode($request['post']['export_filters'] ?? '{}', true) ?? []
                ];
                break;

            case 'backup':
                $parameters = [
                    'backup_type' => trim($request['post']['backup_type'] ?? 'full'),
                    'target' => trim($request['post']['backup_target'] ?? 'local'),
                    'compress' => isset($request['post']['backup_compress'])
                ];
                break;
        }

        $result = $this->taskService->createTask($taskName, $taskType, $parameters, $priority, $maxRetries);

        if ($result['success']) {
            $_SESSION['success'] = 'Task created successfully';
            $this->redirect('/async/tasks');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $request['post'];
            $this->redirect('/async/task/create');
        }

        return $result;
    }

    /**
     * Show task details
     */
    public function taskDetails($request)
    {
        // Check authentication
        $this->requireAdmin();

        $taskId = $request['params']['id'] ?? null;

        if (!$taskId) {
            $_SESSION['errors'] = ['Task ID is required'];
            $this->redirect('/async/tasks');
            return;
        }

        $result = $this->taskService->getTaskStatus($taskId);

        if (!$result['success']) {
            $_SESSION['errors'] = [$result['message']];
            $this->redirect('/async/tasks');
            return;
        }

        $data = [
            'title' => 'Task Details - APS Dream Home',
            'user' => ['id' => ($_SESSION['user_id'] ?? 0), 'name' => ($_SESSION['user_name'] ?? ''), 'email' => ($_SESSION['user_email'] ?? ''), 'role' => ($_SESSION['role'] ?? '')],
            'task' => $result['data'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        $this->render('async/task_details', $data);
    }

    /**
     * Cancel task
     */
    public function cancelTask($request)
    {
        // Check authentication
        $this->requireAdmin();

        $taskId = $request['params']['id'] ?? null;

        if (!$taskId) {
            return [
                'success' => false,
                'message' => 'Task ID is required'
            ];
        }

        $result = $this->taskService->cancelTask($taskId);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect("/async/task/$taskId");

        return $result;
    }

    /**
     * Retry task
     */
    public function retryTask($request)
    {
        // Check authentication
        $this->requireAdmin();

        $taskId = $request['params']['id'] ?? null;

        if (!$taskId) {
            return [
                'success' => false,
                'message' => 'Task ID is required'
            ];
        }

        $result = $this->taskService->retryTask($taskId);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['errors'] = [$result['message']];
        }

        $this->redirect("/async/task/$taskId");

        return $result;
    }

    /**
     * Process next task (for workers)
     */
    public function processNextTask()
    {
        // Check authentication
        $this->requireAdmin();

        $workerName = $_POST['worker_name'] ?? 'worker_' . ($_SESSION['user_id'] ?? 0);
        $queueName = $_POST['queue_name'] ?? 'default';

        $result = $this->taskService->getNextTask($workerName, $queueName);

        if (!$result['success']) {
            return $result;
        }

        $task = $result['data'];

        // Process task based on type
        switch ($task['task_type']) {
            case 'email':
                return $this->taskService->processEmailTask($task);

            case 'image_processing':
                return $this->taskService->processImageTask($task);

            case 'report_generation':
                return $this->taskService->processReportTask($task);

            default:
                return $this->taskService->failTask($task['id'], 'Unknown task type: ' . $task['task_type']);
        }
    }

    /**
     * Update task progress
     */
    public function updateTaskProgress($request)
    {
        // Check authentication
        $this->requireAdmin();

        $taskId = $request['post']['task_id'] ?? null;
        $progress = intval($request['post']['progress'] ?? 0);
        $result = json_decode($request['post']['result'] ?? '{}', true) ?? [];

        if (!$taskId) {
            return [
                'success' => false,
                'message' => 'Task ID is required'
            ];
        }

        return $this->taskService->updateTaskProgress($taskId, $progress, $result);
    }

    /**
     * Get tasks (AJAX)
     */
    public function getTasks($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'task_type' => $request['get']['task_type'] ?? null,
            'assigned_worker' => $request['get']['assigned_worker'] ?? null,
            'priority' => $request['get']['priority'] ?? null
        ];

        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $offset = max(0, intval($request['get']['offset'] ?? 0));

        return $this->taskService->getTasks($filters, $limit, $offset);
    }

    /**
     * Get task status (AJAX)
     */
    public function getTaskStatus($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $taskId = $request['get']['id'] ?? null;

        if (!$taskId) {
            return [
                'success' => false,
                'message' => 'Task ID is required'
            ];
        }

        return $this->taskService->getTaskStatus($taskId);
    }

    /**
     * Get task statistics (AJAX)
     */
    public function getTaskStats($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        return $this->taskService->getTaskStats();
    }

    /**
     * Create task (AJAX)
     */
    public function createTaskAjax($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $taskName = trim($request['post']['task_name'] ?? '');
        $taskType = trim($request['post']['task_type'] ?? '');
        $priority = intval($request['post']['priority'] ?? AsyncTaskService::PRIORITY_NORMAL);
        $maxRetries = intval($request['post']['max_retries'] ?? 3);
        $parameters = json_decode($request['post']['parameters'] ?? '{}', true) ?? [];

        return $this->taskService->createTask($taskName, $taskType, $parameters, $priority, $maxRetries);
    }

    /**
     * Cancel task (AJAX)
     */
    public function cancelTaskAjax($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $taskId = $request['post']['task_id'] ?? null;

        if (!$taskId) {
            return [
                'success' => false,
                'message' => 'Task ID is required'
            ];
        }

        return $this->taskService->cancelTask($taskId);
    }

    /**
     * Retry task (AJAX)
     */
    public function retryTaskAjax($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $taskId = $request['post']['task_id'] ?? null;

        if (!$taskId) {
            return [
                'success' => false,
                'message' => 'Task ID is required'
            ];
        }

        return $this->taskService->retryTask($taskId);
    }

    /**
     * Cleanup old tasks (AJAX)
     */
    public function cleanupOldTasks($request = null)
    {
        // Check authentication and admin access
        $this->requireAdmin();

        $daysOld = intval($request['post']['days_old'] ?? 30);

        return $this->taskService->cleanupOldTasks($daysOld);
    }

    /**
     * Worker endpoint for processing tasks
     */
    public function worker($request = null)
    {
        // Check authentication
        $this->requireAdmin();

        $workerName = $request['get']['worker'] ?? 'worker_' . ($_SESSION['user_id'] ?? 0);
        $queueName = $request['get']['queue'] ?? 'default';
        $continuous = isset($request['get']['continuous']) ? true : false;

        do {
            $result = $this->taskService->getNextTask($workerName, $queueName);

            if (!$result['success']) {
                // No tasks available
                if ($continuous) {
                    // REMOVED: blocking sleep call
                    continue;
                } else {
                    break;
                }
            }

            $task = $result['data'];

            // Process task based on type
            switch ($task['task_type']) {
                case 'email':
                    $processResult = $this->taskService->processEmailTask($task);
                    break;

                case 'image_processing':
                    $processResult = $this->taskService->processImageTask($task);
                    break;

                case 'report_generation':
                    $processResult = $this->taskService->processReportTask($task);
                    break;

                default:
                    $processResult = $this->taskService->failTask($task['id'], 'Unknown task type: ' . $task['task_type']);
            }

            if (!$continuous) {
                break;
            }

            // Small delay between tasks
            sleep(1);
        } while ($continuous);

        return [
            'success' => true,
            'message' => 'Worker completed successfully'
        ];
    }

    /**
     * Check if user is admin
     */
    protected function checkUserIsAdmin($user)
    {
        return $user && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
    }

}

<?php

namespace App\Controllers\Async;

use App\Services\Async\AsyncTaskService;
use App\Services\Auth\AuthenticationService;

/**
 * Async Task Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AsyncTaskController
{
    private $taskService;
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        $this->taskService = new AsyncTaskService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new \App\Core\ViewRenderer();
    }

    /**
     * Show task dashboard
     */
    public function dashboard($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access task dashboard'];
            $this->redirect('/login');
            return;
        }

        // Get task statistics
        $statsResult = $this->taskService->getTaskStats();

        $data = [
            'title' => 'Task Dashboard - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'stats' => $statsResult['success'] ? $statsResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('async/dashboard', $data);
    }

    /**
     * Show tasks list
     */
    public function tasks($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access tasks'];
            $this->redirect('/login');
            return;
        }

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'task_type' => $request['get']['task_type'] ?? null,
            'assigned_worker' => $request['get']['assigned_worker'] ?? null,
            'priority' => $request['get']['priority'] ?? null
        ];

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->taskService->getTasks($filters, $limit, $offset);

        $data = [
            'title' => 'Tasks - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'tasks' => $result['success'] ? $result['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('async/tasks', $data);
    }

    /**
     * Show create task form
     */
    public function createTask($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to create tasks'];
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'Create Task - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
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

        return $this->viewRenderer->render('async/create_task', $data);
    }

    /**
     * Handle create task
     */
    public function handleCreateTask($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to view task details'];
            $this->redirect('/login');
            return;
        }

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
            'user' => $this->authService->getCurrentUser(),
            'task' => $result['data'],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('async/task_details', $data);
    }

    /**
     * Cancel task
     */
    public function cancelTask($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
    public function processNextTask($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $workerName = $request['post']['worker_name'] ?? 'worker_' . $this->authService->getCurrentUser()['id'];
        $queueName = $request['post']['queue_name'] ?? 'default';

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
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
    public function getTasks($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
    public function getTaskStatus($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
    public function getTaskStats($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->taskService->getTaskStats();
    }

    /**
     * Create task (AJAX)
     */
    public function createTaskAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
    public function cancelTaskAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
    public function retryTaskAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

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
    public function cleanupOldTasks($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $daysOld = intval($request['post']['days_old'] ?? 30);

        return $this->taskService->cleanupOldTasks($daysOld);
    }

    /**
     * Worker endpoint for processing tasks
     */
    public function worker($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $workerName = $request['get']['worker'] ?? 'worker_' . $this->authService->getCurrentUser()['id'];
        $queueName = $request['get']['queue'] ?? 'default';
        $continuous = isset($request['get']['continuous']) ? true : false;

        do {
            $result = $this->taskService->getNextTask($workerName, $queueName);

            if (!$result['success']) {
                // No tasks available
                if ($continuous) {
                    sleep(5); // Wait 5 seconds before checking again
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
    private function isAdmin($user)
    {
        return $user && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}

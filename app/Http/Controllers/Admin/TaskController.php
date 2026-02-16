<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\TaskService;
use App\Services\CleanLeadService; // For getting assignable users

class TaskController extends BaseController
{
    private $taskService;
    private $leadService;

    public function __construct()
    {
        parent::__construct();

        $user = $this->auth->user();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        // Access control: only employees/admins/managers can access tasks
        // Assuming 'customer' role should not access admin tasks
        if ($user->role === 'customer') {
             $this->redirect('dashboard'); 
             return;
        }

        $this->layout = 'layouts/admin';
        $this->taskService = new TaskService();
        $this->leadService = new CleanLeadService();
    }

    /**
     * Display a listing of tasks
     */
    public function index()
    {
        $user = $this->auth->user();
        
        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'related_type' => $_GET['related_type'] ?? null,
            'related_id' => $_GET['related_id'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20)
        ];

        // Non-admins/managers only see tasks assigned to them or created by them
        if (!in_array($user->role, ['admin', 'super_admin', 'manager'])) {
            $filters['assigned_to'] = $user->id;
            // Note: If we want them to see tasks created by them too, TaskService needs update.
            // For now, let's stick to assigned tasks.
        }

        $tasks = $this->taskService->getTasks($filters);
        $taskStats = $this->taskService->getTaskStats();
        $users = $this->leadService->getAssignableUsers();

        $this->data['title'] = 'Task Management';
        $this->data['tasks'] = $tasks;
        $this->data['filters'] = $filters;
        $this->data['taskStats'] = $taskStats;
        $this->data['users'] = $users;

        $this->render('admin/tasks/index');
    }

    /**
     * Show the form for creating a new task
     */
    public function create()
    {
        $users = $this->leadService->getAssignableUsers();

        $this->data['title'] = 'Create New Task';
        $this->data['users'] = $users;
        $this->data['related_type'] = $_GET['related_type'] ?? null;
        $this->data['related_id'] = $_GET['related_id'] ?? null;

        $this->render('admin/tasks/create');
    }

    /**
     * Store a newly created task
     */
    public function store()
    {
        try {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
                'created_by' => $_SESSION['user_id'] ?? null,
                'priority' => $_POST['priority'] ?? 'medium',
                'status' => 'pending',
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'related_type' => $_POST['related_type'] ?? null,
                'related_id' => $_POST['related_id'] ?? null,
                'notes' => $_POST['notes'] ?? ''
            ];

            $taskId = $this->taskService->createTask($data);

            if ($taskId) {
                $this->setFlash('success', 'Task created successfully!');
                $this->redirect('admin/tasks');
                return;
            }

            throw new \Exception('Failed to create task');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $this->redirect('admin/tasks/create');
        }
    }

    /**
     * Show the form for editing a task
     */
    public function edit($id)
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            $this->notFound();
            return;
        }

        $user = $this->auth->user();
        // Permission check: only admin/manager or assigned user can edit
        if (!in_array($user->role, ['admin', 'super_admin', 'manager']) && $task['assigned_to'] != $user->id && $task['created_by'] != $user->id) {
            $this->forbidden();
            return;
        }

        $users = $this->leadService->getAssignableUsers();

        $this->data['title'] = 'Edit Task: ' . $task['title'];
        $this->data['task'] = $task;
        $this->data['users'] = $users;

        $this->render('admin/tasks/edit');
    }

    /**
     * Update the specified task
     */
    public function update($id)
    {
        try {
            $task = $this->taskService->getTaskById($id);

            if (!$task) {
                $this->notFound();
                return;
            }

            $user = $this->auth->user();
            // Permission check
            if (!in_array($user->role, ['admin', 'super_admin', 'manager']) && $task['assigned_to'] != $user->id && $task['created_by'] != $user->id) {
                $this->forbidden();
                return;
            }

            $data = [
                'title' => $_POST['title'] ?? $task['title'],
                'description' => $_POST['description'] ?? $task['description'],
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : $task['assigned_to'],
                'priority' => $_POST['priority'] ?? $task['priority'],
                'status' => $_POST['status'] ?? $task['status'],
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : $task['due_date'],
                'related_type' => $_POST['related_type'] ?? $task['related_type'],
                'related_id' => $_POST['related_id'] ?? $task['related_id'],
                'notes' => $_POST['notes'] ?? $task['notes']
            ];

            $result = $this->taskService->updateTask($id, $data);

            if ($result) {
                $this->setFlash('success', 'Task updated successfully!');
                $this->redirect('admin/tasks');
                return;
            }

            throw new \Exception('Failed to update task');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $this->redirect("admin/tasks/edit/$id");
        }
    }

    /**
     * Delete task
     */
    public function destroy($id)
    {
        try {
            $task = $this->taskService->getTaskById($id);

            if (!$task) {
                $this->notFound();
                return;
            }

            $user = $this->auth->user();
            // Permission check: only admin/manager or creator can delete
            if (!in_array($user->role, ['admin', 'super_admin', 'manager']) && $task['created_by'] != $user->id) {
                $this->forbidden();
                return;
            }

            $result = $this->taskService->deleteTask($id);

            if ($result) {
                $this->setFlash('success', 'Task deleted successfully!');
            } else {
                $this->setFlash('error', 'Failed to delete task');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('admin/tasks');
    }
}

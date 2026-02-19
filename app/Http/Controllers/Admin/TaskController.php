<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\TaskService;
use App\Services\CleanLeadService; // For getting assignable users

class TaskController extends AdminController
{
    private $taskService;
    private $leadService;

    public function __construct()
    {
        parent::__construct();

        // AdminController handles basic auth check.
        // We can add specific role checks if needed, but AdminController ensures isAdmin() is true.

        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);

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
            'search' => $this->request->get('search') ?? null,
            'status' => $this->request->get('status') ?? null,
            'priority' => $this->request->get('priority') ?? null,
            'assigned_to' => $this->request->get('assigned_to') ?? null,
            'related_type' => $this->request->get('related_type') ?? null,
            'related_id' => $this->request->get('related_id') ?? null,
            'page' => (int)($this->request->get('page') ?? 1),
            'per_page' => (int)($this->request->get('per_page') ?? 20)
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

        $this->data['title'] = $this->mlSupport->translate('Task Management');
        $this->data['tasks'] = $tasks;
        $this->data['filters'] = $filters;
        $this->data['taskStats'] = $taskStats;
        $this->data['users'] = $users;
        $this->data['mlSupport'] = $this->mlSupport;

        $this->render('admin/tasks/index');
    }

    /**
     * Show the form for creating a new task
     */
    public function create()
    {
        $users = $this->leadService->getAssignableUsers();

        $this->data['title'] = $this->mlSupport->translate('Create New Task');
        $this->data['users'] = $users;
        $this->data['related_type'] = $this->request->get('related_type') ?? null;
        $this->data['related_id'] = $this->request->get('related_id') ?? null;
        $this->data['mlSupport'] = $this->mlSupport;

        $this->render('admin/tasks/create');
    }

    /**
     * Store a newly created task
     */
    public function store()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/tasks/create');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            $this->redirect('admin/tasks/create');
            return;
        }

        try {
            $data = [
                'title' => $this->request->post('title') ?? '',
                'description' => $this->request->post('description') ?? '',
                'assigned_to' => !empty($this->request->post('assigned_to')) ? $this->request->post('assigned_to') : null,
                'created_by' => $this->session->get('user_id'),
                'priority' => $this->request->post('priority') ?? 'medium',
                'status' => 'pending',
                'due_date' => !empty($this->request->post('due_date')) ? $this->request->post('due_date') : null,
                'related_type' => $this->request->post('related_type') ?? null,
                'related_id' => $this->request->post('related_id') ?? null,
                'notes' => $this->request->post('notes') ?? ''
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
            $this->session->set('form_data', $this->request->all());
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
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect("admin/tasks/edit/$id");
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            $this->redirect("admin/tasks/edit/$id");
            return;
        }

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
                'title' => $this->request->post('title') ?? $task['title'],
                'description' => $this->request->post('description') ?? $task['description'],
                'assigned_to' => !empty($this->request->post('assigned_to')) ? $this->request->post('assigned_to') : $task['assigned_to'],
                'priority' => $this->request->post('priority') ?? $task['priority'],
                'status' => $this->request->post('status') ?? $task['status'],
                'due_date' => !empty($this->request->post('due_date')) ? $this->request->post('due_date') : $task['due_date'],
                'related_type' => $this->request->post('related_type') ?? $task['related_type'],
                'related_id' => $this->request->post('related_id') ?? $task['related_id'],
                'notes' => $this->request->post('notes') ?? $task['notes']
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
            $this->session->set('form_data', $this->request->all());
            $this->redirect("admin/tasks/edit/$id");
        }
    }

    /**
     * Delete task
     */
    public function destroy($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            $this->redirect('admin/tasks');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            $this->redirect('admin/tasks');
            return;
        }

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

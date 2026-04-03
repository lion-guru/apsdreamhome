<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Task Controller - Custom MVC Implementation
 * Handles task management operations in Admin panel
 */
class TaskController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of tasks
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $priority = $_GET['priority'] ?? '';
            $assignedTo = $_GET['assigned_to'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT t.*, 
                           u.name as assigned_to_name,
                           u.email as assigned_to_email
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND t.status = ?";
                $params[] = $status;
            }

            if (!empty($priority)) {
                $sql .= " AND t.priority = ?";
                $params[] = $priority;
            }

            if (!empty($assignedTo)) {
                $sql .= " AND t.assigned_to = ?";
                $params[] = $assignedTo;
            }

            $sql .= " ORDER BY t.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT t.*, u.name as assigned_to_name, u.email as assigned_to_email", "SELECT COUNT(DISTINCT t.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get assignable users
            $users = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'associate', 'manager') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Tasks - APS Dream Home',
                'active_page' => 'tasks',
                'tasks' => $tasks,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'priority' => $priority,
                    'assigned_to' => $assignedTo
                ],
                'users' => $users
            ];

            return $this->render('admin/tasks/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Task Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load tasks');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new task
     */
    public function create()
    {
        try {
            // Get assignable users
            $users = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'associate', 'manager') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Create Task - APS Dream Home',
                'active_page' => 'tasks',
                'users' => $users
            ];

            return $this->render('admin/tasks/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Task Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load task form');
            return $this->redirect('admin/tasks');
        }
    }

    /**
     * Store a newly created task
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['title', 'description', 'priority', 'due_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate priority
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($data['priority'], $validPriorities)) {
                return $this->jsonError('Invalid priority level', 400);
            }

            // Validate due date
            $dueDate = $data['due_date'];
            if (!empty($dueDate) && !strtotime($dueDate)) {
                return $this->jsonError('Invalid due date format', 400);
            }

            // Generate task number
            $taskNumber = 'TSK' . date('YmdHis') . rand(1000, 9999);

            // Insert task
            $sql = "INSERT INTO tasks 
                    (task_number, title, description, priority, due_date, 
                     assigned_to, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $taskNumber,
                CoreFunctionsServiceCustom::validateInput($data['title'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['description'], 'string'),
                $data['priority'],
                $dueDate,
                !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null,
                $data['status'] ?? 'pending'
            ]);

            if ($result) {
                $taskId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'task_created', [
                    'task_id' => $taskId,
                    'task_number' => $taskNumber,
                    'assigned_to' => $data['assigned_to']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Task created successfully',
                    'task_id' => $taskId,
                    'task_number' => $taskNumber
                ]);
            }

            return $this->jsonError('Failed to create task', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Task Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create task', 500);
        }
    }

    /**
     * Display the specified task
     */
    public function show($id)
    {
        try {
            $taskId = intval($id);
            if ($taskId <= 0) {
                $this->setFlash('error', 'Invalid task ID');
                return $this->redirect('admin/tasks');
            }

            // Get task details
            $sql = "SELECT t.*, 
                           u.name as assigned_to_name,
                           u.email as assigned_to_email
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    WHERE t.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$task) {
                $this->setFlash('error', 'Task not found');
                return $this->redirect('admin/tasks');
            }

            $data = [
                'page_title' => 'Task Details - APS Dream Home',
                'active_page' => 'tasks',
                'task' => $task
            ];

            return $this->render('admin/tasks/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Task Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load task details');
            return $this->redirect('admin/tasks');
        }
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit($id)
    {
        try {
            $taskId = intval($id);
            if ($taskId <= 0) {
                $this->setFlash('error', 'Invalid task ID');
                return $this->redirect('admin/tasks');
            }

            // Get task details
            $sql = "SELECT * FROM tasks WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$task) {
                $this->setFlash('error', 'Task not found');
                return $this->redirect('admin/tasks');
            }

            // Get assignable users
            $users = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'associate', 'manager') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Edit Task - APS Dream Home',
                'active_page' => 'tasks',
                'task' => $task,
                'users' => $users
            ];

            return $this->render('admin/tasks/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Task Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load task form');
            return $this->redirect('admin/tasks');
        }
    }

    /**
     * Update the specified task
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $taskId = intval($id);
            if ($taskId <= 0) {
                return $this->jsonError('Invalid task ID', 400);
            }

            $data = $_POST;

            // Check if task exists
            $sql = "SELECT * FROM tasks WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$task) {
                return $this->jsonError('Task not found', 404);
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['title'], 'string');
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            if (isset($data['priority'])) {
                $validPriorities = ['low', 'medium', 'high', 'urgent'];
                if (in_array($data['priority'], $validPriorities)) {
                    $updateFields[] = "priority = ?";
                    $updateValues[] = $data['priority'];
                }
            }

            if (isset($data['due_date'])) {
                if (!empty($data['due_date']) && strtotime($data['due_date'])) {
                    $updateFields[] = "due_date = ?";
                    $updateValues[] = $data['due_date'];
                }
            }

            if (isset($data['assigned_to'])) {
                $updateFields[] = "assigned_to = ?";
                $updateValues[] = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null;
            }

            if (isset($data['status'])) {
                $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            if (empty($updateFields)) {
                return $this->jsonError('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $taskId;

            $sql = "UPDATE tasks SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'task_updated', [
                    'task_id' => $taskId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Task updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update task', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Task Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update task', 500);
        }
    }

    /**
     * Remove the specified task
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $taskId = intval($id);
            if ($taskId <= 0) {
                return $this->jsonError('Invalid task ID', 400);
            }

            // Check if task exists
            $sql = "SELECT * FROM tasks WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$task) {
                return $this->jsonError('Task not found', 404);
            }

            // Delete task
            $sql = "DELETE FROM tasks WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$taskId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'task_deleted', [
                    'task_id' => $taskId,
                    'task_number' => $task['task_number']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Task deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete task', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Task Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete task', 500);
        }
    }

    /**
     * Get task statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total tasks
            $sql = "SELECT COUNT(*) as total FROM tasks";
            $result = $this->db->fetchOne($sql);
            $stats['total_tasks'] = (int)($result['total'] ?? 0);

            // Tasks by status
            $sql = "SELECT status, COUNT(*) as count FROM tasks GROUP BY status";
            $result = $this->db->fetchAll($sql);
            $stats['by_status'] = $result ?: [];

            // Tasks by priority
            $sql = "SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority";
            $result = $this->db->fetchAll($sql);
            $stats['by_priority'] = $result ?: [];

            // Overdue tasks
            $sql = "SELECT COUNT(*) as overdue FROM tasks WHERE due_date < NOW() AND status IN ('pending', 'in_progress')";
            $result = $this->db->fetchOne($sql);
            $stats['overdue_tasks'] = (int)($result['overdue'] ?? 0);

            // Tasks completed this week
            $sql = "SELECT COUNT(*) as completed_this_week FROM tasks 
                    WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['completed_this_week'] = (int)($result['completed_this_week'] ?? 0);

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Task Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * JSON response helper
     */
    public function jsonResponse($data, $status = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    protected function jsonError($message, $status = 400)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

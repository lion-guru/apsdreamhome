<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Auth;
use Exception;
use PDO;

class TaskService
{
    private $db;
    private $auth;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /**
     * Get tasks with filters and RBAC
     */
    public function getTasks($filters = [])
    {
        try {
            // RBAC: If not admin, only show assigned tasks or created tasks
            if (!$this->auth->isAdmin()) {
                $userId = $this->auth->id();
                // Force filter to current user if not explicitly filtering (or enforce it)
                // For now, let's enforce: User can only see tasks assigned to them or created by them
                // unless they are admin.
                
                // However, the original code allowed filtering by 'assigned_to'.
                // If a user filters by 'assigned_to' = themselves, it's fine.
                // If they filter by someone else, they shouldn't see it unless they are admin.
                
                // Let's add a base condition for non-admins
                $filters['user_context'] = $userId;
            }

            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(title LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['priority'])) {
                $where[] = "priority = ?";
                $params[] = $filters['priority'];
            }

            if (!empty($filters['assigned_to'])) {
                $where[] = "assigned_to = ?";
                $params[] = $filters['assigned_to'];
            }

            if (!empty($filters['related_type']) && !empty($filters['related_id'])) {
                $where[] = "related_type = ? AND related_id = ?";
                $params[] = $filters['related_type'];
                $params[] = $filters['related_id'];
            }

            // RBAC Enforcement
            if (isset($filters['user_context'])) {
                $where[] = "(assigned_to = ? OR created_by = ?)";
                $params[] = $filters['user_context'];
                $params[] = $filters['user_context'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 20;
            $offset = ($page - 1) * $perPage;

            $sql = "
                SELECT tasks.*, 
                       users.name as assigned_to_name,
                       creator.name as created_by_name
                FROM tasks
                LEFT JOIN users ON tasks.assigned_to = users.id
                LEFT JOIN users as creator ON tasks.created_by = creator.id
                $whereClause
                ORDER BY tasks.due_date ASC, tasks.priority DESC
                LIMIT $offset, $perPage
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("TaskService::getTasks Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get task by ID with RBAC
     */
    public function getTaskById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT tasks.*, 
                       users.name as assigned_to_name,
                       creator.name as created_by_name
                FROM tasks
                LEFT JOIN users ON tasks.assigned_to = users.id
                LEFT JOIN users as creator ON tasks.created_by = creator.id
                WHERE tasks.id = ?
            ");
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task) {
                return null;
            }

            // RBAC Check
            if (!$this->auth->isAdmin()) {
                $userId = $this->auth->id();
                if ($task['assigned_to'] != $userId && $task['created_by'] != $userId) {
                    return null; // Unauthorized
                }
            }

            return $task;
        } catch (Exception $e) {
            error_log("TaskService::getTaskById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create task
     */
    public function createTask($data)
    {
        try {
            // Validate required fields
            if (empty($data['title']) || empty($data['created_by'])) {
                return false;
            }

            $stmt = $this->db->prepare(
                "INSERT INTO tasks (title, description, assigned_to, created_by, priority, status, due_date, related_type, related_id, notes, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $data['title'],
                $data['description'] ?? '',
                $data['assigned_to'] ?? null,
                $data['created_by'],
                $data['priority'] ?? 'medium',
                $data['status'] ?? 'pending',
                $data['due_date'] ?? null,
                $data['related_type'] ?? null,
                $data['related_id'] ?? null,
                $data['notes'] ?? null
            ]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("TaskService::createTask Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update task with RBAC
     */
    public function updateTask($id, $data)
    {
        try {
            // Check existence and permission
            $existingTask = $this->getTaskById($id);
            if (!$existingTask) {
                return false; // Not found or unauthorized
            }

            $fields = [];
            $params = [];

            // Whitelist updatable fields
            $updatable = ['title', 'description', 'assigned_to', 'priority', 'status', 'due_date', 'related_type', 'related_id', 'notes'];
            
            foreach ($updatable as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return true; // Nothing to update
            }

            $fields[] = "updated_at = NOW()";
            $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = ?";
            $params[] = $id;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("TaskService::updateTask Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete task with RBAC
     */
    public function deleteTask($id)
    {
        try {
            // Check existence and permission
            $existingTask = $this->getTaskById($id);
            if (!$existingTask) {
                return false; // Not found or unauthorized
            }

            // Only Admin or Creator can delete?
            // Or maybe just Admin?
            // Let's allow Creator to delete too.
            if (!$this->auth->isAdmin() && $existingTask['created_by'] != $this->auth->id()) {
                return false; // Only creator or admin can delete
            }

            $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("TaskService::deleteTask Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get task statistics
     */
    public function getTaskStats()
    {
        try {
            // RBAC: Admins see all stats, Users see their own stats?
            // For now, let's just return global stats as it might be for dashboard
            // But ideally should be filtered.
            
            $whereClause = "";
            $params = [];
            
            if (!$this->auth->isAdmin()) {
                $userId = $this->auth->id();
                $whereClause = "WHERE assigned_to = ? OR created_by = ?";
                $params = [$userId, $userId];
            }

            $stats = [];

            // Total tasks by status
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count
                FROM tasks
                $whereClause
                GROUP BY status
            ");
            $stmt->execute($params);
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Total tasks by priority
            $stmt = $this->db->prepare("
                SELECT priority, COUNT(*) as count
                FROM tasks
                $whereClause
                GROUP BY priority
            ");
            $stmt->execute($params);
            $stats['by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;
        } catch (Exception $e) {
            error_log("TaskService::getTaskStats Error: " . $e->getMessage());
            return [];
        }
    }
}

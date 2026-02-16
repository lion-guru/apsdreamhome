<?php

namespace App\Services;

use App\Models\Database;

class TaskService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get tasks with filters
     */
    public function getTasks($filters = [])
    {
        try {
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

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 20;
            $offset = ($page - 1) * $perPage;

            $stmt = $this->db->prepare("
                SELECT tasks.*, 
                       users.name as assigned_to_name,
                       creator.name as created_by_name
                FROM tasks
                LEFT JOIN users ON tasks.assigned_to = users.id
                LEFT JOIN users as creator ON tasks.created_by = creator.id
                $whereClause
                ORDER BY tasks.due_date ASC, tasks.priority DESC
                LIMIT $offset, $perPage
            ");
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get task by ID
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
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create task
     */
    public function createTask($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tasks (title, description, assigned_to, created_by, priority, status, due_date, related_type, related_id, notes, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $data['title'],
                $data['description'],
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
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update task
     */
    public function updateTask($id, $data)
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE tasks SET
                 title = ?, description = ?, assigned_to = ?, priority = ?, status = ?, 
                 due_date = ?, related_type = ?, related_id = ?, notes = ?, updated_at = NOW()
                 WHERE id = ?"
            );
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['assigned_to'] ?? null,
                $data['priority'],
                $data['status'],
                $data['due_date'] ?? null,
                $data['related_type'] ?? null,
                $data['related_id'] ?? null,
                $data['notes'] ?? null,
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete task
     */
    public function deleteTask($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
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
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM tasks
                GROUP BY status
            ");
            $stats['by_status'] = $stmt->fetchAll();

            // Total tasks by priority
            $stmt = $this->db->query("
                SELECT priority, COUNT(*) as count
                FROM tasks
                GROUP BY priority
            ");
            $stats['by_priority'] = $stmt->fetchAll();

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }
}

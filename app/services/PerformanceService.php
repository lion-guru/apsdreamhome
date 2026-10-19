<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;

class PerformanceService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    // ==================== GOALS / OKRs ====================

    public function getGoals(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ['eg.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['employee_id'])) { $where[] = 'eg.employee_id = ?'; $params[] = $filters['employee_id']; }
        if (!empty($filters['status'])) { $where[] = 'eg.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['goal_type'])) { $where[] = 'eg.goal_type = ?'; $params[] = $filters['goal_type']; }
        if (!empty($filters['assigned_by'])) { $where[] = 'eg.assigned_by = ?'; $params[] = $filters['assigned_by']; }
        if (!empty($filters['parent_goal_id'])) { $where[] = 'eg.parent_goal_id = ?'; $params[] = $filters['parent_goal_id']; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT eg.*, u.name as employee_name, u.email as employee_email,
                    p.title as parent_goal_title
                FROM employee_goals eg
                JOIN employees e ON eg.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                LEFT JOIN employee_goals p ON eg.parent_goal_id = p.id
                WHERE $whereSql
                ORDER BY eg.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit; $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge([$this->tenantId()], $params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoal(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT eg.*, u.name as employee_name, u.email as employee_email,
                   p.title as parent_goal_title
            FROM employee_goals eg
            JOIN employees e ON eg.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            LEFT JOIN employee_goals p ON eg.parent_goal_id = p.id
            WHERE eg.id = ? AND eg.tenant_id = ?
        ");
        $stmt->execute([$id, $this->tenantId()]);
        $goal = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($goal) {
            $goal['progress'] = $this->getGoalProgress($id);
            $goal['children'] = $this->getChildGoals($id);
        }
        return $goal ?: null;
    }

    public function getEmployeeGoals(int $employeeId): array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM employee_goals WHERE employee_id = ? AND tenant_id = ? ORDER BY start_date DESC
        ");
        $stmt->execute([$employeeId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChildGoals(int $parentId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM employee_goals WHERE parent_goal_id = ? AND tenant_id = ? ORDER BY created_at");
        $stmt->execute([$parentId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createGoal(array $data): array
    {
        $required = ['employee_id', 'title', 'target_value', 'target_unit', 'start_date', 'end_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $columns = array_merge(
            ['employee_id', 'goal_type', 'title', 'description', 'target_value', 'target_unit', 'current_value', 'weightage', 'start_date', 'end_date', 'status', 'parent_goal_id', 'assigned_by', 'review_frequency', 'next_review_date', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['employee_id'], $data['goal_type'] ?? 'okr', $data['title'], $data['description'] ?? null,
            $data['target_value'], $data['target_unit'], 0, $data['weightage'] ?? 100,
            $data['start_date'], $data['end_date'], 'active',
            $data['parent_goal_id'] ?? null, $data['assigned_by'] ?? 1, $data['review_frequency'] ?? 'monthly',
            $data['next_review_date'] ?? null, date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO employee_goals (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        return ['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'message' => 'Goal created'];
    }

    public function updateGoal(int $id, array $data): array
    {
        $allowed = ['title', 'description', 'target_value', 'target_unit', 'current_value', 'weightage', 'status', 'progress_percentage', 'next_review_date'];
        $fields = []; $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) { $fields[] = "$field = ?"; $params[] = $data[$field]; }
        }
        if (empty($fields)) return ['success' => false, 'message' => 'No valid fields'];

        // Auto-calculate progress if current_value and target_value provided
        if (isset($data['current_value']) || isset($data['target_value'])) {
            $goal = $this->getGoal($id);
            $current = $data['current_value'] ?? $goal['current_value'];
            $target = $data['target_value'] ?? $goal['target_value'];
            if ($target > 0) {
                $pct = min(100, max(0, round(($current / $target) * 100, 2)));
                $fields[] = 'progress_percentage = ?'; $params[] = $pct;
                if ($pct >= 100 && !$fields['status']) { $fields[] = 'status = ?'; $params[] = 'completed'; }
            }
        }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE employee_goals SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Goal updated' : 'Not found'];
    }

    public function recordProgress(int $goalId, array $data): array
    {
        $required = ['checkpoint_date', 'actual_value'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $goal = $this->getGoal($goalId);
        if (!$goal) throw new InvalidArgumentException('Goal not found');

        $pct = $goal['target_value'] > 0 ? round(($data['actual_value'] / $goal['target_value']) * 100, 2) : 0;
        $status = $pct >= 100 ? 'achieved' : ($pct >= 75 ? 'on_track' : ($pct >= 50 ? 'at_risk' : 'off_track'));

        $stmt = $this->conn->prepare("
            INSERT INTO goal_progress (goal_id, checkpoint_date, actual_value, percentage_complete, status, notes, reviewer_id, tenant_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE actual_value = VALUES(actual_value), percentage_complete = VALUES(percentage_complete), status = VALUES(status), notes = VALUES(notes), reviewer_id = VALUES(reviewer_id), updated_at = NOW()
        ");
        $stmt->execute([$data['goal_id'] ?? $goalId, $data['checkpoint_date'], $data['actual_value'], $pct, $status, $data['notes'] ?? null, $data['reviewer_id'] ?? null, $this->tenantId()]);

        // Update goal progress
        $this->conn->execute("UPDATE employee_goals SET current_value = ?, progress_percentage = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?", [$data['actual_value'], min(100, $pct), $goalId, $this->tenantId()]);

        return ['success' => true, 'message' => 'Progress recorded'];
    }

    public function getGoalProgress(int $goalId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM goal_progress WHERE goal_id = ? AND tenant_id = ? ORDER BY checkpoint_date ASC');
        $stmt->execute([$goalId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== PERFORMANCE REVIEWS ====================

    public function getReviews(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = ['pr.tenant_id = ?'];
        $params = [$this->tenantId()];

        if (!empty($filters['employee_id'])) { $where[] = 'pr.employee_id = ?'; $params[] = $filters['employee_id']; }
        if (!empty($filters['reviewer_id'])) { $where[] = 'pr.reviewer_id = ?'; $params[] = $filters['reviewer_id']; }
        if (!empty($filters['status'])) { $where[] = 'pr.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['review_type'])) { $where[] = 'pr.review_type = ?'; $params[] = $filters['review_type']; }
        if (!empty($filters['period_start'])) { $where[] = 'pr.review_period_start >= ?'; $params[] = $filters['period_start']; }
        if (!empty($filters['period_end'])) { $where[] = 'pr.review_period_end <= ?'; $params[] = $filters['period_end']; }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT pr.*, u.name as employee_name, u.email as employee_email,
                    r.name as reviewer_name
                FROM performance_reviews pr
                JOIN employees e ON pr.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                JOIN users r ON pr.reviewer_id = r.id
                $whereSql
                ORDER BY pr.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit; $params[] = $offset;
        $params[] = $this->tenantId();

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReview(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT pr.*, u.name as employee_name, u.email as employee_email,
                   r.name as reviewer_name, r.email as reviewer_email
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            JOIN users r ON pr.reviewer_id = r.id
            WHERE pr.id = ? AND pr.tenant_id = ?
        ");
        $stmt->execute([$id, $this->tenantId()]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($review) {
            $review['ratings'] = $this->getReviewRatings($id);
        }
        return $review ?: null;
    }

    public function getEmployeeReviews(int $employeeId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM performance_reviews WHERE employee_id = ? AND tenant_id = ? ORDER BY review_period_end DESC");
        $stmt->execute([$employeeId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createReview(array $data): array
    {
        $required = ['employee_id', 'reviewer_id', 'review_period_start', 'review_period_end', 'review_type'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new InvalidArgumentException("$field is required");
        }

        $columns = array_merge(
            ['employee_id', 'reviewer_id', 'review_period_start', 'review_period_end', 'review_type', 'status', 'created_at'],
            array_keys($this->tenantInsertData())
        );

        $values = array_merge([
            $data['employee_id'], $data['reviewer_id'], $data['review_period_start'], $data['review_period_end'],
            $data['review_type'], 'draft', date('Y-m-d H:i:s')
        ], array_values($this->tenantInsertData()));

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO performance_reviews (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($values);

        $reviewId = (int)$this->conn->lastInsertId();

        // Create default rating criteria if provided
        if (!empty($data['criteria']) && is_array($data['criteria'])) {
            foreach ($data['criteria'] as $c) {
                $this->conn->execute("
                    INSERT INTO performance_review_ratings (review_id, criterion_name, criterion_description, weightage, tenant_id, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ", [$reviewId, $c['name'], $c['description'] ?? null, $c['weightage'] ?? 1, $this->tenantId()]);
            }
        }

        return ['success' => true, 'id' => $reviewId, 'message' => 'Review created'];
    }

    public function updateReview(int $id, array $data): array
    {
        $allowed = ['review_period_start', 'review_period_end', 'review_type', 'status', 'overall_rating', 'strengths', 'areas_of_improvement', 'goals_achievement', 'reviewer_comments', 'employee_comments', 'development_plan', 'next_review_date'];
        $fields = []; $params = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) { $fields[] = "$field = ?"; $params[] = $data[$field]; }
        }
        if (empty($fields)) return ['success' => false, 'message' => 'No valid fields'];

        // Handle status transitions
        if (isset($data['status'])) {
            $status = $data['status'];
            if ($status === 'submitted') $fields[] = 'submitted_at = NOW()';
            elseif ($status === 'acknowledged') $fields[] = 'acknowledged_at = NOW()';
            elseif ($status === 'completed') $fields[] = 'completed_at = NOW()';
        }

        $params[] = $id; $params[] = $this->tenantId();
        $sql = "UPDATE performance_reviews SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Review updated' : 'Not found'];
    }

    public function getReviewRatings(int $reviewId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM performance_review_ratings WHERE review_id = ? AND tenant_id = ? ORDER BY id');
        $stmt->execute([$reviewId, $this->tenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveRating(int $reviewId, array $data): array
    {
        $stmt = $this->conn->prepare("
            INSERT INTO performance_review_ratings (review_id, criterion_name, criterion_description, weightage, self_rating, reviewer_rating, final_rating, comments, tenant_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE self_rating = VALUES(self_rating), reviewer_rating = VALUES(reviewer_rating), final_rating = VALUES(final_rating), comments = VALUES(comments), updated_at = NOW()
        ");
        $stmt->execute([$reviewId, $data['criterion_name'], $data['criterion_description'] ?? null, $data['weightage'] ?? 1, $data['self_rating'] ?? null, $data['reviewer_rating'] ?? null, $data['final_rating'] ?? null, $data['comments'] ?? null, $this->tenantId()]);
        return ['success' => true, 'message' => 'Rating saved'];
    }

    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->conn->prepare('SELECT COUNT(*) as c, status FROM employee_goals WHERE tenant_id = ? GROUP BY status');
        $stmt->execute([$this->tenantId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $stats['goals_by_status'][$row['status']] = $row['c'];

        $stmt = $this->conn->prepare('SELECT COUNT(*) as c, status FROM performance_reviews WHERE tenant_id = ? GROUP BY status');
        $stmt->execute([$this->tenantId()]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $stats['reviews_by_status'][$row['status']] = $row['c'];

        $stmt = $this->conn->prepare('SELECT AVG(progress_percentage) as avg_progress FROM employee_goals WHERE tenant_id = ? AND status = "active"');
        $stmt->execute([$this->tenantId()]);
        $stats['avg_goal_progress'] = round($stmt->fetchColumn() ?? 0, 1);

        return $stats;
    }
}
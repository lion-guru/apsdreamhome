<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * EngagementService - MLM engagement features
 * 
 * Provides: associate metrics, leaderboards, goals CRUD, notification feed
 */
class EngagementService
{
    use ServiceTenantTrait;

    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    public function getAssociateMetrics(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = $this->buildMetricsFilter($filters);
        $sql = 'SELECT am.*, u.name AS user_name, u.email AS user_email FROM mlm_associate_metrics am JOIN users u ON am.user_id = u.id';
        if ($where) $sql .= ' WHERE ' . $where;
        $sql .= ' ORDER BY am.period_end DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLeaderboardSnapshot(string $metricType, ?string $snapshotDate = null, int $limit = 20): array
    {
        $metricType = trim($metricType);
        if ($metricType === '') throw new InvalidArgumentException('metricType is required');

        if ($snapshotDate === null) {
            $stmt = $this->conn->prepare('SELECT MAX(snapshot_date) AS latest_date FROM mlm_leaderboard_snapshots WHERE metric_type = ?');
            $stmt->execute([$metricType]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || empty($row['latest_date'])) return ['metric_type' => $metricType, 'snapshot_date' => null, 'records' => []];
            $snapshotDate = $row['latest_date'];
        }

        $stmt = $this->conn->prepare('SELECT ls.*, u.name AS user_name FROM mlm_leaderboard_snapshots ls JOIN users u ON ls.user_id = u.id WHERE ls.metric_type = ? AND ls.snapshot_date = ? ORDER BY ls.rank_position ASC LIMIT ?');
        $stmt->execute([$metricType, $snapshotDate, $limit]);
        return ['metric_type' => $metricType, 'snapshot_date' => $snapshotDate, 'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function getGoals(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = $this->buildGoalFilter($filters);
        $sql = 'SELECT g.*, owner.name AS owner_name FROM mlm_goals g LEFT JOIN users owner ON g.user_id = owner.id';
        if ($where) $sql .= ' WHERE ' . $where;
        $sql .= ' ORDER BY g.start_date DESC, g.id DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoalProgress(int $goalId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_goal_progress WHERE goal_id = ? ORDER BY checkpoint_date ASC');
        $stmt->execute([$goalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoalEvents(int $goalId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_goal_events WHERE goal_id = ? ORDER BY created_at ASC');
        $stmt->execute([$goalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNotificationFeed(int $userId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM notifications WHERE user_id = ?' . $this->tenantSql() . ' ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $params = [$userId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markNotificationRead(int $notificationId, int $userId): bool
    {
        if ($notificationId <= 0 || $userId <= 0) throw new InvalidArgumentException('Valid notification_id and user_id required');
        $stmt = $this->conn->prepare('UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL' . $this->tenantSql());
        $params = [$notificationId, $userId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function markAllNotificationsRead(int $userId): int
    {
        if ($userId <= 0) throw new InvalidArgumentException('Valid user_id required');
        $stmt = $this->conn->prepare('UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL' . $this->tenantSql());
        $params = [$userId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function createGoal(array $payload): array
    {
        $validTypes = ['sales', 'recruits', 'commission', 'custom'];
        $validScopes = ['individual', 'team'];

        $goalType = strtolower(trim($payload['goal_type'] ?? 'sales'));
        if (!in_array($goalType, $validTypes, true)) throw new InvalidArgumentException('Invalid goal_type');

        $scope = strtolower(trim($payload['scope'] ?? 'individual'));
        if (!in_array($scope, $validScopes, true)) throw new InvalidArgumentException('Invalid scope');

        $targetValue = (float) ($payload['target_value'] ?? 0);
        if ($targetValue <= 0) throw new InvalidArgumentException('target_value must be > 0');

        $startDate = $payload['start_date'] ?? null;
        $endDate = $payload['end_date'] ?? null;
        if (!$startDate || !$endDate) throw new InvalidArgumentException('start_date and end_date required');

        $userId = !empty($payload['user_id']) ? (int) $payload['user_id'] : null;

        $columns = array_merge(['goal_type', 'scope', 'user_id', 'target_value', 'target_units', 'start_date', 'end_date', 'status', 'created_at'], array_keys($this->tenantInsertData()));
        $values  = array_merge([$goalType, $scope, $userId, $targetValue, $payload['target_units'] ?? null, $startDate, $endDate, 'active', date('Y-m-d H:i:s')], array_values($this->tenantInsertData()));
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $stmt = $this->conn->prepare("INSERT INTO mlm_goals (" . implode(', ', $columns) . ") VALUES ({$placeholders})");
        $stmt->execute($values);

        return ['id' => (int) $this->conn->lastInsertId()];
    }

    public function updateGoal(int $goalId, array $payload): bool
    {
        $fields = [];
        $params = [];
        if (isset($payload['target_value'])) { $fields[] = 'target_value = ?'; $params[] = (float)$payload['target_value']; }
        if (isset($payload['status'])) { $fields[] = 'status = ?'; $params[] = $payload['status']; }
        if (empty($fields)) return false;
        $params[] = $goalId;
        $params[] = (int)$this->tenantId();
        $stmt = $this->conn->prepare('UPDATE mlm_goals SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ? AND tenant_id = ?');
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function recordGoalProgress(int $goalId, string $checkpointDate, float $actualValue): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO mlm_goal_progress (goal_id, checkpoint_date, actual_value, percentage_complete, tenant_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE actual_value = VALUES(actual_value)');
        $goal = $this->fetchGoal($goalId);
        $target = $goal ? (float)$goal['target_value'] : 1;
        $pct = $target > 0 ? ($actualValue / $target) * 100 : 0;
        $stmt->execute([$goalId, $checkpointDate, $actualValue, min(100, max(0, $pct)), (int)$this->tenantId()]);
        return true;
    }

    private function fetchGoal(int $goalId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_goals WHERE id = ? LIMIT 1');
        $stmt->execute([$goalId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function buildMetricsFilter(array $filters): array
    {
        $where = []; $params = [];
        if (!empty($filters['user_id'])) { $where[] = 'am.user_id = ?'; $params[] = (int)$filters['user_id']; }
        if (!empty($filters['from'])) { $where[] = 'am.period_start >= ?'; $params[] = $filters['from']; }
        if (!empty($filters['to'])) { $where[] = 'am.period_end <= ?'; $params[] = $filters['to']; }
        return [implode(' AND ', $where), $params];
    }

    private function buildGoalFilter(array $filters): array
    {
        $where = []; $params = [];
        if (!empty($filters['status'])) { $where[] = 'g.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['user_id'])) { $where[] = 'g.user_id = ?'; $params[] = (int)$filters['user_id']; }
        if (!empty($filters['goal_type'])) { $where[] = 'g.goal_type = ?'; $params[] = $filters['goal_type']; }
        return [implode(' AND ', $where), $params];
    }
}

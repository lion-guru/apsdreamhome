<?php
/**
 * EngagementService
 * Provides read models for engagement features (metrics, leaderboards, goals, notifications).
 */

class EngagementService
{
    private mysqli $conn;

    public function __construct()
    {
        $config = AppConfig::getInstance();
        $this->conn = $config->getDatabaseConnection();
    }

    /**
     * Fetch aggregated associate metrics with optional filters.
     */
    public function getAssociateMetrics(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$where, $types, $params] = $this->buildMetricsFilter($filters);

        $sql = 'SELECT am.*, u.name AS user_name, u.email AS user_email,
                       mp.current_level, mp.referral_code
                FROM mlm_associate_metrics am
                JOIN users u ON am.user_id = u.id
                LEFT JOIN mlm_profiles mp ON mp.user_id = am.user_id';

        if ($where) {
            $sql .= ' WHERE ' . $where;
        }

        $sql .= ' ORDER BY am.period_end DESC, am.period_start DESC LIMIT ? OFFSET ?';

        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * Return leaderboard snapshot for the given metric type (latest by default).
     */
    public function getLeaderboardSnapshot(string $metricType, ?string $snapshotDate = null, int $limit = 20): array
    {
        $metricType = trim($metricType);
        if ($metricType === '') {
            throw new InvalidArgumentException('metricType is required');
        }

        if ($snapshotDate === null) {
            $stmt = $this->conn->prepare('SELECT MAX(snapshot_date) AS latest_date FROM mlm_leaderboard_snapshots WHERE metric_type = ?');
            $stmt->bind_param('s', $metricType);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row || empty($row['latest_date'])) {
                return [
                    'metric_type' => $metricType,
                    'snapshot_date' => null,
                    'records' => [],
                ];
            }

            $snapshotDate = $row['latest_date'];
        }

        $sql = 'SELECT ls.*, u.name AS user_name, u.email AS user_email,
                       mp.current_level, mp.referral_code
                FROM mlm_leaderboard_snapshots ls
                JOIN users u ON ls.user_id = u.id
                LEFT JOIN mlm_profiles mp ON mp.user_id = ls.user_id
                WHERE ls.metric_type = ? AND ls.snapshot_date = ?
                ORDER BY ls.rank_position ASC
                LIMIT ?';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $metricType, $snapshotDate, $limit);
        $stmt->execute();
        $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return [
            'metric_type' => $metricType,
            'snapshot_date' => $snapshotDate,
            'records' => $records,
        ];
    }

    /**
     * Retrieve goals with optional filters.
     */
    public function getGoals(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$where, $types, $params] = $this->buildGoalFilter($filters);

        $sql = 'SELECT g.*, owner.name AS owner_name, creator.name AS created_by_name
                FROM mlm_goals g
                LEFT JOIN users owner ON g.user_id = owner.id
                LEFT JOIN users creator ON g.created_by = creator.id';

        if ($where) {
            $sql .= ' WHERE ' . $where;
        }

        $sql .= ' ORDER BY g.start_date DESC, g.id DESC LIMIT ? OFFSET ?';

        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Goal progress checkpoints ordered by date.
     */
    public function getGoalProgress(int $goalId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_goal_progress WHERE goal_id = ? ORDER BY checkpoint_date ASC');
        $stmt->bind_param('i', $goalId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Goal events timeline ordered by creation.
     */
    public function getGoalEvents(int $goalId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_goal_events WHERE goal_id = ? ORDER BY created_at ASC');
        $stmt->bind_param('i', $goalId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Fetch notification feed entries for a user.
     */
    public function getNotificationFeed(int $userId, int $limit = 20, int $offset = 0, ?string $category = null): array
    {
        $sql = 'SELECT * FROM mlm_notification_feed WHERE user_id = ?';
        $types = 'i';
        $params = [$userId];

        if ($category) {
            $sql .= ' AND category = ?';
            $types .= 's';
            $params[] = $category;
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Retrieve notification preferences for a user.
     */
    public function getNotificationPreferences(int $userId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_notification_preferences WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    public function markNotificationRead(int $notificationId, int $userId): bool
    {
        if ($notificationId <= 0) {
            throw new InvalidArgumentException('notification_id is required.');
        }

        if ($userId <= 0) {
            throw new InvalidArgumentException('user_id is required.');
        }

        $stmt = $this->conn->prepare(
            'UPDATE mlm_notification_feed SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL'
        );
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare markNotificationRead statement.');
        }

        $stmt->bind_param('ii', $notificationId, $userId);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to mark notification read: ' . $error);
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected > 0;
    }

    public function markAllNotificationsRead(int $userId): int
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('user_id is required.');
        }

        $stmt = $this->conn->prepare(
            'UPDATE mlm_notification_feed SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL'
        );
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare markAllNotificationsRead statement.');
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to mark notifications read: ' . $error);
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected;
    }

    public function createGoal(array $payload): array
    {
        $validTypes = ['sales', 'recruits', 'commission', 'custom'];
        $validScopes = ['individual', 'team'];

        $goalType = strtolower(trim($payload['goal_type'] ?? 'sales'));
        if (!in_array($goalType, $validTypes, true)) {
            throw new InvalidArgumentException('Invalid goal_type supplied.');
        }

        $scope = strtolower(trim($payload['scope'] ?? 'individual'));
        if (!in_array($scope, $validScopes, true)) {
            throw new InvalidArgumentException('Invalid scope supplied.');
        }

        $targetValue = (float) ($payload['target_value'] ?? 0);
        if ($targetValue <= 0) {
            throw new InvalidArgumentException('target_value must be greater than zero.');
        }

        $startDate = $payload['start_date'] ?? null;
        $endDate = $payload['end_date'] ?? null;
        if (!$startDate || !$endDate) {
            throw new InvalidArgumentException('Both start_date and end_date are required.');
        }

        if (strtotime($endDate) < strtotime($startDate)) {
            throw new InvalidArgumentException('end_date cannot be earlier than start_date.');
        }

        $userId = !empty($payload['user_id']) ? (int) $payload['user_id'] : null;
        if ($scope === 'individual' && $userId <= 0) {
            throw new InvalidArgumentException('user_id is required for individual goals.');
        }

        $targetUnits = !empty($payload['target_units']) ? trim($payload['target_units']) : null;
        $createdBy = !empty($payload['created_by']) ? (int) $payload['created_by'] : null;

        $stmt = $this->conn->prepare(
            'INSERT INTO mlm_goals (goal_type, scope, user_id, target_value, target_units, start_date, end_date, status, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $status = 'active';
        $userIdParam = $userId ?? null;
        $targetUnitsParam = $targetUnits ?? null;

        $stmt->bind_param(
            'ssidssssi',
            $goalType,
            $scope,
            $userIdParam,
            $targetValue,
            $targetUnitsParam,
            $startDate,
            $endDate,
            $status,
            $createdBy
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to create goal: ' . $error);
        }

        $goalId = (int) $stmt->insert_id;
        $stmt->close();

        return ['id' => $goalId];
    }

    public function updateGoal(int $goalId, array $payload): bool
    {
        if ($goalId <= 0) {
            throw new InvalidArgumentException('goal_id is required.');
        }

        $goal = $this->fetchGoal($goalId);
        if (!$goal) {
            throw new InvalidArgumentException('Goal not found.');
        }

        $validTypes = ['sales', 'recruits', 'commission', 'custom'];
        $validScopes = ['individual', 'team'];
        $allowedStatuses = ['draft', 'active', 'completed', 'expired', 'cancelled'];

        $fields = [];
        $types = '';
        $params = [];
        $updatedKeys = [];

        if (array_key_exists('goal_type', $payload)) {
            $goalType = strtolower(trim((string) $payload['goal_type']));
            if (!in_array($goalType, $validTypes, true)) {
                throw new InvalidArgumentException('Invalid goal_type supplied.');
            }
            $fields[] = 'goal_type = ?';
            $types .= 's';
            $params[] = $goalType;
            $updatedKeys[] = 'goal_type';
        }

        if (array_key_exists('scope', $payload)) {
            $scope = strtolower(trim((string) $payload['scope']));
            if (!in_array($scope, $validScopes, true)) {
                throw new InvalidArgumentException('Invalid scope supplied.');
            }
            $fields[] = 'scope = ?';
            $types .= 's';
            $params[] = $scope;
            $updatedKeys[] = 'scope';
        } else {
            $scope = $goal['scope'];
        }

        if (array_key_exists('user_id', $payload)) {
            $userId = $payload['user_id'] !== '' ? (int) $payload['user_id'] : null;
            $currentScope = $scope ?? $goal['scope'];
            if ($currentScope === 'individual' && ($userId === null || $userId <= 0)) {
                throw new InvalidArgumentException('user_id is required for individual goals.');
            }

            if ($userId === null) {
                $fields[] = 'user_id = NULL';
            } else {
                $fields[] = 'user_id = ?';
                $types .= 'i';
                $params[] = $userId;
            }
            $updatedKeys[] = 'user_id';
        }

        if (array_key_exists('target_value', $payload)) {
            $targetValue = (float) $payload['target_value'];
            if ($targetValue <= 0) {
                throw new InvalidArgumentException('target_value must be greater than zero.');
            }
            $fields[] = 'target_value = ?';
            $types .= 'd';
            $params[] = $targetValue;
            $updatedKeys[] = 'target_value';
        }

        if (array_key_exists('target_units', $payload)) {
            $targetUnits = $payload['target_units'] !== '' ? trim((string) $payload['target_units']) : null;
            if ($targetUnits === null) {
                $fields[] = 'target_units = NULL';
            } else {
                $fields[] = 'target_units = ?';
                $types .= 's';
                $params[] = $targetUnits;
            }
            $updatedKeys[] = 'target_units';
        }

        if (array_key_exists('start_date', $payload) || array_key_exists('end_date', $payload)) {
            $startDate = $payload['start_date'] ?? $goal['start_date'];
            $endDate = $payload['end_date'] ?? $goal['end_date'];

            if (!$startDate || !$endDate) {
                throw new InvalidArgumentException('Both start_date and end_date are required.');
            }

            if (strtotime($endDate) < strtotime($startDate)) {
                throw new InvalidArgumentException('end_date cannot be earlier than start_date.');
            }

            if (array_key_exists('start_date', $payload)) {
                $fields[] = 'start_date = ?';
                $types .= 's';
                $params[] = $startDate;
                $updatedKeys[] = 'start_date';
            }

            if (array_key_exists('end_date', $payload)) {
                $fields[] = 'end_date = ?';
                $types .= 's';
                $params[] = $endDate;
                $updatedKeys[] = 'end_date';
            }
        }

        if (array_key_exists('status', $payload)) {
            $status = strtolower(trim((string) $payload['status']));
            if (!in_array($status, $allowedStatuses, true)) {
                throw new InvalidArgumentException('Invalid status supplied.');
            }
            $fields[] = 'status = ?';
            $types .= 's';
            $params[] = $status;
            $updatedKeys[] = 'status';
        }

        if (empty($fields)) {
            throw new InvalidArgumentException('No fields supplied for update.');
        }

        $sql = 'UPDATE mlm_goals SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?';
        $types .= 'i';
        $params[] = $goalId;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            $this->logGoalEvent(
                $goalId,
                'updated',
                'Goal details updated',
                [
                    'fields' => $updatedKeys,
                    'updated_by' => $payload['updated_by'] ?? null,
                ]
            );
        }

        return $affected > 0;
    }

    public function recordGoalProgress(int $goalId, string $checkpointDate, float $actualValue, ?float $percentage = null, ?array $payload = null): bool
    {
        if ($goalId <= 0) {
            throw new InvalidArgumentException('goal_id is required.');
        }

        $goal = $this->fetchGoal($goalId);
        if (!$goal) {
            throw new InvalidArgumentException('Goal not found.');
        }

        if (!$checkpointDate) {
            throw new InvalidArgumentException('checkpoint_date is required.');
        }

        $timestamp = strtotime($checkpointDate);
        if ($timestamp === false) {
            throw new InvalidArgumentException('Invalid checkpoint_date supplied.');
        }

        if (strtotime($goal['start_date']) > $timestamp || strtotime($goal['end_date']) < $timestamp) {
            throw new InvalidArgumentException('Checkpoint date must fall within the goal period.');
        }

        if ($actualValue < 0) {
            throw new InvalidArgumentException('actual_value must be zero or greater.');
        }

        if ($percentage === null) {
            $target = (float) $goal['target_value'];
            $percentage = $target > 0 ? ($actualValue / $target) * 100 : 0.0;
        }

        $percentage = max(0.0, min(100.0, $percentage));

        $stmt = $this->conn->prepare(
            'INSERT INTO mlm_goal_progress (goal_id, checkpoint_date, actual_value, percentage_complete)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE actual_value = VALUES(actual_value), percentage_complete = VALUES(percentage_complete), updated_at = CURRENT_TIMESTAMP'
        );

        $stmt->bind_param('isdd', $goalId, $checkpointDate, $actualValue, $percentage);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to record progress: ' . $error);
        }
        $stmt->close();

        $this->logGoalEvent(
            $goalId,
            'progress',
            'Progress updated',
            array_merge(
                $payload ?? [],
                [
                    'checkpoint_date' => $checkpointDate,
                    'actual_value' => $actualValue,
                    'percentage_complete' => $percentage,
                ]
            )
        );

        return true;
    }

    public function updateGoalStatus(int $goalId, string $status): bool
    {
        $allowed = ['active', 'completed', 'expired', 'cancelled'];
        $status = strtolower(trim($status));
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Invalid status value.');
        }

        $stmt = $this->conn->prepare('UPDATE mlm_goals SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('si', $status, $goalId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            $this->logGoalEvent(
                $goalId,
                'status',
                'Goal status updated',
                [
                    'status' => $status,
                ]
            );
        }

        return $affected > 0;
    }

    // ---------------------------------------------------------------------
    // Internal helpers
    // ---------------------------------------------------------------------

    private function fetchGoal(int $goalId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM mlm_goals WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $goalId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    private function logGoalEvent(int $goalId, string $eventType, string $message, ?array $payload = null): void
    {
        $payloadJson = $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null;

        $stmt = $this->conn->prepare(
            'INSERT INTO mlm_goal_events (goal_id, event_type, event_message, event_payload)
             VALUES (?, ?, ?, ?)' 
        );
        $stmt->bind_param('isss', $goalId, $eventType, $message, $payloadJson);
        $stmt->execute();
        $stmt->close();
    }

    private function buildMetricsFilter(array $filters): array
    {
        $where = [];
        $types = '';
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'am.user_id = ?';
            $types .= 'i';
            $params[] = (int) $filters['user_id'];
        }

        if (!empty($filters['from'])) {
            $where[] = 'am.period_start >= ?';
            $types .= 's';
            $params[] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $where[] = 'am.period_end <= ?';
            $types .= 's';
            $params[] = $filters['to'];
        }

        if (!empty($filters['rank_label'])) {
            $where[] = 'am.rank_label = ?';
            $types .= 's';
            $params[] = $filters['rank_label'];
        }

        return [implode(' AND ', $where), $types, $params];
    }

    private function buildGoalFilter(array $filters): array
    {
        $where = [];
        $types = '';
        $params = [];

        if (!empty($filters['status'])) {
            $statuses = (array) $filters['status'];
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $where[] = 'g.status IN (' . $placeholders . ')';
            $types .= str_repeat('s', count($statuses));
            $params = array_merge($params, $statuses);
        }

        if (!empty($filters['scope'])) {
            $where[] = 'g.scope = ?';
            $types .= 's';
            $params[] = $filters['scope'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'g.user_id = ?';
            $types .= 'i';
            $params[] = (int) $filters['user_id'];
        }

        if (!empty($filters['goal_type'])) {
            $where[] = 'g.goal_type = ?';
            $types .= 's';
            $params[] = $filters['goal_type'];
        }

        if (!empty($filters['active_on'])) {
            $where[] = 'g.start_date <= ? AND g.end_date >= ?';
            $types .= 'ss';
            $params[] = $filters['active_on'];
            $params[] = $filters['active_on'];
        }

        return [implode(' AND ', $where), $types, $params];
    }

    private function bindParams(mysqli_stmt $stmt, string $types, array $params): void
    {
        if ($types === '' || empty($params)) {
            return;
        }

        $bindParams = [];
        $bindParams[] = $types;
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
}

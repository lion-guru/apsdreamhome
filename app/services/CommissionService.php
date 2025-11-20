<?php
/**
 * CommissionService
 * Phase 2 analytics helper for MLM commission insights.
 */

class CommissionService
{
    private mysqli $conn;
    private NotificationService $notifier;

    public function __construct()
    {
        $config = AppConfig::getInstance();
        $this->conn = $config->getDatabaseConnection();
        $this->notifier = new NotificationService();
    }

    public function getSummary(array $filters = []): array
    {
        $filter = $this->buildFilter($filters);
        $sql = 'SELECT status, SUM(amount) AS total_amount, COUNT(*) AS total_records
                FROM mlm_commission_ledger';

        if ($filter['where']) {
            $sql .= ' WHERE ' . $filter['where'];
        }

        $sql .= ' GROUP BY status';

        $statement = $this->prepare($sql, $filter['types'], $filter['params']);
        $statement->execute();
        $result = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $statement->close();

        $this->checkPendingThreshold($filter['where'], $filter['types'], $filter['params']);

        return $result;
    }

    public function getLevelBreakdown(array $filters = []): array
    {
        $filter = $this->buildFilter($filters);
        $sql = 'SELECT IFNULL(level, 0) AS level, SUM(amount) AS total_amount, COUNT(*) AS total_records
                FROM mlm_commission_ledger';

        if ($filter['where']) {
            $sql .= ' WHERE ' . $filter['where'];
        }

        $sql .= ' GROUP BY level ORDER BY level';

        $stmt = $this->prepare($sql, $filter['types'], $filter['params']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function getTopBeneficiaries(array $filters = [], int $limit = 10): array
    {
        $filter = $this->buildFilter($filters);
        $sql = 'SELECT u.id, u.name, u.email, u.type,
                       SUM(l.amount) AS total_amount,
                       SUM(CASE WHEN l.status = "paid" THEN l.amount ELSE 0 END) AS total_paid,
                       SUM(CASE WHEN l.status = "pending" THEN l.amount ELSE 0 END) AS total_pending,
                       COUNT(*) AS commissions
                FROM mlm_commission_ledger l
                JOIN users u ON l.beneficiary_user_id = u.id';

        if ($filter['where']) {
            $sql .= ' WHERE ' . $filter['where'];
        }

        $sql .= ' GROUP BY u.id, u.name, u.email, u.type
                  ORDER BY total_amount DESC
                  LIMIT ?';

        $types = $filter['types'] . 'i';
        $params = array_merge($filter['params'], [$limit]);

        $stmt = $this->prepare($sql, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function getTopReferrers(array $filters = [], int $limit = 10): array
    {
        $filter = $this->buildFilter($filters);
        $sql = 'SELECT u.id, u.name, u.email, u.type,
                       mp.direct_referrals,
                       mp.total_commission,
                       SUM(l.amount) AS total_amount
                FROM mlm_commission_ledger l
                JOIN mlm_profiles mp ON l.beneficiary_user_id = mp.user_id
                JOIN users u ON mp.user_id = u.id';

        if ($filter['where']) {
            $sql .= ' WHERE ' . $filter['where'];
        }

        $sql .= ' GROUP BY u.id, u.name, u.email, u.type, mp.direct_referrals, mp.total_commission
                  ORDER BY mp.direct_referrals DESC, total_amount DESC
                  LIMIT ?';

        $types = $filter['types'] . 'i';
        $params = array_merge($filter['params'], [$limit]);

        $stmt = $this->prepare($sql, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function getTimeline(array $filters = [], string $groupBy = 'day'): array
    {
        $filter = $this->buildFilter($filters);

        $column = 'DATE(created_at)';
        if ($groupBy === 'month') {
            $column = 'DATE_FORMAT(created_at, "%Y-%m")';
        } elseif ($groupBy === 'week') {
            $column = 'DATE_FORMAT(created_at, "%x-W%v")';
        }

        $sql = "SELECT {$column} AS bucket, SUM(amount) AS total_amount, COUNT(*) AS total_records
                FROM mlm_commission_ledger";

        if ($filter['where']) {
            $sql .= ' WHERE ' . $filter['where'];
        }

        $sql .= ' GROUP BY bucket ORDER BY bucket';

        $stmt = $this->prepare($sql, $filter['types'], $filter['params']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function getLedger(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $filter = $this->buildFilter($filters);

        $sql = 'SELECT l.*, u.name AS beneficiary_name, u.email AS beneficiary_email,
                       src.name AS source_name, src.email AS source_email
                FROM mlm_commission_ledger l
                JOIN users u ON l.beneficiary_user_id = u.id
                LEFT JOIN users src ON l.source_user_id = src.id';

        if ($filter['where']) {
            $sql .= ' WHERE ' . $filter['where'];
        }

        $sql .= ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?';

        $types = $filter['types'] . 'ii';
        $params = array_merge($filter['params'], [$limit, $offset]);

        $stmt = $this->prepare($sql, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function exportLedger(array $filters = []): array
    {
        $filter = $this->buildFilter($filters);
        $sql = 'SELECT l.id, l.beneficiary_user_id, u.name AS beneficiary_name, u.email AS beneficiary_email,
                       l.source_user_id, src.name AS source_name, src.email AS source_email,
                       l.commission_type, l.amount, l.level, l.status, l.created_at, l.updated_at
                FROM mlm_commission_ledger l
                JOIN users u ON l.beneficiary_user_id = u.id
                LEFT JOIN users src ON l.source_user_id = src.id';

        if ($filter['where']) {
            $sql .= ' WHERE ' . $filter['where'];
        }

        $sql .= ' ORDER BY l.created_at DESC';

        $stmt = $this->prepare($sql, $filter['types'], $filter['params']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    private function prepare(string $sql, string $types = '', array $params = []): mysqli_stmt
    {
        $stmt = $this->conn->prepare($sql);
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }

        return $stmt;
    }

    private function buildFilter(array $filters): array
    {
        $where = [];
        $types = '';
        $params = [];

        if (!empty($filters['status'])) {
            $statuses = (array) $filters['status'];
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $where[] = 'status IN (' . $placeholders . ')';
            $types .= str_repeat('s', count($statuses));
            $params = array_merge($params, $statuses);
        }

        if (!empty($filters['commission_type'])) {
            $typesFilter = (array) $filters['commission_type'];
            $placeholders = implode(',', array_fill(0, count($typesFilter), '?'));
            $where[] = 'commission_type IN (' . $placeholders . ')';
            $types .= str_repeat('s', count($typesFilter));
            $params = array_merge($params, $typesFilter);
        }

        if (!empty($filters['beneficiary_id'])) {
            $where[] = 'beneficiary_user_id = ?';
            $types .= 'i';
            $params[] = (int) $filters['beneficiary_id'];
        }

        if (!empty($filters['source_user_id'])) {
            $where[] = 'source_user_id = ?';
            $types .= 'i';
            $params[] = (int) $filters['source_user_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $types .= 's';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $types .= 's';
            $params[] = $filters['date_to'];
        }

        return [
            'where' => implode(' AND ', $where),
            'types' => $types,
            'params' => $params
        ];
    }

    private function checkPendingThreshold(string $baseWhere, string $types, array $params): void
    {
        $threshold = (float) MlmSettings::getFloat('pending_commission_threshold', 0);
        if ($threshold <= 0) {
            return;
        }

        $sql = 'SELECT SUM(amount) AS pending_total FROM mlm_commission_ledger WHERE status = \'pending\'';
        if ($baseWhere) {
            $sql .= ' AND ' . $baseWhere;
        }

        $stmt = $this->prepare($sql, 's' . $types, array_merge(['pending'], $params));
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $pendingTotal = (float) ($row['pending_total'] ?? 0);
        if ($pendingTotal >= $threshold) {
            $payload = [
                'pending_total' => $pendingTotal,
                'threshold' => $threshold,
            ];
            $subject = 'Pending commissions exceed threshold';
            $body = '<h2>Pending Commission Alert</h2>'
                . '<p><strong>Pending Total:</strong> ₹' . number_format($pendingTotal, 2)
                . '<br><strong>Threshold:</strong> ₹' . number_format($threshold, 2)
                . '</p>'
                . '<p>Review pending approvals in the admin analytics dashboard.</p>';

            $this->notifier->notifyAdmin($subject, $body, 'pending_commission_threshold', $payload);
        }
    }
}

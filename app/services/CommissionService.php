<?php

namespace App\Services;

use App\Models\Database;
use App\Services\NotificationService;
use App\Services\MlmSettings;
use PDO;

/**
 * CommissionService
 * Phase 2 analytics helper for MLM commission insights.
 */

class CommissionService
{
    private PDO $conn;
    private NotificationService $notifier;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
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

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($filter['params']);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->checkPendingThreshold($filter['where'], $filter['params']);

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

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($filter['params']);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters manually to handle LIMIT
        $paramIndex = 1;
        foreach ($filter['params'] as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex, $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $stmt = $this->conn->prepare($sql);
        
        $paramIndex = 1;
        foreach ($filter['params'] as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex, $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($filter['params']);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $stmt = $this->conn->prepare($sql);
        
        $paramIndex = 1;
        foreach ($filter['params'] as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex, $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($filter['params']);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    private function buildFilter(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $statuses = (array) $filters['status'];
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $where[] = 'status IN (' . $placeholders . ')';
            $params = array_merge($params, $statuses);
        }

        if (!empty($filters['commission_type'])) {
            $typesFilter = (array) $filters['commission_type'];
            $placeholders = implode(',', array_fill(0, count($typesFilter), '?'));
            $where[] = 'commission_type IN (' . $placeholders . ')';
            $params = array_merge($params, $typesFilter);
        }

        if (!empty($filters['beneficiary_id'])) {
            $where[] = 'beneficiary_user_id = ?';
            $params[] = (int) $filters['beneficiary_id'];
        }

        if (!empty($filters['source_user_id'])) {
            $where[] = 'source_user_id = ?';
            $params[] = (int) $filters['source_user_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'];
        }

        return [
            'where' => implode(' AND ', $where),
            'params' => $params
        ];
    }

    private function checkPendingThreshold(string $baseWhere, array $params): void
    {
        $threshold = (float) MlmSettings::getFloat('pending_commission_threshold', 0);
        if ($threshold <= 0) {
            return;
        }

        $sql = 'SELECT SUM(amount) AS pending_total FROM mlm_commission_ledger WHERE status = \'pending\'';
        if ($baseWhere) {
            $sql .= ' AND ' . $baseWhere;
        }

        // Prepend 'pending' to params if filtering by status, or just use base params if status is part of filter?
        // Wait, the query adds `status='pending'` manually.
        // If `$baseWhere` contains `status IN (...)`, we might have a conflict or just redundant condition.
        // Assuming `$baseWhere` filters by date/user, not status, or if it does, it might be restrictive.
        // The original code merged `['pending']` with `$params`.
        // Wait, the original code had:
        // $stmt = $this->prepare($sql, 's' . $types, array_merge(['pending'], $params));
        // But `status = 'pending'` is hardcoded in SQL.
        // If `status` was in `$baseWhere`, it would be `status = 'pending' AND status IN (...)`.
        // This seems fine.
        // However, `params` for `$baseWhere` must match placeholders in `$baseWhere`.
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params); // Wait, original code merged `['pending']`?
        
        // Original code:
        // $sql = 'SELECT ... WHERE status = \'pending\'';
        // if ($baseWhere) $sql .= ' AND ' . $baseWhere;
        // $stmt = $this->prepare($sql, 's' . $types, array_merge(['pending'], $params));
        
        // Wait, if `status = 'pending'` is hardcoded, where does the extra parameter come from?
        // Ah, maybe the original code used `WHERE status = ?`?
        // Let's check original code.
        // Line 270: $sql = 'SELECT SUM(amount) AS pending_total FROM mlm_commission_ledger WHERE status = \'pending\'';
        // Line 275: $stmt = $this->prepare($sql, 's' . $types, array_merge(['pending'], $params));
        // This looks like a bug in original code if SQL didn't have `?` for status.
        // Or maybe `prepare` ignored extra params? No, `bind_param` would fail if types count mismatch.
        // Unless `prepare` wrapper handles it?
        
        // Let's look at `CommissionService.php` again.
        // Line 270: `WHERE status = 'pending'`
        // Line 275: `prepare($sql, 's' . $types, array_merge(['pending'], $params))`
        // This definitely looks like the original code was trying to bind 'pending' but hardcoded it in SQL.
        // If I keep `status = 'pending'`, I don't need to bind it.
        // I will trust the SQL and remove the extra bind if it's not needed.
        // But wait, if `$baseWhere` has params, I need to pass them.
        
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

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

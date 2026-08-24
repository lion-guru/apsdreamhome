<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Collection Service
 * Handles cash collections, collector reconciliation, and collection stats
 */
class CollectionService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function recordCollection(array $data): array
    {
        $tid = TenantContext::getId();

        $payload = [
            'collector_id'       => $data['collector_id'] ?? null,
            'plot_booking_id'    => $data['plot_booking_id'] ?? null,
            'amount'             => (float)($data['amount'] ?? 0),
            'payment_mode'       => $data['payment_mode'] ?? 'cash',
            'cheque_number'      => $data['cheque_number'] ?? null,
            'cheque_date'        => $data['cheque_date'] ?? null,
            'cheque_bank'        => $data['cheque_bank'] ?? null,
            'remarks'            => $data['remarks'] ?? null,
            'receipt_generated'  => 0,
            'receipt_number'     => null,
            'status'             => 'pending',
            'verified_by'        => null,
            'verified_at'        => null,
            'rejection_reason'   => null,
            'collection_date'    => $data['collection_date'] ?? date('Y-m-d'),
            'tenant_id'          => $tid,
        ];
        $this->db->insert('cash_collections', $payload);
        $id = (int)$this->db->lastInsertId();

        return ['success' => true, 'collection_id' => $id];
    }

    public function getCollections(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if (!empty($filters['collector_id'])) {
            $where .= " AND collector_id = ?";
            $params[] = $filters['collector_id'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND collection_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND collection_date <= ?";
            $params[] = $filters['to_date'];
        }
        if (!empty($filters['plot_booking_id'])) {
            $where .= " AND plot_booking_id = ?";
            $params[] = $filters['plot_booking_id'];
        }

        $where .= " ORDER BY collection_date DESC, id DESC";
        return $this->db->fetchAll("SELECT * FROM cash_collections $where", $params) ?: [];
    }

    public function getCollection(int $id): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM cash_collections WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]) ?: null;
    }

    public function verifyCollection(int $id, int $verifiedBy): bool
    {
        $tid = TenantContext::getId();
        $receiptNumber = 'RCPT-' . date('Ymd') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);

        $sql = "UPDATE cash_collections SET status = 'verified', verified_by = ?, verified_at = NOW(), receipt_generated = 1, receipt_number = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$verifiedBy, $receiptNumber, $id], $tid > 1 ? [$tid] : []));
    }

    public function rejectCollection(int $id, int $rejectedBy, string $reason): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE cash_collections SET status = 'rejected', rejection_reason = ?, verified_by = ?, verified_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$reason, $rejectedBy, $id], $tid > 1 ? [$tid] : []));
    }

    public function startReconciliation(int $collectorId, string $date): array
    {
        $tid = TenantContext::getId();

        $collections = $this->db->fetchAll("
            SELECT * FROM cash_collections
            WHERE collector_id = ? AND collection_date = ? AND status = 'verified'
            " . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$collectorId, $date, $tid] : [$collectorId, $date]
        );

        $total = array_sum(array_column($collections, 'amount'));

        $session = [
            'collector_id'    => $collectorId,
            'reconciliation_date' => $date,
            'total_amount'    => array_sum(array_column($collections, 'amount')),
            'collection_count'=> count($collections),
            'status'          => 'open',
            'started_by'      => TenantContext::getId(),
            'tenant_id'       => $tid,
        ];
        $this->db->insert('collection_reconciliation_sessions', $session);
        $sessionId = (int)$this->db->lastInsertId();

        return [
            'session_id'       => $sessionId,
            'collector_id'     => $collectorId,
            'date'             => $date,
            'collections'      => $collections,
            'total_amount'     => array_sum(array_column($collections, 'amount')),
            'collection_count' => count($collections),
        ];
    }

    public function closeReconciliation(int $sessionId, int $closedBy): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE collection_reconciliation_sessions SET status = 'closed', closed_by = ?, closed_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$closedBy, $sessionId], $tid > 1 ? [$tid] : []));
    }

    public function getReconciliationSessions(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if (!empty($filters['collector_id'])) {
            $where .= " AND collector_id = ?";
            $params[] = $filters['collector_id'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND reconciliation_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND reconciliation_date <= ?";
            $params[] = $filters['to_date'];
        }

        $where .= " ORDER BY reconciliation_date DESC";
        return $this->db->fetchAll("SELECT * FROM collection_reconciliation_sessions $where", $params) ?: [];
    }

    public function getCollectionStats(): array
    {
        $tid = TenantContext::getId();
        $where = $tid > 1 ? " WHERE tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $pending = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_collections WHERE status = 'pending'" . $where, $params);
        $verified = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total, COUNT(*) as count FROM cash_collections WHERE status = 'verified'" . $where, $params);
        $rejected = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_collections WHERE status = 'rejected'" . $where, $params);

        return [
            'pending'  => (float)($pending['total'] ?? 0),
            'verified' => (float)($verified['total'] ?? 0),
            'verified_count' => (int)($verified['count'] ?? 0),
            'rejected' => (float)($rejected['total'] ?? 0),
            'total'    => (float)(($pending['total'] ?? 0) + ($verified['total'] ?? 0) + ($rejected['total'] ?? 0)),
        ];
    }

    public function listCollectors(): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT id, name, email, phone FROM users WHERE role IN ('employee', 'telecaller', 'associate') AND status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchAll($sql, $tid > 1 ? [$tid] : []) ?: [];
    }
}
<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class InterCompanyService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function getEntities(): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM inter_company_entities WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY entity_name";
        $params = $tid > 1 ? [$tid] : [];
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getAllTransactions(?string $status = null, ?string $fromDate = null, ?string $toDate = null, int $fromEntity = 0, int $toEntity = 0): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT t.*, fe.entity_name as from_entity_name, te.entity_name as to_entity_name
                FROM inter_company_transactions t
                INNER JOIN inter_company_entities fe ON fe.id = t.from_entity_id
                INNER JOIN inter_company_entities te ON te.id = t.to_entity_id
                WHERE 1=1" . ($tid > 1 ? " AND t.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($status) { $sql .= " AND t.status = ?"; $params[] = $status; }
        if ($fromDate) { $sql .= " AND t.transaction_date >= ?"; $params[] = $fromDate; }
        if ($toDate) { $sql .= " AND t.transaction_date <= ?"; $params[] = $toDate; }
        if ($fromEntity) { $sql .= " AND t.from_entity_id = ?"; $params[] = $fromEntity; }
        if ($toEntity) { $sql .= " AND t.to_entity_id = ?"; $params[] = $toEntity; }
        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT t.*, fe.entity_name as from_entity_name, te.entity_name as to_entity_name
                FROM inter_company_transactions t
                INNER JOIN inter_company_entities fe ON fe.id = t.from_entity_id
                INNER JOIN inter_company_entities te ON te.id = t.to_entity_id
                WHERE t.id = ?" . ($tid > 1 ? " AND t.tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchOne($sql, $params);
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $txnNumber = $data['transaction_number'] ?? ('IC-' . date('Ymd') . '-' . mt_rand(1000, 9999));
        $amount = (float)($data['amount'] ?? 0);
        $exchangeRate = (float)($data['exchange_rate'] ?? 1);
        $amountInr = round($amount * $exchangeRate, 2);
        $data = array_merge($data, [
            'tenant_id' => $tid,
            'transaction_number' => $txnNumber,
            'amount' => $amount,
            'exchange_rate' => $exchangeRate,
            'amount_inr' => $amountInr,
            'status' => 'draft',
        ]);
        $this->db->insert('inter_company_transactions', $data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['transaction_number','transaction_date','from_entity_id','to_entity_id','transaction_type','description','amount','currency','exchange_rate','amount_inr','reference_document','notes'];
        $updateData = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) $updateData[$f] = $data[$f];
        }
        if (isset($data['amount'])) {
            $updateData['amount'] = (float)$data['amount'];
        }
        if (isset($data['exchange_rate'])) {
            $updateData['exchange_rate'] = (float)$data['exchange_rate'];
        }
        if (isset($data['amount_inr'])) {
            $updateData['amount_inr'] = (float)$data['amount_inr'];
        } elseif (isset($data['amount']) && isset($data['exchange_rate'])) {
            $updateData['amount_inr'] = round((float)$data['amount'] * (float)$data['exchange_rate'], 2);
        }
        if (empty($updateData)) return false;
        return $this->db->update('inter_company_transactions', $updateData, 'id = ?', [$id]);
    }

    public function delete(int $id): bool
    {
        $txn = $this->getById($id);
        if (!$txn || $txn['status'] !== 'draft') return false;
        return $this->db->delete('inter_company_transactions', 'id = ?', [$id]);
    }

    public function postTransaction(int $id): bool
    {
        $txn = $this->getById($id);
        if (!$txn || $txn['status'] !== 'draft') return false;
        return $this->db->update('inter_company_transactions', [
            'status' => 'posted',
            'posted_by' => $_SESSION['user_id'] ?? 1,
            'posted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function cancelTransaction(int $id): bool
    {
        $txn = $this->getById($id);
        if (!$txn || !in_array($txn['status'], ['draft','posted'])) return false;
        return $this->db->update('inter_company_transactions', [
            'status' => 'cancelled',
        ], 'id = ?', [$id]);
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tf = $tid > 1 ? " AND tenant_id = ?" : "";
        $tp = $tid > 1 ? [$tid] : [];
        return [
            'total_entities' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inter_company_entities WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'total_transactions' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inter_company_transactions WHERE 1=1" . $tf, $tp),
            'draft_transactions' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inter_company_transactions WHERE status = 'draft'" . $tf, $tp),
            'posted_transactions' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inter_company_transactions WHERE status = 'posted'" . $tf, $tp),
            'reconciled_transactions' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inter_company_transactions WHERE status = 'reconciled'" . $tf, $tp),
            'total_amount' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount_inr),0) FROM inter_company_transactions WHERE status != 'cancelled'" . $tf, $tp),
            'open_reconciliations' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inter_company_reconciliations WHERE status = 'open'" . $tf, $tp),
        ];
    }

    public function getReconciliations(?string $status = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT r.*, fe.entity_name as from_entity_name, te.entity_name as to_entity_name
                FROM inter_company_reconciliations r
                INNER JOIN inter_company_entities fe ON fe.id = r.from_entity_id
                INNER JOIN inter_company_entities te ON te.id = r.to_entity_id
                WHERE 1=1" . ($tid > 1 ? " AND r.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($status) { $sql .= " AND r.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY r.reconciliation_date DESC, r.id DESC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function createReconciliation(array $data): int
    {
        $tid = $this->tenantId();
        $recNumber = $data['reconciliation_number'] ?? ('ICR-' . date('Ymd') . '-' . mt_rand(1000, 9999));
        $data = array_merge($data, [
            'tenant_id' => $tid,
            'reconciliation_number' => $recNumber,
            'status' => 'open',
        ]);
        $this->db->insert('inter_company_reconciliations', $data);
        return (int)$this->db->lastInsertId();
    }

    public function getUnreconciledPosted(string $fromEntity, string $toEntity, string $periodStart, string $periodEnd): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT t.*
                FROM inter_company_transactions t
                WHERE t.status = 'posted'
                  AND t.from_entity_id = ?
                  AND t.to_entity_id = ?
                  AND t.transaction_date >= ?
                  AND t.transaction_date <= ?" . ($tid > 1 ? " AND t.tenant_id = ?" : "") . "
                ORDER BY t.transaction_date ASC, t.id ASC";
        $params = [$fromEntity, $toEntity, $periodStart, $periodEnd];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchAll($sql, $params) ?: [];
    }
}

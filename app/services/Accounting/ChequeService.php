<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Cheque Service
 * Handles cheque/DD register (issue / clear / bounce)
 */
class ChequeService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function issueCheque(array $data): int
    {
        $tid = TenantContext::getId();

        // If using voucher-style with bank account
        $bankId = $data['bank_account_id'] ?? null;
        if ($bankId) {
            $bank = $this->db->fetchOne("SELECT current_balance FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$data['bank_account_id'], $tid] : [$data['bank_account_id']]);
            if (!$bank) throw new Exception('Bank account not found');
        }

        $payload = [
            'cheque_number'      => trim($data['cheque_number'] ?? ''),
            'cheque_date'        => $data['cheque_date'] ?? date('Y-m-d'),
            'amount'             => (float)($data['amount'] ?? 0),
            'payee_name'         => $data['payee_name'] ?? null,
            'purpose'            => $data['purpose'] ?? null,
            'bank_account_id'    => $data['bank_account_id'] ?? null,
            'status'             => 'issued',
            'issued_by'          => $data['issued_by'] ?? null,
            'cleared_date'       => null,
            'bounce_reason'      => null,
            'voucher_number'     => $data['voucher_number'] ?? null,
            'tenant_id'          => $tid,
        ];
        $this->db->insert('cheque_register', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function markChequeCleared(int $id, string $date): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE cheque_register SET status = 'cleared', cleared_date = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$date, $id], $tid > 1 ? [$tid] : []));
    }

    public function markChequeBounced(int $id, string $reason): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE cheque_register SET status = 'bounced', bounce_reason = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$reason, $id], $tid > 1 ? [$tid] : []));
    }

    public function markChequeStatus(int $id, string $status, string $reason = ''): bool
    {
        $tid = TenantContext::getId();
        $allowed = ['issued', 'cleared', 'bounced', 'cancelled', 'stale'];
        if (!in_array($status, $allowed)) {
            throw new Exception('Invalid cheque status');
        }
        $sql = "UPDATE cheque_register SET status = ?, bounce_reason = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$status, $reason, $id];
        if ($tid > 1) $params[] = $tid;
        return $this->db->execute($sql, $params);
    }

    public function issueChequeWithVoucher(array $data): int
    {
        // Wrapper for issueCheque with voucher generation
        return $this->issueCheque($data);
    }

    public function getChequeRegister(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1";
        $params = [];

        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['bank_account_id'])) {
            $where .= " AND bank_account_id = ?";
            $params[] = $filters['bank_account_id'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND cheque_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND cheque_date <= ?";
            $params[] = $filters['to_date'];
        }
        if (!empty($filters['payee_name'])) {
            $where .= " AND payee_name LIKE ?";
            $params[] = '%' . $filters['payee_name'] . '%';
        }

        $where .= " ORDER BY cheque_date DESC, id DESC";
        return $this->db->fetchAll("SELECT * FROM cheque_register $where", $params) ?: [];
    }

    public function getChequeById(int $id): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM cheque_register WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]) ?: null;
    }

    public function getChequeSummary(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1";
        $params = [];

        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['bank_account_id'])) {
            $where .= " AND bank_account_id = ?";
            $params[] = $filters['bank_account_id'];
        }

        $sql = "SELECT status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount FROM cheque_register $where GROUP BY status";
        return $this->db->fetchAll($sql, $params) ?: [];
    }
}
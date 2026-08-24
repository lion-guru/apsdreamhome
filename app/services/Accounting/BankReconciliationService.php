<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Bank Reconciliation Service
 * Handles bank reconciliation (statement vs book)
 */
class BankReconciliationService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function createReconciliation(int $bankAccountId, array $data): int
    {
        $tid = TenantContext::getId();

        $payload = [
            'bank_account_id'     => $bankAccountId,
            'statement_date'      => $data['statement_date'] ?? date('Y-m-d'),
            'statement_balance'   => (float)($data['statement_balance'] ?? 0),
            'book_balance'        => (float)($data['book_balance'] ?? 0),
            'difference'          => (float)($data['statement_balance'] ?? 0) - (float)($data['book_balance'] ?? 0),
            'status'              => 'open',
            'reconciled_by'       => $data['reconciled_by'] ?? null,
            'notes'               => $data['notes'] ?? null,
            'tenant_id'           => $tid,
        ];
        $this->db->insert('bank_reconciliations', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function getReconciliationItems(int $reconciliationId): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM reconciliation_items WHERE reconciliation_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchAll($sql, $tid > 1 ? [$reconciliationId, $tid] : [$reconciliationId]) ?: [];
    }

    public function getReconciliations(?int $bankAccountId = null): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if ($bankAccountId) {
            $where .= " AND bank_account_id = ?";
            $params[] = $bankAccountId;
        }
        $where .= " ORDER BY statement_date DESC";
        return $this->db->fetchAll("SELECT * FROM bank_reconciliations $where", $params) ?: [];
    }

    public function startBankReconciliation(int $bankAccountId, array $data): int
    {
        return $this->createReconciliation($bankAccountId, $data);
    }

    public function matchTransaction(int $itemId, string $status, ?int $cashBookId = null): bool
    {
        $tid = TenantContext::getId();
        $allowed = ['matched', 'unmatched', 'bank_only', 'book_only'];
        if (!in_array($status, $allowed)) {
            throw new Exception('Invalid reconciliation status');
        }
        $sql = "UPDATE reconciliation_items SET status = ?, cash_book_id = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$status, $cashBookId, $itemId];
        if ($tid > 1) $params[] = $tid;
        return $this->db->execute($sql, $params);
    }

    public function completeReconciliation(int $id): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE bank_reconciliations SET status = 'completed', reconciled_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, $tid > 1 ? [$id, $tid] : [$id]);
    }

    public function getReconciliationItemsForBank(int $bankAccountId, string $fromDate, string $toDate): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM reconciliation_items ri
                JOIN bank_reconciliations br ON ri.reconciliation_id = br.id
                WHERE br.bank_account_id = ? AND br.statement_date BETWEEN ? AND ?" . ($tid > 1 ? " AND br.tenant_id = ?" : "");
        $params = [$bankAccountId, $fromDate, $toDate];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchAll($sql, $params) ?: [];
    }
}
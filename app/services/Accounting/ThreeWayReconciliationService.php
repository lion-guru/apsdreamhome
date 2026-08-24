<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Three-Way Reconciliation Service
 * Specialized service for trust account reconciliation (bank vs book vs trust ledger)
 */
class ThreeWayReconciliationService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function reconcile(int $trustAccountId, ?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $tid = TenantContext::getId();

        // 1. Bank Statement Balance
        $bankStmt = $this->db->fetchOne("SELECT current_balance FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$trustAccountId, $tid] : [$trustAccountId]);
        $bankBalance = $bankStmt ? (float)$bankStmt['current_balance'] : 0.0;

        // 2. Book Balance (Cash Book)
        $bookStmt = $this->db->fetchOne("
            SELECT COALESCE(SUM(CASE WHEN transaction_type = 'receipt' THEN amount ELSE -amount END), 0) AS balance
            FROM cash_book WHERE bank_account_id = ? AND transaction_date <= ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$trustAccountId, $asOfDate], $tid > 1 ? [$tid] : [])
        );
        $bookBalance = $bookStmt ? (float)$bookStmt['balance'] : 0.0;

        // 3. Trust Ledger Balance (client funds held)
        $trustStmt = $this->db->fetchOne("
            SELECT COALESCE(SUM(amount), 0) AS balance
            FROM trust_ledger WHERE account_id = ? AND entry_date <= ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$trustAccountId, $asOfDate], $tid > 1 ? [$tid] : [])
        );
        $trustBalance = $trustStmt ? (float)$trustStmt['balance'] : 0.0;

        $discrepancy = $bankBalance - $bookBalance - $trustBalance;

        return [
            'as_of_date'      => $asOfDate,
            'trust_account_id'=> $trustAccountId,
            'bank_balance'    => round($bankBalance, 2),
            'book_balance'    => round($bookBalance, 2),
            'trust_balance'   => round($trustBalance, 2),
            'discrepancy'     => round($discrepancy, 2),
            'reconciled'      => abs($discrepancy) < 0.01,
        ];
    }

    public function getReconciliationHistory(int $trustAccountId, int $limit = 12): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM three_way_reconciliation_log WHERE trust_account_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY reconciled_at DESC LIMIT ?";
        $params = array_merge([$trustAccountId], $tid > 1 ? [$tid] : [], [$limit]);
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function logReconciliation(int $trustAccountId, array $result): void
    {
        $tid = TenantContext::getId();
        $this->db->insert('three_way_reconciliation_log', [
            'trust_account_id' => $trustAccountId,
            'bank_balance'     => $result['bank_balance'],
            'book_balance'     => $result['book_balance'],
            'trust_balance'    => $result['trust_balance'],
            'discrepancy'      => $result['discrepancy'],
            'reconciled'       => $result['reconciled'] ? 1 : 0,
            'reconciled_at'    => date('Y-m-d H:i:s'),
            'tenant_id'        => $tid,
        ]);
    }
}
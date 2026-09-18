<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Cash Book Service
 * Handles cash transactions, petty cash, and daily cash book operations
 */
class CashBookService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    // ============================================================
    //  CASH BOOK (auto-creates journal entry)
    // ============================================================

    public function recordCashTransaction(array $data): array
    {
        $type     = $data['transaction_type'] ?? 'receipt';
        $amount   = (float)($data['amount'] ?? 0);
        $bankId   = !empty($data['bank_account_id']) ? (int)$data['bank_account_id'] : null;
        $party    = $data['party_name'] ?? null;
        $narr     = $data['narration'] ?? '';
        $txnDate  = $data['transaction_date'] ?? date('Y-m-d');

        if ($amount <= 0) {
            throw new Exception('Amount must be positive');
        }
        if (!in_array($type, ['receipt', 'payment', 'contra', 'journal'])) {
            throw new Exception('Invalid transaction type');
        }

        $tid = TenantContext::getId();

        // Update bank balance if bank_id provided
        if ($bankId) {
            $bank = $this->db->fetchOne("SELECT current_balance FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$bankId, $tid] : [$bankId]);
            if (!$bank) throw new Exception('Bank account not found');

            $newBal = $bank['current_balance'] + ($type === 'receipt' ? $amount : -$amount);
            $this->db->execute("UPDATE bank_accounts_master SET current_balance = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), array_merge([$newBal, $bankId], $tid > 1 ? [$tid] : []));
        }

        // Insert cash book entry
        $cb = [
            'transaction_type' => $type,
            'amount'           => $amount,
            'bank_account_id'  => $bankId,
            'party_name'       => $party,
            'narration'        => $narr,
            'transaction_date' => $txnDate,
            'tenant_id'        => $tid,
        ];
        try {
            $this->db->insert('cash_book_entries', [
                'entry_date'     => $txnDate,
                'type'           => $type === 'receipt' ? 'credit' : 'debit',
                'amount'         => $amount,
                'description'    => ($party ? $party . ' - ' : '') . $narr,
                'reference_type' => 'cash_book',
                'payment_mode'   => 'cash',
                'created_by'     => null,
                'tenant_id'      => $tid,
            ]);
            $cbId = (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('CashBookService::recordCashTransaction insert error: ' . $e->getMessage());
            throw new \Exception('Failed to record cash transaction: ' . $e->getMessage());
        }

        // Auto-create journal entry for double-entry
        $this->createJournalForCashBook($cbId, $type, $amount, $bankId, $party, $narr, $txnDate);

        return ['success' => true, 'cash_book_id' => $cbId];
    }

    private function createJournalForCashBook(int $cbId, string $type, float $amount, ?int $bankId, ?string $party, string $narr, string $txnDate): void
    {
        $tid = TenantContext::getId();

        // Simple journal: Cash/Bank <-> Party/Revenue/Expense
        $entries = [];
        if ($bankId) {
            $entries[] = ['account_type' => 'bank', 'account_id' => $bankId, 'debit' => $type === 'receipt' ? $amount : 0, 'credit' => $type === 'payment' ? $amount : 0];
        } else {
            $entries[] = ['account_type' => 'cash', 'account_id' => 0, 'debit' => $type === 'receipt' ? $amount : 0, 'credit' => $type === 'payment' ? $amount : 0];
        }
        $entries[] = ['account_type' => 'party', 'account_id' => 0, 'debit' => $type === 'payment' ? $amount : 0, 'credit' => $type === 'receipt' ? $amount : 0];

        $je = [
            'entry_date'     => $txnDate,
            'narration'      => $narr . ' (Cash Book #' . $cbId . ')',
            'reference_type' => 'cash_book',
            'reference_id'   => $cbId,
            'tenant_id'      => TenantContext::getId(),
        ];
        $this->db->insert('journal_entries', $je);
        $jeId = (int)$this->db->lastInsertId();

        foreach ($entries as $e) {
            $e['journal_entry_id'] = $jeId;
            $e['tenant_id'] = $tid;
            $this->db->insert('journal_entry_lines', $e);
        }
    }

    // ============================================================
    //  PETTY CASH
    // ============================================================

    public function topupPettyCash(float $amount, array $data = []): int
    {
        $tid = TenantContext::getId();
        $cb = [
            'transaction_type' => 'receipt',
            'amount'           => $amount,
            'bank_account_id'  => $data['bank_account_id'] ?? null,
            'party_name'       => 'Petty Cash Top-up',
            'narration'        => $data['narration'] ?? 'Petty cash top-up',
            'transaction_date' => $data['transaction_date'] ?? date('Y-m-d'),
            'tenant_id'        => $tid,
        ];
        try {
            $this->db->insert('cash_book_entries', [
                'entry_date'     => $cb['transaction_date'],
                'type'           => 'credit',
                'amount'         => $amount,
                'description'    => $cb['party_name'] . ' - ' . $cb['narration'],
                'reference_type' => 'petty_cash',
                'payment_mode'   => 'cash',
                'created_by'     => null,
                'tenant_id'      => $tid,
            ]);
            $cbId = (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('CashBookService::topupPettyCash insert error: ' . $e->getMessage());
            throw new \Exception('Failed to record petty cash top-up: ' . $e->getMessage());
        }

        $pc = [
            'type'            => 'topup',
            'amount'          => $amount,
            'cash_book_id'    => $cbId,
            'narration'       => $data['narration'] ?? 'Petty cash top-up',
            'transaction_date'=> $data['transaction_date'] ?? date('Y-m-d'),
            'tenant_id'       => $tid,
        ];
        $this->db->insert('petty_cash', $pc);
        return (int)$this->db->lastInsertId();
    }

    public function recordPettyExpense(array $data): int
    {
        $tid = TenantContext::getId();
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) throw new Exception('Amount must be positive');

        $pc = [
            'type'            => 'expense',
            'amount'          => $amount,
            'category'        => $data['category'] ?? 'general',
            'narration'       => $data['narration'] ?? '',
            'receipt_path'    => $data['receipt_path'] ?? null,
            'transaction_date'=> $data['transaction_date'] ?? date('Y-m-d'),
            'tenant_id'       => $tid,
        ];
        $this->db->insert('petty_cash', $pc);
        return (int)$this->db->lastInsertId();
    }

    public function getPettyCashBalance(): float
    {
        $tid = TenantContext::getId();
        $sql = "SELECT COALESCE(SUM(CASE WHEN type = 'topup' THEN amount ELSE -amount END), 0) AS balance FROM petty_cash WHERE tenant_id = ?";
        $row = $this->db->fetchOne($sql, [$tid]);
        return $row ? (float)$row['balance'] : 0.0;
    }

    // ============================================================
    //  DAILY CASH BOOK REPORTS
    // ============================================================

    public function getDailyCashBook(string $fromDate, string $toDate, ?int $bankAccountId = null): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE entry_date BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];
        if ($bankAccountId) {
            $where .= " AND reference_id = ?";
            $params[] = $bankAccountId;
        }
        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        } else {
            $where .= " AND (tenant_id = 1 OR tenant_id IS NULL)";
        }
        try {
            return $this->db->fetchAll("SELECT * FROM cash_book_entries $where ORDER BY entry_date, id", $params) ?: [];
        } catch (\Throwable $e) {
            error_log('CashBookService::getDailyCashBook error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCashBookSummary(string $fromDate, string $toDate): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$fromDate, $toDate];
        if ($tid > 1) $params[] = $tid;

        try {
            $cbWhere = "AND entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $cbParams = $params;
            $receipts = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_book_entries WHERE type = 'credit' $cbWhere", $cbParams);
            $payments = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_book_entries WHERE type = 'debit' $cbWhere", $cbParams);

            return [
                'total_receipts' => (float)($receipts['total'] ?? 0),
                'total_payments' => (float)($payments['total'] ?? 0),
                'total_contras'  => 0.0,
                'net_flow'       => (float)($receipts['total'] ?? 0) - (float)($payments['total'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log('CashBookService::getCashBookSummary error: ' . $e->getMessage());
            return ['total_receipts' => 0.0, 'total_payments' => 0.0, 'total_contras' => 0.0, 'net_flow' => 0.0];
        }
    }
}
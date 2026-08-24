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
        $this->db->insert('cash_book', $cb);
        $cbId = (int)$this->db->lastInsertId();

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
        $this->db->insert('cash_book', $cb);
        $cbId = (int)$this->db->lastInsertId();

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
        $where = "WHERE transaction_date BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];
        if ($bankAccountId) {
            $where .= " AND bank_account_id = ?";
            $params[] = $bankAccountId;
        }
        if ($tid > 1) {
            $where .= " AND tenant_id = ?";
            $params[] = $tid;
        } else {
            $where .= " AND (tenant_id = 1 OR tenant_id IS NULL)";
        }
        return $this->db->fetchAll("SELECT * FROM cash_book $where ORDER BY transaction_date, id", $params) ?: [];
    }

    public function getCashBookSummary(string $fromDate, string $toDate): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE transaction_date BETWEEN ? AND ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$fromDate, $toDate];
        if ($tid > 1) $params[] = $tid;

        $receipts = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_book WHERE transaction_type = 'receipt' $where", $params);
        $payments = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_book WHERE transaction_type = 'payment' $where", $params);
        $contras  = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_book WHERE transaction_type = 'contra' $where", $params);

        return [
            'total_receipts' => (float)($receipts['total'] ?? 0),
            'total_payments' => (float)($payments['total'] ?? 0),
            'total_contras'  => (float)($contras['total'] ?? 0),
            'net_flow'       => (float)($receipts['total'] ?? 0) - (float)($payments['total'] ?? 0),
        ];
    }
}
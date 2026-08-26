<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Journal Entry Service
 * Handles double-entry journal entries and general ledger
 */
class JournalEntryService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function postJournalEntry(array $data): int
    {
        $tid = TenantContext::getId();

        // Validate balanced entry
        $lines = $data['lines'] ?? [];
        $totalDebit = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new Exception('Journal entry must balance: Debits (' . $totalDebit . ') != Credits (' . $totalCredit . ')');
        }

        $je = [
            'journal_number' => $data['journal_number'] ?? ('JE-' . date('YmdHis')),
            'entry_date'     => $data['entry_date'] ?? date('Y-m-d'),
            'narration'      => $data['narration'] ?? '',
            'reference_type' => $data['reference_type'] ?? 'manual',
            'reference_id'   => $data['reference_id'] ?? null,
            'tenant_id'      => $tid,
        ];
        // Map to actual journal_entries schema
        $je['description']      = $je['narration'];
        $je['source_document']  = $je['reference_type'];
        $je['source_id']        = $je['reference_id'];
        unset($je['narration'], $je['reference_type'], $je['reference_id']);
        $this->db->insert('journal_entries', $je);
        $jeId = (int)$this->db->lastInsertId();

        foreach ($lines as $line) {
            if (isset($line['debit']) && !isset($line['debit_amount'])) {
                $line['debit_amount'] = $line['debit'];
                unset($line['debit']);
            }
            if (isset($line['credit']) && !isset($line['credit_amount'])) {
                $line['credit_amount'] = $line['credit'];
                unset($line['credit']);
            }
            $line['journal_entry_id'] = $jeId;
            $line['tenant_id'] = $tid;
            $this->db->insert('journal_entry_lines', $line);
        }

        return $jeId;
    }

    public function getLedger(int $accountId, string $fromDate, string $toDate): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT jel.*, je.entry_date, je.description as narration, je.source_document as reference_type, je.source_id as reference_id
                FROM journal_entry_lines jel
                JOIN journal_entries je ON je.id = jel.journal_entry_id
                WHERE jel.account_id = ? AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "");
        $params = [$accountId, $fromDate, $toDate];
        if ($tid > 1) $params[] = $tid;
        $sql .= " ORDER BY je.entry_date, je.id, jel.id";
        return $this->db->fetchAll($sql, array_merge([$accountId, $fromDate, $toDate], $tid > 1 ? [$tid] : [])) ?: [];
    }

    public function getTrialBalance(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $tid = TenantContext::getId();

        // Get all accounts with balances
        $sql = "SELECT
                    jel.account_id,
                    coa.account_type,
                    coa.account_name,
                    COALESCE(SUM(CASE WHEN je.entry_date <= ? THEN jel.debit_amount ELSE 0 END), 0) AS total_debit,
                    COALESCE(SUM(CASE WHEN je.entry_date <= ? THEN jel.credit_amount ELSE 0 END), 0) AS total_credit
                FROM journal_entry_lines jel
                JOIN journal_entries je ON je.id = jel.journal_entry_id
                JOIN chart_of_accounts coa ON coa.id = jel.account_id
                WHERE je.entry_date <= ?
                " . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
                " GROUP BY jel.account_id, coa.account_type, coa.account_name
                HAVING ABS(COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)) > 0.01
                ORDER BY coa.account_type, coa.account_name";

        $params = [$asOfDate, $asOfDate, $asOfDate];
        if ($tid > 1) $params[] = $tid;

        $rows = $this->db->fetchAll($sql, $params) ?: [];

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($rows as &$row) {
            $row['balance'] = (float)$row['total_debit'] - (float)$row['total_credit'];
            $totalDebit += (float)$row['total_debit'];
            $totalCredit += (float)$row['total_credit'];
        }

        return [
            'as_of_date'    => $asOfDate,
            'accounts'      => $rows,
            'total_debit'   => round($totalDebit, 2),
            'total_credit'  => round($totalCredit, 2),
            'balanced'      => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }
}
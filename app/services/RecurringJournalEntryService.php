<?php
/**
 * RecurringJournalEntryService — Recurring Journal Entry templates + auto-posting (Gap 27).
 *
 * Templates live in recurring_journal_entries + recurring_journal_entry_lines.
 * A cron (processDue) fires every template whose next_execution_date <= today,
 * posting a real journal_entry via JournalEntryService::postJournalEntry and
 * advancing next_execution_date by the template frequency.
 */
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\Accounting\JournalEntryService;
use Exception;

class RecurringJournalEntryService
{
    private $db;
    private $jeService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->jeService = new JournalEntryService();
    }

    /* ---------------------------------------------------------------- CRUD */

    public function getAll(?string $status = null): array
    {
        $tid = $this->tenantId();
        $params = [$tid];
        $where = "r.tenant_id = ?";
        if ($status) {
            $where .= " AND r.is_active = ?";
            $params[] = ($status === 'active' ? 1 : 0);
        }
        $sql = "SELECT r.*, COUNT(l.id) AS line_count
                FROM recurring_journal_entries r
                LEFT JOIN recurring_journal_entry_lines l ON l.recurring_je_id = r.id
                WHERE $where
                GROUP BY r.id
                ORDER BY r.next_execution_date ASC, r.template_name ASC";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        $rje = $this->db->fetchOne(
            "SELECT * FROM recurring_journal_entries WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        );
        if (!$rje) {
            return null;
        }
        $rje['lines'] = $this->db->fetchAll(
            "SELECT * FROM recurring_journal_entry_lines WHERE recurring_je_id = ? ORDER BY line_order ASC, id ASC",
            [$id]
        ) ?: [];
        return $rje;
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $userId = $_SESSION['user_id'] ?? 1;

        $frequency = !empty($data['frequency']) ? $data['frequency'] : 'monthly';
        $startDate = !empty($data['start_date']) ? $data['start_date'] : date('Y-m-d');
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;
        $nextExec = $this->nextExecutionDate($startDate, $frequency);

        $this->db->beginTransaction();
        try {
            $rjeId = (int)$this->db->insert('recurring_journal_entries', [
                'tenant_id' => $tid,
                'template_name' => $data['template_name'] ?? '',
                'description' => $data['description'] ?? null,
                'entry_type' => $data['entry_type'] ?? 'manual',
                'frequency' => $frequency,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'next_execution_date' => $nextExec,
                'last_execution_date' => null,
                'is_active' => !empty($data['is_active']) ? 1 : 0,
                'execution_count' => 0,
                'max_executions' => !empty($data['max_executions']) ? (int)$data['max_executions'] : null,
                'created_by' => $userId,
            ]);

            $lines = $data['lines'] ?? [];
            if (empty($lines)) {
                throw new Exception('At least one journal line is required');
            }
            $totalDebit = 0.0;
            $totalCredit = 0.0;
            $lineOrder = 0;
            foreach ($lines as $line) {
                $debit = (float)($line['debit_amount'] ?? 0);
                $credit = (float)($line['credit_amount'] ?? 0);
                $totalDebit += $debit;
                $totalCredit += $credit;
                $lineOrder++;
                $this->db->insert('recurring_journal_entry_lines', [
                    'tenant_id' => $tid,
                    'recurring_je_id' => $rjeId,
                    'account_id' => (int)($line['account_id'] ?? 0),
                    'debit_amount' => $debit,
                    'credit_amount' => $credit,
                    'description' => $line['description'] ?? null,
                    'line_order' => $lineOrder,
                ]);
            }
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new Exception('Recurring JE must balance: Debits (' . number_format($totalDebit, 2) . ') != Credits (' . number_format($totalCredit, 2) . ')');
            }
            $this->db->commit();
            return $rjeId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $tid = $this->tenantId();
        $rje = $this->db->fetchOne(
            "SELECT id FROM recurring_journal_entries WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        );
        if (!$rje) {
            return false;
        }
        $update = [];
        foreach (['template_name', 'description', 'entry_type', 'is_active', 'max_executions'] as $f) {
            if (array_key_exists($f, $data)) {
                $update[$f] = $data[$f];
            }
        }
        if (!empty($data['frequency'])) {
            $update['frequency'] = $data['frequency'];
        }
        if (!empty($data['start_date'])) {
            $update['start_date'] = $data['start_date'];
        }
        if (array_key_exists('end_date', $data)) {
            $update['end_date'] = !empty($data['end_date']) ? $data['end_date'] : null;
        }
        if (empty($update)) {
            return true;
        }
        if (!empty($data['frequency']) || !empty($data['start_date'])) {
            $update['next_execution_date'] = $this->nextExecutionDate(
                $update['start_date'] ?? $rje['start_date'],
                $update['frequency'] ?? $rje['frequency']
            );
        }
        $this->db->beginTransaction();
        try {
            $this->db->update('recurring_journal_entries', $update, ['id' => $id]);
            // Replace lines
            $this->db->delete('recurring_journal_entry_lines', ['recurring_je_id' => $id]);
            $lines = $data['lines'] ?? [];
            $lineOrder = 0;
            foreach ($lines as $line) {
                $lineOrder++;
                $this->db->insert('recurring_journal_entry_lines', [
                    'tenant_id' => $tid,
                    'recurring_je_id' => $id,
                    'account_id' => (int)($line['account_id'] ?? 0),
                    'debit_amount' => (float)($line['debit_amount'] ?? 0),
                    'credit_amount' => (float)($line['credit_amount'] ?? 0),
                    'description' => $line['description'] ?? null,
                    'line_order' => $lineOrder,
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $tid = $this->tenantId();
        $rje = $this->db->fetchOne(
            "SELECT id FROM recurring_journal_entries WHERE id = ? AND tenant_id = ?",
            [$id, $tid]
        );
        if (!$rje) {
            return false;
        }
        $this->db->delete('recurring_journal_entry_lines', ['recurring_je_id' => $id]);
        return (bool)$this->db->delete('recurring_journal_entries', ['id' => $id]);
    }

    /* ---------------------------------------------------------------- Execution */

    /**
     * Post a real journal entry from this recurring template.
     * Returns the new journal_entry id.
     */
    public function execute(int $id): int
    {
        $tid = $this->tenantId();
        $rje = $this->getById($id);
        if (!$rje) {
            throw new Exception('Recurring journal entry not found');
        }
        if (!$rje['is_active']) {
            throw new Exception('Recurring journal entry is not active');
        }
        $now = date('Y-m-d');
        if ($rje['end_date'] && $now > $rje['end_date']) {
            throw new Exception('Recurring journal entry has expired');
        }
        if ($rje['max_executions'] !== null && $rje['execution_count'] >= $rje['max_executions']) {
            throw new Exception('Recurring journal entry has reached max executions');
        }

        // Build lines for the real journal entry
        $lines = [];
        foreach ($rje['lines'] as $line) {
            $lines[] = [
                'account_id' => $line['account_id'],
                'debit' => (float)$line['debit_amount'],
                'credit' => (float)$line['credit_amount'],
                'description' => $line['description'] ?? $rje['template_name'],
            ];
        }

        $jeId = $this->jeService->postJournalEntry([
            'journal_number' => 'RJE-' . $rje['template_name'] . '-' . date('YmdHis'),
            'entry_date' => $now,
            'narration' => 'Recurring JE: ' . $rje['template_name'] . ' (' . $rje['description'] ?? '' . ')',
            'reference_type' => 'system',
            'reference_id' => $rje['id'],
            'lines' => $lines,
        ]);

        // Advance execution tracking
        $nextExec = $this->nextExecutionDate($rje['next_execution_date'], $rje['frequency']);
        $newCount = (int)$rje['execution_count'] + 1;
        $update = [
            'last_execution_date' => $now,
            'execution_count' => $newCount,
        ];
        // Stop if max reached or past end date
        $shouldDeactivate = false;
        if ($rje['end_date'] && $nextExec > $rje['end_date']) {
            $shouldDeactivate = true;
        }
        if ($rje['max_executions'] !== null && $newCount >= $rje['max_executions']) {
            $shouldDeactivate = true;
        }
        if ($shouldDeactivate) {
            $update['is_active'] = 0;
        } else {
            $update['next_execution_date'] = $nextExec;
        }
        $this->db->update('recurring_journal_entries', $update, ['id' => $id]);

        return $jeId;
    }

    /**
     * Cron entry point: execute all due recurring journal entries.
     * Returns summary stats.
     */
    public function processDue(): array
    {
        $tid = $this->tenantId();
        $today = date('Y-m-d');
        $due = $this->db->fetchAll(
            "SELECT id FROM recurring_journal_entries
             WHERE tenant_id = ? AND is_active = 1
               AND next_execution_date <= ?
               AND (end_date IS NULL OR end_date >= ?)
               AND (max_executions IS NULL OR execution_count < max_executions)
             ORDER BY next_execution_date ASC",
            [$tid, $today, $today]
        ) ?: [];

        $processed = 0;
        $failed = [];
        foreach ($due as $row) {
            try {
                $this->execute((int)$row['id']);
                $processed++;
            } catch (Exception $e) {
                $failed[] = ['id' => (int)$row['id'], 'error' => $e->getMessage()];
            }
        }
        return [
            'processed' => $processed,
            'failed' => count($failed),
            'failures' => $failed,
            'processed_at' => $today,
        ];
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $total = (int)$this->db->fetchOne(
            "SELECT COUNT(*) FROM recurring_journal_entries WHERE tenant_id = ?",
            [$tid]
        )[''] ?? 0;
        $active = (int)$this->db->fetchOne(
            "SELECT COUNT(*) FROM recurring_journal_entries WHERE tenant_id = ? AND is_active = 1",
            [$tid]
        )[''] ?? 0;
        $dueToday = (int)$this->db->fetchOne(
            "SELECT COUNT(*) FROM recurring_journal_entries
             WHERE tenant_id = ? AND is_active = 1
               AND next_execution_date <= ?
               AND (end_date IS NULL OR end_date >= ?)",
            [$tid, date('Y-m-d'), date('Y-m-d')]
        )[''] ?? 0;
        return ['total' => $total, 'active' => $active, 'due_today' => $dueToday];
    }

    /* ---------------------------------------------------------------- Helpers */

    private function nextExecutionDate(string $fromDate, string $frequency): string
    {
        $ts = strtotime($fromDate);
        switch ($frequency) {
            case 'weekly':   $ts = strtotime('+1 week', $ts); break;
            case 'quarterly': $ts = strtotime('+3 months', $ts); break;
            case 'yearly':   $ts = strtotime('+1 year', $ts); break;
            case 'monthly':
            default:         $ts = strtotime('+1 month', $ts); break;
        }
        return date('Y-m-d', $ts);
    }

    private function tenantId(): int
    {
        return (int)TenantContext::getId();
    }
}

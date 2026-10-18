<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;
use Exception;

class RecurringTransactionService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function getAll(?string $status = null): array
    {
        $tid = $this->tenantId();
        $sql = "SELECT rt.*, u.name as user_name, v.name as vendor_name
                FROM recurring_transactions rt
                LEFT JOIN users u ON u.id = rt.user_id
                LEFT JOIN vendors v ON v.id = rt.vendor_id
                WHERE 1=1" . ($tid > 1 ? " AND rt.tenant_id = ?" : "");
        $params = [];
        if ($tid > 1) $params[] = $tid;
        if ($status === 'active') { $sql .= " AND rt.is_active = 1"; }
        elseif ($status === 'inactive') { $sql .= " AND rt.is_active = 0"; }
        elseif ($status === 'due') { $sql .= " AND rt.is_active = 1 AND rt.next_execution_date <= CURDATE()"; }
        $sql .= " ORDER BY rt.next_execution_date, rt.id";
        return $this->db->fetchAll($sql, $params) ?: [];
    }

    public function getById(int $id): ?array
    {
        $tid = $this->tenantId();
        $sql = "SELECT rt.*, u.name as user_name, v.name as vendor_name
                FROM recurring_transactions rt
                LEFT JOIN users u ON u.id = rt.user_id
                LEFT JOIN vendors v ON v.id = rt.vendor_id
                WHERE rt.id = ?" . ($tid > 1 ? " AND rt.tenant_id = ?" : "");
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchOne($sql, $params);
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $insertData = [
            'tenant_id' => $tid,
            'transaction_type' => $data['transaction_type'],
            'user_id' => !empty($data['user_id']) ? (int)$data['user_id'] : null,
            'vendor_id' => !empty($data['vendor_id']) ? (int)$data['vendor_id'] : null,
            'bank_account_id' => !empty($data['bank_account_id']) ? (int)$data['bank_account_id'] : null,
            'amount' => (float)$data['amount'],
            'frequency' => $data['frequency'] ?? 'monthly',
            'start_date' => $data['start_date'],
            'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
            'next_execution_date' => $data['start_date'],
            'last_execution_date' => null,
            'payment_method' => $data['payment_method'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'execution_count' => 0,
            'max_executions' => !empty($data['max_executions']) ? (int)$data['max_executions'] : null,
        ];
        $this->db->insert('recurring_transactions', $insertData);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['transaction_type','user_id','vendor_id','bank_account_id','amount','frequency','start_date','end_date','payment_method','reference_id','description','is_active','max_executions'];
        $updateData = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) $updateData[$f] = $data[$f];
        }
        if (isset($updateData['user_id'])) $updateData['user_id'] = !empty($updateData['user_id']) ? (int)$updateData['user_id'] : null;
        if (isset($updateData['vendor_id'])) $updateData['vendor_id'] = !empty($updateData['vendor_id']) ? (int)$updateData['vendor_id'] : null;
        if (isset($updateData['bank_account_id'])) $updateData['bank_account_id'] = !empty($updateData['bank_account_id']) ? (int)$updateData['bank_account_id'] : null;
        if (isset($updateData['amount'])) $updateData['amount'] = (float)$updateData['amount'];
        if (isset($updateData['max_executions'])) $updateData['max_executions'] = !empty($updateData['max_executions']) ? (int)$updateData['max_executions'] : null;
        if (isset($updateData['is_active'])) $updateData['is_active'] = (int)$updateData['is_active'];
        if (empty($updateData)) return false;
        return $this->db->update('recurring_transactions', $updateData, 'id = ?', [$id]);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('recurring_transactions', 'id = ?', [$id]);
    }

    public function getDueTransactions(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll(
            "SELECT * FROM recurring_transactions
             WHERE is_active = 1 AND next_execution_date <= CURDATE()
             AND (end_date IS NULL OR end_date >= CURDATE())
             AND (max_executions IS NULL OR execution_count < max_executions)" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
             ORDER BY next_execution_date",
            $tid > 1 ? [$tid] : []
        ) ?: [];
    }

    public function execute(int $id, int $userId): int
    {
        $tid = $this->tenantId();
        $txn = $this->getById($id);
        if (!$txn) throw new Exception('Recurring transaction not found');
        if (!$txn['is_active']) throw new Exception('Transaction is inactive');
        if ($txn['next_execution_date'] > date('Y-m-d')) throw new Exception('Not yet due for execution');
        if ($txn['end_date'] && $txn['end_date'] < date('Y-m-d')) throw new Exception('Transaction has ended');
        if ($txn['max_executions'] && $txn['execution_count'] >= $txn['max_executions']) throw new Exception('Max executions reached');

        $jeService = new \App\Services\Accounting\JournalEntryService();
        $monthStr = date('Y-m');
        $jeData = [
            'journal_number' => 'JE-REC-' . date('YmdHis'),
            'entry_date' => date('Y-m-d'),
            'narration' => 'Recurring: ' . ($txn['description'] ?? $txn['transaction_type']) . ' (#' . $txn['id'] . ')',
            'reference_type' => 'recurring',
            'reference_id' => $txn['id'],
            'lines' => [
                [
                    'account_id' => $this->getAccountForType($txn['transaction_type']),
                    'debit' => (float)$txn['amount'],
                    'credit' => 0,
                    'description' => 'Debit for ' . $txn['transaction_type'],
                ],
                [
                    'account_id' => $this->getCounterAccountForType($txn['transaction_type']),
                    'debit' => 0,
                    'credit' => (float)$txn['amount'],
                    'description' => 'Credit for ' . $txn['transaction_type'],
                ],
            ],
        ];
        $jeId = $jeService->postJournalEntry($jeData);

        $nextDate = $this->calculateNextDate($txn['next_execution_date'], $txn['frequency']);
        $newCount = (int)$txn['execution_count'] + 1;
        $isActive = ($txn['max_executions'] && $newCount >= $txn['max_executions']) ? 0 : 1;

        $this->db->update('recurring_transactions', [
            'last_execution_date' => date('Y-m-d'),
            'next_execution_date' => $nextDate,
            'execution_count' => $newCount,
            'is_active' => $isActive,
        ], 'id = ?', [$id]);

        return $jeId;
    }

    public function processAllDue(int $userId): array
    {
        $due = $this->getDueTransactions();
        $executed = 0;
        $errors = [];
        foreach ($due as $txn) {
            try {
                $this->execute((int)$txn['id'], $userId);
                $executed++;
            } catch (Exception $e) {
                $errors[] = "ID {$txn['id']}: " . $e->getMessage();
            }
        }
        return ['due' => count($due), 'executed' => $executed, 'errors' => $errors];
    }

    public function getStats(): array
    {
        $tid = $this->tenantId();
        $tf = $tid > 1 ? " AND tenant_id = ?" : "";
        $tp = $tid > 1 ? [$tid] : [];
        return [
            'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM recurring_transactions WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'active' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM recurring_transactions WHERE is_active=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'inactive' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM recurring_transactions WHERE is_active=0" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'due' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM recurring_transactions WHERE is_active=1 AND next_execution_date <= CURDATE() AND (end_date IS NULL OR end_date >= CURDATE())" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
            'total_amount' => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount),0) FROM recurring_transactions WHERE is_active=1" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tp),
        ];
    }

    private function getAccountForType(string $type): int
    {
        $map = ['emi' => 9, 'rent' => 3, 'subscription' => 3, 'salary' => 3, 'vendor' => 3, 'utility' => 3, 'other' => 3];
        $tid = $this->tenantId();
        $coa = $this->db->fetchOne("SELECT id FROM chart_of_accounts WHERE account_type = 'expense' AND is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY id LIMIT 1", $tid > 1 ? [$tid] : []);
        return $coa ? (int)$coa['id'] : ($map[$type] ?? 3);
    }

    private function getCounterAccountForType(string $type): int
    {
        $map = ['emi' => 9, 'rent' => 9, 'subscription' => 9, 'salary' => 3, 'vendor' => 9, 'utility' => 9, 'other' => 9];
        $tid = $this->tenantId();
        $coa = $this->db->fetchOne("SELECT id FROM chart_of_accounts WHERE account_type = 'liability' AND is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY id LIMIT 1", $tid > 1 ? [$tid] : []);
        if (!$coa) {
            $coa = $this->db->fetchOne("SELECT id FROM chart_of_accounts WHERE account_type = 'asset' AND is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY id LIMIT 1", $tid > 1 ? [$tid] : []);
        }
        return $coa ? (int)$coa['id'] : ($map[$type] ?? 9);
    }

    private function calculateNextDate(string $current, string $frequency): string
    {
        $dt = new \DateTime($current);
        switch ($frequency) {
            case 'weekly': $dt->modify('+1 week'); break;
            case 'biweekly': $dt->modify('+2 weeks'); break;
            case 'monthly': $dt->modify('+1 month'); break;
            case 'quarterly': $dt->modify('+3 months'); break;
            case 'yearly': $dt->modify('+1 year'); break;
            default: $dt->modify('+1 month');
        }
        return $dt->format('Y-m-d');
    }

    public function getTransactionTypes(): array
    {
        return ['emi','rent','subscription','salary','vendor','utility','other'];
    }

    public function getFrequencies(): array
    {
        return ['weekly','biweekly','monthly','quarterly','yearly'];
    }

    public function getUsers(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll("SELECT id, name FROM users WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY name LIMIT 100", $tid > 1 ? [$tid] : []) ?: [];
    }

    public function getVendors(): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll("SELECT id, name FROM vendors WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY name LIMIT 100", $tid > 1 ? [$tid] : []) ?: [];
    }
}
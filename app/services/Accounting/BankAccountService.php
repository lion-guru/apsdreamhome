<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Bank Account Service
 * Handles bank accounts master, balances, and basic operations
 */
class BankAccountService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Create a new bank account
     */
    public function createBankAccount(array $data): int
    {
        $payload = [
            'account_name'         => trim($data['account_name'] ?? ''),
            'account_number'       => trim($data['account_number'] ?? ''),
            'ifsc_code'            => strtoupper(trim($data['ifsc_code'] ?? '')),
            'bank_name'            => trim($data['bank_name'] ?? ''),
            'branch'               => $data['branch'] ?? null,
            'account_type'         => $data['account_type'] ?? 'current',
            'opening_balance'      => (float)($data['opening_balance'] ?? 0),
            'current_balance'      => (float)($data['opening_balance'] ?? 0),
            'is_escrow'            => !empty($data['is_escrow']) ? 1 : 0,
            'rera_project_id'      => !empty($data['rera_project_id']) ? (int)$data['rera_project_id'] : null,
            'gst_registered'       => !empty($data['gst_registered']) ? 1 : 0,
            'signatory_name'       => $data['signatory_name'] ?? null,
            'signatory_pan'        => strtoupper($data['signatory_pan'] ?? ''),
            'cancelled_cheque_path'=> $data['cancelled_cheque_path'] ?? null,
            'active'               => isset($data['active']) ? (int)$data['active'] : 1,
            'tenant_id'            => TenantContext::getId(),
        ];
        $this->db->insert('bank_accounts_master', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function listBankAccounts(bool $activeOnly = true): array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM bank_accounts_master WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "") . ($activeOnly ? " AND active = 1" : "") . " ORDER BY account_name";
        return $this->db->fetchAll($sql, $tid > 1 ? [$tid] : []) ?: [];
    }

    public function getBankBalance(int $bankAccountId, ?string $asOfDate = null): float
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $tid = TenantContext::getId();

        $bank = $this->db->fetchOne("SELECT current_balance FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$bankAccountId, $tid] : [$bankAccountId]);
        if (!$bank) {
            return 0.0;
        }

        return (float)$bank['current_balance'];
    }

    public function getBankAccount(int $id): ?array
    {
        $tid = TenantContext::getId();
        $sql = "SELECT * FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->fetchOne($sql, $tid > 1 ? [$id, $tid] : [$id]) ?: null;
    }

    public function updateBankAccount(int $id, array $data): bool
    {
        $tid = TenantContext::getId();
        $allowedFields = ['account_name', 'account_number', 'ifsc_code', 'bank_name', 'branch', 'account_type', 'branch', 'is_escrow', 'rera_project_id', 'gst_registered', 'signatory_name', 'signatory_pan', 'cancelled_cheque_path', 'active'];
        $updates = [];
        $params = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($updates)) return false;
        $params[] = $id;
        if ($tid > 1) $params[] = $tid;
        $sql = "UPDATE bank_accounts_master SET " . implode(', ', $updates) . " WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, $params);
    }

    public function deleteBankAccount(int $id): bool
    {
        $tid = TenantContext::getId();
        $sql = "DELETE FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, $tid > 1 ? [$id, $tid] : [$id]);
    }

    public function getBankAccounts(bool $activeOnly = true): array
    {
        return $this->listBankAccounts($activeOnly);
    }
}
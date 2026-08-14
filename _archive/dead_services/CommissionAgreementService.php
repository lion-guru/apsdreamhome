<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Services;

use App\Core\Database;
use PDO;

use \App\Traits\ServiceTenantTrait;

class CommissionAgreementService
{
    use \App\Traits\ServiceTenantTrait;

    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function listAgreements(array $filters = []): array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
            $sql = "SELECT a.*, u.name AS user_name, u.email AS user_email
                    FROM mlm_commission_agreements a
                    JOIN users u ON a.user_id = u.id" . ($tid > 1 ? " AND u.tenant_id = ?" : "");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $where = [];
        $params = [];

        if ($tid > 1) {
            $params[] = $tid;
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'a.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY a.created_at DESC';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAgreement(array $data): array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? ", tenant_id" : "";
            $tidParams = $tid > 1 ? [$tid] : [];
            $stmt = $this->conn->prepare(
                'INSERT INTO mlm_commission_agreements (user_id, property_id, commission_rate, flat_amount, valid_from, valid_to, notes' . $tidSql . ')
                 VALUES (?, ?, ?, ?, ?, ?, ?' . ($tid > 1 ? ', ?' : '') . ')'
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $userId = (int) ($data['user_id'] ?? 0);
        $propertyId = isset($data['property_id']) && $data['property_id'] !== '' ? (int) $data['property_id'] : null;
        $commissionRate = isset($data['commission_rate']) && $data['commission_rate'] !== '' ? (float) $data['commission_rate'] : null;
        $flatAmount = isset($data['flat_amount']) && $data['flat_amount'] !== '' ? (float) $data['flat_amount'] : null;
        $validFrom = $data['valid_from'] ?? null;
        $validTo = $data['valid_to'] ?? null;
        $notes = $data['notes'] ?? null;

        $success = $stmt->execute(array_merge([
            $userId,
            $propertyId,
            $commissionRate,
            $flatAmount,
            $validFrom,
            $validTo,
            $notes
        ], $tidParams));

        $id = $this->conn->lastInsertId();

        return ['success' => $success, 'id' => $id];
    }

    public function updateAgreement(int $id, array $data): bool
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->conn->prepare(
                'UPDATE mlm_commission_agreements
                 SET property_id = ?, commission_rate = ?, flat_amount = ?, valid_from = ?, valid_to = ?, notes = ?, updated_at = NOW()
                 WHERE id = ?' . ($tid > 1 ? " AND EXISTS (SELECT 1 FROM users WHERE users.id = mlm_commission_agreements.user_id AND users.tenant_id = ?)" : "")
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $propertyId = isset($data['property_id']) && $data['property_id'] !== '' ? (int) $data['property_id'] : null;
        $commissionRate = isset($data['commission_rate']) && $data['commission_rate'] !== '' ? (float) $data['commission_rate'] : null;
        $flatAmount = isset($data['flat_amount']) && $data['flat_amount'] !== '' ? (float) $data['flat_amount'] : null;
        $validFrom = $data['valid_from'] ?? null;
        $validTo = $data['valid_to'] ?? null;
        $notes = $data['notes'] ?? null;

        $params = [
            $propertyId,
            $commissionRate,
            $flatAmount,
            $validFrom,
            $validTo,
            $notes,
            $id
        ];

        if ($tid > 1) {
            $params[] = $tid;
        }

        return $stmt->execute($params);
    }

    public function getAgreement(int $id): ?array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
            $stmt = $this->conn->prepare("SELECT a.* FROM mlm_commission_agreements a JOIN users u ON a.user_id = u.id WHERE a.id = ?" . ($tid > 1 ? " AND u.tenant_id = ?" : ""));
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteAgreement(int $id): bool
    {
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND EXISTS (SELECT 1 FROM users WHERE users.id = mlm_commission_agreements.user_id AND users.tenant_id = ?)" : "";
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->conn->prepare("DELETE FROM mlm_commission_agreements WHERE id = ?" . $tidSql);
        return $stmt->execute($params);
    }
}?>
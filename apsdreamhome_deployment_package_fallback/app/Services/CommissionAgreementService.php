<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class CommissionAgreementService
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function listAgreements(array $filters = []): array
    {
        $sql = "SELECT a.*, u.name AS user_name, u.email AS user_email
                FROM mlm_commission_agreements a
                JOIN users u ON a.user_id = u.id";
        $where = [];
        $params = [];

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
        $stmt = $this->conn->prepare(
            'INSERT INTO mlm_commission_agreements (user_id, property_id, commission_rate, flat_amount, valid_from, valid_to, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $userId = (int) ($data['user_id'] ?? 0);
        $propertyId = isset($data['property_id']) && $data['property_id'] !== '' ? (int) $data['property_id'] : null;
        $commissionRate = isset($data['commission_rate']) && $data['commission_rate'] !== '' ? (float) $data['commission_rate'] : null;
        $flatAmount = isset($data['flat_amount']) && $data['flat_amount'] !== '' ? (float) $data['flat_amount'] : null;
        $validFrom = $data['valid_from'] ?? null;
        $validTo = $data['valid_to'] ?? null;
        $notes = $data['notes'] ?? null;

        $success = $stmt->execute([
            $userId,
            $propertyId,
            $commissionRate,
            $flatAmount,
            $validFrom,
            $validTo,
            $notes
        ]);

        $id = $this->conn->lastInsertId();

        return ['success' => $success, 'id' => $id];
    }

    public function updateAgreement(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE mlm_commission_agreements
             SET property_id = ?, commission_rate = ?, flat_amount = ?, valid_from = ?, valid_to = ?, notes = ?, updated_at = NOW()
             WHERE id = ?'
        );

        $propertyId = isset($data['property_id']) && $data['property_id'] !== '' ? (int) $data['property_id'] : null;
        $commissionRate = isset($data['commission_rate']) && $data['commission_rate'] !== '' ? (float) $data['commission_rate'] : null;
        $flatAmount = isset($data['flat_amount']) && $data['flat_amount'] !== '' ? (float) $data['flat_amount'] : null;
        $validFrom = $data['valid_from'] ?? null;
        $validTo = $data['valid_to'] ?? null;
        $notes = $data['notes'] ?? null;

        return $stmt->execute([
            $propertyId,
            $commissionRate,
            $flatAmount,
            $validFrom,
            $validTo,
            $notes,
            $id
        ]);
    }

    public function getAgreement(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM mlm_commission_agreements WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteAgreement(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM mlm_commission_agreements WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

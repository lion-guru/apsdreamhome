<?php
class CommissionAgreementService
{
    private mysqli $conn;

    public function __construct()
    {
        $config = AppConfig::getInstance();
        $this->conn = $config->getDatabaseConnection();
    }

    public function listAgreements(array $filters = []): array
    {
        $sql = "SELECT a.*, u.name AS user_name, u.email AS user_email
                FROM mlm_commission_agreements a
                JOIN users u ON a.user_id = u.id";
        $where = [];
        $types = '';
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'a.user_id = ?';
            $types .= 'i';
            $params[] = (int) $filters['user_id'];
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY a.created_at DESC';

        $stmt = $this->conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
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

        $stmt->bind_param(
            'iidddss',
            $userId,
            $propertyId,
            $commissionRate,
            $flatAmount,
            $validFrom,
            $validTo,
            $notes
        );

        $success = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

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

        $stmt->bind_param(
            'dddddsi',
            $propertyId,
            $commissionRate,
            $flatAmount,
            $validFrom,
            $validTo,
            $notes,
            $id
        );

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function deleteAgreement(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM mlm_commission_agreements WHERE id = ?');
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}

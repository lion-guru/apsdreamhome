<?php
namespace App\Services;

use PDO;

class AddressService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;
            return;
        }
        $this->pdo = $this->resolvePdo();
    }

    public function listForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_primary DESC, id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(int $userId, array $data): array
    {
        $required = ['label','address_line1','city','state','pincode'];
        foreach ($required as $f) {
            if (empty($data[$f])) return ['success' => false, 'error' => "Field '$f' is required"];
        }
        if (!preg_match('/^\d{4,10}$/', preg_replace('/\D/', '', $data['pincode']))) {
            return ['success' => false, 'error' => 'Invalid pincode (4-10 digits)'];
        }

        $isPrimary = !empty($data['is_primary']) ? 1 : 0;
        if ($isPrimary) {
            $this->pdo->prepare("UPDATE user_addresses SET is_primary = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $stmt = $this->pdo->prepare("INSERT INTO user_addresses (user_id, label, address_type, address_line1, address_line2, city, state, pincode, country, phone, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            trim($data['label']),
            $data['address_type'] ?? 'home',
            trim($data['address_line1']),
            $data['address_line2'] ?? null,
            trim($data['city']),
            trim($data['state']),
            preg_replace('/\D/', '', $data['pincode']),
            $data['country'] ?? 'India',
            $data['phone'] ?? null,
            $isPrimary,
        ]);
        return ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }

    public function update(int $id, int $userId, array $data): array
    {
        $existing = $this->get($id, $userId);
        if (!$existing) return ['success' => false, 'error' => 'Address not found'];

        if (!empty($data['is_primary'])) {
            $this->pdo->prepare("UPDATE user_addresses SET is_primary = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $stmt = $this->pdo->prepare("UPDATE user_addresses SET label=?, address_type=?, address_line1=?, address_line2=?, city=?, state=?, pincode=?, country=?, phone=?, is_primary=? WHERE id=? AND user_id=?");
        $stmt->execute([
            trim($data['label'] ?? $existing['label']),
            $data['address_type'] ?? $existing['address_type'],
            trim($data['address_line1'] ?? $existing['address_line1']),
            $data['address_line2'] ?? $existing['address_line2'],
            trim($data['city'] ?? $existing['city']),
            trim($data['state'] ?? $existing['state']),
            preg_replace('/\D/', '', $data['pincode'] ?? $existing['pincode']),
            $data['country'] ?? $existing['country'] ?? 'India',
            $data['phone'] ?? $existing['phone'],
            !empty($data['is_primary']) ? 1 : 0,
            $id,
            $userId,
        ]);
        return ['success' => true];
    }

    public function delete(int $id, int $userId): array
    {
        $stmt = $this->pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function setPrimary(int $id, int $userId): array
    {
        $this->pdo->prepare("UPDATE user_addresses SET is_primary = 0 WHERE user_id = ?")->execute([$userId]);
        $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_primary = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function lookupByPincode(string $pincode): ?array
    {
        $clean = preg_replace('/\D/', '', $pincode);
        if (strlen($clean) < 4) return null;
        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE pincode = ? AND city != '' AND state != '' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$clean]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return [
            'city' => $row['city'],
            'state' => $row['state'],
            'country' => $row['country'],
            'pincode' => $row['pincode'],
        ];
    }

    private function resolvePdo(): PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }
}

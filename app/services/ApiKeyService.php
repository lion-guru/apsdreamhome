<?php
namespace App\Services;

use PDO;

class ApiKeyService
{
    private $db;
    private $pdo;
    private $defaultScopes = ['read:leads', 'read:properties', 'read:bookings'];
    private $defaultRateLimit = 60;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    public function create(string $name, ?int $userId = null, array $scopes = [], int $rateLimit = 60, ?string $expiresAt = null): array
    {
        $apiKey = 'apk_' . bin2hex(random_bytes(16));
        $apiSecret = 'aps_' . bin2hex(random_bytes(32));
        $secretHash = password_hash($apiSecret, PASSWORD_BCRYPT);

        $st = $this->db->prepare("INSERT INTO api_keys (name, api_key, api_secret_hash, scopes, user_id, is_active, rate_limit_per_minute, expires_at) VALUES (:n, :k, :h, :s, :u, 1, :r, :e)");
        $st->execute([
            ':n' => $name,
            ':k' => $apiKey,
            ':h' => $secretHash,
            ':s' => implode(',', $scopes ?: $this->defaultScopes),
            ':u' => $userId,
            ':r' => $rateLimit,
            ':e' => $expiresAt
        ]);

        return [
            'id' => (int)$this->db->lastInsertId(),
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'name' => $name,
            'scopes' => $scopes ?: $this->defaultScopes,
            'rate_limit' => $rateLimit,
            'expires_at' => $expiresAt,
            'warning' => 'Save the api_secret now. It will not be shown again.'
        ];
    }

    public function list(int $userId = 0, bool $activeOnly = false): array
    {
        $sql = "SELECT id, name, api_key, scopes, user_id, is_active, rate_limit_per_minute, last_used_at, expires_at, created_at FROM api_keys";
        $params = [];
        $where = [];
        if ($userId) { $where[] = "user_id = :u"; $params[':u'] = $userId; }
        if ($activeOnly) { $where[] = "is_active = 1"; }
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY created_at DESC";
        try {
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    public function revoke(int $id): bool
    {
        try {
            $st = $this->db->prepare("UPDATE api_keys SET is_active = 0 WHERE id = :id");
            $st->execute([':id' => $id]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function activate(int $id): bool
    {
        try {
            $st = $this->db->prepare("UPDATE api_keys SET is_active = 1 WHERE id = :id");
            $st->execute([':id' => $id]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function delete(int $id): bool
    {
        try {
            $st = $this->db->prepare("DELETE FROM api_keys WHERE id = :id");
            $st->execute([':id' => $id]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function verify(string $apiKey, string $apiSecret): ?array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM api_keys WHERE api_key = :k AND is_active = 1");
            $st->execute([':k' => $apiKey]);
            $key = $st->fetch(PDO::FETCH_ASSOC);
            if (!$key) return null;
            if ($key['expires_at'] && strtotime($key['expires_at']) < time()) return null;
            if (!password_verify($apiSecret, $key['api_secret_hash'])) return null;

            $this->db->prepare("UPDATE api_keys SET last_used_at = NOW() WHERE id = :id")->execute([':id' => $key['id']]);
            return $key;
        } catch (\Throwable $e) { return null; }
    }

    public function getStats(): array
    {
        $stats = ['total' => 0, 'active' => 0, 'revoked' => 0, 'used_today' => 0];
        try {
            $stats['total'] = (int)$this->db->query("SELECT COUNT(*) FROM api_keys")->fetchColumn();
            $stats['active'] = (int)$this->db->query("SELECT COUNT(*) FROM api_keys WHERE is_active = 1")->fetchColumn();
            $stats['revoked'] = $stats['total'] - $stats['active'];
            $stats['used_today'] = (int)$this->db->query("SELECT COUNT(*) FROM api_keys WHERE last_used_at >= CURDATE()")->fetchColumn();
        } catch (\Throwable $e) {}
        return $stats;
    }
}

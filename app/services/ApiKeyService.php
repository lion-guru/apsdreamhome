<?php
namespace App\Services;

use PDO;

class ApiKeyService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    public function create(string $name, string $service = '', string $type = 'api_key', string $description = '', ?string $keyValue = null): array
    {
        $actualKey = $keyValue ?: 'apk_' . bin2hex(random_bytes(16));

        $insertData = $this->tenantInsertData();
        $extraCols = $insertData ? ', tenant_id' : '';
        $extraVals = $insertData ? ', ?' : '';
        $st = $this->db->prepare("INSERT INTO api_keys (key_name, key_value, key_type, service_name, description, is_active{$extraCols}) VALUES (:n, :v, :t, :s, :d, 1{$extraVals})");
        $st->execute(array_merge([
            ':n' => $name,
            ':v' => $actualKey,
            ':t' => $type,
            ':s' => $service,
            ':d' => $description,
        ], array_values($insertData)));

        return [
            'id' => (int)$this->db->lastInsertId(),
            'key_name' => $name,
            'key_value' => $actualKey,
            'key_type' => $type,
            'service_name' => $service,
            'description' => $description,
            'warning' => $keyValue ? null : 'Save this key. It will not be fully shown again.'
        ];
    }

    public function list(bool $activeOnly = false): array
    {
        $sql = "SELECT id, key_name, key_value, key_type, service_name, description, is_active, last_used_at, usage_count, created_at, updated_at FROM api_keys WHERE 1=1";
        $params = [];
        if ($activeOnly) { $sql .= " AND is_active = 1"; }
        $sql .= $this->tenantSql() . " ORDER BY created_at DESC";
        try {
            $st = $this->db->prepare($sql);
            $st->execute($params);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $row['key_value_masked'] = substr($row['key_value'], 0, 8) . '...' . substr($row['key_value'], -4);
            }
            return $rows;
        } catch (\Throwable $e) { return []; }
    }

    public function revoke(int $id): bool
    {
        try {
            $st = $this->db->prepare("UPDATE api_keys SET is_active = 0, updated_at = NOW() WHERE id = :id" . $this->tenantSql());
            $st->execute([':id' => $id]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function activate(int $id): bool
    {
        try {
            $st = $this->db->prepare("UPDATE api_keys SET is_active = 1, updated_at = NOW() WHERE id = :id" . $this->tenantSql());
            $st->execute([':id' => $id]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function delete(int $id): bool
    {
        try {
            $st = $this->db->prepare("DELETE FROM api_keys WHERE id = :id" . $this->tenantSql());
            $st->execute([':id' => $id]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function verify(string $keyValue): ?array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM api_keys WHERE key_value = :v AND is_active = 1" . $this->tenantSql());
            $st->execute([':v' => $keyValue]);
            $key = $st->fetch(PDO::FETCH_ASSOC);
            if (!$key) return null;
            $this->db->prepare("UPDATE api_keys SET last_used_at = NOW(), usage_count = usage_count + 1 WHERE id = :id" . $this->tenantSql())->execute([':id' => $key['id']]);
            return $key;
        } catch (\Throwable $e) { return null; }
    }

    public function getStats(): array
    {
        $stats = ['total' => 0, 'active' => 0, 'revoked' => 0, 'used_today' => 0];
        try {
             $stats['total'] = (int)$this->db->query("SELECT COUNT(*) FROM api_keys" . $this->tenantSql())->fetchColumn();
            $stats['active'] = (int)$this->db->query("SELECT COUNT(*) FROM api_keys WHERE is_active = 1" . $this->tenantSql())->fetchColumn();
            $stats['revoked'] = $stats['total'] - $stats['active'];
            $stats['used_today'] = (int)$this->db->query("SELECT COUNT(*) FROM api_keys WHERE last_used_at >= CURDATE()" . $this->tenantSql())->fetchColumn();
        } catch (\Throwable $e) { error_log($e->getMessage()); }
        return $stats;
    }
}

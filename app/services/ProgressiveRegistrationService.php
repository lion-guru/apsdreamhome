<?php
namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

/**
 * ProgressiveRegistrationService - multi-step user registration with abandoned cart capture
 */
class ProgressiveRegistrationService
{
    use ServiceTenantTrait;
    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function startStep(array $data): array
    {
        $token = bin2hex(random_bytes(16));
        $sessionId = session_id() ?: bin2hex(random_bytes(8));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $step = (int)($data['step'] ?? 1);
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);

        $tid = $this->tenantId();
        $sql = "INSERT INTO incomplete_registrations (token, session_id, step, payload, ip_address, user_agent, expires_at" . ($tid > 1 ? ", tenant_id" : "") . ")
                VALUES (:t, :s, :step, :p, :ip, :ua, DATE_ADD(NOW(), INTERVAL 7 DAY)" . ($tid > 1 ? ", ?" : "") . ")
                ON DUPLICATE KEY UPDATE step = GREATEST(step, VALUES(step)), payload = VALUES(payload), updated_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 7 DAY)";
        $st = $this->db->prepare($sql);
        $params = [':t' => $token, ':s' => $sessionId, ':step' => $step, ':p' => $payload, ':ip' => $ip, ':ua' => $ua];
        if ($tid > 1) $params[':tid'] = $tid;
        $st->execute($params);
        $id = (int)$this->db->lastInsertId();
        return ['id' => $id, 'token' => $token, 'step' => $step];
    }

    public function progress(string $token, int $newStep, array $extra = []): array
    {
        $row = $this->get($token);
        if (!$row) return ['error' => 'Token not found or expired'];

        $payload = json_decode($row['payload'] ?? '{}', true) ?: [];
        $payload = array_merge($payload, $extra);
        $newStep = max($newStep, (int)$row['step']);

        $st = $this->db->prepare("UPDATE incomplete_registrations SET step = :s, payload = :p, updated_at = NOW() WHERE token = :t {$this->tenantSql()}");
        $st->execute([':s' => $newStep, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':t' => $token]);

        return ['ok' => true, 'step' => $newStep, 'payload' => $payload];
    }

    public function complete(string $token, array $userData): array
    {
        $row = $this->get($token);
        if (!$row) return ['error' => 'Token not found'];

        $payload = json_decode($row['payload'] ?? '{}', true) ?: [];
        $payload = array_merge($payload, $userData);

        $st = $this->db->prepare("UPDATE incomplete_registrations SET step = 99, payload = :p, completed = 1, completed_at = NOW() WHERE token = :t {$this->tenantSql()}");
        $st->execute([':p' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':t' => $token]);

        $tid = $this->tenantId();
        $cols = "token, user_data, source, ip_address";
        $vals = ":t, :u, :src, :ip";
        $params = [':t' => $token, ':u' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':src' => $payload['source'] ?? 'web', ':ip' => $_SERVER['REMOTE_ADDR'] ?? ''];
        if ($tid > 1) { $cols .= ", tenant_id"; $vals .= ", :tid"; $params[':tid'] = $tid; }
        $st = $this->db->prepare("INSERT INTO progressive_registrations ($cols) VALUES ($vals)");
        $st->execute($params);

        return ['ok' => true, 'data' => $payload];
    }

    public function get(string $token): ?array
    {
        $st = $this->db->prepare("SELECT * FROM incomplete_registrations WHERE token = :t AND expires_at > NOW() AND completed = 0 {$this->tenantSql()}");
        $st->execute([':t' => $token]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function listIncomplete(int $limit = 50): array
    {
        $st = $this->db->prepare("SELECT * FROM incomplete_registrations WHERE recovered_at IS NULL {$this->tenantSql()} ORDER BY last_activity_at DESC LIMIT :lim");
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function abandonmentStats(int $days = 30): array
    {
        $st = $this->db->prepare("SELECT current_step AS step, COUNT(*) AS count, AVG(progress_percent) AS avg_step FROM incomplete_registrations WHERE recovered_at IS NULL AND last_activity_at > DATE_SUB(NOW(), INTERVAL :d DAY) {$this->tenantSql()} GROUP BY current_step");
        $st->execute([':d' => $days]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}

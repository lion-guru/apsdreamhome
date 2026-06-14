<?php
namespace App\Services;

use PDO;

/**
 * SecurityService - 2FA, password reset, IP blocking, failed login tracking
 */
class SecurityService
{
    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function generate2FAToken(int $userId, string $method = 'totp'): array
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 600);
        $st = $this->db->prepare("INSERT INTO two_factor_tokens (user_id, code, method, expires_at, created_at) VALUES (:u, :c, :m, :e, NOW())");
        $st->execute([':u' => $userId, ':c' => $code, ':m' => $method, ':e' => $expires]);
        return ['ok' => true, 'code' => $code, 'expires_at' => $expires, 'id' => (int)$this->db->lastInsertId()];
    }

    public function verify2FA(int $userId, string $code): array
    {
        $st = $this->db->prepare("SELECT * FROM two_factor_tokens WHERE user_id = :u AND code = :c AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $st->execute([':u' => $userId, ':c' => $code]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return ['ok' => false, 'error' => 'Invalid or expired code'];

        $st2 = $this->db->prepare("UPDATE two_factor_tokens SET used = 1, used_at = NOW() WHERE id = :id");
        $st2->execute([':id' => $row['id']]);
        return ['ok' => true];
    }

    public function generatePasswordReset(int $userId): array
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $st = $this->db->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) VALUES (:u, :t, :e, NOW())");
        $st->execute([':u' => $userId, ':t' => $token, ':e' => $expires]);
        return ['ok' => true, 'token' => $token, 'expires_at' => $expires, 'id' => (int)$this->db->lastInsertId()];
    }

    public function verifyPasswordReset(string $token): ?array
    {
        $st = $this->db->prepare("SELECT * FROM password_reset_tokens WHERE token = :t AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $st->execute([':t' => $token]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function usePasswordReset(string $token, string $newPassword): array
    {
        $row = $this->verifyPasswordReset($token);
        if (!$row) return ['error' => 'Invalid or expired token'];

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $st = $this->db->prepare("UPDATE users SET password = :p, updated_at = NOW() WHERE id = :u");
        $st->execute([':p' => $hash, ':u' => $row['user_id']]);

        $st2 = $this->db->prepare("UPDATE password_reset_tokens SET used = 1, used_at = NOW() WHERE id = :id");
        $st2->execute([':id' => $row['id']]);

        $st3 = $this->db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = :u AND id != :id");
        $st3->execute([':u' => $row['user_id'], ':id' => $row['id']]);

        return ['ok' => true];
    }

    public function recordFailedLogin(string $email, string $ip, string $reason = 'invalid_password'): array
    {
        $st = $this->db->prepare("INSERT INTO failed_login_attempts (email, ip_address, reason, attempt_at) VALUES (:e, :ip, :r, NOW())");
        $st->execute([':e' => $email, ':ip' => $ip, ':r' => $reason]);

        $count = $this->getRecentFailedAttempts($email, 15);
        if ($count >= 5) {
            $this->blockIp($ip, 'auto', 'Too many failed login attempts', 60);
        }
        return ['ok' => true, 'attempts' => $count, 'shouldBlock' => $count >= 5];
    }

    public function clearFailedLogins(string $email, string $ip): void
    {
        $st = $this->db->prepare("DELETE FROM failed_login_attempts WHERE email = :e OR ip_address = :ip");
        $st->execute([':e' => $email, ':ip' => $ip]);
    }

    public function getRecentFailedAttempts(string $email, int $minutes = 15): int
    {
        $st = $this->db->prepare("SELECT COUNT(*) as c FROM failed_login_attempts WHERE (email = :e OR ip_address = :ip2) AND attempt_at > DATE_SUB(NOW(), INTERVAL :m MINUTE)");
        $st->execute([':e' => $email, ':ip2' => $email, ':m' => $minutes]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return (int)($r['c'] ?? 0);
    }

    public function blockIp(string $ip, string $type = 'manual', string $reason = '', int $durationMinutes = 60): array
    {
        $expires = $durationMinutes > 0 ? date('Y-m-d H:i:s', time() + $durationMinutes * 60) : null;
        $st = $this->db->prepare("INSERT INTO blocked_ips (ip_address, block_type, reason, expires_at, created_at) VALUES (:ip, :t, :r, :e, NOW())");
        $st->execute([':ip' => $ip, ':t' => $type, ':r' => $reason, ':e' => $expires]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function unblockIp(string $ip): array
    {
        $st = $this->db->prepare("UPDATE blocked_ips SET expires_at = NOW() WHERE ip_address = :ip AND (expires_at IS NULL OR expires_at > NOW())");
        $st->execute([':ip' => $ip]);
        return ['ok' => true];
    }

    public function isBlocked(string $ip): bool
    {
        $st = $this->db->prepare("SELECT COUNT(*) as c FROM blocked_ips WHERE ip_address = :ip AND (expires_at IS NULL OR expires_at > NOW())");
        $st->execute([':ip' => $ip]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return ((int)($r['c'] ?? 0)) > 0;
    }

    public function listBlocked(int $limit = 100): array
    {
        $st = $this->db->prepare("SELECT * FROM blocked_ips WHERE expires_at IS NULL OR expires_at > NOW() ORDER BY created_at DESC LIMIT :lim");
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFailedAttempts(string $email = '', int $hours = 24): array
    {
        $sql = "SELECT email, ip_address, reason, attempt_at FROM failed_login_attempts WHERE attempt_at > DATE_SUB(NOW(), INTERVAL :h HOUR)";
        $params = [':h' => $hours];
        if ($email) { $sql .= " AND email = :e"; $params[':e'] = $email; }
        $sql .= " ORDER BY attempt_at DESC LIMIT 100";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}

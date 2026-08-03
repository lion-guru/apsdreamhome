<?php
namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

class TotpService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;
    private $algorithm = 'sha1';
    private $digits = 6;
    private $period = 30;
    private $issuer = 'APS Dream Home';

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    public function generateSecret(int $length = 20): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    public function base32Decode(string $secret): string
    {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper($secret);
        $base32 = rtrim($base32, '=');
        $binary = '';
        foreach (str_split($base32) as $c) {
            $binary .= str_pad(decbin(strpos($base32chars, $c)), 5, '0', STR_PAD_LEFT);
        }
        $output = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $output .= chr(bindec($byte));
            }
        }
        return $output;
    }

    public function getOtp(string $secret, ?int $timeSlice = null): string
    {
        $timeSlice = $timeSlice ?? floor(time() / $this->period);
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac($this->algorithm, $time, $secretKey, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % (10 ** $this->digits);
        return str_pad((string)$code, $this->digits, '0', STR_PAD_LEFT);
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $timeSlice = floor(time() / $this->period);
        for ($i = -$window; $i <= $window; $i++) {
            $otp = $this->getOtp($secret, $timeSlice + $i);
            if (hash_equals($otp, $code)) return true;
        }
        return false;
    }

    public function provisioningUri(string $secret, string $userEmail): string
    {
        return 'otpauth://totp/' . rawurlencode($this->issuer) . ':' . rawurlencode($userEmail) .
               '?secret=' . $secret .
               '&issuer=' . rawurlencode($this->issuer) .
               '&algorithm=' . strtoupper($this->algorithm) .
               '&digits=' . $this->digits .
               '&period=' . $this->period;
    }

    public function qrCodeUrl(string $secret, string $userEmail, int $size = 200): string
    {
        $uri = $this->provisioningUri($secret, $userEmail);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($uri);
    }

    public function enableForUser(int $userId, string $secret): bool
    {
        try {
            $st = $this->db->prepare("UPDATE users SET two_factor_secret = :s, two_factor_enabled = 1 WHERE id = :id" . $this->tenantSql());
            $params = [':s' => $secret, ':id' => $userId];
            if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
            $st->execute($params);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function disableForUser(int $userId): bool
    {
        try {
            $st = $this->db->prepare("UPDATE users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id = :id" . $this->tenantSql());
            $params = [':id' => $userId];
            if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
            $st->execute($params);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) { return false; }
    }

    public function isEnabled(int $userId): bool
    {
        try {
            $st = $this->db->prepare("SELECT two_factor_enabled FROM users WHERE id = :id" . $this->tenantSql());
            $params = [':id' => $userId];
            if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
            $st->execute($params);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            return !empty($r['two_factor_enabled']);
        } catch (\Throwable $e) { return false; }
    }

    public function getSecret(int $userId): ?string
    {
        try {
            $st = $this->db->prepare("SELECT two_factor_secret FROM users WHERE id = :id" . $this->tenantSql());
            $params = [':id' => $userId];
            if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
            $st->execute($params);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            return $r['two_factor_secret'] ?? null;
        } catch (\Throwable $e) { return null; }
    }

    public function generateBackupCodes(int $count = 8, int $length = 8): array
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $codes[] = $code;
        }
        return $codes;
    }

    public function getBackupCodes(int $userId): array
    {
        try {
            $st = $this->db->prepare("SELECT two_factor_backup_codes FROM users WHERE id = :id" . $this->tenantSql());
            $st->execute([':id' => $userId]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            $json = $r['two_factor_backup_codes'] ?? null;
            if (!$json) return [];
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) { return []; }
    }

    public function saveBackupCodes(int $userId, array $codes): bool
    {
        try {
            $st = $this->db->prepare("UPDATE users SET two_factor_backup_codes = :c WHERE id = :id" . $this->tenantSql());
            $st->execute([':c' => json_encode(array_values($codes)), ':id' => $userId]);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    public function verifyBackupCode(int $userId, string $code): bool
    {
        $code = strtoupper(trim($code));
        if ($code === '') return false;

        $codes = $this->getBackupCodes($userId);
        $usedCodes = $this->getUsedBackupCodes($userId);
        if (in_array($code, $usedCodes, true)) return false;

        $remaining = [];
        $matched = false;
        foreach ($codes as $c) {
            if (hash_equals((string)$c, $code)) {
                $matched = true;
            } else {
                $remaining[] = $c;
            }
        }

        if (!$matched) return false;

        $this->saveBackupCodes($userId, $remaining);
        $this->markBackupCodeUsed($userId, $code);
        return true;
    }

    public function getUsedBackupCodes(int $userId): array
    {
        try {
            $st = $this->pdo->prepare("SELECT used_codes FROM two_factor_backup_codes_log WHERE user_id = :id" . $this->tenantSql());
            $st->execute([':id' => $userId]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            $json = $r['used_codes'] ?? null;
            if (!$json) return [];
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) { return []; }
    }

    public function markBackupCodeUsed(int $userId, string $code): bool
    {
        try {
            $existing = $this->getUsedBackupCodes($userId);
            $existing[] = $code;
            $tenantData = $this->tenantInsertData();
            $tenantCols = count($tenantData) > 0 ? ', ' . implode(', ', array_keys($tenantData)) : '';
            $tenantPhs = count($tenantData) > 0 ? ', ' . implode(', ', array_fill(0, count($tenantData), '?')) : '';
            $st = $this->pdo->prepare("
                INSERT INTO two_factor_backup_codes_log (user_id, used_codes, last_used_at{$tenantCols})
                VALUES (:id, :codes, NOW(){$tenantPhs})
                ON DUPLICATE KEY UPDATE used_codes = :codes2, last_used_at = NOW()
            ");
            $params = [
                ':id' => $userId,
                ':codes' => json_encode(array_values($existing)),
                ':codes2' => json_encode(array_values($existing)),
            ];
            foreach ($tenantData as $col => $val) {
                $params[":{$col}"] = $val;
            }
            $st->execute($params);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    public function getBackupCodeStats(int $userId): array
    {
        $all = $this->getBackupCodes($userId);
        $used = $this->getUsedBackupCodes($userId);
        return [
            'total_generated' => 8,
            'remaining' => count($all),
            'used' => count($used),
        ];
    }
}

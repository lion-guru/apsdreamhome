<?php
namespace App\Services\Auth;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * Two-Factor Authentication Service (TOTP)
 * Supports Google Authenticator, Authy, Microsoft Authenticator
 */
class TwoFactorService
{
    private $db;
    private $issuer = 'APS Dream Home';
    private $window = 1; // Allow 1 window (30s) tolerance
    private $backupCodesCount = 8;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get current tenant ID
     */
    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Generate secret for new 2FA setup
     */
    public function generateSecret(int $userId): array
    {
        $secret = $this->generateBase32Secret(20); // 160 bits
        $accountName = $this->getUserEmail($userId);

        // Generate QR code data URI
        $qrCodeUrl = $this->getQRCodeUrl($accountName, $secret);

        return [
            'secret' => $secret,
            'account' => $accountName,
            'issuer' => $this->issuer,
            'qr_code_url' => $qrCodeUrl,
            'backup_codes' => $this->generateBackupCodes(),
        ];
    }

    /**
     * Verify TOTP code
     */
    public function verifyCode(int $userId, string $code): array
    {
        try {
            $tid = $this->getTenantId();
            $user = $this->db->fetchOne(
                "SELECT two_factor_secret, two_factor_enabled FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$userId, $tid] : [$userId]
            );

            if (!$user || empty($user['two_factor_secret']) || empty($user['two_factor_enabled'])) {
                return ['success' => false, 'message' => '2FA not enabled for this user'];
            }

            $secret = $user['two_factor_secret'];
            $currentTimeSlice = floor(time() / 30);

            // Check current and adjacent windows
            for ($i = -$this->window; $i <= $this->window; $i++) {
                $expectedCode = $this->generateTOTP($secret, $currentTimeSlice + $i);
                if (hash_equals($expectedCode, $code)) {
                    return ['success' => true, 'message' => 'Code verified successfully'];
                }
            }

            // Check backup codes
            if ($this->verifyBackupCode($userId, $code)) {
                return ['success' => true, 'message' => 'Backup code verified successfully', 'backup_code_used' => true];
            }

            return ['success' => false, 'message' => 'Invalid or expired code'];
        } catch (\Exception $e) {
            error_log("TwoFactorService::verifyCode error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Verification failed'];
        }
    }

    /**
     * Enable 2FA for user after verification
     */
    public function enable2FA(int $userId, string $secret): array
    {
        try {
            $tid = $this->getTenantId();
            $backupCodes = $this->generateBackupCodes();
            $hashedBackupCodes = array_map('password_hash', $backupCodes);

            $updateData = [
                'two_factor_secret' => $secret,
                'two_factor_enabled' => 1,
                'two_factor_backup_codes' => json_encode($hashedBackupCodes),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $whereClause = 'id = ?';
            $whereParams = [$userId];
            if ($tid > 1) {
                $whereClause .= ' AND tenant_id = ?';
                $whereParams[] = $tid;
            }

            $updated = $this->db->update('users', $updateData, $whereClause, $whereParams);

            if ($updated) {
                return ['success' => true, 'message' => '2FA enabled successfully', 'backup_codes' => $backupCodes];
            }

            return ['success' => false, 'message' => 'Failed to enable 2FA'];
        } catch (\Exception $e) {
            error_log("TwoFactorService::enable2FA error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to enable 2FA'];
        }
    }

    /**
     * Disable 2FA for user
     */
    public function disable2FA(int $userId): array
    {
        try {
            $tid = $this->getTenantId();

            $updateData = [
                'two_factor_secret' => null,
                'two_factor_enabled' => 0,
                'two_factor_backup_codes' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $whereClause = 'id = ?';
            $whereParams = [$userId];
            if ($tid > 1) {
                $whereClause .= ' AND tenant_id = ?';
                $whereParams[] = $tid;
            }

            $updated = $this->db->update('users', $updateData, $whereClause, $whereParams);

            if ($updated) {
                return ['success' => true, 'message' => '2FA disabled successfully'];
            }

            return ['success' => false, 'message' => 'Failed to disable 2FA'];
        } catch (\Exception $e) {
            error_log("TwoFactorService::disable2FA error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to disable 2FA'];
        }
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(int $userId): array
    {
        try {
            $tid = $this->getTenantId();
            $backupCodes = $this->generateBackupCodes();
            $hashedBackupCodes = array_map('password_hash', $backupCodes);

            $updateData = [
                'two_factor_backup_codes' => json_encode($hashedBackupCodes),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $whereClause = 'id = ?';
            $whereParams = [$userId];
            if ($tid > 1) {
                $whereClause .= ' AND tenant_id = ?';
                $whereParams[] = $tid;
            }

            $updated = $this->db->update('users', $updateData, $whereClause, $whereParams);

            if ($updated) {
                return ['success' => true, 'message' => 'Backup codes regenerated', 'backup_codes' => $backupCodes];
            }

            return ['success' => false, 'message' => 'Failed to regenerate backup codes'];
        } catch (\Exception $e) {
            error_log("TwoFactorService::regenerateBackupCodes error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to regenerate backup codes'];
        }
    }

    /**
     * Check if 2FA is enabled for user
     */
    public function isEnabled(int $userId): bool
    {
        try {
            $tid = $this->getTenantId();
            $user = $this->db->fetchOne(
                "SELECT two_factor_enabled FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$userId, $tid] : [$userId]
            );
            return !empty($user['two_factor_enabled']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get user email for QR code
     */
    private function getUserEmail(int $userId): string
    {
        try {
            $tid = $this->getTenantId();
            $user = $this->db->fetchOne(
                "SELECT email FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$userId, $tid] : [$userId]
            );
            return $user['email'] ?? 'user@example.com';
        } catch (\Exception $e) {
            return 'user@example.com';
        }
    }

    /**
     * Generate base32 secret
     */
    private function generateBase32Secret(int $bytes = 20): string
    {
        $random = random_bytes($bytes);
        return $this->base32Encode($random);
    }

    /**
     * Base32 encoding (RFC 4648)
     */
    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($data); $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $output .= $alphabet[($buffer >> $bitsLeft) & 0x1F];
            }
        }

        if ($bitsLeft > 0) {
            $output .= $alphabet[($buffer << (5 - $bitsLeft)) & 0x1F];
        }

        // Pad to multiple of 8 chars
        while (strlen($output) % 8 !== 0) {
            $output .= '=';
        }

        return $output;
    }

    /**
     * Generate QR code URL for authenticator apps
     */
    private function getQRCodeUrl(string $accountName, string $secret): string
    {
        $params = [
            'otpauth://totp/',
            urlencode($this->issuer . ':' . $accountName),
            '?secret=' . $secret,
            '&issuer=' . urlencode($this->issuer),
            '&algorithm=SHA1',
            '&digits=6',
            '&period=30',
        ];
        return implode('', $params);
    }

    /**
     * Generate TOTP code (RFC 6238)
     */
    private function generateTOTP(string $secret, int $timeSlice): string
    {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;
        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Base32 decode
     */
    private function base32Decode(string $encoded): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $encoded = str_replace('=', '', $encoded);
        $output = '';

        for ($i = 0; $i < strlen($encoded); $i += 8) {
            $chunk = substr($encoded, $i, 8);
            $buffer = 0;
            $bits = 0;

            for ($j = 0; $j < strlen($chunk); $j++) {
                $val = strpos($alphabet, $chunk[$j]);
                if ($val === false) continue;
                $buffer = ($buffer << 5) | $val;
                $bits += 5;
            }

            while ($bits >= 8) {
                $bits -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }

    /**
     * Generate backup codes
     */
    private function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < $this->backupCodesCount; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        }
        return $codes;
    }

    /**
     * Verify backup code
     */
    private function verifyBackupCode(int $userId, string $code): bool
    {
        try {
            $tid = $this->getTenantId();
            $user = $this->db->fetchOne(
                "SELECT two_factor_backup_codes FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$userId, $tid] : [$userId]
            );

            if (!$user || empty($user['two_factor_backup_codes'])) {
                return false;
            }

            $hashedCodes = json_decode($user['two_factor_backup_codes'], true);
            if (!is_array($hashedCodes)) return false;

            $code = strtoupper(trim($code));

            foreach ($hashedCodes as $index => $hashedCode) {
                if (password_verify($code, $hashedCode)) {
                    // Remove used backup code
                    unset($hashedCodes[$index]);
                    $this->db->update('users', [
                        'two_factor_backup_codes' => json_encode(array_values($hashedCodes))
                    ], 'id = ?' . ($tid > 1 ? ' AND tenant_id = ?' : ''), $tid > 1 ? [$userId, $tid] : [$userId]);
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}


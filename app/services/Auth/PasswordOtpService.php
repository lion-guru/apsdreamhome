<?php
namespace App\Services\Auth;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\Communication\EmailSenderService;
use App\Services\NotificationService;

/**
 * Password OTP Service
 * - Generates 6-digit numeric OTPs
 * - Stores in otp_verifications table
 * - Sends via email (with DB-log fallback for testing)
 * - Verifies with rate limit and expiry
 */
class PasswordOtpService
{
    private $db;
    private $email;
    private $logger;
    private $maxAttempts = 5;
    private $otpExpiryMinutes = 10;
    private $maxPerHour = 5; // Rate limit: max 5 OTPs per email per hour

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->email = new EmailSenderService();
    }

    /**
     * Get current tenant ID for multi-tenant scoping.
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
     * Send OTP for password reset (forgot password flow)
     * @param string $identifier email or phone
     * @param string $purpose forgot_password | change_password | email_verification
     * @return array
     */
    public function sendOtp(string $identifier, string $purpose = 'password_reset'): array
    {
        try {
            // Normalize identifier (always lowercase email for our case)
            $identifier = strtolower(trim($identifier));

            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email address'];
            }

            // Rate limit: max 5 OTPs per email per hour
            $tid = $this->getTenantId();
            $recent = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM otp_verifications
                 WHERE identifier = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$identifier, $tid] : [$identifier]
            );
            if (($recent['cnt'] ?? 0) >= $this->maxPerHour) {
                return ['success' => false, 'message' => 'Too many OTP requests. Please try again after 1 hour.'];
            }

            // For forgot password, verify user exists
            if ($purpose === 'password_reset' || $purpose === 'change_password') {
                $tid = $this->getTenantId();
                $user = $this->db->fetchOne(
                    "SELECT id, name, email FROM users WHERE email = ? AND deleted_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                    $tid > 1 ? [$identifier, $tid] : [$identifier]
                );
                if (!$user) {
                    // Don't reveal if email exists; just say "if account exists, OTP sent"
                    return [
                        'success' => true,
                        'message' => 'If an account exists with this email, an OTP has been sent.',
                        'silent' => true
                    ];
                }
                $name = $user['name'];
            } else {
                $name = 'User';
            }

            // Generate 6-digit OTP
            $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->otpExpiryMinutes} minutes"));

            // Mark previous OTPs as used (single-active-OTP per identifier+purpose)
            $this->db->query(
                "UPDATE otp_verifications SET used_at = NOW()
                 WHERE identifier = ? AND purpose = ? AND used_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$identifier, $purpose, $tid] : [$identifier, $purpose]
            );

            // Insert new OTP
            $otpData = [
                'identifier' => $identifier,
                'otp_code' => $otp,
                'type' => 'email',
                'purpose' => $purpose,
                'expires_at' => $expiresAt,
                'attempts' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if ($tid > 1) $otpData['tenant_id'] = $tid;
            $this->db->insert('otp_verifications', $otpData);

            // Send OTP via email
            $sent = $this->sendOtpEmail($identifier, $name, $otp, $purpose);

            // Always log to file (fallback so user can find OTP if email fails)
            $this->logOtpToFile($identifier, $otp, $purpose);

            return [
                'success' => true,
                'message' => 'OTP sent successfully to your email',
                'sent_via_email' => $sent,
                'otp' => getenv('APP_DEBUG') === 'true' ? $otp : null, // Only in debug
                'expires_in' => $this->otpExpiryMinutes,
            ];
        } catch (\Exception $e) {
            error_log("PasswordOtpService::sendOtp error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send OTP. Please try again.'];
        }
    }

    /**
     * Verify OTP for an identifier
     * @param string $identifier
     * @param string $otp
     * @param string $purpose
     * @return array
     */
    public function verifyOtp(string $identifier, string $otp, string $purpose = 'password_reset'): array
    {
        try {
            $identifier = strtolower(trim($identifier));
            $otp = trim($otp);

            if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
                return ['success' => false, 'message' => 'Please enter a valid 6-digit OTP'];
            }

            // Find active OTP
            $tid = $this->getTenantId();
            $record = $this->db->fetchOne(
                "SELECT * FROM otp_verifications
                 WHERE identifier = ? AND purpose = ? AND used_at IS NULL
                   AND expires_at >= NOW()" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
                 ORDER BY created_at DESC LIMIT 1",
                $tid > 1 ? [$identifier, $purpose, $tid] : [$identifier, $purpose]
            );

            if (!$record) {
                return ['success' => false, 'message' => 'OTP expired or invalid. Please request a new one.'];
            }

            // Check attempts
            if ((int)$record['attempts'] >= $this->maxAttempts) {
                return ['success' => false, 'message' => 'Too many wrong attempts. Please request a new OTP.'];
            }

            // Verify OTP (constant-time compare)
            if (!hash_equals($record['otp_code'], $otp)) {
                // Increment attempts
                $this->db->query(
                    "UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                    $tid > 1 ? [$record['id'], $tid] : [$record['id']]
                );
                $remaining = $this->maxAttempts - ((int)$record['attempts'] + 1);
                return [
                    'success' => false,
                    'message' => "Invalid OTP. {$remaining} attempt(s) remaining."
                ];
            }

            // Mark OTP as used
            $this->db->query(
                "UPDATE otp_verifications SET used_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$record['id'], $tid] : [$record['id']]
            );

            // Generate a short-lived reset token (valid 30 min) so user can change password
            $resetToken = bin2hex(random_bytes(32));

            // Get user
            $tid = $this->getTenantId();
            $user = $this->db->fetchOne(
                "SELECT id FROM users WHERE email = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$identifier, $tid] : [$identifier]
            );

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Store reset token in users table
            $tid = $this->getTenantId();
            $this->db->query(
                "UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 30 MINUTE), updated_at = NOW()
                 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$resetToken, $user['id'], $tid] : [$resetToken, $user['id']]
            );

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'reset_token' => $resetToken,
                'user_id' => $user['id'],
            ];
        } catch (\Exception $e) {
            error_log("PasswordOtpService::verifyOtp error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Verification failed. Please try again.'];
        }
    }

    /**
     * Reset password using verified token
     */
    public function resetPasswordWithToken(string $token, string $newPassword): array
    {
        try {
            if (empty($token) || strlen($token) < 32) {
                return ['success' => false, 'message' => 'Invalid reset token'];
            }
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters'];
            }

            $tid = $this->getTenantId();
            $user = $this->db->fetchOne(
                "SELECT id, name, email FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() AND deleted_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1",
                $tid > 1 ? [$token, $tid] : [$token]
            );

            if (!$user) {
                return ['success' => false, 'message' => 'Reset token expired or invalid'];
            }

            // Hash new password
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $tid = $this->getTenantId();

            // Update password and clear token
            $this->db->query(
                "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$hash, $user['id'], $tid] : [$hash, $user['id']]
            );

            return [
                'success' => true,
                'message' => 'Password reset successful',
                'user_id' => $user['id'],
            ];
        } catch (\Exception $e) {
            error_log("PasswordOtpService::resetPasswordWithToken error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Password reset failed'];
        }
    }

    /**
     * Send OTP email using EmailSenderService
     */
    private function sendOtpEmail(string $email, string $name, string $otp, string $purpose): bool
    {
        $subjects = [
            'password_reset' => 'Password Reset OTP - APS Dream Home',
            'change_password' => 'Password Change OTP - APS Dream Home',
            'email_verification' => 'Email Verification OTP - APS Dream Home',
        ];
        $subject = $subjects[$purpose] ?? 'Your OTP - APS Dream Home';

        $body = $this->getOtpEmailTemplate($name, $otp, $purpose);

        try {
            return $this->email->send($email, $subject, $body, "Your OTP is: {$otp}");
        } catch (\Exception $e) {
            error_log("PasswordOtpService: email send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Always log OTP to a file as fallback (so user can find it during testing)
     */
    private function logOtpToFile(string $identifier, string $otp, string $purpose): void
    {
        try {
            $logDir = dirname(__DIR__, 3) . '/storage/logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/otp_log.txt';
            $line = sprintf(
                "[%s] %s | purpose=%s | otp=%s | ip=%s\n",
                date('Y-m-d H:i:s'),
                $identifier,
                $purpose,
                $otp,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );
            @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
        // Silently fail
        error_log($e->getMessage());
        }
    }

    private function getOtpEmailTemplate(string $name, string $otp, string $purpose): string
    {
        $purposeText = [
            'password_reset' => 'reset your password',
            'change_password' => 'change your password',
            'email_verification' => 'verify your email',
        ];
        $action = $purposeText[$purpose] ?? 'verify your account';

        $baseUrl = rtrim(BASE_URL, '/');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP - APS Dream Home</title>
</head>
<body class="style-25082">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="style-6542">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0" border="0" class="style-72925">
                    <tr>
                        <td class="style-26748">
                            <h1 class="style-68412">ðŸ"� Verification Code</h1>
                            <p class="style-53513">APS Dream Home - Real Estate</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="style-14825">
                            <p class="style-87071">Hi <strong>{$name}</strong>,</p>
                            <p class="style-76092">Use the following One-Time Password (OTP) to {$action}. This code is valid for <strong>10 minutes</strong>:</p>

                            <div class="style-14957">
                                <p class="style-46196">Your OTP Code</p>
                                <p class="style-77963">{$otp}</p>
                            </div>

                            <div class="style-73851">
                                <p class="style-61628"><strong>âš ï¸� Security Tip:</strong> Never share this code with anyone. APS Dream Home staff will never ask for your OTP.</p>
                            </div>

                            <p class="style-49139">If you didn't request this code, please ignore this email or contact our support team if you have concerns.</p>

                            <hr class="style-69678">

                            <p class="style-71176">This is an automated email from <a href="{$baseUrl}" class="style-82449">APS Dream Home</a>. Please do not reply.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}

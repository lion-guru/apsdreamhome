<?php

namespace App\Services\Legacy;

/**
 * Advanced Password Reset Mechanism
 * Provides secure password reset functionality
 */
class PasswordReset
{
    private const TOKEN_EXPIRY = 3600; // 1 hour
    private const MAX_RESET_ATTEMPTS = 3;

    /**
     * Generate secure password reset token
     * @param int $userId
     * @return string
     */
    public static function generateResetToken($userId)
    {
        // Create cryptographically secure token
        $token = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));

        // Store token details securely
        $hashedToken = hash('sha256', $token);

        try {
            $db = \App\Core\App::database();
            $stmt = $db->prepare("
                INSERT INTO password_reset_tokens
                (user_id, token_hash, created_at, expires_at)
                VALUES (:user_id, :token_hash, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ");
            $stmt->execute([
                'user_id' => $userId,
                'token_hash' => $hashedToken
            ]);

            // Log token generation
            AdminLogger::log('PASSWORD_RESET_TOKEN_GENERATED', [
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);

            return $token;
        } catch (PDOException $e) {
            // Log error
            AdminLogger::logError('PASSWORD_RESET_TOKEN_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => $userId
            ]);

            throw new Exception('Unable to generate reset token');
        }
    }

    /**
     * Validate password reset token
     * @param string $token
     * @return array|false
     */
    public static function validateResetToken($token)
    {
        $hashedToken = hash('sha256', $token);

        try {
            $db = \App\Core\App::database();
            $stmt = $db->prepare("
                SELECT user_id, created_at
                FROM password_reset_tokens
                WHERE token_hash = :token_hash
                AND expires_at > NOW()
                AND used = 0
            ");
            $stmt->execute(['token_hash' => $hashedToken]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenData) {
                // Log invalid token attempt
                AdminLogger::log('INVALID_RESET_TOKEN_ATTEMPT', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return false;
            }

            return $tokenData;
        } catch (PDOException $e) {
            AdminLogger::logError('TOKEN_VALIDATION_ERROR', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process password reset
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public static function processPasswordReset($token, $newPassword)
    {
        // Validate token
        $tokenData = self::validateResetToken($token);
        if (!$tokenData) {
            return false;
        }

        try {
            $db = \App\Core\App::database();

            // Begin transaction
            $db->beginTransaction();

            // Hash new password
            $passwordData = PasswordManager::hashPassword($newPassword);

            // Update user password
            $stmt = $db->prepare("
                UPDATE user
                SET upass = :upass,
                    last_password_change = NOW()
                WHERE uid = :uid
            ");
            $stmt->execute([
                'upass' => $passwordData['hash'],
                'uid' => $tokenData['user_id']
            ]);

            // Mark token as used
            $stmt = $db->prepare("
                UPDATE password_reset_tokens
                SET used = 1, used_at = NOW()
                WHERE token_hash = :token_hash
            ");
            $stmt->execute(['token_hash' => hash('sha256', $token)]);

            // Log successful password reset
            AdminLogger::log('PASSWORD_RESET_SUCCESS', [
                'user_id' => $tokenData['user_id'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);

            // Commit transaction
            $db->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            if (isset($db)) $db->rollBack();

            AdminLogger::logError('PASSWORD_RESET_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => $tokenData['user_id']
            ]);
            return false;
        }
    }

    /**
     * Send password reset email
     * @param string $email
     * @return bool
     */
    public static function sendResetEmail($email)
    {
        try {
            $db = \App\Core\App::database();
            $stmt = $db->prepare("SELECT uid as id FROM user WHERE uemail = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                AdminLogger::log('RESET_EMAIL_NOT_FOUND', [
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return false;
            }

            // Generate reset token
            $resetToken = self::generateResetToken($user['id']);

            // Construct reset link
            $resetLink = "https://yourdomain.com/admin/reset-password.php?token=" . urlencode($resetToken);

            // Send email (use a secure email library like PHPMailer)
            $emailBody = "Click the following link to reset your password:\n\n" . $resetLink;

            // Implement secure email sending
            $emailSent = self::sendSecureEmail($email, 'Password Reset', $emailBody);

            if ($emailSent) {
                AdminLogger::log('RESET_EMAIL_SENT', [
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                return true;
            }

            return false;
        } catch (Exception $e) {
            AdminLogger::logError('RESET_EMAIL_ERROR', [
                'message' => $e->getMessage(),
                'email' => $email
            ]);
            return false;
        }
    }

    /**
     * Send secure email (placeholder - replace with actual implementation)
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return bool
     */
    private static function sendSecureEmail($to, $subject, $body)
    {
        // Implement secure email sending
        // Use libraries like PHPMailer with SMTP encryption
        return true; // Placeholder
    }
}

// Helper function for global use
function send_password_reset($email)
{
    return PasswordReset::sendResetEmail($email);
}

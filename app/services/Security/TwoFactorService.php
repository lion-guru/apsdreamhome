<?php

namespace App\Services\Legacy;

use App\Http\Middleware\AdminLogger;
// NOTE (S448): without this import AdminLogger:: resolved to
// App\Services\Legacy\AdminLogger -> class not found -> fatal.
// Two-Factor Authentication Management System

class TwoFactorManager
{
    // 2FA Methods
    const METHOD_EMAIL = 'email';
    const METHOD_SMS = 'sms';
    const METHOD_AUTHENTICATOR = 'authenticator_app';

    // Configuration
    private $config = [
        'token_length' => 6,
        'token_expiry' => 900, // 15 minutes
        'max_attempts' => 3
    ];

    // Dependencies
    private $db;
    private $email_service;
    private $sms_service;

    public function __construct($db = null, $email_service = null, $sms_service = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->email_service = $email_service;
        $this->sms_service = $sms_service;
    }

    /**
     * Generate Two-Factor Authentication Token
     * @param int $user_id User ID
     * @param string $method Authentication method
     * @param string $user_type User type ('user' or 'admin')
     * @return string|false Generated token or false
     */
    public function generateToken($user_id, $method = self::METHOD_EMAIL, $user_type = 'user')
    {
        try {
            // Validate method
            $this->validateMethod($method);

            // Generate secure token
            $token = $this->generateSecureToken();

            // Store token in database
            $sql = "INSERT INTO two_factor_tokens
                    (user_id, token, method, expires_at)
                    VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))";

            $this->db->execute($sql, [
                $user_id,
                $token,
                $method,
                $this->config['token_expiry']
            ]);

            // Send token via selected method
            $this->sendToken($user_id, $token, $method, $user_type);

            return $token;
        } catch (\Throwable $e) {
            if (\class_exists('AdminLogger')) {
                AdminLogger::logAction("2FA token generation error: " . $e->getMessage(), ['user_id' => $user_id] + ['level' => 'security']);;
            } else {
                \error_log("2FA token generation error: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Validate Two-Factor Authentication Token
     * @param int $user_id User ID
     * @param string $token Token to validate
     * @return bool
     */
    public function validateToken($user_id, $token)
    {
        try {
            // Check token validity
            $sql = "SELECT id, expires_at, used
                    FROM two_factor_tokens
                    WHERE user_id = ? AND token = ? AND expires_at > NOW() AND used = 0";

            $token_data = $this->db->fetch($sql, [$user_id, $token]);

            // Token not found or expired
            if (!$token_data) {
                if (\class_exists('AdminLogger')) {
                    AdminLogger::logAction("Invalid 2FA token attempt", ['user_id' => $user_id] + ['level' => 'security']);;
                }
                return false;
            }

            // Mark token as used
            $sql = "UPDATE two_factor_tokens SET used = 1 WHERE id = ?";
            $this->db->execute($sql, [$token_data['id']]);

            return true;
        } catch (\Throwable $e) {
            if (\class_exists('AdminLogger')) {
                AdminLogger::logAction("2FA token validation error: " . $e->getMessage(), ['user_id' => $user_id] + ['level' => 'security']);;
            } else {
                \error_log("2FA token validation error: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Send token via selected method
     * @param int $user_id User ID
     * @param string $token Token to send
     * @param string $method Send method
     * @param string $user_type User type ('user' or 'admin')
     */
    private function sendToken($user_id, $token, $method, $user_type = 'user')
    {
        // Fetch user details based on type
        if ($user_type === 'admin') {
            $sql = "SELECT email, NULL as phone FROM admin WHERE id = ?";
        } else {
            $sql = "SELECT email, phone FROM users WHERE id = ?";
        }

        $user = $this->db->fetch($sql, [$user_id]);

        if (!$user) return;

        switch ($method) {
            case self::METHOD_EMAIL:
                if (!empty($user['email'])) {
                    $this->sendEmailToken($user['email'], $token);
                }
                break;
            case self::METHOD_SMS:
                if (!empty($user['phone'])) {
                    $this->sendSmsToken($user['phone'], $token);
                }
                break;
            case self::METHOD_AUTHENTICATOR:
                // Placeholder for authenticator app integration
                break;
        }
    }

    /**
     * Send token via email
     * @param string $email Recipient email
     * @param string $token Token to send
     */
    private function sendEmailToken($email, $token)
    {
        if (!$this->email_service) {
            if (\class_exists('AdminLogger')) {
                AdminLogger::logAction("Email service not configured for 2FA", ['email' => $email] + ['level' => 'security']);;
            }
            return;
        }

        $subject = "Your Two-Factor Authentication Token";
        $body = "Your 2FA token is: {$token}. It will expire in 15 minutes.";

        $this->email_service->send($email, $subject, $body);
    }

    /**
     * Send token via SMS
     * @param string $phone Recipient phone number
     * @param string $token Token to send
     */
    private function sendSmsToken($phone, $token)
    {
        if (!$this->sms_service) {
            if (\class_exists('AdminLogger')) {
                AdminLogger::logAction("SMS service not configured for 2FA", ['phone' => $phone] + ['level' => 'security']);;
            }
            return;
        }

        $message = "Your 2FA token is: {$token}. It will expire in 15 minutes.";
        $this->sms_service->send($phone, $message);
    }

    /**
     * Generate secure token
     * @return string Generated token
     */
    private function generateSecureToken()
    {
        // Generate numeric token
        $token = \str_pad(
            \App\Helpers\SecurityHelper::secureRandomInt(0, 10 ** $this->config['token_length'] - 1),
            $this->config['token_length'],
            '0',
            \STR_PAD_LEFT
        );

        return $token;
    }

    /**
     * Validate authentication method
     * @param string $method Method to validate
     * @throws \Exception If method is invalid
     */
    private function validateMethod($method)
    {
        $valid_methods = [
            self::METHOD_EMAIL,
            self::METHOD_SMS,
            self::METHOD_AUTHENTICATOR
        ];

        if (!\in_array($method, $valid_methods)) {
            throw new \Exception("Invalid 2FA method");
        }
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens()
    {
        try {
            $sql = "DELETE FROM two_factor_tokens WHERE expires_at < NOW() OR used = 1";
            $this->db->execute($sql);

            if (\class_exists('AdminLogger')) {
                AdminLogger::logAction("Cleaned up expired 2FA tokens", [] + ['level' => 'info']);;
            }
        } catch (\Throwable $e) {
            if (\class_exists('AdminLogger')) {
                AdminLogger::logAction("Error cleaning up 2FA tokens: " . $e->getMessage(), [] + ['level' => 'error']);;
            }
        }
    }
}

// Helper function for dependency injection
function getTwoFactorManager()
{
    // NOTE (S448): container() is a LARAVEL helper - it does not exist in
    // this codebase -> "Call to undefined function" -> fatal whenever the
    // 2FA service was constructed. Resolve the dependencies directly.
    $db = \App\Core\Database\Database::getInstance();

    $logger = null;
    if (class_exists('App\Core\Logger')) {
        // App\Core\Logger is a class_alias of App\Services\SystemLogger, whose
        // API is instance-based (error()/info()/...) - there is no
        // getInstance(). Build it directly.
        $logger = new \App\Services\SystemLogger();
    }

    $email_service = null;
    if (class_exists('App\Services\EmailService')) {
        $email_service = new \App\Services\EmailService();
    }

    $sms_service = null;
    if (class_exists('App\Services\SmsService')) {
        $sms_service = new \App\Services\SmsService();
    }

    return new TwoFactorManager($db, $logger, $email_service, $sms_service);
}

return getTwoFactorManager();
